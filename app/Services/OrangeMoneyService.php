<?php

namespace App\Services;

use App\Models\Parametre;
use Illuminate\Support\Facades\Http;

class OrangeMoneyService
{
    private const OAUTH_URL  = 'https://api.orange.com/oauth/v3/token';
    private const PAY_URL    = 'https://api.orange.com/orange-money-webpay/v1/webpayment';

    // ─── Initier un paiement ───────────────────────────────────────────────────
    public function initierPaiement(int $montant, string $orderId, string $returnUrl, string $notifUrl): array
    {
        $token = $this->getToken();

        $res = Http::withToken($token)
            ->timeout(20)
            ->post(self::PAY_URL, [
                'merchant_key' => Parametre::get('om_merchant_key'),
                'currency'     => Parametre::get('om_currency', 'ORA'),
                'order_id'     => $orderId,
                'amount'       => $montant,
                'return_url'   => $returnUrl,
                'cancel_url'   => $returnUrl,
                'notif_url'    => $notifUrl,
                'lang'         => 'fr',
                'reference'    => $orderId,
            ]);

        if (!$res->successful()) {
            throw new \Exception($res->json('message', 'Erreur Orange Money : ' . $res->body()));
        }

        return [
            'url'       => $res->json('payment_url'),
            'reference' => $res->json('pay_token') ?? $orderId,
        ];
    }

    // ─── Vérifier un webhook Orange Money ─────────────────────────────────────
    // Orange Money envoie : status=SUCCESS, order_id, pay_token, notif_token
    public static function verifierWebhook(array $data): bool
    {
        $expectedToken = Parametre::get('om_notif_token');
        if ($expectedToken && ($data['notif_token'] ?? '') !== $expectedToken) {
            return false;
        }
        return ($data['status'] ?? '') === 'SUCCESS';
    }

    public static function estConfigured(): bool
    {
        return (bool) (Parametre::get('om_client_id') && Parametre::get('om_merchant_key'));
    }

    // ─── Obtenir le token OAuth ────────────────────────────────────────────────
    private function getToken(): string
    {
        $clientId     = Parametre::get('om_client_id');
        $clientSecret = Parametre::get('om_client_secret');

        if (!$clientId || !$clientSecret) {
            throw new \Exception('Identifiants Orange Money manquants (Client ID / Client Secret).');
        }

        $res = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->timeout(15)
            ->post(self::OAUTH_URL, ['grant_type' => 'client_credentials']);

        if (!$res->successful()) {
            throw new \Exception('Authentification Orange Money échouée : ' . $res->body());
        }

        return $res->json('access_token');
    }
}
