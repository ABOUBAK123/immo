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
        Schema::table('paiements', function (Blueprint $table) {
            $table->unsignedSmallInteger('nb_relances')->default(0)->after('note');
            $table->timestamp('derniere_relance_at')->nullable()->after('nb_relances');
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropColumn(['nb_relances', 'derniere_relance_at']);
        });
    }
};
