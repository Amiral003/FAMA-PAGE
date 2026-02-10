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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // Utilisation de text() au lieu de string() pour le contenu (plus de 255 caractères)
            $table->text('content')->nullable();
            $table->enum('status', [
                'Brouillion',
                'En attente',
                'approuvée',
                'Rejetée',
            ])->default('Brouillion');

            // Correction : ajout des parenthèses manquantes ()
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamp('validated_at')->nullable();

            // Syntaxe plus fluide pour la deuxième clé étrangère
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
