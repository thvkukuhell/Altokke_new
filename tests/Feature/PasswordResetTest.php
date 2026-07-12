<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\BrevoResetPasswordNotification;
use App\Services\BrevoMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_request_page_loads(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('email', false);
    }

    public function test_reset_link_request_requires_valid_email(): void
    {
        $this->post(route('password.email'), ['email' => 'correo-invalido'])
            ->assertSessionHasErrors('email');
    }

    public function test_reset_link_request_requires_email(): void
    {
        $this->post(route('password.email'), [])
            ->assertSessionHasErrors('email');
    }

    public function test_registered_email_receives_reset_link_without_revealing_account_state(): void
    {
        Notification::fake();
        config(['app.url' => 'https://altokkeweb-production.up.railway.app']);
        URL::forceRootUrl(config('app.url'));

        $user = User::factory()->create(['email' => 'pasajero@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('exito');

        Notification::assertSentTo($user, BrevoResetPasswordNotification::class, function (BrevoResetPasswordNotification $notification) use ($user): bool {
            $url = $notification->resetUrl($user);

            return str_starts_with($url, 'https://altokkeweb-production.up.railway.app')
                && str_contains($url, route('password.reset', $notification->token, false))
                && str_contains($url, 'email='.urlencode($user->email));
        });
    }

    public function test_unknown_email_gets_same_generic_response(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'nadie@example.test'])
            ->assertSessionHas('exito')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_reset_password_form_loads_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'pasajero@example.test']);
        $token = app('auth.password.broker')->createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('name="token"', false)
            ->assertSee('name="email"', false);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'pasajero@example.test']);

        $this->post(route('password.update'), [
            'token' => 'token-invalido',
            'email' => $user->email,
            'password' => 'NuevaClaveSegura123',
            'password_confirmation' => 'NuevaClaveSegura123',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_confirmation_and_rules_are_enforced(): void
    {
        $user = User::factory()->create(['email' => 'pasajero@example.test']);

        $this->post(route('password.update'), [
            'token' => 'token-invalido',
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['password']);
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'pasajero@example.test']);
        $token = app('auth.password.broker')->createToken($user);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subMinutes(61)]);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NuevaClaveSegura123',
            'password_confirmation' => 'NuevaClaveSegura123',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_can_be_reset_once_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'pasajero@example.test',
            'contrasena_hash' => Hash::make('ClaveAnterior123'),
        ]);
        $token = app('auth.password.broker')->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NuevaClaveSegura123',
            'password_confirmation' => 'NuevaClaveSegura123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NuevaClaveSegura123', $user->fresh()->contrasena_hash));

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'OtraClaveSegura123',
            'password_confirmation' => 'OtraClaveSegura123',
        ])->assertSessionHasErrors('email');
    }

    public function test_reset_link_request_is_broker_throttled(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'pasajero@example.test']);

        $this->post(route('password.email'), ['email' => $user->email]);
        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentToTimes($user, BrevoResetPasswordNotification::class, 1);
    }

    public function test_brevo_errors_are_not_shown_to_the_user(): void
    {
        $this->mock(BrevoMailService::class, function ($mock): void {
            $mock->shouldReceive('send')
                ->once()
                ->andThrow(new RuntimeException('Brevo rejected the message.'));
        });

        $user = User::factory()->create(['email' => 'pasajero@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('exito')
            ->assertSessionHasNoErrors();
    }

    public function test_reset_link_request_route_is_rate_limited(): void
    {
        Notification::fake();

        foreach (range(1, 5) as $index) {
            $this->post(route('password.email'), ['email' => "nadie{$index}@example.test"])
                ->assertSessionHas('exito');
        }

        $this->post(route('password.email'), ['email' => 'limite@example.test'])
            ->assertTooManyRequests();
    }
}
