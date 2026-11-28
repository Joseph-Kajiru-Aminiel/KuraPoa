<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('site_settings')->insert(array_map(
            fn (string $key): array => ['key' => $key, 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['youtube_url', 'instagram_url', 'whatsapp_number', 'contact_email'],
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
