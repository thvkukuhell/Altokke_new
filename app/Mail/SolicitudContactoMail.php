<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudContactoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $datos)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva solicitud de ayuda Altokke: ' . ($this->datos['tipo_solicitud'] ?? 'Consulta'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud_contacto',
            with: [
                'datos' => $this->datos,
            ],
        );
    }
}
