<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_en');
            $table->string('title_bn')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('content_bn')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_bn')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_bn')->nullable();
            $table->boolean('show_in_footer')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title_en')->nullable();
            $table->string('title_bn')->nullable();
            $table->text('subtitle_en')->nullable();
            $table->text('subtitle_bn')->nullable();
            $table->string('image');
            $table->string('mobile_image')->nullable();
            $table->string('cta_label_en')->nullable();
            $table->string('cta_label_bn')->nullable();
            $table->string('cta_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->string('label_en');
            $table->string('label_bn')->nullable();
            $table->unsignedInteger('value')->default(0);
            $table->string('suffix')->nullable();   // "+", "K+"
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_read')->default(false);
            $table->text('admin_note')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index(['is_read', 'created_at']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group')->default('general');
            $table->string('type')->default('text'); // text | textarea | image | boolean | number
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('stats');
        Schema::dropIfExists('sliders');
        Schema::dropIfExists('pages');
    }
};
