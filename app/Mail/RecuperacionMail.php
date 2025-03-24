<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecuperacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $detalles;
    public $tipo_app;

    /**
     * Create a new message instance.
     */
    public function __construct($detalles, $tipo_app)
    {
        $this->detalles = $detalles;
        $this->tipo_app = $tipo_app;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("carlos.cmtz@hotmail.com", 'cdlcmtz_eats'),
            subject: 'Codigo Verificador',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.codigo_verificador',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
