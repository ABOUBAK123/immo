<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('profil_configs')->insert([
            [
                'role'        => 'proprietaire',
                'module'      => 'depenses',
                'label'       => 'Dépenses',
                'icone'       => 'receipt-cutoff',
                'description' => 'Suivi des dépenses et bénéfice agence',
                'actif'       => true,
                'verrouillee' => false,
                'ordre'       => 9,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('profil_configs')
            ->where('module', 'depenses')
            ->delete();
    }
};
