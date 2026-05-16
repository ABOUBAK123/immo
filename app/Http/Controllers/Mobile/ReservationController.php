<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Parametre;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    // ─── Formulaire de réservation ────────────────────────────────────────────
    public function create(Annonce $annonce, Request $request)
    {
        abort_unless($annonce->estCourtTerme(), 404, 'Cette annonce n\'accepte pas les réservations en ligne.');

        $debut     = $request->debut ?? today()->addDay()->format('Y-m-d');
        $fin       = $request->fin   ?? today()->addDays(2)->format('Y-m-d');
        $voyageurs = max(1, (int) $request->get('voyageurs', 1));

        $nbNuits   = max(1, \Carbon\Carbon::parse($debut)->diffInDays(\Carbon\Carbon::parse($fin)));
        $frais     = round($annonce->prix_nuit * $nbNuits * 0.05, 2); // 5% frais service
        $total     = round($annonce->prix_nuit * $nbNuits + $frais, 2);

        return view('mobile.reserver', compact('annonce', 'debut', 'fin', 'voyageurs', 'nbNuits', 'frais', 'total'));
    }

    // ─── Enregistrement réservation ──────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'annonce_id'  => 'required|exists:annonces,id',
            'nom'         => 'required|string|max:100',
            'prenom'      => 'required|string|max:100',
            'email'       => 'required|email|max:191',
            'telephone'   => 'required|string|max:20',
            'date_debut'  => 'required|date|after_or_equal:today',
            'date_fin'    => 'required|date|after:date_debut',
            'nb_voyageurs'=> 'required|integer|min:1|max:20',
        ]);

        $annonce  = Annonce::findOrFail($data['annonce_id']);
        $debut    = \Carbon\Carbon::parse($data['date_debut']);
        $fin      = \Carbon\Carbon::parse($data['date_fin']);
        $nbNuits  = max(1, $debut->diffInDays($fin));

        // Vérifier disponibilité
        $conflit = Reservation::where('annonce_id', $annonce->id)
            ->whereIn('statut', ['paiement_initie', 'payee', 'confirmee'])
            ->where('date_debut', '<', $data['date_fin'])
            ->where('date_fin', '>', $data['date_debut'])
            ->exists();

        if ($conflit) {
            return back()->withErrors(['date_debut' => 'Ces dates ne sont plus disponibles.'])->withInput();
        }

        $prixNuit = $annonce->prix_nuit;
        $frais    = round($prixNuit * $nbNuits * 0.05, 2);
        $total    = round($prixNuit * $nbNuits + $frais, 2);

        $reservation = Reservation::create([
            'annonce_id'    => $annonce->id,
            'nom'           => $data['nom'],
            'prenom'        => $data['prenom'],
            'email'         => $data['email'],
            'telephone'     => $data['telephone'],
            'date_debut'    => $data['date_debut'],
            'date_fin'      => $data['date_fin'],
            'nb_voyageurs'  => $data['nb_voyageurs'],
            'nb_nuits'      => $nbNuits,
            'prix_nuit'     => $prixNuit,
            'frais_service' => $frais,
            'montant_total' => $total,
        ]);

        return redirect()->route('mobile.paiement', $reservation->token);
    }

    // ─── Page de paiement ─────────────────────────────────────────────────────
    public function paiement(string $token)
    {
        $reservation = Reservation::where('token', $token)
            ->with('annonce.bien')
            ->firstOrFail();

        if ($reservation->statut === 'payee' || $reservation->statut === 'confirmee') {
            return redirect()->route('mobile.confirmation', $token);
        }

        return view('mobile.paiement', compact('reservation'));
    }

    // ─── Initier le paiement ─────────────────────────────────────────────────
    public function initier(Request $request, string $token)
    {
        $reservation = Reservation::where('token', $token)->with('annonce.bien')->firstOrFail();

        $request->validate(['canal' => 'required|in:orange_money,mtn_money,wave,carte,virement']);
        $canal = $request->canal;

        $provider = Parametre::get('paiement_provider', 'cinetpay');
        $apiKey   = Parametre::get('paiement_api_key');

        // Mode simulation si aucune clé configurée
        if (!$apiKey) {
            $reservation->update([
                'canal_paiement'     => $canal,
                'statut'             => 'confirmee',
                'reference_paiement' => 'SIMU_' . strtoupper(Str::random(8)),
            ]);
            return response()->json(['ok' => true, 'url' => null]);
        }

        try {
            if ($provider === 'stripe') {
                $result = $this->initierStripe($reservation);
            } else {
                $result = $this->initierCinetpay($reservation, $canal);
            }

            $reservation->update([
                'canal_paiement'     => $canal,
                'statut'             => 'paiement_initie',
                'reference_paiement' => $result['reference'],
                'payment_url'        => $result['url'],
                'metadata'           => array_merge($reservation->metadata ?? [], ['provider_response' => $result['raw']]),
            ]);

            return response()->json(['ok' => true, 'url' => $result['url']]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── Retour après paiement ────────────────────────────────────────────────
    public function retour(Request $request)
    {
        $token = $request->get('token') ?? $request->get('transaction_id');
        if (!$token) return redirect()->route('mobile.index');

        $reservation = Reservation::where('token', $token)
            ->orWhere('reference_paiement', $token)
            ->first();

        if (!$reservation) return redirect()->route('mobile.index');

        // Vérifier le statut auprès du provider
        $this->verifierPaiement($reservation);

        return redirect()->route('mobile.confirmation', $reservation->token);
    }

    // ─── Webhook de notification ──────────────────────────────────────────────
    public function webhook(Request $request)
    {
        $cpm = $request->get('cpm_trans_id') ?? $request->get('data');
        if (!$cpm) return response('OK');

        $reservation = Reservation::where('reference_paiement', $request->get('cpm_trans_id'))
            ->first();

        if ($reservation) {
            $this->verifierPaiement($reservation);
        }

        return response('OK');
    }

    // ─── Page de confirmation ─────────────────────────────────────────────────
    public function confirmation(string $token)
    {
        $reservation = Reservation::where('token', $token)
            ->with('annonce.bien')
            ->firstOrFail();

        return view('mobile.confirmation', compact('reservation'));
    }

    // ─── Mes réservations (par email) ─────────────────────────────────────────
    public function mesReservations(Request $request)
    {
        $reservations = collect();
        if ($request->filled('email')) {
            $reservations = Reservation::where('email', $request->email)
                ->with('annonce.bien')
                ->latest()
                ->get();
        }
        return view('mobile.mes-reservations', compact('reservations'));
    }

    // ─── CinetPay ─────────────────────────────────────────────────────────────
    private function initierCinetpay(Reservation $r, string $canal): array
    {
        $apiKey  = Parametre::get('paiement_api_key');
        $siteId  = Parametre::get('paiement_site_id');
        $baseUrl = url('/');

        if (!$apiKey || !$siteId) {
            throw new \Exception('Paramètres CinetPay manquants. Configurez les clés dans Administration > Config. APIs.');
        }

        $channelMap = [
            'orange_money' => 'ORANGE_MONEY',
            'mtn_money'    => 'MTN_MONEY',
            'wave'         => 'WAVE',
            'carte'        => 'CREDIT_CARD',
        ];
        $channels = $channelMap[$canal] ?? 'ALL';

        $transactionId = 'RES' . $r->id . '_' . time();

        $payload = [
            'apikey'                  => $apiKey,
            'site_id'                 => $siteId,
            'transaction_id'          => $transactionId,
            'amount'                  => (int) $r->montant_total,
            'currency'                => Parametre::get('paiement_devise', 'XOF'),
            'description'             => 'Réservation — ' . Str::limit($r->annonce->titre, 50),
            'return_url'              => route('mobile.paiement.retour', ['token' => $r->token]),
            'notify_url'              => route('mobile.paiement.webhook'),
            'channels'                => $channels,
            'customer_name'           => $r->prenom,
            'customer_surname'        => $r->nom,
            'customer_email'          => $r->email,
            'customer_phone_number'   => $r->telephone,
            'customer_address'        => $r->annonce->bien->ville ?? '',
            'customer_city'           => $r->annonce->bien->ville ?? '',
            'customer_country'        => 'CI',
            'customer_state'          => 'CI',
            'customer_zip_code'       => '00000',
        ];

        $response = Http::timeout(20)->post('https://api-checkout.cinetpay.com/v2/payment', $payload);

        if (!$response->successful() || $response->json('code') !== '201') {
            $msg = $response->json('message', $response->json('description', 'Erreur CinetPay'));
            throw new \Exception($msg);
        }

        $data = $response->json('data');
        $r->update(['reference_paiement' => $transactionId]);

        return [
            'reference' => $transactionId,
            'url'       => $data['payment_url'],
            'raw'       => $data,
        ];
    }

    // ─── Stripe ───────────────────────────────────────────────────────────────
    private function initierStripe(Reservation $r): array
    {
        $secretKey = Parametre::get('stripe_secret_key');
        if (!$secretKey) {
            throw new \Exception('Clé secrète Stripe manquante. Configurez-la dans Administration > Config. APIs.');
        }

        $response = Http::withBasicAuth($secretKey, '')
            ->timeout(20)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'payment_method_types[]'         => 'card',
                'line_items[0][price_data][currency]'                    => strtolower(Parametre::get('paiement_devise', 'eur')),
                'line_items[0][price_data][product_data][name]'          => $r->annonce->titre,
                'line_items[0][price_data][product_data][description]'   => $r->nb_nuits . ' nuit(s) — ' . $r->date_debut->format('d/m/Y') . ' au ' . $r->date_fin->format('d/m/Y'),
                'line_items[0][price_data][unit_amount]'                 => (int) ($r->montant_total * 100),
                'line_items[0][quantity]'                                => 1,
                'mode'                           => 'payment',
                'success_url'                    => route('mobile.paiement.retour', ['token' => $r->token]),
                'cancel_url'                     => route('mobile.paiement', $r->token),
                'customer_email'                 => $r->email,
                'metadata[reservation_token]'    => $r->token,
            ]);

        if (!$response->successful()) {
            throw new \Exception($response->json('error.message', 'Erreur Stripe'));
        }

        $session = $response->json();

        return [
            'reference' => $session['id'],
            'url'       => $session['url'],
            'raw'       => $session,
        ];
    }

    // ─── Vérification paiement ────────────────────────────────────────────────
    private function verifierPaiement(Reservation $r): void
    {
        $provider = Parametre::get('paiement_provider', 'cinetpay');

        try {
            if ($provider === 'stripe') {
                $response = Http::withBasicAuth(Parametre::get('stripe_secret_key'), '')
                    ->get('https://api.stripe.com/v1/checkout/sessions/' . $r->reference_paiement);
                if ($response->successful() && $response->json('payment_status') === 'paid') {
                    $r->update(['statut' => 'payee']);
                }
            } else {
                $response = Http::timeout(15)->post('https://api-checkout.cinetpay.com/v2/payment/check', [
                    'apikey'         => Parametre::get('paiement_api_key'),
                    'site_id'        => Parametre::get('paiement_site_id'),
                    'transaction_id' => $r->reference_paiement,
                ]);
                $code = $response->json('data.status') ?? $response->json('code');
                if (in_array($code, ['ACCEPTED', '00'])) {
                    $r->update(['statut' => 'payee']);
                }
            }
        } catch (\Exception) {
            // Silently fail — statut inchangé
        }
    }
}
