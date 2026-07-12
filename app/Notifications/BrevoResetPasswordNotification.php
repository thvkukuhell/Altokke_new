<?php

namespace App\Notifications;

use App\Notifications\Channels\BrevoChannel;
use Illuminate\Notifications\Notification;

class BrevoResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return [BrevoChannel::class];
    }

    public function toBrevo(object $notifiable): array
    {
        $url = $this->resetUrl($notifiable);
        $expires = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        $name = $notifiable->nombre_completo ?? null;

        return [
            'to_email' => $notifiable->getEmailForPasswordReset(),
            'to_name' => $name,
            'subject' => 'Restablece tu contraseña de Altokke',
            'html' => view('emails.auth.reset-password', [
                'url' => $url,
                'expires' => $expires,
            ])->render(),
            'text' => $this->plainText($url, $expires),
        ];
    }

    public function resetUrl(object $notifiable): string
    {
        return route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }

    private function plainText(string $url, int $expires): string
    {
        return implode("\n", [
            'Altokke',
            '',
            'Recibimos una solicitud para restablecer la contraseña de tu cuenta.',
            "Este enlace expirará en {$expires} minutos.",
            '',
            $url,
            '',
            'Si no solicitaste este cambio, ignora este mensaje.',
        ]);
    }
}
