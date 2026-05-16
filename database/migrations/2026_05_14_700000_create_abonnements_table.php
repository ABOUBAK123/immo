<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->string('devise', 5)->default('XOF');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['en_attente', 'actif', 'expire', 'annule'])->default('en_attente');
            $table->string('methode_paiement', 30)->nullable();
            $table->string('canal_paiement', 30)->nullable();
            $table->string('provider_reference')->nullable();
            $table->string('payment_token', 64)->nullable()->unique();
            $table->string('payment_url')->nullable();
            $table->string('invoice_number', 30)->nullable()->unique();
            $table->boolean('essai')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'statut']);
            $table->index('date_fin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};
