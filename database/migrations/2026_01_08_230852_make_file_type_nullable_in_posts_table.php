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
            // On s'assure que la colonne peut accepter des valeurs NULL
            // Note : Sous PostgreSQL, cela nécessite 'composer require doctrine/dbal'
            $table->string('file_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Retour à l'état obligatoire (NOT NULL)
            $table->string('file_type')->nullable(false)->change();
        });
    }
};
