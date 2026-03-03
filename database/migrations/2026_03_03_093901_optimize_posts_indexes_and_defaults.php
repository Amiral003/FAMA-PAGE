<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Fix DEFAULT status (actuellement "Brouillion" dans ton DDL)
        DB::statement("ALTER TABLE posts ALTER COLUMN status SET DEFAULT 'brouillon'");

        // (Optionnel mais conseillé) Si tu veux empêcher slug null à terme :
        // DB::statement("ALTER TABLE posts ALTER COLUMN slug SET NOT NULL");

        // 2) Index unique sur slug (PostgreSQL autorise plusieurs NULL même en unique)
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS posts_slug_unique ON posts (slug)");

        // 3) Index feed public (posts publiés + tri)
        DB::statement("
            CREATE INDEX IF NOT EXISTS posts_public_feed_idx
            ON posts (published_at DESC, validated_at DESC, created_at DESC)
            WHERE status = 'publie'
        ");

        // 4) Index feed avec filtre type (type + tri) pour index(), latest(), photos(), videos()
        DB::statement("
            CREATE INDEX IF NOT EXISTS posts_public_type_feed_idx
            ON posts (type, published_at DESC, validated_at DESC, created_at DESC)
            WHERE status = 'publie'
        ");

        // 5) Index flashes (flash + publié + tri)
        DB::statement("
            CREATE INDEX IF NOT EXISTS posts_public_flash_idx
            ON posts (published_at DESC)
            WHERE status = 'publie' AND type = 'flash'
        ");
    }

    public function down(): void
    {
        // Revenir à l’ancien default (je te déconseille), donc on peut juste enlever le default :
        DB::statement("ALTER TABLE posts ALTER COLUMN status DROP DEFAULT");

        DB::statement("DROP INDEX IF EXISTS posts_public_flash_idx");
        DB::statement("DROP INDEX IF EXISTS posts_public_type_feed_idx");
        DB::statement("DROP INDEX IF EXISTS posts_public_feed_idx");
        DB::statement("DROP INDEX IF EXISTS posts_slug_unique");
    }
};