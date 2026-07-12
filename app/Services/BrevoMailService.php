<?php

namespace App\Services;

use Brevo\Brevo;
use Brevo\Exceptions\BrevoApiException;
use Brevo\Exceptions\BrevoException;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class BrevoMailService
{
    public function send(
        string $toEmail,
        ?string $toName,
        string $subject,
        string $html,
        string $text
    ): string {
        $apiKey = (string) config('services.brevo.key', '');
        $senderEmail = (string) config('services.brevo.sender_email', '');
        $senderName = (string) config('services.brevo.sender_name', config('app.name', 'Altokke'));

        $this->validateAddress($toEmail, 'destinatario');
        $this->validateAddress($senderEmail, 'remitente');

        if ($apiKey === '') {
            throw new RuntimeException('BREVO_API_KEY no está configurada.');
        }

        $client = new Brevo(
            apiKey: $apiKey,
            options: [
                'client' => new Client(['timeout' => 10.0]),
                'timeout' => 10.0,
            ],
        );

        try {
            $response = $client->transactionalEmails->sendTransacEmail(
                new SendTransacEmailRequest([
                    'sender' => new SendTransacEmailRequestSender([
                        'email' => $senderEmail,
                        'name' => $senderName,
                    ]),
                    'to' => [
                        new SendTransacEmailRequestToItem([
                            'email' => $toEmail,
                            'name' => $toName,
                        ]),
                    ],
                    'subject' => $subject,
                    'htmlContent' => $html,
                    'textContent' => $text,
                ])
            );
        } catch (BrevoApiException $exception) {
            Log::error('Brevo rejected transactional email.', [
                'status' => $exception->getCode(),
                'recipient_hash' => hash('sha256', strtolower($toEmail)),
            ]);

            throw new RuntimeException('Brevo rechazó el correo transaccional.', 0, $exception);
        } catch (BrevoException $exception) {
            Log::error('Brevo transactional email failed.', [
                'exception' => $exception::class,
                'recipient_hash' => hash('sha256', strtolower($toEmail)),
            ]);

            throw new RuntimeException('No se pudo enviar el correo transaccional con Brevo.', 0, $exception);
        }

        $messageId = $response?->messageId ?? ($response?->messageIds[0] ?? null);

        if (! is_string($messageId) || $messageId === '') {
            throw new RuntimeException('Brevo no devolvió un identificador de mensaje.');
        }

        return $messageId;
    }

    private function validateAddress(string $email, string $label): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El correo de {$label} no tiene un formato válido.");
        }
    }
}
