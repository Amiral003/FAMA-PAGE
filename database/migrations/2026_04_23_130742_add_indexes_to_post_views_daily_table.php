<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_views_daily', function (Blueprint $table) {
            // Empêche plusieurs lignes pour le même post, la même IP, le même jour
            $table->unique(['post_id', 'ip_hash', 'view_date'], 'pvd_post_ip_date_unique');

            // Accélère le graphique AudienceChart + stats par période
            $table->index(['view_date'], 'pvd_view_date_idx');

            // Accélère les stats par post / agrégations par post
            $table->index(['post_id'], 'pvd_post_id_idx');

            // Accélère les regroupements journaliers par post
            $table->index(['post_id', 'view_date'], 'pvd_post_date_idx');

            // Utile si un jour tu réactives les stats par pays
            $table->index(['country'], 'pvd_country_idx');
        });
    }

    public function down(): void
    {
        Schema::table('post_views_daily', function (Blueprint $table) {
            $table->dropUnique('pvd_post_ip_date_unique');
            $table->dropIndex('pvd_view_date_idx');
            $table->dropIndex('pvd_post_id_idx');
            $table->dropIndex('pvd_post_date_idx');
            $table->dropIndex('pvd_country_idx');
        });
    }
};