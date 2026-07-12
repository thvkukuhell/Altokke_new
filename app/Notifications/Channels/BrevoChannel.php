<?php

namespace App\Notifications\Channels;

use App\Services\BrevoMailService;
use Illuminate\Notifications\Notification;

class BrevoChannel
{
    public function __construct(private BrevoMailService $mail) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toBrevo')) {
            return;
        }

        $message = $notification->toBrevo($notifiable);

        $this->mail->send(
            $message['to_email'],
            $message['to_name'] ?? null,
            $message['subject'],
            $message['html'],
            $message['text'],
        );
    }
}
