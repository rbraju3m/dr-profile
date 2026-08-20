<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['journal', 'conference', 'book', 'chapter', 'thesis', 'other'])->default('journal');
            $table->string('title_en');
            $table->string('title_bn')->nullable();
            $table->string('authors')->nullable();
            $table->string('venue_en')->nullable();   // journal / conference name
            $table->string('venue_bn')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('volume')->nullable();
            $table->string('pages')->nullable();
            $table->string('doi')->nullable();
            $table->string('url')->nullable();
            $table->string('file')->nullable();
            $table->longText('abstract_en')->nullable();
            $table->longText('abstract_bn')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
