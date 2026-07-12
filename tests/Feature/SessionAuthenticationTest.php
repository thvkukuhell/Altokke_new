<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SessionAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_id_changes_after_login(): void
    {
        $user = User::factory()->pasajero()->create([
            'email' => 'pasajero@example.test',
            'contrasena_hash' => Hash::make('password123'),
        ]);

        $this->get(route('login'));
        $before = session()->getId();

        $this->post(route('login.proceso'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('pasajero.solicitarViaje'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($before, session()->getId());
    }

    public function test_session_id_changes_after_passenger_registration_auto_login(): void
    {
        $this->get(route('registro_pasajero'));
        $before = session()->getId();

        $this->post(route('proc_regist_pasajero'), [
            'nombre' => 'Maria',
            'apellidos' => 'Prueba',
            'dni' => '12345678',
            'email' => 'maria@example.test',
            'telefono' => '999111222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('pasajero.solicitarViaje'));

        $this->assertAuthenticated();
        $this->assertNotSame($before, session()->getId());
    }

    public function test_session_id_changes_after_driver_registration_auto_login(): void
    {
        $this->get(route('registro_conductor'));
        $before = session()->getId();

        $this->post(route('proc_regist_conductor'), [
            'nombre' => 'Juan',
            'apellidos' => 'Conductor',
            'dni' => '87654321',
            'email' => 'juan@example.test',
            'telefono' => '999222333',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'numero_licencia' => 'LIC123',
            'placa' => 'ABC123',
            'numero_soat' => 'SOAT123',
            'marca' => 'Honda',
            'modelo' => 'XR',
            'color' => 'Rojo',
            'year' => 2024,
        ])->assertRedirect(route('conductor.dashboard'));

        $this->assertAuthenticated();
        $this->assertNotSame($before, session()->getId());
    }

    public function test_invalid_credentials_do_not_authenticate(): void
    {
        User::factory()->pasajero()->create([
            'email' => 'pasajero@example.test',
            'contrasena_hash' => Hash::make('password123'),
        ]);

        $this->post(route('login.proceso'), [
            'email' => 'pasajero@example.test',
            'password' => 'incorrecta',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_invalidates_protected_access(): void
    {
        $user = User::factory()->pasajero()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('inicio'));

        $this->get(route('pasajero.solicitarViaje'))
            ->assertRedirect(route('login'));
    }
}
