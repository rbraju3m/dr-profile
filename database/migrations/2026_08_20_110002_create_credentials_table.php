<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One table drives the whole "about" timeline: education, career history,
 * training, awards, memberships and certifications, separated by `type`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['education', 'experience', 'training', 'award', 'membership', 'certification']);
            $table->string('title_en');
            $table->string('title_bn')->nullable();
            $table->string('organization_en')->nullable();
            $table->string('organization_bn')->nullable();
            $table->string('location_en')->nullable();
            $table->string('location_bn')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};
