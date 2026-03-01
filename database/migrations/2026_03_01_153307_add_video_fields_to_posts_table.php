<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('video_url', 2048)->nullable()->after('content');
            $table->string('video_platform', 50)->nullable()->after('video_url'); // youtube|facebook|mp4|other
            $table->string('video_thumbnail_url', 2048)->nullable()->after('video_platform'); // optionnel
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'video_platform', 'video_thumbnail_url']);
        });
    }
};