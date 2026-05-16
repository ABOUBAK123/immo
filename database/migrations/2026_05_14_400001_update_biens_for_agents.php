<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biens', function (Blueprint $table) {
            $table->dropForeign(['proprietaire_id']);
            $table->unsignedBigInteger('proprietaire_id')->nullable()->change();
            $table->foreign('proprietaire_id')->references('id')->on('users')->onDelete('set null');
            $table->foreignId('agent_id')->nullable()->after('proprietaire_id')
                  ->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('biens', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
            $table->dropForeign(['proprietaire_id']);
            $table->unsignedBigInteger('proprietaire_id')->nullable(false)->change();
            $table->foreign('proprietaire_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
