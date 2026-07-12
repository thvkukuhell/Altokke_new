<?php

namespace Tests\Feature;

use App\Mail\SolicitudContactoMail;
use App\Models\SolicitudContacto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SecurityHeadersAndContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_response_has_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy');
    }

    public function test_contact_request_is_saved_and_mailable_is_sent(): void
    {
        Mail::fake();

        $this->post(route('ayuda.enviar'), $this->payload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('solicitudes_contacto', [
            'correo' => 'persona@example.test',
            'tipo_solicitud' => 'consulta',
        ]);
        Mail::assertSent(SolicitudContactoMail::class);
    }

    public function test_contact_request_validation_and_throttle(): void
    {
        $this->post(route('ayuda.enviar'), [])->assertSessionHasErrors([
            'nombre',
            'correo',
            'asunto',
            'tipo_solicitud',
            'descripcion',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('ayuda'))->post(route('ayuda.enviar'), $this->payload([
                'correo' => "persona{$i}@example.test",
            ]));
        }

        $this->from(route('ayuda'))
            ->post(route('ayuda.enviar'), $this->payload(['correo' => 'limite@example.test']))
            ->assertStatus(429);
    }

    public function test_mail_failure_does_not_remove_saved_contact_request(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('mail down'));

        $this->post(route('ayuda.enviar'), $this->payload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, SolicitudContacto::where('correo', 'persona@example.test')->count());
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Persona Demo',
            'correo' => 'persona@example.test',
            'asunto' => 'Consulta',
            'tipo_solicitud' => 'consulta',
            'descripcion' => 'Necesito ayuda con una solicitud de prueba.',
        ], $overrides);
    }
}
