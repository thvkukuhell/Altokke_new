<?php

namespace Tests\Feature;

use App\Models\Conductor;
use App\Models\Pasajero;
use App\Models\User;
use App\Models\Viaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViajePolicyAndBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_viaje_policy_allows_owner_and_blocks_unrelated_user(): void
    {
        [$pasajeroUser] = $this->crearPasajero('pasajero@example.test');
        [$otroPasajero] = $this->crearPasajero('otro@example.test');
        [$conductorUser, $conductor] = $this->crearConductor('conductor@example.test');
        $viaje = $this->crearViaje($pasajeroUser->id_usuario, $conductor->id_conductor, 'en_curso');

        $this->assertTrue($pasajeroUser->can('view', $viaje));
        $this->assertTrue($conductorUser->can('updateLocation', $viaje));
        $this->assertTrue($conductorUser->can('complete', $viaje));
        $this->assertFalse($otroPasajero->can('view', $viaje));
    }

    public function test_driver_can_accept_available_trip_but_not_assigned_trip(): void
    {
        [$pasajeroUser] = $this->crearPasajero('pasajero@example.test');
        [$conductorUser, $conductor] = $this->crearConductor('conductor@example.test');
        $disponible = $this->crearViaje($pasajeroUser->id_usuario, null, 'buscando');
        $asignado = $this->crearViaje($pasajeroUser->id_usuario, $conductor->id_conductor, 'aceptado');

        $this->assertTrue($conductorUser->can('accept', $disponible));
        $this->assertFalse($conductorUser->can('accept', $asignado));
    }

    public function test_history_badge_renders_escaped_text_not_controller_html(): void
    {
        [$pasajeroUser] = $this->crearPasajero('pasajero@example.test');
        $this->crearViaje($pasajeroUser->id_usuario, null, '<script>alert(1)</script>');

        $response = $this->actingAs($pasajeroUser)->get(route('pasajero.historial'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    private function crearPasajero(string $email): array
    {
        $user = User::factory()->pasajero()->create(['email' => $email]);
        $pasajero = Pasajero::create([
            'id_pasajero' => $user->id_usuario,
            'metodo_pago_preferido' => 'efectivo',
        ]);

        return [$user, $pasajero];
    }

    private function crearConductor(string $email): array
    {
        $user = User::factory()->conductor()->create(['email' => $email]);
        $conductor = Conductor::create([
            'id_conductor' => $user->id_usuario,
            'licencia_numero' => 'LIC-' . $user->id_usuario,
            'estado_conductor' => 'activo',
            'saldo_disponible' => 100,
            'verificado_dni' => true,
            'fecha_verificacion_dni' => now(),
        ]);

        return [$user, $conductor];
    }

    private function crearViaje(int $pasajeroId, ?int $conductorId, string $estado): Viaje
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
            'tarifa_estimada' => 20,
            'tipo_servicio' => 'normal',
            'metodo_pago' => 'efectivo',
            'estado_viaje' => $estado,
            'fecha_solicitud' => now(),
        ]);
    }
}
