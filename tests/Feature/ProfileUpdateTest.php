<?php

namespace Tests\Feature;

use App\Models\Conductor;
use App\Models\Pasajero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_can_update_phone_and_profile_shows_it(): void
    {
        [$user] = $this->crearPasajero();

        $this->actingAs($user)
            ->patch(route('pasajero.guardarPerfil'), [
                'nombre_completo' => $user->nombre_completo,
                'apellidos' => $user->apellidos,
                'dni' => $user->dni,
                'telefono' => '987654321',
                'metodo_pago_preferido' => 'yape',
            ])
            ->assertRedirect(route('pasajero.perfil'));

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $user->id_usuario,
            'telefono' => '987654321',
        ]);
        $this->assertDatabaseHas('pasajeros', [
            'id_pasajero' => $user->id_usuario,
            'metodo_pago_preferido' => 'yape',
        ]);

        $this->actingAs($user->fresh())
            ->get(route('pasajero.perfil'))
            ->assertOk()
            ->assertSee('987654321');
    }

    public function test_passenger_update_does_not_modify_other_user_or_sensitive_fields(): void
    {
        [$user] = $this->crearPasajero();
        [$other] = $this->crearPasajero('otro@example.test');

        $this->actingAs($user)
            ->patch(route('pasajero.guardarPerfil'), [
                'id_usuario' => $other->id_usuario,
                'tipo_usuario' => 'conductor',
                'activo' => false,
                'nombre_completo' => 'Nombre Editado',
                'apellidos' => $user->apellidos,
                'dni' => $user->dni,
                'telefono' => '987111222',
                'metodo_pago_preferido' => 'efectivo',
            ])
            ->assertRedirect(route('pasajero.perfil'));

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $user->id_usuario,
            'nombre_completo' => 'Nombre Editado',
            'telefono' => '987111222',
            'tipo_usuario' => 'pasajero',
            'activo' => true,
        ]);
        $this->assertSame($other->telefono, $other->fresh()->telefono);
    }

    public function test_passenger_invalid_phone_is_rejected_and_guest_is_redirected(): void
    {
        [$user] = $this->crearPasajero();

        $this->actingAs($user)
            ->patch(route('pasajero.guardarPerfil'), [
                'nombre_completo' => $user->nombre_completo,
                'apellidos' => $user->apellidos,
                'dni' => $user->dni,
                'telefono' => 'abc',
                'metodo_pago_preferido' => 'efectivo',
            ])
            ->assertSessionHasErrors('telefono');

        auth()->logout();
        $this->flushSession();

        $this->patch(route('pasajero.guardarPerfil'))
            ->assertRedirect(route('login'));
    }

    public function test_driver_can_update_phone_and_profile_shows_it(): void
    {
        [$user] = $this->crearConductor();

        $this->actingAs($user)
            ->patch(route('conductor.actualizarPerfil'), [
                'nombre_completo' => $user->nombre_completo,
                'apellidos' => $user->apellidos,
                'telefono' => '986654321',
                'email' => $user->email,
            ])
            ->assertRedirect(route('conductor.perfil'));

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $user->id_usuario,
            'telefono' => '986654321',
        ]);

        $this->actingAs($user->fresh())
            ->get(route('conductor.perfil'))
            ->assertOk()
            ->assertSee('986654321');
    }

    public function test_driver_update_does_not_modify_other_driver_or_sensitive_fields(): void
    {
        [$user, $conductor] = $this->crearConductor();
        [$other] = $this->crearConductor('otro-conductor@example.test');

        $this->actingAs($user)
            ->patch(route('conductor.actualizarPerfil'), [
                'id_conductor' => $other->id_usuario,
                'tipo_usuario' => 'pasajero',
                'estado_conductor' => 'inactivo',
                'saldo_disponible' => 0,
                'nombre_completo' => 'Conductor Editado',
                'apellidos' => $user->apellidos,
                'telefono' => '986111222',
                'email' => $user->email,
            ])
            ->assertRedirect(route('conductor.perfil'));

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $user->id_usuario,
            'nombre_completo' => 'Conductor Editado',
            'telefono' => '986111222',
            'tipo_usuario' => 'conductor',
        ]);
        $this->assertDatabaseHas('conductores', [
            'id_conductor' => $conductor->id_conductor,
            'estado_conductor' => 'activo',
            'saldo_disponible' => 100,
        ]);
        $this->assertSame($other->telefono, $other->fresh()->telefono);
    }

    public function test_driver_invalid_phone_is_rejected_and_guest_is_redirected(): void
    {
        [$user] = $this->crearConductor();

        $this->actingAs($user)
            ->patch(route('conductor.actualizarPerfil'), [
                'nombre_completo' => $user->nombre_completo,
                'apellidos' => $user->apellidos,
                'telefono' => '12345',
                'email' => $user->email,
            ])
            ->assertSessionHasErrors('telefono');

        auth()->logout();
        $this->flushSession();

        $this->patch(route('conductor.actualizarPerfil'))
            ->assertRedirect(route('login'));
    }

    public function test_roles_cannot_call_each_other_profile_update_routes(): void
    {
        [$passenger] = $this->crearPasajero();
        [$driver] = $this->crearConductor();

        $this->actingAs($driver)
            ->patch(route('pasajero.guardarPerfil'), [
                'nombre_completo' => 'No permitido',
                'telefono' => '987123123',
                'metodo_pago_preferido' => 'efectivo',
            ])
            ->assertForbidden();

        $this->actingAs($passenger)
            ->patch(route('conductor.actualizarPerfil'), [
                'nombre_completo' => 'No permitido',
                'telefono' => '986123123',
                'email' => $passenger->email,
            ])
            ->assertForbidden();
    }

    private function crearPasajero(string $email = 'pasajero@example.test'): array
    {
        $user = User::factory()->pasajero()->create(['email' => $email]);
        $pasajero = Pasajero::create([
            'id_pasajero' => $user->id_usuario,
            'metodo_pago_preferido' => 'efectivo',
        ]);

        return [$user, $pasajero];
    }

    private function crearConductor(string $email = 'conductor@example.test'): array
    {
        $user = User::factory()->conductor()->create(['email' => $email]);
        $conductor = Conductor::create([
            'id_conductor' => $user->id_usuario,
            'licencia_numero' => 'LIC-'.$user->id_usuario,
            'estado_conductor' => 'activo',
            'saldo_disponible' => 100,
            'verificado_dni' => true,
            'fecha_verificacion_dni' => now(),
        ]);

        return [$user, $conductor];
    }
}
