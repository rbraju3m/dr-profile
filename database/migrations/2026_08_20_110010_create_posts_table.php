<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * News, events and health-tip articles share one table, split by `type`.
 * Event-only columns stay null for the other types.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('type', ['news', 'event', 'blog'])->default('news');
            $table->foreignId('post_category_id')->nullable()->constrained('post_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title_en');
            $table->string('title_bn')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->text('excerpt_bn')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('content_bn')->nullable();
            $table->string('image')->nullable();
            $table->json('tags')->nullable();

            // event-only
            $table->dateTime('event_start_at')->nullable();
            $table->dateTime('event_end_at')->nullable();
            $table->string('event_venue_en')->nullable();
            $table->string('event_venue_bn')->nullable();
            $table->string('event_registration_url')->nullable();
            $table->boolean('event_is_online')->default(false);

            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_bn')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_bn')->nullable();

            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('reading_minutes')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_published', 'published_at']);
            $table->index('event_start_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
