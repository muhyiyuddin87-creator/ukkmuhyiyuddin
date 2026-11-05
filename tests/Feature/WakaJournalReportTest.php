<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WakaJournalReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_marks_teachers_with_fewer_journals_than_schedules(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $scheduleIds = [];

        foreach (['Senin', 'Selasa'] as $day) {
            $scheduleIds[] = DB::table('schedules')->insertGetId([
                'teacher_id' => $teacher->getKey(),
                'subject' => 'Matematika',
                'day_of_week' => $day,
                'start_time' => '08:00',
                'end_time' => '09:00',
                'cycle_type' => 'Regular',
                'cycle_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('journals')->insert([
            'schedule_id' => $scheduleIds[0],
            'date' => now()->toDateString(),
            'topic_description' => 'Jurnal sudah diisi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::where('username', 'waka')->firstOrFail())
            ->get(route('waka.laporan'))
            ->assertOk()
            ->assertSee('Jurnal belum lengkap', false)
            ->assertSee('>!</span>', false);
    }

    public function test_waka_can_open_a_teachers_journal_list_from_the_report(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Bahasa Indonesia',
            'day_of_week' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('journals')->insert([
            'schedule_id' => $scheduleId,
            'date' => '2026-10-20',
            'topic_description' => 'Pembahasan Teks Eksplanasi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::where('username', 'waka')->firstOrFail())
            ->get(route('waka.laporan'))
            ->assertOk()
            ->assertSee(route('waka.laporan.guru', $teacher->getKey()), false);

        $this->get(route('waka.laporan.guru', $teacher->getKey()))
            ->assertOk()
            ->assertSee('Pembahasan Teks Eksplanasi')
            ->assertSee('Bahasa Indonesia');
    }

    public function test_only_waka_can_open_teacher_journal_details(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $waka = User::where('username', 'waka')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('waka.laporan.guru', $teacher->getKey()))
            ->assertForbidden();

        $this->actingAs($waka)
            ->get(route('waka.laporan.guru', 999999))
            ->assertNotFound();
    }
}
