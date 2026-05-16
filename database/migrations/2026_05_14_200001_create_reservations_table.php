<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annonce_id')->constrained('annonces')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email');
            $table->string('telephone', 20);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->unsignedTinyInteger('nb_voyageurs')->default(1);
            $table->unsignedSmallInteger('nb_nuits');
            $table->decimal('prix_nuit', 10, 2);
            $table->decimal('frais_service', 10, 2)->default(0);
            $table->decimal('montant_total', 12, 2);
            $table->enum('statut', ['en_attente', 'paiement_initie', 'payee', 'confirmee', 'annulee'])->default('en_attente');
            $table->enum('canal_paiement', ['orange_money', 'mtn_money', 'wave', 'carte', 'virement'])->nullable();
            $table->string('reference_paiement', 100)->nullable();
            $table->string('payment_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['annonce_id', 'statut']);
            $table->index(['date_debut', 'date_fin']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
