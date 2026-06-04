<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staffs', function (Blueprint $table) {

            // ===== SECOND CHEF =====

            $table->string('second_leader_name')
                ->nullable()
                ->after('leader_word');

            $table->string('second_leader_rank')
                ->nullable()
                ->after('second_leader_name');

            $table->string('second_leader_photo')
                ->nullable()
                ->after('second_leader_rank');

            $table->text('second_leader_word')
                ->nullable()
                ->after('second_leader_photo');

            // ===== STRUCTURE PARENTE =====

            $table->foreignId('parent_staff_id')
                ->nullable()
                ->after('second_leader_word')
                ->constrained('staffs')
                ->nullOnDelete();

            $table->index('parent_staff_id');
        });
    }

    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {

            $table->dropForeign(['parent_staff_id']);
            $table->dropIndex(['parent_staff_id']);

            $table->dropColumn([
                'second_leader_name',
                'second_leader_rank',
                'second_leader_photo',
                'second_leader_word',
                'parent_staff_id',
            ]);
        });
    }
};
