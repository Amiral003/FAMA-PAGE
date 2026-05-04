<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =========================
        // SECURITY EVENTS
        // =========================
        Schema::table('security_events', function (Blueprint $table) {

            // Requêtes globales (filtre + date)
            $table->index(['event_type', 'created_at'], 'sec_evt_type_created_idx');

            // Recherche par email
            $table->index(['email', 'created_at'], 'sec_evt_email_created_idx');

            // Recherche par IP
            $table->index(['ip_address', 'created_at'], 'sec_evt_ip_created_idx');

            // Sévérité (dashboard)
            $table->index(['severity', 'created_at'], 'sec_evt_severity_created_idx');
        });

        // ⚡ INDEX PARTIEL (ultra important PostgreSQL)
        DB::statement("
            CREATE INDEX sec_evt_failed_email_partial_idx
            ON security_events (email, created_at)
            WHERE event_type = 'login_failed' AND email IS NOT NULL
        ");

        DB::statement("
            CREATE INDEX sec_evt_failed_ip_partial_idx
            ON security_events (ip_address, created_at)
            WHERE event_type = 'login_failed' AND ip_address IS NOT NULL
        ");

        // =========================
        // SECURITY LOCKOUTS
        // =========================
        Schema::table('security_lockouts', function (Blueprint $table) {

            // blocages actifs
            $table->index(['locked_until'], 'sec_lock_until_idx');

            // recherche ciblée
            $table->index(['email', 'locked_until'], 'sec_lock_email_until_idx');

            $table->index(['ip_address', 'locked_until'], 'sec_lock_ip_until_idx');
        });
    }

    public function down(): void
    {
        Schema::table('security_events', function (Blueprint $table) {
            $table->dropIndex('sec_evt_type_created_idx');
            $table->dropIndex('sec_evt_email_created_idx');
            $table->dropIndex('sec_evt_ip_created_idx');
            $table->dropIndex('sec_evt_severity_created_idx');
        });

        DB::statement("DROP INDEX IF EXISTS sec_evt_failed_email_partial_idx");
        DB::statement("DROP INDEX IF EXISTS sec_evt_failed_ip_partial_idx");

        Schema::table('security_lockouts', function (Blueprint $table) {
            $table->dropIndex('sec_lock_until_idx');
            $table->dropIndex('sec_lock_email_until_idx');
            $table->dropIndex('sec_lock_ip_until_idx');
        });
    }
};
