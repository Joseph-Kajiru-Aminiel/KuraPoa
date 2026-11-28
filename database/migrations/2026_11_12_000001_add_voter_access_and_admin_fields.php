<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voters', function (Blueprint $table) {
            $table->string('access_code');
            $table->string('access_code_fingerprint', 64)->unique();
            $table->boolean('has_voted')->default(false);
            $table->timestamp('voted_at')->nullable();
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_admin']);
            $table->dropColumn('is_admin');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('voters', function (Blueprint $table) {
            $table->dropUnique(['access_code_fingerprint']);
            $table->dropColumn(['access_code', 'access_code_fingerprint', 'has_voted', 'voted_at']);
        });
    }
};
