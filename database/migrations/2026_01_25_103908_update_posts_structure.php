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
        // 1. On nettoie les anciennes contraintes PostgreSQL manuellement
        DB::statement('ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_status_check');
        DB::statement('ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_type_check');

        // 2. On modifie les types en VARCHAR pour éviter le bug de l'ENUM
        DB::statement('ALTER TABLE posts ALTER COLUMN status TYPE VARCHAR(255)');

        Schema::table('posts', function (Blueprint $table) {
            // Slug
            if (!Schema::hasColumn('posts', 'slug')) {
                $table->string('slug')->nullable();
            }

            // Content
            $table->text('content')->nullable()->change();

            // Type (création si n'existe pas)
            if (!Schema::hasColumn('posts', 'type')) {
                $table->string('type')->default('article');
            }

            // PDF path
            if (!Schema::hasColumn('posts', 'pdf_path')) {
                $table->string('pdf_path')->nullable();
            }

            // Published at
            if (!Schema::hasColumn('posts', 'published_at')) {
                $table->timestamp('published_at')->nullable();
            }
        });

        // 3. On remet les règles de validation "CHECK" en SQL brut (compatible Postgres)
        DB::statement("ALTER TABLE posts ADD CONSTRAINT posts_status_check CHECK (status IN ('brouillon', 'revision', 'publie'))");
        DB::statement("ALTER TABLE posts ADD CONSTRAINT posts_type_check CHECK (type IN ('flash', 'article', 'recrutement', 'pdf'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['slug', 'pdf_path', 'published_at']);
        });

        DB::statement('ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_status_check');
        DB::statement('ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_type_check');
    }
};
