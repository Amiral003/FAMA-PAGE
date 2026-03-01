<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_type_check");
        DB::statement("ALTER TABLE posts ADD CONSTRAINT posts_type_check CHECK (type IN ('flash','article','recrutement','pdf','video'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_type_check");
        DB::statement("ALTER TABLE posts ADD CONSTRAINT posts_type_check CHECK (type IN ('flash','article','recrutement','pdf'))");
    }
};