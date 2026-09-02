<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_views_daily', function (Blueprint $table) {
            $table->dropUnique('pvd_post_ip_date_unique');
            $table->dropIndex('pvd_post_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('post_views_daily', function (Blueprint $table) {
            $table->unique(
                ['post_id', 'ip_hash', 'view_date'],
                'pvd_post_ip_date_unique'
            );

            $table->index(
                ['post_id', 'view_date'],
                'pvd_post_date_idx'
            );
        });
    }
};
