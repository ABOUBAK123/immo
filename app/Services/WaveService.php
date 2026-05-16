<?php

namespace App\Services;

use App\Models\Parametre;
use Illuminate\Support\Facades\Http;

class WaveService
{
    private const BASE_URL = 'https://api.wave.com';

    // ─── Créer une session de paiement Wave Checkout ──────────────────────────
    // Retourne ['url' => wave_launch_url, 'reference' => session_id]
    public function initierPaiement(int $montant, string $successUrl, string $errorUrl, string $webhookUrl): array
    {
        $apiKey   = Parametre::get('wave_api_key');
        $currency = Parametre::get('wave_currency', 'XOF');

        if (!$apiKey) {
            throw new \Exception('Clé API Wave manquante.');
        }

        $res = Http::withToken($apiKey)
            ->timeout(20)
            ->post(self::BASE_URL . '/v1/checkout/sessions', [
                'amount'              => (string) $montant,
                'currency'            => $currency,
                'success_url'         => $successUrl,
                'error_url'           => $errorUrl,
                'checkout_status_url' => $webhookUrl,
            ]);

        if (!$res->successful()) {
            $msg = $res->json('code') ?? $res->json('message') ?? $res->body();
            throw new \Exception('Erreur Wave : ' . $msg);
        }

        return [
            'url'       => $res->json('wave_launch_url'),
            'reference' => $res->json('id'),
        ];
    }

    // ─── Vérifier un webhook Wave ──────────────────────────────────────────────
    // Wave envoie : id, checkout_status (complete | expired)
    public static function verifierWebhook(array $data): bool
    {
        return ($data['checkout_status'] ?? '') === 'complete';
    }

    public static function estConfigured(): bool
    {
        return (bool) Parametre::get('wave_api_key');
    }
}
