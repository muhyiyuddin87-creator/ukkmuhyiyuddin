<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('schedules', 'academic_year')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->string('academic_year', 9)->nullable();
            });
        }

        $year = now()->month >= 7 ? now()->year : now()->year - 1;
        DB::table('schedules')->whereNull('academic_year')->update([
            'academic_year' => $year.'/'.($year + 1),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('schedules', 'academic_year')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropColumn('academic_year');
            });
        }
    }
};
