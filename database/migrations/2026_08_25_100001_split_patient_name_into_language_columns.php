<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `patient_name` was a single column on two tables whose every other content
 * column comes in a pair, so a story credited to মুগ্ধ was credited to মুগ্ধ on
 * the English page as well. It is the one field on those forms a Bengali
 * speaker is most likely to type in Bengali.
 *
 * The existing value becomes the English half — that is where it was being
 * read from — and is copied into the Bangla half when it is written in
 * Bengali, so the Bangla page keeps showing what it shows today and the
 * English half can be corrected without losing the name.
 */
return new class extends Migration
{
    private const TABLES = ['success_stories', 'testimonials'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->renameColumn('patient_name', 'patient_name_en');
            });

            Schema::table($table, function (Blueprint $t) {
                $t->string('patient_name_bn')->nullable()->after('patient_name_en');
            });

            DB::table($table)->select('id', 'patient_name_en')->orderBy('id')
                ->each(function (object $row) use ($table) {
                    if (preg_match('/\p{Bengali}/u', (string) $row->patient_name_en)) {
                        DB::table($table)->where('id', $row->id)
                            ->update(['patient_name_bn' => $row->patient_name_en]);
                    }
                });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('patient_name_bn');
            });

            Schema::table($table, function (Blueprint $t) {
                $t->renameColumn('patient_name_en', 'patient_name');
            });
        }
    }
};
