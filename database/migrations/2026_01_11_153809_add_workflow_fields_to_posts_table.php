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
        Schema::table('posts', function (Blueprint $table) {
          if(!Schema::hasColumn('posts','validated_at')){
           $table->timestamp('validated_at')->nullable();
          }
          if(!Schema::hasColumn('posts', 'validated_by')){
           $table->foreignid('validate_by')->nullable()->constrained('users');
          }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            //
        });
    }
};
