<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('nom');
            $table->enum('type', ['bail', 'quittance', 'etat_des_lieux', 'diagnostic', 'photo', 'assurance', 'autre'])->default('autre');
            $table->string('chemin');
            $table->unsignedBigInteger('taille')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
