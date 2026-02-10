<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration neutralisée : le type 'pdf' a déjà été ajouté
     * dans la migration précédente via SQL brut.
     */
    public function up(): void
    {
        // On laisse vide pour éviter l'erreur SQLSTATE[42601]
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On laisse vide
    }
};
