<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security_events', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

    $table->string('event_type'); 
    // login_success, login_failed, account_locked, password_reset_requested, 2fa_failed, suspicious_ip

    $table->string('email')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();

    $table->string('severity')->default('info');
    // info, warning, danger, critical

    $table->json('metadata')->nullable();

    $table->timestamps();

    $table->index(['event_type', 'created_at']);
    $table->index(['email', 'created_at']);
    $table->index(['ip_address', 'created_at']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};
