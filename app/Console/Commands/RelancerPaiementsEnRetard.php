<?php

namespace App\Console\Commands;

use App\Models\Paiement;
use App\Services\RelanceIaService;
use Illuminate\Console\Command;

class RelancerPaiementsEnRetard extends Command
{
    protected $signature = 'paiements:relancer
                            {--dry-run : Simuler sans envoyer ni modifier la base}
                            {--force  : Ignorer l\'intervalle minimal entre relances}';

    protected $description = 'Envoie des relances IA par e-mail pour tous les loyers en retard';

    public function handle(RelanceIaService $service): int
    {
        $dryRun = $this->option('dry-run');
        $force  = $this->option('force');

        $this->info($dryRun ? '[DRY-RUN] Simulation — aucun e-mail ne sera envoyé.' : 'Recherche des paiements en retard…');

        $paiements = Paiement::with('location.bien', 'location.locataire')
            ->where('statut', 'en_attente')
            ->where('date_echeance', '<', today())
            ->get();

        if ($paiements->isEmpty()) {
            $this->info('Aucun paiement en retard trouvé.');
            return Command::SUCCESS;
        }

        $eligibles = $paiements->filter(fn($p) => $force || $service->estEligible($p));

        $this->info("{$paiements->count()} paiement(s) en retard — {$eligibles->count()} éligible(s) à relance.");

        if ($eligibles->isEmpty()) {
            $this->line('Tous les locataires ont déjà été relancés récemment.');
            return Command::SUCCESS;
        }

        $envoyes = 0;
        $ignores = 0;

        foreach ($eligibles as $p) {
            $locataire = $p->location->locataire;
            $email     = $locataire?->email ?? '—';
            $bien      = $p->location->bien->titre ?? '—';
            $retard    = today()->diffInDays($p->date_echeance);
            $montant   = number_format((float) $p->montant, 0, ',', ' ');

            if ($dryRun) {
                $this->line("  [simulation] {$locataire?->name} <{$email}> — {$bien} — {$montant} FCFA — {$retard}j de retard (relance #{$p->nb_relances})");
                $envoyes++;
                continue;
            }

            if (!$email || $email === '—') {
                $this->warn("  [ignoré] Paiement #{$p->id} — pas d'adresse e-mail pour le locataire.");
                $ignores++;
                continue;
            }

            $ok = $service->envoyer($p, $force);

            if ($ok) {
                $this->line("  ✓ Relance #{$p->nb_relances} envoyée → {$email} ({$bien}, {$retard}j de retard)");
                $envoyes++;
            } else {
                $this->warn("  [échec] Paiement #{$p->id} — relance non envoyée.");
                $ignores++;
            }
        }

        $this->newLine();
        $this->info("{$envoyes} relance(s) envoyée(s)" . ($ignores ? ", {$ignores} ignorée(s)." : '.'));

        return Command::SUCCESS;
    }
}
