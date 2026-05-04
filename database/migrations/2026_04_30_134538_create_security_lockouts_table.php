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
        Schema::create('security_lockouts', function (Blueprint $table) {
    $table->id();

    $table->string('email')->nullable();
    $table->string('ip_address', 45)->nullable();

    $table->string('reason')->default('brute_force');
    $table->string('severity')->default('danger');

    $table->timestamp('locked_until');

    $table->json('metadata')->nullable();

    $table->timestamps();

    $table->index(['email', 'ip_address']);
    $table->index('locked_until');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_lockouts');
    }
};
