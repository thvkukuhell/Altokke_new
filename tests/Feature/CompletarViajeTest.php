<?php

namespace Tests\Feature;

use App\Models\Comision;
use App\Models\Conductor;
use App\Models\Pasajero;
use App\Models\User;
use App\Models\Viaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CompletarViajeTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_controller_completes_valid_trip_atomically(): void
    {
        [$conductorUser, $conductor] = $this->crearConductor(saldo: 100, totalViajes: 0);
        [$pasajeroUser] = $this->crearPasajero();
        $viaje = $this->crearViaje($pasajeroUser->id_usuario, $conductor->id_conductor);

        $this->actingAs($conductorUser)
            ->post(route('conductor.completarViaje'), ['id_viaje' => $viaje->id_viaje])
            ->assertRedirect(route('conductor.solicitudes'));

        $this->assertDatabaseHas('viajes', [
            'id_viaje' => $viaje->id_viaje,
            'estado_viaje' => 'completado',
            'tarifa_final' => 50,
        ]);
        $this->assertDatabaseHas('comisiones', [
            'id_viaje' => $viaje->id_viaje,
            'id_conductor' => $conductor->id_conductor,
            'monto_comision' => 4,
        ]);
        $this->assertSame('96.00', $conductor->fresh()->saldo_disponible);
        $this->assertSame(1, $conductor->fresh()->total_viajes);
    }

    public function test_internal_api_uses_same_completion_logic(): void
    {
        [$conductorUser, $conductor] = $this->crearConductor(saldo: 100);
        [$pasajeroUser] = $this->crearPasajero();
        $viaje = $this->crearViaje($pasajeroUser->id_usuario, $conductor->id_conductor);

        $this->actingAs($conductorUser)
            ->postJson("/api/internal/viajes/{$viaje->id_viaje}/completar")
            ->assertOk()
            ->assertJsonPath('data.estado', 'completado');

        $this->assertSame(1, Comision::where('id_viaje', $viaje->id_viaje)->count());
        $this->assertSame('96.00', $conductor->fresh()->saldo_disponible);
    }

    public function test_cannot_complete_trip_from_another_driver(): void
    {
        [$conductorUser] = $this->crearConductor(email: 'uno@example.test');
        [, $otherConductor] = $this->crearConductor(email: 'dos@example.test');
        [$pasajeroUser] = $this->crearPasajero();
        $viaje = $this->crearViaje($pasajeroUser->id_usuario, $otherConductor->id_conductor);

        $this->actingAs($conductorUser)
            ->postJson("/api/internal/viajes/{$viaje->id_viaje}/completar")
            ->assertForbidden();

        $this->assertSame('en_curso', $viaje->fresh()->estado_viaje);
        $this->assertSame(0, Comision::count());
    }

    public function test_cannot_complete_cancelled_or_already_completed_trip(): void
    {
        [$conductorUser, $conductor] = $this->crearConductor(saldo: 100);
        [$pasajeroUser] = $this->crearPasajero();
        $cancelado = $this->crearViaje($pasajeroUser->id_usuario, $conductor->id_conductor, 'cancelado');

        $this->actingAs($conductorUser)
            ->postJson("/api/internal/viajes/{$cancelado->id_viaje}/completar")
            ->assertStatus(409);

        $activo = $this->crearViaje($pasajeroUser->id_usuario, $conductor->id_conductor);

        $this->actingAs($conductorUser)
            ->postJson("/api/internal/viajes/{$activo->id_viaje}/completar")
            ->assertOk();

        $this->actingAs($conductorUser)
            ->postJson("/api/internal/viajes/{$activo->id_viaje}/completar")
            ->assertStatus(409);

        $this->assertSame(1, Comision::where('id_viaje', $activo->id_viaje)->count());
        $this->assertSame('96.00', $conductor->fresh()->saldo_disponible);
        $this->assertSame(1, $conductor->fresh()->total_viajes);
    }

    public function test_failed_validation_inside_transaction_keeps_trip_unchanged(): void
    {
        [$conductorUser, $conductor] = $this->crearConductor(saldo: 1);
        [$pasajeroUser] = $this->crearPasajero();
        $viaje = $this->crearViaje($pasajeroUser->id_usuario, $conductor->id_conductor);

        $this->actingAs($conductorUser)
            ->postJson("/api/internal/viajes/{$viaje->id_viaje}/completar")
            ->assertStatus(409);

        $this->assertSame('en_curso', $viaje->fresh()->estado_viaje);
        $this->assertSame(0, Comision::count());
        $this->assertSame('1.00', $conductor->fresh()->saldo_disponible);
    }

    public function test_mail_failure_after_commit_does_not_revert_completed_trip(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('mail down'));

        [$conductorUser, $conductor] = $this->crearConductor(saldo: 100);
        [$pasajeroUser] = $this->crearPasajero(email: 'pasajero@example.test');
        $viaje = $this->crearViaje($pasajeroUser->id_usuario, $conductor->id_conductor);

        $this->actingAs($conductorUser)
            ->post(route('conductor.completarViaje'), ['id_viaje' => $viaje->id_viaje])
            ->assertRedirect(route('conductor.solicitudes'));

        $this->assertSame('completado', $viaje->fresh()->estado_viaje);
        $this->assertSame(1, Comision::where('id_viaje', $viaje->id_viaje)->count());
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

    private function crearConductor(string $email = 'conductor@example.test', float $saldo = 100, int $totalViajes = 0): array
    {
        $user = User::factory()->conductor()->create(['email' => $email]);
        $conductor = Conductor::create([
            'id_conductor' => $user->id_usuario,
            'licencia_numero' => 'LIC-' . $user->id_usuario,
            'estado_conductor' => 'activo',
            'saldo_disponible' => $saldo,
            'total_viajes' => $totalViajes,
            'verificado_dni' => true,
            'fecha_verificacion_dni' => now(),
        ]);

        return [$user, $conductor];
    }

    private function crearViaje(int $pasajeroId, int $conductorId, string $estado = 'en_curso'): Viaje
    {
        return Viaje::create([
            'id_pasajero' => $pasajeroId,
            'id_conductor' => $conductorId,
            'origen_texto' => 'Origen',
            'destino_texto' => 'Destino',
            'lat_origen' => -5.63889,
            'lng_origen' => -78.53110,
            'lat_destino' => -5.64000,
            'lng_destino' => -78.53000,
            'tarifa_estimada' => 50,
            'tipo_servicio' => 'normal',
            'metodo_pago' => 'efectivo',
            'estado_viaje' => $estado,
            'fecha_solicitud' => now(),
            'fecha_inicio' => now(),
        ]);
    }
}
