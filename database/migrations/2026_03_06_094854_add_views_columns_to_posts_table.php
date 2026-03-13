<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('total_views')->default(0)->after('video_thumbnail_url');
            $table->unsignedBigInteger('unique_views')->default(0)->after('total_views');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['total_views', 'unique_views']);
        });
    }
};