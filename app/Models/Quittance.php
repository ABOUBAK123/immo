<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quittance extends Model
{
    protected $fillable = ['paiement_id', 'numero', 'date_emission', 'pdf_path'];

    protected $casts = ['date_emission' => 'date'];

    public function paiement() { return $this->belongsTo(Paiement::class); }

    // ─── Format : QUIT-2026-05-00127 ─────────────────────────────────────────
    public static function genererNumero(): string
    {
        $derniere = static::latest()->first();
        $num = $derniere ? (int) substr($derniere->numero, -5) + 1 : 1;
        return 'QUIT-' . date('Y') . '-' . date('m') . '-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ─── Montant en lettres français ──────────────────────────────────────────
    // Ex : montantEnLettres(300000, 'FRANCS CFA') → "TROIS CENT MILLE (300 000) FRANCS CFA"
    public static function montantEnLettres(int $montant, string $devise = 'FRANCS CFA'): string
    {
        $lettres  = str_replace('-', ' ', static::enLettres($montant));
        $lettres  = mb_strtoupper($lettres, 'UTF-8');
        $formate  = number_format($montant, 0, ',', ' ');
        return "{$lettres} ({$formate}) {$devise}";
    }

    // ─── Conversion nombre → lettres français ────────────────────────────────
    private static function enLettres(int $n, bool $suivi = false): string
    {
        if ($n === 0) return 'zéro';

        $u = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
              'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
              'dix-sept', 'dix-huit', 'dix-neuf'];

        if ($n < 20) return $u[$n];

        if ($n < 70) {
            $d = intdiv($n, 10); $r = $n % 10;
            $diz = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante'][$d];
            if ($r === 0) return $diz;
            return $diz . ($r === 1 ? '-et-un' : '-' . $u[$r]);
        }

        if ($n < 80) {
            return 'soixante-' . $u[$n - 60];
        }

        if ($n < 100) {
            $r = $n - 80;
            return $r ? 'quatre-vingt-' . $u[$r] : 'quatre-vingts';
        }

        if ($n < 1000) {
            $c = intdiv($n, 100); $r = $n % 100;
            $base = $c === 1 ? 'cent' : $u[$c] . '-cent';
            if ($r) return $base . '-' . static::enLettres($r, true);
            return ($c > 1 && !$suivi) ? $u[$c] . '-cents' : $base;
        }

        if ($n < 1000000) {
            $m = intdiv($n, 1000); $r = $n % 1000;
            $mille = $m === 1 ? 'mille' : static::enLettres($m, true) . '-mille';
            return $r ? $mille . '-' . static::enLettres($r, true) : $mille;
        }

        if ($n < 1000000000) {
            $m = intdiv($n, 1000000); $r = $n % 1000000;
            $mil = static::enLettres($m, true) . ($m > 1 ? '-millions' : '-million');
            return $r ? $mil . '-' . static::enLettres($r, true) : $mil;
        }

        $m = intdiv($n, 1000000000); $r = $n % 1000000000;
        $mil = static::enLettres($m, true) . ($m > 1 ? '-milliards' : '-milliard');
        return $r ? $mil . '-' . static::enLettres($r, true) : $mil;
    }
}
