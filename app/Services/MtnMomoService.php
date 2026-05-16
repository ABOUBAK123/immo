<?php

namespace App\Services;

use App\Models\Parametre;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MtnMomoService
{
    // ─── URL de base selon l'environnement ────────────────────────────────────
    private function baseUrl(): string
    {
        return Parametre::get('mtn_environment', 'sandbox') === 'production'
            ? 'https://proxy.momoapi.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';
    }

    // ─── Initier un paiement (RequestToPay → USSD push) ───────────────────────
    // Retourne le referenceId UUID à stocker comme provider_reference
    public function initierPaiement(string $telephone, int $montant, string $externalId, string $description): string
    {
        $token       = $this->getToken();
        $subKey      = Parametre::get('mtn_subscription_key');
        $env         = Parametre::get('mtn_environment', 'sandbox');
        $currency    = Parametre::get('mtn_currency', 'XOF');
        $referenceId = Str::uuid()->toString();

        // Nettoyage numéro : garder chiffres uniquement
        $phone = preg_replace('/[^0-9]/', '', $telephone);

        $res = Http::withToken($token)
            ->withHeaders([
                'Ocp-Apim-Subscription-Key' => $subKey,
                'X-Reference-Id'            => $referenceId,
                'X-Target-Environment'      => $env,
                'Content-Type'              => 'application/json',
            ])
            ->timeout(20)
            ->post($this->baseUrl() . '/collection/v1_0/requesttopay', [
                'amount'       => (string) $montant,
                'currency'     => $currency,
                'externalId'   => $externalId,
                'payer'        => ['partyIdType' => 'MSISDN', 'partyId' => $phone],
                'payerMessage' => $description,
                'payeeNote'    => $description,
            ]);

        // MTN renvoie 202 Accepted (pas de corps)
        if ($res->status() !== 202) {
            $err = $res->json('message') ?? $res->json('code') ?? $res->body();
            throw new \Exception('Erreur MTN MoMo : ' . $err);
        }

        return $referenceId;
    }

    // ─── Vérifier le statut d'une transaction ─────────────────────────────────
    // Retourne : PENDING | SUCCESSFUL | FAILED
    public function verifierStatut(string $referenceId): string
    {
        try {
            $token  = $this->getToken();
            $subKey = Parametre::get('mtn_subscription_key');
            $env    = Parametre::get('mtn_environment', 'sandbox');

            $res = Http::withToken($token)
                ->withHeaders([
                    'Ocp-Apim-Subscription-Key' => $subKey,
                    'X-Target-Environment'      => $env,
                ])
                ->timeout(15)
                ->get($this->baseUrl() . '/collection/v1_0/requesttopay/' . $referenceId);

            if (!$res->successful()) return 'PENDING';

            return $res->json('status', 'PENDING'); // PENDING | SUCCESSFUL | FAILED
        } catch (\Exception) {
            return 'PENDING';
        }
    }

    public static function estConfigured(): bool
    {
        return (bool) (Parametre::get('mtn_subscription_key') && Parametre::get('mtn_api_user'));
    }

    // ─── Obtenir le token OAuth (Basic auth avec apiuser:apikey) ──────────────
    private function getToken(): string
    {
        $subKey  = Parametre::get('mtn_subscription_key');
        $apiUser = Parametre::get('mtn_api_user');
        $apiKey  = Parametre::get('mtn_api_key');

        if (!$subKey || !$apiUser || !$apiKey) {
            throw new \Exception('Paramètres MTN MoMo incomplets (Subscription Key / API User / API Key).');
        }

        $res = Http::withBasicAuth($apiUser, $apiKey)
            ->withHeaders(['Ocp-Apim-Subscription-Key' => $subKey])
            ->timeout(15)
            ->post($this->baseUrl() . '/collection/token/');

        if (!$res->successful()) {
            throw new \Exception('Authentification MTN MoMo échouée : ' . $res->body());
        }

        return $res->json('access_token');
    }
}
