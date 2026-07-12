<?php

namespace App\Console\Commands;

use App\Services\BrevoMailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Throwable;

class BrevoTestCommand extends Command
{
    protected $signature = 'brevo:test {email}';

    protected $description = 'Envía un correo de prueba usando la API HTTPS de Brevo.';

    public function handle(BrevoMailService $mail): int
    {
        $email = (string) $this->argument('email');

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            $this->error('El correo indicado no tiene un formato válido.');

            return self::FAILURE;
        }

        try {
            $messageId = $mail->send(
                $email,
                null,
                'Prueba de correo Altokke',
                '<p>Este es un correo de prueba de Altokke enviado mediante la API HTTPS de Brevo.</p>',
                'Este es un correo de prueba de Altokke enviado mediante la API HTTPS de Brevo.'
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->error('No se pudo enviar el correo de prueba con Brevo.');
            $this->line('Revisa BREVO_API_KEY, BREVO_SENDER_EMAIL, el remitente verificado y los logs.');

            return self::FAILURE;
        }

        $this->info('Brevo aceptó el correo de prueba.');
        $this->line('Message ID: '.$messageId);

        return self::SUCCESS;
    }
}
