<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications_envoyees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietaire_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('locataire_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('paiement_id')->nullable()->constrained('paiements')->onDelete('set null');
            $table->enum('canal', ['email', 'sms', 'whatsapp']);
            $table->enum('type', ['alerte_loyer', 'relance_retard', 'quittance', 'personnalise']);
            $table->string('sujet')->nullable();
            $table->text('message');
            $table->string('destinataire_contact', 191)->nullable();
            $table->enum('statut', ['envoye', 'echec', 'simule'])->default('simule');
            $table->text('erreur')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['proprietaire_id', 'created_at']);
            $table->index(['locataire_id', 'canal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_envoyees');
    }
};
