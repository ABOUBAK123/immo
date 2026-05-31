<?php

namespace Database\Seeders;

use App\Models\FormuleAbonnement;
use Illuminate\Database\Seeder;

class FormuleAbonnementSeeder extends Seeder
{
    public function run(): void
    {
        $formules = [
            [
                'nom'                   => 'Starter',
                'slug'                  => 'starter',
                'description'           => 'Idéal pour débuter la gestion locative. Gérez vos premiers biens facilement.',
                'couleur'               => '#6B7280',
                'icone'                 => 'bi-house',
                'populaire'             => false,
                'prix_mensuel'          => 5000,
                'prix_annuel'           => 50000,
                'devise'                => 'XOF',
                'duree_jours'           => 30,
                'max_biens'             => 3,
                'max_locataires'        => 10,
                'max_agents'            => 0,
                'max_annonces'          => 0,
                'has_interventions'     => false,
                'has_annonces'          => false,
                'has_depenses'          => false,
                'has_ia'                => false,
                'has_agents'            => false,
                'has_documents'         => true,
                'has_export_pdf'        => true,
                'has_notifications_sms' => false,
                'has_api_access'        => false,
                'support_prioritaire'   => false,
                'is_active'             => true,
                'ordre'                 => 1,
            ],
            [
                'nom'                   => 'Pro',
                'slug'                  => 'pro',
                'description'           => 'Pour les propriétaires actifs. Toutes les fonctionnalités essentielles débloquées.',
                'couleur'               => '#EA580C',
                'icone'                 => 'bi-buildings',
                'populaire'             => true,
                'prix_mensuel'          => 15000,
                'prix_annuel'           => 150000,
                'devise'                => 'XOF',
                'duree_jours'           => 30,
                'max_biens'             => 15,
                'max_locataires'        => 50,
                'max_agents'            => 0,
                'max_annonces'          => 10,
                'has_interventions'     => true,
                'has_annonces'          => true,
                'has_depenses'          => false,
                'has_ia'                => false,
                'has_agents'            => false,
                'has_documents'         => true,
                'has_export_pdf'        => true,
                'has_notifications_sms' => true,
                'has_api_access'        => false,
                'support_prioritaire'   => false,
                'is_active'             => true,
                'ordre'                 => 2,
            ],
            [
                'nom'                   => 'Premium',
                'slug'                  => 'premium',
                'description'           => 'Pour les agences et grands propriétaires. Tout illimité avec IA et agents.',
                'couleur'               => '#7C3AED',
                'icone'                 => 'bi-gem',
                'populaire'             => false,
                'prix_mensuel'          => 30000,
                'prix_annuel'           => 300000,
                'devise'                => 'XOF',
                'duree_jours'           => 30,
                'max_biens'             => -1,
                'max_locataires'        => -1,
                'max_agents'            => -1,
                'max_annonces'          => -1,
                'has_interventions'     => true,
                'has_annonces'          => true,
                'has_depenses'          => true,
                'has_ia'                => true,
                'has_agents'            => true,
                'has_documents'         => true,
                'has_export_pdf'        => true,
                'has_notifications_sms' => true,
                'has_api_access'        => true,
                'support_prioritaire'   => true,
                'is_active'             => true,
                'ordre'                 => 3,
            ],
        ];

        foreach ($formules as $data) {
            FormuleAbonnement::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
