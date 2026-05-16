<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Biens
            'biens.voir', 'biens.creer', 'biens.modifier', 'biens.supprimer',
            // Locations
            'locations.voir', 'locations.creer', 'locations.modifier', 'locations.supprimer',
            // Paiements
            'paiements.voir', 'paiements.creer', 'paiements.modifier',
            // Quittances
            'quittances.generer', 'quittances.telecharger',
            // Annonces
            'annonces.voir', 'annonces.creer', 'annonces.modifier', 'annonces.supprimer',
            // Interventions
            'interventions.voir', 'interventions.creer', 'interventions.modifier',
            // Documents
            'documents.voir', 'documents.uploader', 'documents.supprimer',
            // Messages
            'messages.envoyer', 'messages.voir',
            // Admin
            'users.gerer', 'stats.voir',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $roles = [
            'admin' => $permissions,
            'proprietaire' => [
                'biens.voir', 'biens.creer', 'biens.modifier', 'biens.supprimer',
                'locations.voir', 'locations.creer', 'locations.modifier',
                'paiements.voir', 'paiements.creer',
                'quittances.generer', 'quittances.telecharger',
                'annonces.voir', 'annonces.creer', 'annonces.modifier',
                'interventions.voir', 'interventions.modifier',
                'documents.voir', 'documents.uploader', 'documents.supprimer',
                'messages.envoyer', 'messages.voir',
                'stats.voir',
            ],
            'locataire' => [
                'biens.voir',
                'locations.voir',
                'paiements.voir',
                'quittances.telecharger',
                'interventions.voir', 'interventions.creer',
                'documents.voir', 'documents.uploader',
                'messages.envoyer', 'messages.voir',
            ],
            'agent' => [
                'biens.voir',
                'annonces.voir', 'annonces.creer', 'annonces.modifier',
                'documents.voir', 'documents.uploader',
                'messages.envoyer', 'messages.voir',
                'stats.voir',
            ],
            'acheteur' => [
                'annonces.voir',
                'messages.envoyer', 'messages.voir',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
