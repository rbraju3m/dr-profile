<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Posts and pages were the only listings without a manual order.
 *
 * Everything defaults to 0, so the existing date ordering is untouched until
 * somebody actually drags a row — at which point their arrangement wins.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['posts', 'pages'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedInteger('sort_order')->default(0)->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['posts', 'pages'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('sort_order');
            });
        }
    }
};
