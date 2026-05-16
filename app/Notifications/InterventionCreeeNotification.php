<?php

namespace App\Notifications;

use App\Models\Intervention;
use Illuminate\Notifications\Notification;

class InterventionCreeeNotification extends Notification
{
    public function __construct(public readonly Intervention $intervention) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $intervention = $this->intervention->load('bien', 'locataire');
        $locataire    = $intervention->locataire;
        $bien         = $intervention->bien;

        $prioriteLabel = match ($intervention->priorite) {
            'urgente' => '🚨 URGENTE',
            'haute'   => 'Haute',
            'moyenne' => 'Moyenne',
            default   => 'Basse',
        };

        return [
            'type'             => 'intervention_creee',
            'titre'            => 'Nouvelle demande d\'intervention',
            'message'          => "{$locataire->name} a signalé : « {$intervention->titre} » — {$bien->titre} (priorité : {$prioriteLabel}).",
            'icone'            => 'bi-tools',
            'couleur'          => $intervention->priorite === 'urgente' ? '#DC2626' : '#D97706',
            'intervention_id'  => $intervention->id,
            'locataire_id'     => $locataire->id,
            'locataire_nom'    => $locataire->name,
            'bien_titre'       => $bien->titre,
            'priorite'         => $intervention->priorite,
            'url'              => route('interventions.show', $intervention->id),
        ];
    }
}
