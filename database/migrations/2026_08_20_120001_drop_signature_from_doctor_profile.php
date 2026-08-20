<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The signature was uploadable but never rendered anywhere on the site — an
 * admin control that quietly did nothing. Dropped rather than left in place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_profile', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_profile', function (Blueprint $table) {
            $table->string('signature')->nullable()->after('hero_image');
        });
    }
};
