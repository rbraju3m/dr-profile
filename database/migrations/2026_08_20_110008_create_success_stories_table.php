<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('title_en');
            $table->string('title_bn')->nullable();
            $table->string('patient_name')->nullable();
            $table->unsignedSmallInteger('patient_age')->nullable();
            $table->string('patient_location_en')->nullable();
            $table->string('patient_location_bn')->nullable();
            $table->text('condition_en')->nullable();
            $table->text('condition_bn')->nullable();
            $table->text('summary_en')->nullable();
            $table->text('summary_bn')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('content_bn')->nullable();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->date('treatment_date')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_bn')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_bn')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('success_stories');
    }
};
