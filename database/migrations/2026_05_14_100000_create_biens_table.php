<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietaire_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->enum('type', ['appartement', 'maison', 'villa', 'studio', 'bureau', 'commerce', 'terrain'])->default('appartement');
            $table->decimal('surface', 8, 2)->nullable();
            $table->unsignedTinyInteger('nb_pieces')->nullable();
            $table->unsignedTinyInteger('nb_chambres')->nullable();
            $table->unsignedTinyInteger('nb_sdb')->nullable();
            $table->unsignedTinyInteger('etage')->nullable();
            $table->string('adresse');
            $table->string('ville');
            $table->string('code_postal', 10)->nullable();
            $table->string('pays', 100)->default('France');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('description')->nullable();
            $table->enum('statut', ['disponible', 'loue', 'vendu', 'en_travaux'])->default('disponible');
            $table->boolean('meuble')->default(false);
            $table->decimal('prix_achat', 12, 2)->nullable();
            $table->decimal('valeur_estimee', 12, 2)->nullable();
            $table->year('annee_construction')->nullable();
            $table->string('dpe', 1)->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biens');
    }
};
