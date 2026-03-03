<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ⚠️ Assure-toi que la table s'appelle bien "post_media"
        DB::statement("CREATE INDEX IF NOT EXISTS post_media_post_order_idx ON post_media (post_id, \"order\")");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS post_media_post_order_idx");
    }
};