<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamber_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chamber_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0 = Sunday .. 6 = Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('slot_minutes')->default(20);
            $table->unsignedSmallInteger('max_patients')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['chamber_id', 'day_of_week', 'start_time'], 'chamber_schedules_unique_sitting');
            $table->index(['day_of_week', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamber_schedules');
    }
};
