<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->string('canal_paiement', 30)->nullable()->after('methode_paiement');
            $table->string('payment_token', 64)->nullable()->unique()->after('canal_paiement');
            $table->string('payment_url')->nullable()->after('payment_token');
            $table->string('provider_reference')->nullable()->after('payment_url');
        });

        // Ajouter mobile_money à l'enum methode_paiement
        DB::statement("ALTER TABLE paiements MODIFY COLUMN methode_paiement ENUM('virement','cheque','especes','prelevement','cb','mobile_money') NULL");
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropColumn(['canal_paiement','payment_token','payment_url','provider_reference']);
        });
        DB::statement("ALTER TABLE paiements MODIFY COLUMN methode_paiement ENUM('virement','cheque','especes','prelevement','cb') NULL");
    }
};
