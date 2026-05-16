<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('biens')->onDelete('cascade');
            $table->foreignId('locataire_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('prestataire_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('titre');
            $table->text('description');
            $table->enum('type', ['urgence', 'normal', 'preventif'])->default('normal');
            $table->enum('priorite', ['basse', 'moyenne', 'haute', 'urgente'])->default('moyenne');
            $table->enum('statut', ['en_attente', 'en_cours', 'termine', 'annule'])->default('en_attente');
            $table->date('date_demande');
            $table->date('date_intervention')->nullable();
            $table->decimal('cout', 10, 2)->nullable();
            $table->text('note_resolution')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
