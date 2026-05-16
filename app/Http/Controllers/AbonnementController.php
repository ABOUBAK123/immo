<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Parametre;
use App\Models\User;
use App\Services\MtnMomoService;
use App\Services\OrangeMoneyService;
use App\Services\WaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AbonnementController extends Controller
{
    // ─── Vue abonnement propriétaire ─────────────────────────────────────────
    public function index()
    {
        $user            = Auth::user();
        $abonnementActif = $user->abonnementActif();
        $historique      = $user->abonnements()->latest()->take(12)->get();
        [$prix, $devise, $devSymbole] = $this->tarif();

        // Infos pour la vue : APIs directes configurées + QR codes
        $operateursDirects = [
            'orange_money' => OrangeMoneyService::estConfigured(),
            'mtn_money'    => MtnMomoService::estConfigured(),
            'wave'         => WaveService::estConfigured(),
        ];
        $qrCodes = [
            'orange_money' => Parametre::get('om_qr_code_url'),
            'mtn_money'    => Parametre::get('mtn_qr_code_url'),
            'wave'         => Parametre::get('wave_qr_code_url'),
        ];

        return view('abonnements.index', compact(
            'abonnementActif', 'historique', 'prix', 'devise', 'devSymbole',
            'operateursDirects', 'qrCodes'
        ));
    }

    // ─── Initier le paiement d'abonnement ────────────────────────────────────
    public function initier(Request $request): JsonResponse
    {
        $user = Auth::user();
        abort_if(!in_array($user->role, ['proprietaire', 'admin']), 403);

        $request->validate([
            'canal'     => 'required|in:orange_money,mtn_money,wave,carte',
            'telephone' => 'nullable|string|max:25',
        ]);

        [$prix, $devise] = $this->tarif();
        $canal = $request->canal;

        $abonnement = Abonnement::create([
            'user_id'        => $user->id,
            'montant'        => $prix,
            'devise'         => $devise,
            'date_debut'     => now()->toDateString(),
            'date_fin'       => now()->addDays((int) Parametre::get('abonnement_duree', 30))->toDateString(),
            'statut'         => 'en_attente',
            'canal_paiement' => $canal,
            'payment_token'  => Str::random(48),
            'invoice_number' => Abonnement::genererNumeroFacture(),
        ]);

        // ── Mode simulation (aucune API configurée) ────────────────────────────
        $apiKey       = Parametre::get('paiement_api_key');
        $hasDirectApi = $this->hasDirectApiForCanal($canal);

        if (!$apiKey && !$hasDirectApi) {
            $abonnement->update([
                'statut'             => 'actif',
                'methode_paiement'   => $canal === 'carte' ? 'carte' : 'mobile_money',
                'provider_reference' => 'SIMU_' . strtoupper(Str::random(8)),
            ]);
            return response()->json(['ok' => true, 'simulation' => true]);
        }

        // ── APIs directes opérateurs ───────────────────────────────────────────
        try {
            // Orange Money direct
            if ($canal === 'orange_money' && OrangeMoneyService::estConfigured()) {
                $result = (new OrangeMoneyService())->initierPaiement(
                    (int) $prix,
                    'ABO_' . $abonnement->id,
                    route('abonnements.retour', ['payment_token' => $abonnement->payment_token]),
                    route('webhooks.orange-money'),
                );
                $abonnement->update(['provider_reference' => $result['reference'], 'payment_url' => $result['url']]);
                return response()->json(['ok' => true, 'url' => $result['url']]);
            }

            // MTN MoMo direct (USSD push — nécessite le numéro de téléphone)
            if ($canal === 'mtn_money' && MtnMomoService::estConfigured()) {
                $telephone = trim($request->telephone ?: ($user->phone ?? ''));
                if (!$telephone) {
                    $abonnement->delete();
                    return response()->json(['ok' => false, 'message' => 'Numéro de téléphone requis pour MTN Mobile Money.'], 422);
                }
                $referenceId = (new MtnMomoService())->initierPaiement(
                    $telephone,
                    (int) $prix,
                    'ABO_' . $abonnement->id,
                    'Abonnement ImmoGest',
                );
                $abonnement->update(['provider_reference' => $referenceId]);
                return response()->json(['ok' => true, 'ussd' => true, 'reference' => $referenceId]);
            }

            // Wave direct
            if ($canal === 'wave' && WaveService::estConfigured()) {
                $result = (new WaveService())->initierPaiement(
                    (int) $prix,
                    route('abonnements.retour', ['payment_token' => $abonnement->payment_token]),
                    route('abonnements.index'),
                    route('webhooks.wave'),
                );
                $abonnement->update(['provider_reference' => $result['reference'], 'payment_url' => $result['url']]);
                return response()->json(['ok' => true, 'url' => $result['url']]);
            }

            // Fallback CinetPay / Stripe
            if ($apiKey) {
                $provider = Parametre::get('paiement_provider', 'cinetpay');
                $result   = $provider === 'stripe'
                    ? $this->initierStripe($abonnement)
                    : $this->initierCinetpay($abonnement, $canal);

                $abonnement->update([
                    'payment_url'        => $result['url'],
                    'provider_reference' => $result['reference'],
                ]);
                return response()->json(['ok' => true, 'url' => $result['url']]);
            }

            throw new \Exception('Aucune passerelle de paiement configurée pour ce canal.');

        } catch (\Exception $e) {
            $abonnement->delete();
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── Polling statut MTN MoMo (appelé par le frontend toutes les 5s) ───────
    public function pollStatut(Request $request, string $reference): JsonResponse
    {
        $abonnement = Abonnement::where('provider_reference', $reference)
            ->where('user_id', Auth::id())
            ->first();

        if (!$abonnement) {
            return response()->json(['statut' => 'NOT_FOUND'], 404);
        }

        if ($abonnement->statut === 'actif') {
            return response()->json(['statut' => 'SUCCESSFUL']);
        }

        if (MtnMomoService::estConfigured()) {
            $statut = (new MtnMomoService())->verifierStatut($reference);

            if ($statut === 'SUCCESSFUL') {
                $abonnement->update(['statut' => 'actif', 'methode_paiement' => 'mobile_money']);
            } elseif ($statut === 'FAILED') {
                $abonnement->delete();
            }

            return response()->json(['statut' => $statut]);
        }

        return response()->json(['statut' => 'PENDING']);
    }

    // ─── Retour depuis la passerelle ──────────────────────────────────────────
    public function retour(Request $request)
    {
        $abonnement = Abonnement::where('payment_token', $request->get('payment_token'))->first();
        if ($abonnement) {
            $this->verifier($abonnement);
        }
        return redirect()->route('abonnements.index')
            ->with('success', 'Paiement traité. Bienvenue sur ImmoGest !');
    }

    // ─── Webhook CinetPay (existant) ──────────────────────────────────────────
    public function webhook(Request $request): Response
    {
        $ref = $request->get('cpm_trans_id') ?? $request->get('transaction_id');
        if ($ref) {
            $abonnement = Abonnement::where('provider_reference', $ref)->first();
            if ($abonnement) $this->verifier($abonnement);
        }
        return response('OK');
    }

    // ─── Webhook Orange Money ─────────────────────────────────────────────────
    public function webhookOrangeMoney(Request $request): Response
    {
        $data    = $request->all();
        $orderId = $data['order_id'] ?? $data['pay_token'] ?? null;

        if ($orderId && OrangeMoneyService::verifierWebhook($data)) {
            $abonnement = Abonnement::where('provider_reference', $orderId)
                ->orWhere(fn($q) => $q->where('canal_paiement', 'orange_money')
                    ->where('statut', 'en_attente')
                    ->where('provider_reference', 'like', 'ABO_' . '%')
                )
                ->first();

            if (!$abonnement) {
                // Chercher par order_id qui correspond à ABO_{id}
                if (preg_match('/ABO_(\d+)/', (string)$orderId, $m)) {
                    $abonnement = Abonnement::find($m[1]);
                }
            }

            if ($abonnement && $abonnement->statut === 'en_attente') {
                $abonnement->update(['statut' => 'actif', 'methode_paiement' => 'mobile_money']);
            }
        }
        return response('OK');
    }

    // ─── Webhook MTN MoMo ─────────────────────────────────────────────────────
    public function webhookMtnMomo(Request $request): Response
    {
        $referenceId = $request->get('referenceId')
            ?? $request->header('X-Reference-Id')
            ?? null;

        if ($referenceId) {
            $abonnement = Abonnement::where('provider_reference', $referenceId)->first();
            if ($abonnement && $abonnement->statut === 'en_attente') {
                $statut = $request->get('status');
                if ($statut === 'SUCCESSFUL') {
                    $abonnement->update(['statut' => 'actif', 'methode_paiement' => 'mobile_money']);
                } elseif ($statut === 'FAILED') {
                    $abonnement->delete();
                }
            }
        }
        return response('OK');
    }

    // ─── Webhook Wave ─────────────────────────────────────────────────────────
    public function webhookWave(Request $request): Response
    {
        $data      = $request->all();
        $sessionId = $data['id'] ?? null;

        if ($sessionId && WaveService::verifierWebhook($data)) {
            $abonnement = Abonnement::where('provider_reference', $sessionId)->first();
            if ($abonnement && $abonnement->statut === 'en_attente') {
                $abonnement->update(['statut' => 'actif', 'methode_paiement' => 'mobile_money']);
            }
        }
        return response('OK');
    }

    // ─── Admin : liste des abonnements ────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        abort_if(Auth::user()->role !== 'admin', 403);

        $statut = $request->statut;

        $abonnements = Abonnement::with('user')
            ->when($statut, fn($q) => $q->where('statut', $statut))
            ->latest()->paginate(25)->withQueryString();

        $stats = [
            'actifs'          => Abonnement::where('statut', 'actif')->where('date_fin', '>=', now())->count(),
            'expires'         => Abonnement::where('statut', 'actif')->where('date_fin', '<', now())->count(),
            'en_attente'      => Abonnement::where('statut', 'en_attente')->count(),
            'revenu_mensuel'  => Abonnement::where('statut', 'actif')
                ->whereMonth('date_debut', now()->month)->sum('montant'),
            'revenu_total'    => Abonnement::where('statut', 'actif')->sum('montant'),
            'sans_abonnement' => User::where('role', 'proprietaire')
                ->whereDoesntHave('abonnements', fn($q) => $q->where('statut', 'actif')->where('date_fin', '>=', now()))
                ->count(),
        ];

        [$prix, $devise, $devSymbole] = $this->tarif();

        return view('admin.abonnements.index', compact('abonnements', 'stats', 'prix', 'devise', 'devSymbole'));
    }

    // ─── Admin : offrir un essai gratuit ─────────────────────────────────────
    public function offrirEssai(Request $request, User $user)
    {
        abort_if(Auth::user()->role !== 'admin', 403);
        abort_if($user->role !== 'proprietaire', 422);

        $jours    = (int) $request->input('jours', 30);
        $existant = $user->abonnementActif();
        $debut    = $existant ? $existant->date_fin->addDay() : now();

        Abonnement::create([
            'user_id'          => $user->id,
            'montant'          => 0,
            'devise'           => Parametre::get('abonnement_devise', 'XOF'),
            'date_debut'       => $debut->toDateString(),
            'date_fin'         => $debut->copy()->addDays($jours)->toDateString(),
            'statut'           => 'actif',
            'methode_paiement' => 'essai_admin',
            'invoice_number'   => Abonnement::genererNumeroFacture(),
            'essai'            => true,
        ]);

        return back()->with('success', "Essai de {$jours} jours accordé à {$user->name}.");
    }

    // ─── Helpers privés ──────────────────────────────────────────────────────

    private function tarif(): array
    {
        $prix       = (int) Parametre::get('abonnement_prix', 5000);
        $devise     = Parametre::get('abonnement_devise', 'XOF');
        $devSymbole = User::DEVISES[$devise]['symbole'] ?? $devise;
        return [$prix, $devise, $devSymbole];
    }

    private function hasDirectApiForCanal(string $canal): bool
    {
        return match($canal) {
            'orange_money' => OrangeMoneyService::estConfigured(),
            'mtn_money'    => MtnMomoService::estConfigured(),
            'wave'         => WaveService::estConfigured(),
            default        => false,
        };
    }

    private function verifier(Abonnement $a): void
    {
        $provider = Parametre::get('paiement_provider', 'cinetpay');
        try {
            $paye = false;
            if ($provider === 'stripe') {
                $r    = Http::withBasicAuth(Parametre::get('stripe_secret_key'), '')
                    ->get('https://api.stripe.com/v1/checkout/sessions/' . $a->provider_reference);
                $paye = $r->successful() && $r->json('payment_status') === 'paid';
            } else {
                $r    = Http::timeout(15)->post('https://api-checkout.cinetpay.com/v2/payment/check', [
                    'apikey'         => Parametre::get('paiement_api_key'),
                    'site_id'        => Parametre::get('paiement_site_id'),
                    'transaction_id' => $a->provider_reference,
                ]);
                $code = $r->json('data.status') ?? $r->json('code');
                $paye = in_array($code, ['ACCEPTED', '00']);
            }
            if ($paye) {
                $methode = $provider === 'stripe' ? 'carte' : ($a->canal_paiement === 'carte' ? 'carte' : 'mobile_money');
                $a->update(['statut' => 'actif', 'methode_paiement' => $methode]);
            }
        } catch (\Exception) {}
    }

    private function initierCinetpay(Abonnement $a, string $canal): array
    {
        $apiKey = Parametre::get('paiement_api_key');
        $siteId = Parametre::get('paiement_site_id');
        if (!$apiKey || !$siteId) throw new \Exception('Clés CinetPay manquantes.');

        $channelMap = [
            'orange_money' => 'ORANGE_MONEY',
            'mtn_money'    => 'MTN_MONEY',
            'wave'         => 'WAVE',
            'carte'        => 'CREDIT_CARD',
        ];

        $transId  = 'ABO_' . $a->id . '_' . time();
        $user     = $a->user;
        $response = Http::timeout(20)->post('https://api-checkout.cinetpay.com/v2/payment', [
            'apikey'                => $apiKey,
            'site_id'               => $siteId,
            'transaction_id'        => $transId,
            'amount'                => (int) $a->montant,
            'currency'              => $a->devise,
            'description'           => 'Abonnement ImmoGest — ' . now()->isoFormat('MMMM YYYY'),
            'return_url'            => route('abonnements.retour', ['payment_token' => $a->payment_token]),
            'notify_url'            => route('abonnements.webhook'),
            'channels'              => $channelMap[$canal] ?? 'ALL',
            'customer_name'         => explode(' ', $user->name)[0] ?? '',
            'customer_surname'      => explode(' ', $user->name)[1] ?? '',
            'customer_email'        => $user->email,
            'customer_phone_number' => $user->phone ?? '',
            'customer_address'      => '',
            'customer_city'         => '',
            'customer_country'      => 'CI',
            'customer_state'        => 'CI',
            'customer_zip_code'     => '00000',
        ]);

        if (!$response->successful() || $response->json('code') !== '201') {
            throw new \Exception($response->json('message', 'Erreur CinetPay'));
        }

        $a->update(['provider_reference' => $transId]);
        return ['reference' => $transId, 'url' => $response->json('data.payment_url')];
    }

    private function initierStripe(Abonnement $a): array
    {
        $secretKey = Parametre::get('stripe_secret_key');
        if (!$secretKey) throw new \Exception('Clé Stripe manquante.');

        $response = Http::withBasicAuth($secretKey, '')->timeout(20)->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'payment_method_types[]'                               => 'card',
                'line_items[0][price_data][currency]'                  => strtolower($a->devise),
                'line_items[0][price_data][product_data][name]'        => 'Abonnement ImmoGest',
                'line_items[0][price_data][product_data][description]' => now()->isoFormat('MMMM YYYY'),
                'line_items[0][price_data][unit_amount]'               => (int) ($a->montant * 100),
                'line_items[0][quantity]'                              => 1,
                'mode'                                                 => 'payment',
                'success_url'                                          => route('abonnements.retour', ['payment_token' => $a->payment_token]),
                'cancel_url'                                           => route('abonnements.index'),
                'customer_email'                                       => $a->user->email,
            ]);

        if (!$response->successful()) {
            throw new \Exception($response->json('error.message', 'Erreur Stripe'));
        }

        $session = $response->json();
        return ['reference' => $session['id'], 'url' => $session['url']];
    }
}
