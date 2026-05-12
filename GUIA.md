
## Requisitos previos
Tener instalado:
- PHP 8.2 o superior
- Composer
- Node.js y npm
- MySQL
- XAMPP o Laragon

## Instalación paso a paso
### 1. Clonar el repositorio
```bash
git clone https://github.com/TU_USUARIO/altokke.git
cd altokke
```

### 2. Instalar dependencias PHP
```bash
composer install
```

### 3. Instalar dependencias JavaScript
```bash
npm install
```

### 4. Configurar el entorno
Copia el archivo de ejemplo y edítalo:
```bash
cp .env.example .env
```

Abre `.env` y cambia estas líneas con tus datos:
APP_NAME=Altokke
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_altokke_laravel
DB_USERNAME=root
DB_PASSWORD=

### 5. Generar la clave de la app
```bash
php artisan key:generate
```

### 6. Crear la base de datos
Abre phpMyAdmin y crea una base de datos llamada: db_altokke_laravel

### 7. Correr las migraciones
```bash
php artisan migrate
```

### 8. Correr el servidor
En terminales separadas corre estos dos comandos:

**Terminal 1 — servidor PHP:**
```bash
php artisan serve
```

**Terminal 2 — assets frontend:**
```bash
npm run dev
```

### 9. Abrir en el navegador http://localhost:8000

## Flujo de trabajo en equipo

### Antes de empezar a trabajar cada día:
```bash
git pull origin main
```

### Cuando terminas algo:
```bash
git add .
git commit -m "descripción de lo que hiciste"
git push origin main
```

### Si alguien agregó migraciones nuevas:
```bash
php artisan migrate
```

## Variables de entorno importantes
Nunca subas el archivo `.env` a GitHub.
Cada integrante tiene su propio `.env` local.

