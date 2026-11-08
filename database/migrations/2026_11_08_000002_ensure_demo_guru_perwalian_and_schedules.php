<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('school_classes') || ! Schema::hasTable('user_school_classes')) {
            return;
        }

        $guru = DB::table('users')->where('username', 'guru')->first();
        if (! $guru) {
            return;
        }

        $rpl2Class = DB::table('school_classes')->where('name', 'XII RPL 2')->first();
        if ($rpl2Class) {
            DB::table('user_school_classes')->updateOrInsert(
                ['school_class_id' => $rpl2Class->id],
                [
                    'user_id' => $guru->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        if (Schema::hasTable('schedules')) {
            $existing = DB::table('schedules')->where('teacher_id', $guru->id)->count();
            if ($existing === 0) {
                $roomId = DB::table('ruangan')->value('id_ruangan') ?? 1;
                $activeCycle = (int) (DB::table('system_configurations')->value('active_cycle') ?? 1);

                DB::table('schedules')->insertOrIgnore([
                    [
                        'teacher_id' => $guru->id,
                        'class_id' => $rpl2Class ? $rpl2Class->id : null,
                        'class_name' => 'XII RPL 2',
                        'room_id' => $roomId,
                        'subject' => 'Pemrograman Web & Mobile',
                        'day_of_week' => 'Senin',
                        'start_time' => '07:30:00',
                        'end_time' => '09:30:00',
                        'cycle_type' => 'Jurusan',
                        'cycle_number' => $activeCycle,
                        'academic_year' => '2026/2027',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'teacher_id' => $guru->id,
                        'class_id' => $rpl2Class ? $rpl2Class->id : null,
                        'class_name' => 'XII RPL 2',
                        'room_id' => $roomId,
                        'subject' => 'Basis Data & Cloud Computing',
                        'day_of_week' => 'Rabu',
                        'start_time' => '09:45:00',
                        'end_time' => '11:45:00',
                        'cycle_type' => 'Jurusan',
                        'cycle_number' => $activeCycle,
                        'academic_year' => '2026/2027',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep demo assignments
    }
};

