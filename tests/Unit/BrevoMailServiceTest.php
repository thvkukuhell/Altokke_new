<?php

namespace Tests\Unit;

use App\Services\BrevoMailService;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class BrevoMailServiceTest extends TestCase
{
    public function test_it_rejects_invalid_recipient_email_before_calling_brevo(): void
    {
        config([
            'services.brevo.key' => 'test-key',
            'services.brevo.sender_email' => 'remitente@example.test',
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(BrevoMailService::class)->send(
            'correo-invalido',
            null,
            'Asunto',
            '<p>HTML</p>',
            'Texto'
        );
    }

    public function test_it_requires_brevo_api_key(): void
    {
        config([
            'services.brevo.key' => '',
            'services.brevo.sender_email' => 'remitente@example.test',
        ]);

        $this->expectException(RuntimeException::class);

        app(BrevoMailService::class)->send(
            'destino@example.test',
            null,
            'Asunto',
            '<p>HTML</p>',
            'Texto'
        );
    }

    public function test_it_requires_valid_sender_email(): void
    {
        config([
            'services.brevo.key' => 'test-key',
            'services.brevo.sender_email' => 'remitente-invalido',
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(BrevoMailService::class)->send(
            'destino@example.test',
            null,
            'Asunto',
            '<p>HTML</p>',
            'Texto'
        );
    }
}
