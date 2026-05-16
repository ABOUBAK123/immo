<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quittances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paiement_id')->constrained('paiements')->onDelete('cascade');
            $table->string('numero')->unique();
            $table->date('date_emission');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quittances');
    }
};
