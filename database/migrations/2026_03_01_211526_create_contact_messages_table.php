<?php
// database/migrations/xxxx_xx_xx_create_contact_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable()->constrained('staffs')->nullOnDelete();

            $table->string('name');
            $table->string('email');
            $table->string('subject');     // ex: information / recrutement / presse (ou texte libre)
            $table->text('message');

            $table->string('status')->default('new'); // new / in_progress / done
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};