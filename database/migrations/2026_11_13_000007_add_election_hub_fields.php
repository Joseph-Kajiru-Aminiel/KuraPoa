<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamp('appeals_deadline_at')->nullable();
        });

        $usedSlugs = [];
        DB::table('elections')->orderBy('id')->get(['id', 'title'])->each(function (object $election) use (&$usedSlugs): void {
            $base = Str::slug($election->title) ?: 'uchaguzi';
            $slug = $base;
            $suffix = 2;

            while (isset($usedSlugs[$slug])) {
                $slug = $base.'-'.$suffix++;
            }

            $usedSlugs[$slug] = true;
            DB::table('elections')->where('id', $election->id)->update(['slug' => $slug]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->nullable()->index();
            $table->string('organization')->nullable();
        });

        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'organization']);
        });

        Schema::table('elections', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'description', 'is_public', 'appeals_deadline_at']);
        });
    }
};
