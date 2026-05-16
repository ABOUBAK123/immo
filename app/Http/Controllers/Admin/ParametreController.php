<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParametreController extends Controller
{
    // ─── Schéma des paramètres par groupe ────────────────────────────────────
    private function schema(): array
    {
        return [
            'sms' => [
                'titre'       => 'SMS',
                'icone'       => 'phone',
                'couleur'     => '#D97706',
                'description' => 'Envoi de SMS via Twilio ou Vonage.',
                'cles_requises' => ['sms_provider', 'sms_api_key', 'sms_from'],
                'champs' => [
                    ['cle' => 'sms_provider',    'label' => 'Fournisseur',           'type' => 'select',   'options' => ['twilio' => 'Twilio', 'vonage' => 'Vonage / Nexmo', 'ovh' => 'OVH SMS'], 'aide' => ''],
                    ['cle' => 'sms_api_key',     'label' => 'API Key / Account SID', 'type' => 'text',     'placeholder' => 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',   'aide' => 'Twilio : Account SID — Vonage : API Key'],
                    ['cle' => 'sms_api_secret',  'label' => 'API Secret / Auth Token','type' => 'password', 'placeholder' => '••••••••••••••••••••••••••••••••',     'aide' => 'Twilio : Auth Token — Vonage : API Secret'],
                    ['cle' => 'sms_from',        'label' => 'Numéro expéditeur',     'type' => 'text',     'placeholder' => '+33600000000',                         'aide' => 'Numéro au format E.164 (+33…)'],
                ],
            ],
            'whatsapp' => [
                'titre'       => 'WhatsApp',
                'icone'       => 'whatsapp',
                'couleur'     => '#059669',
                'description' => 'Envoi de messages WhatsApp via Twilio WhatsApp Business ou Meta Cloud API.',
                'cles_requises' => ['wa_provider', 'wa_api_key', 'wa_from'],
                'champs' => [
                    ['cle' => 'wa_provider',    'label' => 'Fournisseur',              'type' => 'select',   'options' => ['twilio' => 'Twilio WhatsApp', 'meta' => 'Meta Cloud API (WhatsApp Business)'], 'aide' => ''],
                    ['cle' => 'wa_api_key',     'label' => 'API Key / Account SID',   'type' => 'text',     'placeholder' => 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',   'aide' => 'Twilio : Account SID — Meta : Phone Number ID'],
                    ['cle' => 'wa_api_secret',  'label' => 'API Secret / Token',      'type' => 'password', 'placeholder' => '••••••••••••••••••••••••••••••••',     'aide' => 'Twilio : Auth Token — Meta : Access Token permanent'],
                    ['cle' => 'wa_from',        'label' => 'Numéro WhatsApp Business','type' => 'text',     'placeholder' => '+14155238886',                         'aide' => 'Twilio sandbox : +14155238886 — Meta : votre numéro vérifié'],
                    ['cle' => 'wa_meta_token',  'label' => 'Meta Access Token',       'type' => 'password', 'placeholder' => 'EAAxxxxxxx…',                          'aide' => 'Uniquement pour Meta Cloud API — token généré dans Meta for Developers'],
                ],
            ],
            'email' => [
                'titre'       => 'Email / SMTP',
                'icone'       => 'envelope-fill',
                'couleur'     => '#2563EB',
                'description' => 'Configuration SMTP pour l\'envoi d\'emails (quittances, relances, alertes).',
                'cles_requises' => ['mail_host', 'mail_username'],
                'champs' => [
                    ['cle' => 'mail_host',       'label' => 'Serveur SMTP',        'type' => 'text',     'placeholder' => 'smtp.gmail.com',        'aide' => 'Ex : smtp.gmail.com, smtp.sendgrid.net, ssl.smtp.ovh.net'],
                    ['cle' => 'mail_port',       'label' => 'Port',                'type' => 'text',     'placeholder' => '587',                   'aide' => '587 (TLS) ou 465 (SSL) — recommandé : 587'],
                    ['cle' => 'mail_encryption', 'label' => 'Chiffrement',         'type' => 'select',   'options' => ['tls' => 'TLS (recommandé)', 'ssl' => 'SSL', '' => 'Aucun'], 'aide' => ''],
                    ['cle' => 'mail_username',   'label' => 'Identifiant SMTP',    'type' => 'text',     'placeholder' => 'votre@email.com',       'aide' => 'Votre adresse email ou login du compte SMTP'],
                    ['cle' => 'mail_password',   'label' => 'Mot de passe SMTP',   'type' => 'password', 'placeholder' => '••••••••••••••••',      'aide' => 'Gmail : utilisez un mot de passe d\'application (2FA requis)'],
                    ['cle' => 'mail_from_address','label' => 'Email expéditeur',   'type' => 'text',     'placeholder' => 'noreply@immogest.fr',   'aide' => 'Adresse qui apparaîtra dans le champ "De :"'],
                    ['cle' => 'mail_from_name',  'label' => 'Nom expéditeur',      'type' => 'text',     'placeholder' => 'ImmoGest',              'aide' => 'Nom affiché dans le client mail du destinataire'],
                ],
            ],
            'paiement' => [
                'titre'       => 'Paiements Mobile',
                'icone'       => 'phone-fill',
                'couleur'     => '#0891B2',
                'description' => 'Intégration CinetPay (Orange Money, MTN, Wave) et Stripe pour les paiements de réservation en ligne.',
                'cles_requises' => ['paiement_api_key'],
                'champs' => [
                    ['cle' => 'paiement_provider',   'label' => 'Passerelle principale',    'type' => 'select',   'options' => ['cinetpay' => 'CinetPay (Orange Money / MTN / Wave)', 'stripe' => 'Stripe (Carte bancaire)'], 'aide' => 'CinetPay couvre l\'Afrique de l\'Ouest — Stripe pour les cartes Visa/Mastercard'],
                    ['cle' => 'paiement_api_key',     'label' => 'API Key / Clé secrète',   'type' => 'password', 'placeholder' => 'sk_live_... ou votre clé CinetPay', 'aide' => 'CinetPay : Tableau de bord > API Keys — Stripe : Dashboard > Developers > API Keys'],
                    ['cle' => 'paiement_site_id',     'label' => 'Site ID (CinetPay)',       'type' => 'text',     'placeholder' => '12345678', 'aide' => 'Uniquement pour CinetPay — visible dans votre espace développeur'],
                    ['cle' => 'stripe_secret_key',    'label' => 'Clé secrète Stripe',       'type' => 'password', 'placeholder' => 'sk_live_...', 'aide' => 'Uniquement si vous utilisez Stripe en complément'],
                    ['cle' => 'paiement_devise',      'label' => 'Devise',                   'type' => 'select',   'options' => ['XOF' => 'XOF — Franc CFA Ouest', 'XAF' => 'XAF — Franc CFA Centre', 'EUR' => 'EUR — Euro', 'USD' => 'USD — Dollar'], 'aide' => ''],
                    ['cle' => 'paiement_frais_pct',   'label' => 'Frais de service (%)',     'type' => 'text',     'placeholder' => '5', 'aide' => 'Pourcentage ajouté au montant de la réservation (ex : 5 pour 5%)'],
                ],
            ],
            'abonnement' => [
                'titre'       => 'Abonnements',
                'icone'       => 'credit-card-2-front',
                'couleur'     => '#059669',
                'description' => 'Tarification des abonnements mensuels des propriétaires.',
                'cles_requises' => ['abonnement_prix'],
                'champs' => [
                    ['cle' => 'abonnement_prix',   'label' => 'Prix mensuel',              'type' => 'text',   'placeholder' => '5000',  'aide' => 'Montant en unités de la devise choisie'],
                    ['cle' => 'abonnement_devise',  'label' => 'Devise',                   'type' => 'select', 'options' => ['XOF' => 'XOF — Franc CFA Ouest', 'XAF' => 'XAF — Franc CFA Centre', 'EUR' => 'EUR — Euro', 'USD' => 'USD — Dollar'], 'aide' => ''],
                    ['cle' => 'abonnement_essai',   'label' => 'Jours d\'essai gratuit',   'type' => 'text',   'placeholder' => '0',     'aide' => 'Nombre de jours d\'essai offerts à la création du compte (0 = désactivé)'],
                    ['cle' => 'abonnement_duree',   'label' => 'Durée (jours)',             'type' => 'text',   'placeholder' => '30',    'aide' => 'Durée d\'un abonnement en jours (défaut : 30)'],
                ],
            ],
            'ia' => [
                'titre'       => 'Agent IA',
                'icone'       => 'robot',
                'couleur'     => '#7C3AED',
                'description' => 'Configuration de l\'intelligence artificielle pour l\'assistant et la rédaction automatique de messages.',
                'cles_requises' => ['ia_api_key'],
                'champs' => [
                    ['cle' => 'ia_provider', 'label' => 'Fournisseur IA',    'type' => 'select',   'options' => ['anthropic' => 'Anthropic (Claude)', 'openai' => 'OpenAI (ChatGPT)'], 'aide' => 'Choisissez votre fournisseur d\'IA préféré'],
                    ['cle' => 'ia_api_key',  'label' => 'Clé API',           'type' => 'password', 'placeholder' => 'sk-ant-... ou sk-...', 'aide' => 'Anthropic : console.anthropic.com — OpenAI : platform.openai.com'],
                    ['cle' => 'ia_model',    'label' => 'Modèle',            'type' => 'text',     'placeholder' => 'claude-haiku-4-5-20251001', 'aide' => 'Anthropic : claude-haiku-4-5-20251001, claude-sonnet-4-6 — OpenAI : gpt-4o-mini, gpt-4o'],
                ],
            ],

            // ── APIs directes opérateurs mobiles ──────────────────────────────
            'orange_money' => [
                'titre'       => 'Orange Money',
                'icone'       => 'phone-fill',
                'couleur'     => '#FF6B00',
                'description' => 'Intégration directe Orange Money Web Pay (Côte d\'Ivoire, Sénégal, Mali). Évitez les frais CinetPay.',
                'cles_requises' => ['om_client_id', 'om_merchant_key'],
                'champs' => [
                    ['cle' => 'om_client_id',      'label' => 'Client ID',            'type' => 'text',     'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',  'aide' => 'Espace développeur Orange → Mes applications → Client ID'],
                    ['cle' => 'om_client_secret',  'label' => 'Client Secret',        'type' => 'password', 'placeholder' => '••••••••••••••••••••••••••••••',    'aide' => 'Orange Developer Portal → Client Secret'],
                    ['cle' => 'om_merchant_key',   'label' => 'Merchant Key',         'type' => 'password', 'placeholder' => '••••••••••••••••••••••••••••••',    'aide' => 'Clé marchande fournie par Orange Money lors de l\'onboarding'],
                    ['cle' => 'om_notif_token',    'label' => 'Notification Token',   'type' => 'password', 'placeholder' => '••••••••••••••••••••••••••••••',    'aide' => 'Token optionnel pour valider les webhooks entrants'],
                    ['cle' => 'om_currency',       'label' => 'Devise',               'type' => 'select',   'options' => ['ORA' => 'ORA (Orange Money par défaut)', 'XOF' => 'XOF — Franc CFA Ouest', 'XAF' => 'XAF — Franc CFA Centre'], 'aide' => 'ORA est le code devise utilisé par l\'API Orange Money Web Pay'],
                    ['cle' => 'om_qr_code_url',    'label' => 'URL QR Code marchand', 'type' => 'text',     'placeholder' => 'https://...  ou chemin storage/qrcodes/om.png', 'aide' => 'Optionnel — URL de votre QR Code Orange Money affiché sur la page de paiement'],
                ],
            ],

            'mtn_momo' => [
                'titre'       => 'MTN MoMo',
                'icone'       => 'phone-fill',
                'couleur'     => '#FFCC00',
                'description' => 'Intégration directe MTN Mobile Money Collection API (Côte d\'Ivoire, Cameroun, Rwanda, Ghana…).',
                'cles_requises' => ['mtn_subscription_key', 'mtn_api_user', 'mtn_api_key'],
                'champs' => [
                    ['cle' => 'mtn_environment',     'label' => 'Environnement',         'type' => 'select',   'options' => ['sandbox' => 'Sandbox (tests)', 'production' => 'Production'], 'aide' => 'Utilisez Sandbox pendant les tests — basculez en Production pour les vrais paiements'],
                    ['cle' => 'mtn_subscription_key','label' => 'Subscription Key',       'type' => 'password', 'placeholder' => '••••••••••••••••••••••••••••••••', 'aide' => 'MTN Developer Portal → Collections product → Primary/Secondary Key'],
                    ['cle' => 'mtn_api_user',        'label' => 'API User (UUID)',         'type' => 'text',     'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'aide' => 'UUID généré via POST /v1_0/apiuser avec votre Subscription Key'],
                    ['cle' => 'mtn_api_key',         'label' => 'API Key',                'type' => 'password', 'placeholder' => '••••••••••••••••••••••••••••••••', 'aide' => 'Généré via POST /v1_0/apiuser/{X-Reference-Id}/apikey'],
                    ['cle' => 'mtn_currency',        'label' => 'Devise',                 'type' => 'select',   'options' => ['XOF' => 'XOF — Franc CFA Ouest', 'XAF' => 'XAF — Franc CFA Centre', 'GHS' => 'GHS — Cedi Ghana', 'RWF' => 'RWF — Franc Rwanda'], 'aide' => ''],
                    ['cle' => 'mtn_qr_code_url',     'label' => 'URL QR Code marchand',   'type' => 'text',     'placeholder' => 'https://...  ou chemin storage/qrcodes/mtn.png', 'aide' => 'Optionnel — QR Code MTN MoMo affiché en fallback ou en complément'],
                ],
            ],

            'wave' => [
                'titre'       => 'Wave',
                'icone'       => 'water',
                'couleur'     => '#009EE3',
                'description' => 'Intégration directe Wave Checkout API (Sénégal, Côte d\'Ivoire, Mali, Burkina Faso).',
                'cles_requises' => ['wave_api_key'],
                'champs' => [
                    ['cle' => 'wave_api_key',     'label' => 'Clé API Wave',           'type' => 'password', 'placeholder' => '••••••••••••••••••••••••••••••••', 'aide' => 'Tableau de bord Wave Business → Paramètres → Développeurs → Clé API'],
                    ['cle' => 'wave_currency',    'label' => 'Devise',                 'type' => 'select',   'options' => ['XOF' => 'XOF — Franc CFA Ouest', 'XAF' => 'XAF — Franc CFA Centre'], 'aide' => ''],
                    ['cle' => 'wave_qr_code_url', 'label' => 'URL QR Code marchand',  'type' => 'text',     'placeholder' => 'https://... ou chemin storage/qrcodes/wave.png', 'aide' => 'Optionnel — QR Code Wave affiché en fallback ou en complément'],
                ],
            ],
        ];
    }

    public function index(Request $request)
    {
        abort_if(Auth::user()->role !== 'admin', 403);

        $schema   = $this->schema();
        $onglet   = in_array($request->tab, array_keys($schema)) ? $request->tab : 'sms';

        // Charger toutes les valeurs actuelles
        $valeurs = Parametre::whereIn('groupe', array_keys($schema))
            ->get()->keyBy('cle')->map->valeur;

        // Statuts de chaque groupe
        $statuts = [];
        foreach ($schema as $groupe => $cfg) {
            $statuts[$groupe] = Parametre::groupeConfigured($groupe, $cfg['cles_requises']);
        }

        return view('admin.parametres.index', compact('schema', 'onglet', 'valeurs', 'statuts'));
    }

    public function update(Request $request, string $groupe)
    {
        abort_if(Auth::user()->role !== 'admin', 403);

        $schema = $this->schema();
        abort_if(!isset($schema[$groupe]), 404);

        // Sauvegarder chaque champ du groupe
        foreach ($schema[$groupe]['champs'] as $champ) {
            $cle    = $champ['cle'];
            $valeur = $request->input($cle);

            // Ne pas effacer un mot de passe si le champ est vide
            if ($champ['type'] === 'password' && empty($valeur)) {
                continue;
            }

            Parametre::updateOrCreate(
                ['cle' => $cle],
                [
                    'groupe' => $groupe,
                    'valeur' => $valeur,
                    'type'   => $champ['type'],
                    'label'  => $champ['label'],
                ]
            );
        }

        // Sync .env si c'est le groupe email (pour que Mail:: fonctionne immédiatement)
        if ($groupe === 'email') {
            $this->syncEnvEmail($request);
        }

        return redirect()
            ->route('admin.parametres', ['tab' => $groupe])
            ->with('success', 'Configuration ' . strtoupper($groupe) . ' enregistrée.');
    }

    public function test(Request $request, string $groupe)
    {
        abort_if(Auth::user()->role !== 'admin', 403);

        $result = match($groupe) {
            'email'        => $this->testEmail(),
            'sms'          => $this->testSms(),
            'whatsapp'     => $this->testWhatsapp(),
            'ia'           => $this->testIA(),
            'paiement'     => $this->testPaiement(),
            'abonnement'   => ['ok' => true, 'message' => 'Tarif abonnement : ' . \App\Models\Parametre::get('abonnement_prix', '5000') . ' ' . \App\Models\Parametre::get('abonnement_devise', 'XOF') . '/mois'],
            'orange_money' => $this->testOrangeMoney(),
            'mtn_momo'     => $this->testMtnMomo(),
            'wave'         => $this->testWave(),
            default        => ['ok' => false, 'message' => 'Groupe inconnu'],
        };

        return response()->json($result);
    }

    // ─── Sync SMTP vers .env ──────────────────────────────────────────────────
    private function syncEnvEmail(Request $request): void
    {
        $map = [
            'MAIL_HOST'         => $request->mail_host,
            'MAIL_PORT'         => $request->mail_port,
            'MAIL_ENCRYPTION'   => $request->mail_encryption,
            'MAIL_USERNAME'     => $request->mail_username,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            'MAIL_FROM_NAME'    => '"' . ($request->mail_from_name ?? 'ImmoGest') . '"',
        ];
        if ($request->mail_password) {
            $map['MAIL_PASSWORD'] = $request->mail_password;
        }

        $envPath = base_path('.env');
        if (!file_exists($envPath)) return;

        $env = file_get_contents($envPath);
        foreach ($map as $key => $value) {
            $escaped = preg_quote($value, '/');
            if (preg_match("/^{$key}=/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
        }
        file_put_contents($envPath, $env);
    }

    // ─── Tests de connexion ───────────────────────────────────────────────────
    private function testEmail(): array
    {
        try {
            \Illuminate\Support\Facades\Mail::raw('Test ImmoGest — connexion SMTP OK.', function ($m) {
                $m->to(Auth::user()->email)->subject('✅ Test SMTP ImmoGest');
            });
            return ['ok' => true, 'message' => 'Email envoyé à ' . Auth::user()->email];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function testSms(): array
    {
        $provider = Parametre::get('sms_provider', 'twilio');
        $apiKey   = Parametre::get('sms_api_key');
        $secret   = Parametre::get('sms_api_secret');
        $from     = Parametre::get('sms_from');

        if (!$apiKey || !$secret || !$from) {
            return ['ok' => false, 'message' => 'Paramètres SMS incomplets. Remplissez tous les champs requis.'];
        }

        // Simulation — à remplacer par l'appel Twilio/Vonage réel
        return ['ok' => true, 'message' => "Fournisseur : {$provider} — Paramètres enregistrés. Intégrez le SDK {$provider} pour un vrai test d'envoi."];
    }

    private function testWhatsapp(): array
    {
        $provider = Parametre::get('wa_provider', 'twilio');
        $apiKey   = Parametre::get('wa_api_key');
        $from     = Parametre::get('wa_from');

        if (!$apiKey || !$from) {
            return ['ok' => false, 'message' => 'Paramètres WhatsApp incomplets. Remplissez tous les champs requis.'];
        }

        return ['ok' => true, 'message' => "Fournisseur : {$provider} — Paramètres enregistrés. Intégrez le SDK {$provider} pour un vrai test d'envoi."];
    }

    private function testPaiement(): array
    {
        $provider = Parametre::get('paiement_provider', 'cinetpay');
        $apiKey   = Parametre::get('paiement_api_key');
        $siteId   = Parametre::get('paiement_site_id');

        if (!$apiKey) {
            return ['ok' => false, 'message' => 'Clé API paiement manquante.'];
        }

        if ($provider === 'cinetpay' && !$siteId) {
            return ['ok' => false, 'message' => 'Site ID CinetPay manquant.'];
        }

        if ($provider === 'stripe') {
            try {
                $res = \Illuminate\Support\Facades\Http::withBasicAuth($apiKey, '')
                    ->timeout(10)->get('https://api.stripe.com/v1/balance');
                if (!$res->successful()) {
                    return ['ok' => false, 'message' => $res->json('error.message', 'Clé Stripe invalide')];
                }
                return ['ok' => true, 'message' => 'Connexion Stripe OK — Devise : ' . ($res->json('available.0.currency') ?? 'eur')];
            } catch (\Exception $e) {
                return ['ok' => false, 'message' => $e->getMessage()];
            }
        }

        return ['ok' => true, 'message' => "CinetPay — Clé API et Site ID renseignés. Prêt à accepter Orange Money, MTN, Wave, Carte."];
    }

    private function testOrangeMoney(): array
    {
        $clientId     = Parametre::get('om_client_id');
        $clientSecret = Parametre::get('om_client_secret');
        $merchantKey  = Parametre::get('om_merchant_key');

        if (!$clientId || !$clientSecret || !$merchantKey) {
            return ['ok' => false, 'message' => 'Paramètres Orange Money incomplets (Client ID, Client Secret, Merchant Key requis).'];
        }

        try {
            $res = \Illuminate\Support\Facades\Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()->timeout(10)
                ->post('https://api.orange.com/oauth/v3/token', ['grant_type' => 'client_credentials']);

            if (!$res->successful() || !$res->json('access_token')) {
                return ['ok' => false, 'message' => 'Authentification Orange Money échouée : ' . ($res->json('error_description') ?? $res->body())];
            }
            return ['ok' => true, 'message' => 'Connexion Orange Money OK — Token obtenu. Devise : ' . Parametre::get('om_currency', 'ORA')];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function testMtnMomo(): array
    {
        $subKey  = Parametre::get('mtn_subscription_key');
        $apiUser = Parametre::get('mtn_api_user');
        $apiKey  = Parametre::get('mtn_api_key');
        $env     = Parametre::get('mtn_environment', 'sandbox');

        if (!$subKey || !$apiUser || !$apiKey) {
            return ['ok' => false, 'message' => 'Paramètres MTN MoMo incomplets (Subscription Key, API User, API Key requis).'];
        }

        $baseUrl = $env === 'production'
            ? 'https://proxy.momoapi.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';

        try {
            $res = \Illuminate\Support\Facades\Http::withBasicAuth($apiUser, $apiKey)
                ->withHeaders(['Ocp-Apim-Subscription-Key' => $subKey])
                ->timeout(10)
                ->post($baseUrl . '/collection/token/');

            if (!$res->successful() || !$res->json('access_token')) {
                return ['ok' => false, 'message' => 'Authentification MTN MoMo échouée : ' . $res->body()];
            }
            return ['ok' => true, 'message' => "Connexion MTN MoMo OK — Token obtenu. Environnement : {$env}. Devise : " . Parametre::get('mtn_currency', 'XOF')];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function testWave(): array
    {
        $apiKey = Parametre::get('wave_api_key');

        if (!$apiKey) {
            return ['ok' => false, 'message' => 'Clé API Wave manquante.'];
        }

        try {
            // Wave n'a pas d'endpoint de test dédié — on vérifie juste que la clé est acceptée
            $res = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(10)
                ->get('https://api.wave.com/v1/checkout/sessions');

            // 200 ou 404 = clé valide (404 = session non trouvée, pas erreur auth)
            if ($res->status() === 401 || $res->status() === 403) {
                return ['ok' => false, 'message' => 'Clé API Wave invalide ou non autorisée.'];
            }
            return ['ok' => true, 'message' => 'Connexion Wave OK — Clé API acceptée. Devise : ' . Parametre::get('wave_currency', 'XOF')];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function testIA(): array
    {
        $provider = Parametre::get('ia_provider', 'anthropic');
        $apiKey   = Parametre::get('ia_api_key');
        $model    = Parametre::get('ia_model', $provider === 'openai' ? 'gpt-4o-mini' : 'claude-haiku-4-5-20251001');

        if (!$apiKey) {
            return ['ok' => false, 'message' => 'Clé API IA manquante.'];
        }

        try {
            if ($provider === 'openai') {
                $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                    ->timeout(15)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model'    => $model,
                        'messages' => [['role' => 'user', 'content' => 'Dis juste "OK" en un mot.']],
                        'max_tokens' => 10,
                    ]);
                if (!$response->successful()) {
                    return ['ok' => false, 'message' => $response->json('error.message', 'Erreur OpenAI')];
                }
                $reply = $response->json('choices.0.message.content', '');
            } else {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'x-api-key' => $apiKey, 'anthropic-version' => '2023-06-01',
                ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
                    'model'      => $model,
                    'max_tokens' => 10,
                    'messages'   => [['role' => 'user', 'content' => 'Dis juste "OK" en un mot.']],
                ]);
                if (!$response->successful()) {
                    return ['ok' => false, 'message' => $response->json('error.message', 'Erreur Anthropic')];
                }
                $reply = $response->json('content.0.text', '');
            }
            return ['ok' => true, 'message' => "Connexion {$provider} réussie — Modèle : {$model} — Réponse : {$reply}"];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
