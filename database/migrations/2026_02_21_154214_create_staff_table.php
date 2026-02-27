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
    Schema::create('staffs', function (Blueprint $table) {
        $table->id();
        // Identité de l'État-Major
        $table->string('name');            // Ex: État-Major de l'Armée de Terre
        $table->string('initials');        // Ex: EMAT
        $table->string('slug')->unique();  // Pour l'URL : fama.ml/etat-major/emat
        $table->string('logo')->nullable(); // Chemin vers l'emblème
        $table->string('motto')->nullable(); // La devise (ex: "S'instruire pour mieux servir")
        
        // Contenu
        $table->text('description')->nullable(); // Présentation générale
        $table->text('missions')->nullable();    // Liste des missions spécifiques
        
        // Le Chef d'État-Major (CEM)
        $table->string('leader_name')->nullable();  // Nom du Général/Colonel
        $table->string('leader_rank')->nullable();  // Grade
        $table->string('leader_photo')->nullable(); // Photo du CEM
        $table->text('leader_word')->nullable();    // Mot du Chef
        
        $table->integer('order')->default(0); // Pour l'ordre d'importance
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
