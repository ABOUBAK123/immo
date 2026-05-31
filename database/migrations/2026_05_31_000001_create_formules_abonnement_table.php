<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formules_abonnement', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('couleur', 20)->default('#EA580C');
            $table->string('icone', 50)->default('bi-star');
            $table->boolean('populaire')->default(false);

            // Tarification
            $table->unsignedInteger('prix_mensuel')->default(0);
            $table->unsignedInteger('prix_annuel')->default(0);
            $table->string('devise', 10)->default('XOF');
            $table->unsignedInteger('duree_jours')->default(30);

            // Limites quantitatives (-1 = illimité)
            $table->integer('max_biens')->default(3);
            $table->integer('max_locataires')->default(10);
            $table->integer('max_agents')->default(0);
            $table->integer('max_annonces')->default(0);

            // Feature flags
            $table->boolean('has_interventions')->default(false);
            $table->boolean('has_annonces')->default(false);
            $table->boolean('has_depenses')->default(false);
            $table->boolean('has_ia')->default(false);
            $table->boolean('has_agents')->default(false);
            $table->boolean('has_documents')->default(true);
            $table->boolean('has_export_pdf')->default(true);
            $table->boolean('has_notifications_sms')->default(false);
            $table->boolean('has_api_access')->default(false);
            $table->boolean('support_prioritaire')->default(false);

            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formules_abonnement');
    }
};
