<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_email_receives_reset_link_without_revealing_account_state(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'pasajero@example.test']);

        $this->post(route('recuperar_password.proceso'), ['email' => $user->email])
            ->assertSessionHas('exito');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_same_generic_response(): void
    {
        Notification::fake();

        $this->post(route('recuperar_password.proceso'), ['email' => 'nadie@example.test'])
            ->assertSessionHas('exito')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
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

        $this->post(route('recuperar_password.proceso'), ['email' => $user->email]);
        $this->post(route('recuperar_password.proceso'), ['email' => $user->email]);

        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }
}
