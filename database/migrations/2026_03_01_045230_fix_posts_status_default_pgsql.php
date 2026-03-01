<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ Corrige le DEFAULT qui casse le CHECK
        DB::statement("ALTER TABLE public.posts ALTER COLUMN status SET DEFAULT 'brouillon'");

        // ✅ Nettoie les anciennes valeurs qui traînent éventuellement
        DB::statement("UPDATE public.posts SET status = 'brouillon' WHERE status ILIKE 'Brou%'");
        DB::statement("UPDATE public.posts SET status = 'publie' WHERE status ILIKE 'app%'");
        DB::statement("UPDATE public.posts SET status = 'revision' WHERE status ILIKE 'rej%'");

        // Optionnel : si tu avais "En attente"
        DB::statement("UPDATE public.posts SET status = 'brouillon' WHERE status ILIKE 'En attente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
                DB::statement("ALTER TABLE public.posts ALTER COLUMN status DROP DEFAULT");

    }
};
