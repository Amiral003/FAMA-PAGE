<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrderNullableOnStaffsTable extends Migration
{
    public function up(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->integer('order')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->integer('order')->default(0)->nullable(false)->change();
        });
    }
}