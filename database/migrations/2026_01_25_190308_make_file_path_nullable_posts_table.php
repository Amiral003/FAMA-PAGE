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
        Schema::table('posts', function (Blueprint $table) {
            // Rend la colonne file_path optionnelle
            // Note: nécessite 'composer require doctrine/dbal' pour PostgreSQL
            $table->string('file_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Remet la colonne en obligatoire si nécessaire
            $table->string('file_path')->nullable(false)->change();
        });
    }
};
