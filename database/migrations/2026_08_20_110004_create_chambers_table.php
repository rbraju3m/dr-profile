<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chambers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_en');                      // Evercare Hospital, Dhaka
            $table->string('name_bn')->nullable();
            $table->text('address_en')->nullable();
            $table->text('address_bn')->nullable();
            $table->string('city_en')->nullable();
            $table->string('city_bn')->nullable();
            $table->string('room_no')->nullable();
            $table->string('phone')->nullable();
            $table->string('appointment_phone')->nullable();
            $table->string('image')->nullable();
            $table->text('map_embed')->nullable();
            $table->string('map_url')->nullable();
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->decimal('followup_fee', 10, 2)->nullable();
            $table->text('note_en')->nullable();
            $table->text('note_bn')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('accepts_online_booking')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chambers');
    }
};
