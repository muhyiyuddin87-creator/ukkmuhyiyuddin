<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('schedules', 'class_name')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->string('class_name', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('schedules', 'class_name')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropColumn('class_name');
            });
        }
    }
};
