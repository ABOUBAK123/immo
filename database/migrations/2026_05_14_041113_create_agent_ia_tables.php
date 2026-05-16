<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations_ia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietaire_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('locataire_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('titre', 191)->default('Nouvelle conversation');
            $table->timestamps();
            $table->index(['proprietaire_id', 'created_at']);
        });

        Schema::create('messages_ia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations_ia')->onDelete('cascade');
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('contenu');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages_ia');
        Schema::dropIfExists('conversations_ia');
    }
};
