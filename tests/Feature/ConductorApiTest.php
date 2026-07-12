<?php

namespace Tests\Feature;

use App\Models\Conductor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConductorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_conductor_can_update_allowed_profile_field(): void
    {
        [$user, $conductor] = $this->crearConductor('conductor@example.test');

        $this->actingAs($user)
            ->putJson("/api/conductores/{$conductor->id_conductor}", [
                'licencia_numero' => 'LIC-NUEVA',
            ])
            ->assertOk()
            ->assertJsonPath('licencia_numero', 'LIC-NUEVA');

        $this->assertSame('LIC-NUEVA', $conductor->fresh()->licencia_numero);
    }

    public function test_conductor_cannot_change_estado_conductor(): void
    {
        [$user, $conductor] = $this->crearConductor('conductor@example.test', 'en_verificacion');

        $this->actingAs($user)
            ->putJson("/api/conductores/{$conductor->id_conductor}", [
                'estado_conductor' => 'activo',
            ])
            ->assertStatus(422);

        $this->assertSame('en_verificacion', $conductor->fresh()->estado_conductor);
    }

    public function test_conductor_cannot_modify_another_conductor(): void
    {
        [$user] = $this->crearConductor('conductor@example.test');
        [, $otherConductor] = $this->crearConductor('otro@example.test');

        $this->actingAs($user)
            ->putJson("/api/conductores/{$otherConductor->id_conductor}", [
                'licencia_numero' => 'NO-PERMITIDA',
            ])
            ->assertForbidden();
    }

    public function test_passenger_cannot_access_conductor_update_endpoint(): void
    {
        $passenger = User::factory()->pasajero()->create();
        [, $conductor] = $this->crearConductor('conductor@example.test');

        $this->actingAs($passenger)
            ->putJson("/api/conductores/{$conductor->id_conductor}", [
                'licencia_numero' => 'NO-PERMITIDA',
            ])
            ->assertForbidden();
    }

    private function crearConductor(string $email, string $estado = 'activo'): array
    {
        $user = User::factory()->conductor()->create(['email' => $email]);
        $conductor = Conductor::create([
            'id_conductor' => $user->id_usuario,
            'licencia_numero' => 'LIC-' . $user->id_usuario,
            'estado_conductor' => $estado,
            'saldo_disponible' => 100,
            'verificado_dni' => true,
            'fecha_verificacion_dni' => now(),
        ]);

        return [$user, $conductor];
    }
}
