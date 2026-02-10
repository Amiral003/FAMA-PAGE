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
            // Vérifie si la colonne n'existe pas avant de la créer
            if (!Schema::hasColumn('posts', 'validated_at')) {
                $table->timestamp('validated_at')->nullable();
            }

            // Vérifie si la colonne n'existe pas avant de la créer
            // Correction du nom : validated_by (avec un 'd') pour rester cohérent
            if (!Schema::hasColumn('posts', 'validated_by')) {
                $table->foreignId('validated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Suppression des colonnes si on revient en arrière
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['validated_at', 'validated_by']);
        });
    }
};
