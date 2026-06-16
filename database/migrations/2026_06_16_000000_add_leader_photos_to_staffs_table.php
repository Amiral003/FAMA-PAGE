<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->json('leader_photos')->nullable();
        });

        DB::table('staffs')
            ->whereNotNull('leader_photo')
            ->where('leader_photo', '!=', '')
            ->whereNull('leader_photos')
            ->select(['id', 'leader_photo'])
            ->chunkById(100, function ($staffs) {
                foreach ($staffs as $staff) {
                    DB::table('staffs')
                        ->where('id', $staff->id)
                        ->update([
                            'leader_photos' => json_encode([$staff->leader_photo]),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->dropColumn('leader_photos');
        });
    }
};
