<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationEnvoyee extends Model
{
    protected $table = 'notifications_envoyees';

    protected $fillable = [
        'proprietaire_id', 'locataire_id', 'paiement_id',
        'canal', 'type', 'sujet', 'message',
        'destinataire_contact', 'statut', 'erreur', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────
    public function proprietaire()
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    public function locataire()
    {
        return $this->belongsTo(User::class, 'locataire_id');
    }

    public function paiement()
    {
        return $this->belongsTo(Paiement::class);
    }

    // ─── Labels ───────────────────────────────────────────────────────────────
    public static function canalLabel(string $canal): string
    {
        return match($canal) {
            'email'     => 'Email',
            'sms'       => 'SMS',
            'whatsapp'  => 'WhatsApp',
            default     => $canal,
        };
    }

    public static function canalIcon(string $canal): string
    {
        return match($canal) {
            'email'    => 'envelope',
            'sms'      => 'phone',
            'whatsapp' => 'whatsapp',
            default    => 'bell',
        };
    }

    public static function typeLabel(string $type): string
    {
        return match($type) {
            'alerte_loyer'   => 'Alerte loyer',
            'relance_retard' => 'Relance retard',
            'quittance'      => 'Quittance',
            'personnalise'   => 'Message libre',
            default          => $type,
        };
    }
}
