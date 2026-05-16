<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter le module "Mes règlements" pour le locataire
        DB::table('profil_configs')->insert([
            'role'        => 'locataire',
            'module'      => 'mes_reglements',
            'label'       => 'Mes règlements',
            'icone'       => 'wallet2',
            'description' => 'Consulter l\'historique complet des paiements et des règlements',
            'actif'       => true,
            'verrouillee' => false,
            'ordre'       => 5,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Vider le cache du profil locataire
        \Illuminate\Support\Facades\Cache::forget("profil_config_locataire");
    }

    public function down(): void
    {
        DB::table('profil_configs')->where('role', 'locataire')->where('module', 'mes_reglements')->delete();
        \Illuminate\Support\Facades\Cache::forget("profil_config_locataire");
    }
};
