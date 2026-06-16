<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE contact_messages ALTER COLUMN email DROP NOT NULL');
    }

    public function down(): void
    {
        $nullEmailsCount = DB::table('contact_messages')
            ->whereNull('email')
            ->count();

        if ($nullEmailsCount > 0) {
            return;
        }

        DB::statement('ALTER TABLE contact_messages ALTER COLUMN email SET NOT NULL');
    }
};
