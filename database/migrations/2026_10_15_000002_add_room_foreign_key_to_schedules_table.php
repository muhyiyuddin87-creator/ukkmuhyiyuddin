<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('schedules')
            ->whereNotNull('room_id')
            ->whereNotIn('room_id', DB::table('ruangan')->select('id_ruangan'))
            ->update(['room_id' => null]);

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('room_id')
                ->references('id_ruangan')
                ->on('ruangan')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
        });
    }
};
