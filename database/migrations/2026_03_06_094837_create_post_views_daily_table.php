<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_views_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            // On ne stocke pas l'IP brute
            $table->string('ip_hash', 64);

            $table->string('country', 100)->nullable();
            $table->date('view_date');
            $table->unsignedInteger('hits')->default(1);

            $table->timestamps();

            $table->unique(['post_id', 'ip_hash', 'view_date'], 'post_views_daily_unique');
            $table->index(['post_id', 'view_date']);
            $table->index(['country', 'view_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_views_daily');
    }
};