<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Singleton table: this platform profiles exactly one doctor.
 * Always accessed through DoctorProfile::current().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profile', function (Blueprint $table) {
            $table->id();

            $table->string('name_en');
            $table->string('name_bn')->nullable();
            $table->string('title_en')->nullable();              // "Prof. Dr." / "Dr."
            $table->string('title_bn')->nullable();
            $table->string('designation_en')->nullable();        // Senior Consultant, Cardiology
            $table->string('designation_bn')->nullable();
            $table->string('tagline_en')->nullable();
            $table->string('tagline_bn')->nullable();
            $table->text('degrees_en')->nullable();              // MBBS, FCPS (Medicine), MRCP (UK)
            $table->text('degrees_bn')->nullable();
            $table->text('short_bio_en')->nullable();
            $table->text('short_bio_bn')->nullable();
            $table->longText('bio_en')->nullable();
            $table->longText('bio_bn')->nullable();
            $table->longText('philosophy_en')->nullable();
            $table->longText('philosophy_bn')->nullable();

            $table->string('photo')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('signature')->nullable();
            $table->string('cv_file')->nullable();

            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->string('bmdc_reg_no')->nullable();
            $table->string('languages_en')->nullable();
            $table->string('languages_bn')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('hotline')->nullable();
            $table->string('whatsapp')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('x_url')->nullable();

            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_bn')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_bn')->nullable();
            $table->string('og_image')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profile');
    }
};
