<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Active l'extension trigram (une seule fois par DB)
        DB::statement("CREATE EXTENSION IF NOT EXISTS pg_trgm");

        // Index GIN trigram pour accélérer ILIKE '%q%'
        DB::statement("CREATE INDEX IF NOT EXISTS posts_title_trgm_idx ON posts USING gin (title gin_trgm_ops)");
        DB::statement("CREATE INDEX IF NOT EXISTS posts_content_trgm_idx ON posts USING gin (\"content\" gin_trgm_ops)");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS posts_content_trgm_idx");
        DB::statement("DROP INDEX IF EXISTS posts_title_trgm_idx");

        // Je NE drop pas l'extension pg_trgm en down par défaut
        // car elle peut être utilisée ailleurs.
        // Si tu veux vraiment:
        // DB::statement("DROP EXTENSION IF EXISTS pg_trgm");
    }
};