<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            $table->foreignId('formule_id')->nullable()->after('user_id')
                  ->constrained('formules_abonnement')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            $table->dropForeign(['formule_id']);
            $table->dropColumn('formule_id');
        });
    }
};
