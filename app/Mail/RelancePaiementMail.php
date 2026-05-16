<?php

namespace App\Mail;

use App\Models\Paiement;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RelancePaiementMail extends Mailable
{
    public function __construct(
        public readonly Paiement $paiement,
        public readonly string $messageIA,
    ) {}

    public function envelope(): Envelope
    {
        $bien       = $this->paiement->location->bien->titre ?? 'votre logement';
        $nb         = $this->paiement->nb_relances + 1;
        $prefixe    = match (true) {
            $nb === 1 => 'Rappel',
            $nb === 2 => 'Relance n°2',
            default   => "Mise en demeure (relance n°{$nb})",
        };

        return new Envelope(
            subject: "{$prefixe} — Loyer impayé — {$bien}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.relance-paiement');
    }

    public function attachments(): array
    {
        return [];
    }
}
