<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Accélère :
            // - PostsStats (status IN ...)
            // - listes de validation
            // - dashboard admin/validateur
            $table->index(['status'], 'posts_status_idx');

            // Accélère :
            // - historique des validations / PostsChart
            $table->index(['validated_at'], 'posts_validated_at_idx');

            // Accélère :
            // - tri des top posts par vues
            $table->index(['total_views'], 'posts_total_views_idx');

            // Accélère :
            // - futurs widgets "mes contenus" rédacteur
            $table->index(['user_id', 'status'], 'posts_user_status_idx');

            // Accélère :
            // - liste par auteur avec tri temporel
            $table->index(['user_id', 'created_at'], 'posts_user_created_at_idx');
        });

        // Index partiel très utile pour les widgets de validation
        DB::statement("
            CREATE INDEX posts_pending_review_idx
            ON posts (status, created_at DESC)
            WHERE status IN ('brouillon', 'revision')
        ");

        // Index partiel pour les publications validées / chart
        DB::statement("
            CREATE INDEX posts_published_validated_idx
            ON posts (validated_at DESC)
            WHERE status = 'publie' AND validated_at IS NOT NULL
        ");

        // Index partiel pour le top des posts publiés
        DB::statement("
            CREATE INDEX posts_published_total_views_idx
            ON posts (total_views DESC)
            WHERE status = 'publie'
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS posts_pending_review_idx");
        DB::statement("DROP INDEX IF EXISTS posts_published_validated_idx");
        DB::statement("DROP INDEX IF EXISTS posts_published_total_views_idx");

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_status_idx');
            $table->dropIndex('posts_validated_at_idx');
            $table->dropIndex('posts_total_views_idx');
            $table->dropIndex('posts_user_status_idx');
            $table->dropIndex('posts_user_created_at_idx');
        });
    }
};