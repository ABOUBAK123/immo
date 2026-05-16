<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@immo.fr'],
            [
                'name'     => 'Administrateur',
                'phone'    => '+33600000000',
                'role'     => 'admin',
                'password' => Hash::make('Admin@2024'),
            ]
        );
        $admin->assignRole('admin');

        $proprietaire = User::firstOrCreate(
            ['email' => 'proprietaire@immo.fr'],
            [
                'name'     => 'Jean Dupont',
                'phone'    => '+33611111111',
                'role'     => 'proprietaire',
                'password' => Hash::make('Prop@2024'),
            ]
        );
        $proprietaire->assignRole('proprietaire');

        $locataire = User::firstOrCreate(
            ['email' => 'locataire@immo.fr'],
            [
                'name'     => 'Marie Martin',
                'phone'    => '+33622222222',
                'role'     => 'locataire',
                'password' => Hash::make('Loc@2024'),
            ]
        );
        $locataire->assignRole('locataire');

        $agent = User::firstOrCreate(
            ['email' => 'agent@immo.fr'],
            [
                'name'     => 'Pierre Bernard',
                'phone'    => '+33633333333',
                'role'     => 'agent',
                'password' => Hash::make('Agent@2024'),
            ]
        );
        $agent->assignRole('agent');
    }
}
