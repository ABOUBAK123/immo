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
        Schema::create('parametres', function (Blueprint $table) {
            $table->id();
            $table->string('groupe', 50);              // email, sms, whatsapp, general
            $table->string('cle', 100)->unique();
            $table->text('valeur')->nullable();
            $table->enum('type', ['text','password','select','boolean'])->default('text');
            $table->string('label', 191)->nullable();
            $table->timestamps();

            $table->index('groupe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};
