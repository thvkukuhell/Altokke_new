<?php

namespace Tests\Feature;

use App\Models\Conductor;
use App\Models\Pasajero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesAuthenticatedLayoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function publicPages(): array
    {
        return [
            'servicios' => ['servicios'],
            'contacto' => ['contacto'],
            'ayuda' => ['ayuda'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_guest_sees_public_header_and_one_footer(string $routeName): void
    {
        $response = $this->get(route($routeName));

        $response->assertOk()
            ->assertSee('data-header-role="guest"', false)
            ->assertSee('btn-iniciar-sesion', false);

        $this->assertSame(1, substr_count($response->getContent(), '<footer'));
        $this->assertGuest();
    }

    #[DataProvider('publicPages')]
    public function test_passenger_keeps_session_and_sees_passenger_header(string $routeName): void
    {
        $user = User::factory()->pasajero()->create();
        Pasajero::create([
            'id_pasajero' => $user->id_usuario,
            'metodo_pago_preferido' => 'efectivo',
        ]);

        $response = $this->actingAs($user)->get(route($routeName));

        $response->assertOk()
            ->assertSee('data-header-role="pasajero"', false)
            ->assertDontSee('btn-iniciar-sesion', false);

        $this->assertSame(1, substr_count($response->getContent(), '<footer'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('pasajero', $user->fresh()->tipo_usuario);
    }

    #[DataProvider('publicPages')]
    public function test_driver_keeps_session_and_sees_driver_header(string $routeName): void
    {
        $user = User::factory()->conductor()->create();
        Conductor::create([
            'id_conductor' => $user->id_usuario,
            'licencia_numero' => 'LIC-' . $user->id_usuario,
            'estado_conductor' => 'activo',
            'saldo_disponible' => 100,
            'verificado_dni' => true,
            'fecha_verificacion_dni' => now(),
        ]);

        $response = $this->actingAs($user)->get(route($routeName));

        $response->assertOk()
            ->assertSee('data-header-role="conductor"', false)
            ->assertDontSee('btn-iniciar-sesion', false);

        $this->assertSame(1, substr_count($response->getContent(), '<footer'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('conductor', $user->fresh()->tipo_usuario);
    }
}
