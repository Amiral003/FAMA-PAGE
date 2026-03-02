<?php
// database/migrations/xxxx_xx_xx_add_contact_fields_to_staffs.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();     // ex: +223 XX XX XX XX
            $table->string('contact_hotline')->nullable();   // si tu as un numéro vert spécifique
            $table->string('contact_address')->nullable();
            $table->string('contact_hours')->nullable();     // ex: Lun-Ven 08h-16h
            $table->string('contact_map_url')->nullable();   // google maps / openstreetmap
            $table->json('contact_socials')->nullable();     // {"facebook":"...", "x":"...", "youtube":"..."}
        });
    }

    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->dropColumn([
                'contact_email','contact_phone','contact_hotline',
                'contact_address','contact_hours','contact_map_url',
                'contact_socials'
            ]);
        });
    }
};