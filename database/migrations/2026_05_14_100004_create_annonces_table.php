<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annonces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('biens')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type', ['location', 'vente'])->default('location');
            $table->decimal('prix', 12, 2);
            $table->boolean('prix_negociable')->default(false);
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('statut', ['active', 'inactive', 'vendu', 'loue', 'archive'])->default('active');
            $table->date('date_disponibilite')->nullable();
            $table->unsignedInteger('vues')->default(0);
            $table->json('photos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'statut']);
            $table->index('vues');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
