<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedTinyInteger('cycle_number')->default(1)->after('cycle_type');
        });

        Schema::table('system_configurations', function (Blueprint $table) {
            $table->boolean('cycle_one_locked')->default(false)->after('is_scheduled_locked');
            $table->boolean('cycle_two_locked')->default(false)->after('cycle_one_locked');
        });
    }

    public function down(): void
    {
        Schema::table('system_configurations', function (Blueprint $table) {
            $table->dropColumn(['cycle_one_locked', 'cycle_two_locked']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('cycle_number');
        });
    }
};
