<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('biens')->onDelete('cascade');
            $table->foreignId('locataire_id')->constrained('users')->onDelete('cascade');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->decimal('loyer_mensuel', 10, 2);
            $table->decimal('charges', 10, 2)->default(0);
            $table->decimal('depot_garantie', 10, 2)->default(0);
            $table->enum('type_bail', ['meuble', 'vide', 'etudiant', 'mobilite'])->default('vide');
            $table->enum('statut', ['en_attente', 'actif', 'resilie', 'termine'])->default('en_attente');
            $table->date('revision_loyer_date')->nullable();
            $table->decimal('index_irl', 8, 2)->nullable();
            $table->text('conditions_particulieres')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
