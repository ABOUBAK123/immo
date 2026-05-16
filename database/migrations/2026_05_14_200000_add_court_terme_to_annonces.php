<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->enum('mode_location', ['long_terme', 'court_terme'])->default('long_terme')->after('type');
            $table->decimal('prix_nuit', 10, 2)->nullable()->after('prix');
            $table->unsignedTinyInteger('nb_max_voyageurs')->nullable()->after('prix_nuit');
            $table->json('equipements')->nullable()->after('nb_max_voyageurs');
        });
    }

    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropColumn(['mode_location', 'prix_nuit', 'nb_max_voyageurs', 'equipements']);
        });
    }
};
