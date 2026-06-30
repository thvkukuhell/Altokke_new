<?php

namespace App\Mail;

use App\Models\Viaje;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ViajeCompletadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Viaje $viaje) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resumen de tu viaje en Altokke'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.viaje_completado',
            with: [
                'viaje' => $this->viaje,
            ],
        );
    }
}
