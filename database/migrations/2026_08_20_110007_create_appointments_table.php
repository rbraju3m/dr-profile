<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_no')->unique();
            $table->foreignId('chamber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chamber_schedule_id')->nullable()->constrained('chamber_schedules')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();

            $table->string('patient_name');
            $table->string('patient_phone');
            $table->string('patient_email')->nullable();
            $table->enum('patient_gender', ['male', 'female', 'other'])->nullable();
            $table->unsignedSmallInteger('patient_age')->nullable();
            $table->string('patient_address')->nullable();
            $table->enum('visit_type', ['new', 'followup'])->default('new');

            $table->date('appointment_date');
            $table->time('slot_time');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('cancelled_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['chamber_id', 'appointment_date']);
            $table->index(['appointment_date', 'status']);
            $table->index('patient_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
