<?php

namespace App\Mail;

use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class QuittanceMail extends Mailable
{
    public function __construct(public readonly Paiement $paiement) {}

    public function envelope(): Envelope
    {
        $periode = ucfirst($this->paiement->date_echeance->isoFormat('MMMM YYYY'));
        $bien    = $this->paiement->location->bien->titre ?? 'votre logement';

        return new Envelope(
            subject: "Quittance de loyer — {$periode} — {$bien}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quittance');
    }

    public function attachments(): array
    {
        $paiement  = $this->paiement;
        $quittance = $paiement->quittance;

        $pdf = Pdf::loadView('quittances.pdf-download', compact('quittance', 'paiement'))
            ->setPaper('a4', 'portrait');

        $filename = $quittance->numero . '.pdf';

        return [
            Attachment::fromData(fn() => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
