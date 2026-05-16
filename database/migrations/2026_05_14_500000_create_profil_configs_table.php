<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_configs', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['proprietaire', 'locataire', 'agent', 'acheteur']);
            $table->string('module', 60);
            $table->string('label', 120);
            $table->string('icone', 60)->default('circle');
            $table->string('description', 255)->nullable();
            $table->boolean('actif')->default(true);
            $table->boolean('verrouillee')->default(false); // module obligatoire non désactivable
            $table->unsignedTinyInteger('ordre')->default(0);
            $table->unique(['role', 'module']);
            $table->timestamps();
        });

        $rows = [
            // ── Propriétaire ────────────────────────────────────────────────
            ['proprietaire','biens',         'Mes biens',           'buildings',           'Gestion du patrimoine immobilier',            true,  false, 1],
            ['proprietaire','locataires',    'Locataires',          'people',              'Liste et fiches des locataires',              true,  false, 2],
            ['proprietaire','locations',     'Locations / Baux',    'file-earmark-text',   'Contrats de bail et suivi',                   true,  false, 3],
            ['proprietaire','paiements',     'Paiements',           'wallet2',             'Encaissements et historique des loyers',      true,  false, 4],
            ['proprietaire','interventions', 'Interventions',       'tools',               'Demandes de maintenance et travaux',          true,  false, 5],
            ['proprietaire','notifications', 'Notifications',       'bell',                'SMS, WhatsApp et notifications email',        true,  false, 6],
            ['proprietaire','agent_ia',      'Agent IA',            'robot',               'Assistant IA pour rédaction et conseils',     true,  false, 7],
            ['proprietaire','annonces',      'Publier un bien',     'megaphone',           'Publication d\'annonces sur la marketplace',  true,  false, 8],
            // ── Locataire ────────────────────────────────────────────────────
            ['locataire',   'location',      'Mon bail',            'house-check',         'Consulter son contrat de location actuel',    true,  true,  1],
            ['locataire',   'paiements',     'Mes paiements',       'receipt',             'Historique et prochaines échéances',          true,  false, 2],
            ['locataire',   'interventions', 'Déclarer travaux',    'tools',               'Signaler un problème ou une panne',           true,  false, 3],
            ['locataire',   'notifications', 'Notifications',       'bell',                'Recevoir des alertes et rappels',             true,  false, 4],
            // ── Agent immobilier ─────────────────────────────────────────────
            ['agent',       'mes_annonces',  'Mes annonces',        'grid-3x3-gap',        'Gérer les biens publiés',                     true,  true,  1],
            ['agent',       'publier',       'Publier un bien',     'plus-square',         'Créer et mettre en ligne un nouveau bien',    true,  false, 2],
            ['agent',       'notifications', 'Notifications',       'bell',                'Alertes et messages entrants',                true,  false, 3],
            ['agent',       'agent_ia',      'Agent IA',            'robot',               'Aide à la rédaction d\'annonces',             true,  false, 4],
            // ── Client / Acheteur ─────────────────────────────────────────────
            ['acheteur',    'marketplace',   'Annonces',            'search',              'Consulter les biens disponibles',             true,  true,  1],
            ['acheteur',    'notifications', 'Notifications',       'bell',                'Alertes de nouveaux biens correspondants',    true,  false, 2],
        ];

        foreach ($rows as [$role,$module,$label,$icone,$desc,$actif,$verrou,$ordre]) {
            DB::table('profil_configs')->insert([
                'role'        => $role,
                'module'      => $module,
                'label'       => $label,
                'icone'       => $icone,
                'description' => $desc,
                'actif'       => $actif,
                'verrouillee' => $verrou,
                'ordre'       => $ordre,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_configs');
    }
};
