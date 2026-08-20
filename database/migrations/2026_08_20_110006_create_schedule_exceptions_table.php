<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Overrides the weekly pattern for a specific date.
 * chamber_id NULL = applies to every chamber (e.g. the doctor is abroad).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chamber_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_available')->default(false); // false = closed, true = extra sitting
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedSmallInteger('slot_minutes')->nullable();
            $table->string('reason_en')->nullable();
            $table->string('reason_bn')->nullable();
            $table->timestamps();

            $table->unique(['chamber_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
