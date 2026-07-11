Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Step($Message) {
    Write-Host ""
    Write-Host "==> $Message"
}

function HasCommand($Name) {
    return $null -ne (Get-Command $Name -ErrorAction SilentlyContinue)
}

function Run($Command, $Arguments = @()) {
    & $Command @Arguments
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed: $Command $($Arguments -join ' ')"
    }
}

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

Step "Git status"
Run git @("status", "--short", "--branch")

Step "Required production files"
$RequiredFiles = @(
    "Dockerfile",
    ".dockerignore",
    "railway.json",
    "docker/apache/000-default.conf",
    "docker/entrypoint.sh",
    "docker/predeploy.sh",
    "DEPLOY_RAILWAY.md"
)
foreach ($File in $RequiredFiles) {
    if (-not (Test-Path $File)) {
        throw "Missing required file: $File"
    }
}

Step "No tracked .env"
$TrackedEnv = git ls-files ".env" ".env.*" | Where-Object { $_ -ne ".env.example" }
if ($TrackedEnv) {
    throw "Tracked env file found: $TrackedEnv"
}

Step "Composer validate"
Run composer @("validate", "--strict")

Step "Frontend build"
if (HasCommand "npm.cmd") {
    Run npm.cmd @("run", "build")
} elseif (HasCommand "npm") {
    Run npm @("run", "build")
} else {
    throw "npm is not available."
}
if (-not (Test-Path "public/build/manifest.json")) {
    throw "Missing public/build/manifest.json after npm run build."
}

Step "Laravel diagnostics"
Run php @("artisan", "optimize:clear")
Run php @("artisan", "about")
Run php @("artisan", "route:list")

Step "Health route"
$Routes = php artisan route:list --path=up
$RoutesText = $Routes -join "`n"
if ($RoutesText -notmatch "\bup\b") {
    throw "Health route /up was not found."
}

Step "Unsafe command search"
$SearchRoots = @("Dockerfile", "railway.json", "docker", ".env.example", "composer.json")
$UnsafePatterns = "migrate:fresh|db:wipe|migrate:reset|db:seed"
foreach ($Path in $SearchRoots) {
    if (Test-Path $Path) {
        $Matches = Get-ChildItem $Path -Recurse -File -ErrorAction SilentlyContinue |
            Select-String -Pattern $UnsafePatterns -ErrorAction SilentlyContinue
        if ($Matches) {
            $Matches | ForEach-Object { Write-Host "$($_.Path):$($_.LineNumber):$($_.Line)" }
            throw "Unsafe production command found."
        }
    }
}

$DevServerMatches = Get-ChildItem Dockerfile,railway.json,docker -Recurse -File -ErrorAction SilentlyContinue |
    Select-String -Pattern "php artisan serve|npm run dev" -ErrorAction SilentlyContinue
if ($DevServerMatches) {
    $DevServerMatches | ForEach-Object { Write-Host "$($_.Path):$($_.LineNumber):$($_.Line)" }
    throw "Production startup must not depend on php artisan serve or npm run dev."
}

Step "Static Apache/MPM checks"
$Dockerfile = Get-Content "Dockerfile" -Raw
$ApacheConf = Get-Content "docker/apache/000-default.conf" -Raw
$Entrypoint = Get-Content "docker/entrypoint.sh" -Raw
if ($Dockerfile -notmatch "FROM php:8\.2-apache") { throw "Dockerfile must use php:8.2-apache." }
if ($ApacheConf -notmatch "DocumentRoot /var/www/html/public") { throw "Apache DocumentRoot is not /var/www/html/public." }
if ($Entrypoint -match "mpm_event|mpm_worker") {
    Write-Host "Entrypoint removes worker/event MPM links before validation."
}
if ($Entrypoint -notmatch "mpm_prefork") { throw "Entrypoint does not enforce mpm_prefork." }

if (HasCommand "docker") {
    Step "Docker build"
    Run docker @("build", "--no-cache", "-t", "altokke-original-production", ".")

    Step "Docker image inspect"
    Run docker @("image", "inspect", "altokke-original-production")

    Step "Docker Apache validation"
    Run docker @(
        "run", "--rm",
        "--entrypoint", "sh",
        "altokke-original-production",
        "-lc",
        "php -v; apache2ctl configtest; apache2ctl -M 2>&1 | grep -E 'mpm|php'; find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*.load' -print; grep -Rns 'DocumentRoot' /etc/apache2; test -f /var/www/html/public/build/manifest.json; test `$(find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*.load' | wc -l) -eq 1; test -e /etc/apache2/mods-enabled/mpm_prefork.load"
    )

    Step "Docker runtime /up"
    $ContainerName = "altokke-original-test"
    docker rm -f $ContainerName 2>$null | Out-Null
    $TempKey = php artisan key:generate --show
    Run docker @(
        "run", "-d",
        "--name", $ContainerName,
        "-p", "8080:8080",
        "-e", "PORT=8080",
        "-e", "APP_ENV=production",
        "-e", "APP_DEBUG=false",
        "-e", "APP_KEY=$TempKey",
        "-e", "APP_URL=http://localhost:8080",
        "-e", "LOG_CHANNEL=stderr",
        "-e", "SESSION_DRIVER=cookie",
        "-e", "CACHE_STORE=file",
        "-e", "QUEUE_CONNECTION=sync",
        "-e", "FILESYSTEM_DISK=public",
        "-e", "DB_CONNECTION=mysql",
        "-e", "DB_HOST=127.0.0.1",
        "-e", "DB_PORT=3306",
        "-e", "DB_DATABASE=test",
        "-e", "DB_USERNAME=test",
        "-e", "DB_PASSWORD=test",
        "altokke-original-production"
    )
    Start-Sleep -Seconds 8
    docker logs $ContainerName
    Run docker @(
        "exec", $ContainerName, "sh", "-lc",
        "apache2ctl configtest; apache2ctl -M 2>&1 | grep -E 'mpm|php'; find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*.load' -print; ps aux"
    )
    $Response = Invoke-WebRequest -Uri "http://127.0.0.1:8080/up" -UseBasicParsing
    if ($Response.StatusCode -ne 200) {
        throw "Expected HTTP 200 from /up, got $($Response.StatusCode)."
    }
    docker stop $ContainerName | Out-Null
    docker rm $ContainerName | Out-Null
} else {
    Write-Warning "Docker is not available. Skipping docker build, apache2ctl, MPM runtime and /up container checks."
}

Step "Validation completed"
