<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) On supprime l'index unique actuel (créé précédemment)
        DB::statement("DROP INDEX IF EXISTS posts_slug_unique");

        // 2) On recrée un unique index PARTIEL (ignore les NULL)
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS posts_slug_unique
            ON posts (slug)
            WHERE slug IS NOT NULL
        ");
    }

    public function down(): void
    {
        // rollback: on revient à l'ancien comportement (unique sur slug, NULL autorisés)
        DB::statement("DROP INDEX IF EXISTS posts_slug_unique");

        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS posts_slug_unique
            ON posts (slug)
        ");
    }
};