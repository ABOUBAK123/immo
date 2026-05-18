<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->decimal('montant', 12, 2);
            $table->enum('categorie', [
                'loyer_bureau', 'salaires', 'fournitures',
                'publicite', 'transport', 'informatique', 'autres',
            ])->default('autres');
            $table->date('date_depense');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->index('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
