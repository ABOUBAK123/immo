<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Notifications\Notification;

class PaiementRecuNotification extends Notification
{
    public function __construct(public readonly Paiement $paiement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $paiement  = $this->paiement->load('location.bien', 'location.locataire');
        $locataire = $paiement->location->locataire;
        $bien      = $paiement->location->bien;
        $montant   = number_format($paiement->montant, 0, ',', ' ');

        return [
            'type'          => 'paiement_recu',
            'titre'         => 'Loyer reçu',
            'message'       => "{$locataire->name} a réglé son loyer de {$montant} pour « {$bien->titre} ».",
            'icone'         => 'bi-cash-coin',
            'couleur'       => '#16A34A',
            'paiement_id'   => $paiement->id,
            'locataire_id'  => $locataire->id,
            'locataire_nom' => $locataire->name,
            'bien_titre'    => $bien->titre,
            'montant'       => $paiement->montant,
            'url'           => route('paiements.index'),
        ];
    }
}
