<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                if (! Schema::hasColumn('posts', 'total_views')) {
                    $table->unsignedBigInteger('total_views')->default(0);
                }

                if (! Schema::hasColumn('posts', 'unique_views')) {
                    $table->unsignedBigInteger('unique_views')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                if (Schema::hasColumn('posts', 'total_views')) {
                    $table->dropColumn('total_views');
                }

                if (Schema::hasColumn('posts', 'unique_views')) {
                    $table->dropColumn('unique_views');
                }
            });
        }
    }
};