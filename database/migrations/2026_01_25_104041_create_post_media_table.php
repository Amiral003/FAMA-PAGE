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
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();

            // Relation avec la table posts
            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('file_path');

            // unsignedInteger est préférable pour un index d'ordre (pas de chiffres négatifs)
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
