<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   
public function up()
{
    Schema::table('posts', function (Blueprint $table) {

        if (!Schema::hasColumn('posts', 'slug')) {
            $table->string('slug')->nullable()->after('title');
        }

        if (!Schema::hasColumn('posts', 'type')) {
            $table->enum('type',['flash','article','recrutement'])
                ->default('article')
                ->after('slug');
        }

        $table->longText('content')->nullable()->change();

        $table->enum('status',['brouillon','revision','publie'])
              ->default('brouillon')
              ->change();

        if (!Schema::hasColumn('posts','pdf_path')) {
            $table->string('pdf_path')->nullable();
        }

        if (!Schema::hasColumn('posts','published_at')) {
            $table->timestamp('published_at')->nullable();
        }
    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
