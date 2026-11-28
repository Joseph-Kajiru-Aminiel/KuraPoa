<?php

use App\Models\ElectionStage;
use App\Models\Election;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_stages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('key', 40);
            $table->string('title');
            $table->string('title_en');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();
            $table->unique(['election_id', 'key']);
            $table->index(['election_id', 'sort_order']);
        });

        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('body');
            $table->text('body_en')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['institution_id', 'is_pinned', 'published_at']);
        });

        Schema::create('election_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('type', 20);
            $table->string('file_path');
            $table->boolean('is_public')->default(false)->index();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['election_id', 'is_public']);
        });

        Schema::create('complaints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->string('ticket_code', 16)->unique();
            $table->string('name')->nullable();
            $table->string('reg_number')->nullable();
            $table->string('contact')->nullable();
            $table->text('message');
            $table->string('status', 30)->default('mpya')->index();
            $table->text('public_reply')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'type', 'status']);
        });

        Schema::create('complaint_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('note');
            $table->timestamps();
        });

        Schema::create('useful_links', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('description');
            $table->text('description_en')->nullable();
            $table->string('url', 2048);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('observer_elections', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'election_id']);
        });

        Election::query()->orderBy('id')->get()->each(
            fn (Election $election) => ElectionStage::createDefaultsFor($election),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('observer_elections');
        Schema::dropIfExists('useful_links');
        Schema::dropIfExists('complaint_notes');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('election_documents');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('election_stages');
    }
};
