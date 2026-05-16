<?php

namespace App\Services;

use App\Mail\RelancePaiementMail;
use App\Models\Paiement;
use App\Models\Parametre;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RelanceIaService
{
    // Intervalle minimal entre deux relances (jours)
    private int $intervalJours;

    public function __construct()
    {
        $this->intervalJours = (int) Parametre::get('relance_interval_jours', 7);
    }

    // ─── Vérifier si le paiement est éligible à une relance ──────────────────
    public function estEligible(Paiement $p): bool
    {
        if ($p->statut !== 'en_attente' || !$p->date_echeance->isPast()) {
            return false;
        }

        if (!$p->derniere_relance_at) {
            return true;
        }

        return $p->derniere_relance_at->diffInDays(now()) >= $this->intervalJours;
    }

    // ─── Envoyer une relance (génération IA + email + mise à jour BDD) ────────
    public function envoyer(Paiement $p, bool $force = false): bool
    {
        if (!$force && !$this->estEligible($p)) {
            return false;
        }

        $email = optional($p->location->locataire)->email;
        if (!$email) {
            return false;
        }

        $messageIA = $this->genererMessage($p);

        try {
            Mail::to($email)->send(new RelancePaiementMail($p, $messageIA));

            $p->update([
                'nb_relances'         => $p->nb_relances + 1,
                'derniere_relance_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('RelanceIaService: échec envoi email', [
                'paiement_id' => $p->id,
                'email'       => $email,
                'error'       => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ─── Génération du message par Claude ────────────────────────────────────
    public function genererMessage(Paiement $p): string
    {
        $apiKey = Parametre::get('claude_api_key') ?? config('services.anthropic.key');

        if (!$apiKey) {
            return $this->messageFallback($p);
        }

        $joursRetard = today()->diffInDays($p->date_echeance);
        $locataire   = $p->location->locataire;
        $bien        = $p->location->bien;
        $montant     = number_format((float) $p->montant, 0, ',', ' ');
        $echeance    = $p->date_echeance->isoFormat('D MMMM YYYY');
        $nbRelances  = $p->nb_relances;

        $urgence = match (true) {
            $nbRelances === 0 => 'courtoise et professionnelle (premier rappel bienveillant)',
            $nbRelances === 1 => 'ferme mais respectueuse (deuxième rappel, ton plus direct)',
            default           => 'formelle et pressante (mise en demeure amiable, dernier avertissement avant action)',
        };

        $prompt = <<<PROMPT
Tu es un assistant de gestion immobilière en Côte d'Ivoire. Rédige une relance de loyer impayé en français.

Informations :
- Locataire : {$locataire->name}
- Bien loué : {$bien->titre}, {$bien->adresse}, {$bien->ville}
- Montant dû : {$montant} FCFA
- Date d'échéance initiale : {$echeance}
- Nombre de jours de retard : {$joursRetard} jour(s)
- Nombre de relances déjà envoyées : {$nbRelances}

Consigne : Rédige uniquement le corps du message (sans objet, sans formule d'appel, sans signature). Vouvoie le locataire. Ton : {$urgence}. Entre 3 et 5 paragraphes courts. Mentionne le montant exact et la date d'échéance. Termine par une invitation à régulariser ou à contacter le bailleur en cas de difficulté.
PROMPT;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 700,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]);

            if ($response->successful()) {
                $texte = $response->json('content.0.text');
                if ($texte) {
                    return trim($texte);
                }
            }

            Log::warning('RelanceIaService: réponse Claude invalide', ['status' => $response->status()]);
        } catch (\Exception $e) {
            Log::warning('RelanceIaService: erreur API Claude', ['error' => $e->getMessage()]);
        }

        return $this->messageFallback($p);
    }

    // ─── Message de secours si l'API est indisponible ─────────────────────────
    private function messageFallback(Paiement $p): string
    {
        $joursRetard = today()->diffInDays($p->date_echeance);
        $montant     = number_format((float) $p->montant, 0, ',', ' ');
        $echeance    = $p->date_echeance->isoFormat('D MMMM YYYY');
        $nb          = $p->nb_relances;

        if ($nb === 0) {
            return "Nous vous contactons afin de vous rappeler que votre loyer d'un montant de {$montant} FCFA, dont l'échéance était fixée au {$echeance}, n'a pas encore été réglé à ce jour.\n\nIl s'agit d'un simple rappel de notre part. Si ce paiement a déjà été effectué entre-temps, nous vous remercions de bien vouloir nous en informer en transmettant votre justificatif.\n\nDans le cas contraire, nous vous invitons à régulariser cette situation dans les meilleurs délais. En cas de difficulté, n'hésitez pas à prendre contact avec nous afin de convenir d'un arrangement.";
        }

        if ($nb === 1) {
            return "Malgré notre rappel précédent, nous constatons que votre loyer de {$montant} FCFA, échu le {$echeance}, demeure impayé. Le retard s'élève désormais à {$joursRetard} jour(s).\n\nNous vous demandons de procéder au règlement de cette somme dans un délai de 72 heures à compter de la réception de ce message.\n\nSi vous rencontrez des difficultés financières, nous vous invitons à nous contacter immédiatement afin de trouver ensemble une solution amiable.";
        }

        return "En l'absence de règlement de votre loyer de {$montant} FCFA (échu le {$echeance}), malgré nos relances précédentes, nous vous mettons formellement en demeure de régulariser cette situation sous 48 heures.\n\nPassé ce délai, nous serons contraints d'engager les démarches prévues par le contrat de bail et la législation en vigueur (Loi n° 2018-575 du 13 juin 2018).\n\nNous vous prions de bien vouloir nous contacter d'urgence.";
    }
}
