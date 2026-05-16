<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->date('date_echeance');
            $table->date('date_paiement')->nullable();
            $table->enum('statut', ['en_attente', 'paye', 'en_retard', 'annule'])->default('en_attente');
            $table->enum('type', ['loyer', 'charges', 'depot_garantie', 'remboursement', 'autre'])->default('loyer');
            $table->enum('methode_paiement', ['virement', 'cheque', 'especes', 'prelevement', 'cb'])->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
