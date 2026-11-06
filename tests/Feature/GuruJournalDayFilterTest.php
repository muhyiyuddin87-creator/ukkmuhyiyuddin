<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GuruJournalDayFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_journal_page_displays_day_indicator_and_schedule_options_with_day_attribute(): void
    {
        $teacherId = DB::table('users')->insertGetId([
            'role_id' => 2,
            'fullname' => 'Guru Pengajar',
            'username' => 'guru.pengajar',
            'password' => bcrypt('password'),
            'created_at' => now(),
        ]);
        $teacher = User::findOrFail($teacherId);

        DB::table('schedules')->insert([
            'teacher_id' => $teacherId,
            'subject' => 'Matematika Peminatan',
            'day_of_week' => 'Jumat',
            'class_name' => 'XII RPL 1',
            'start_time' => '07:30',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('guru.jurnal'))
            ->assertOk()
            ->assertSee('id="journal-day-indicator"', false)
            ->assertSee('id="journal-schedule-select"', false)
            ->assertSee('data-day="Jumat"', false)
            ->assertSee('Matematika Peminatan');
    }

    public function test_cannot_save_journal_if_schedule_day_does_not_match_date(): void
    {
        $teacherId = DB::table('users')->insertGetId([
            'role_id' => 2,
            'fullname' => 'Guru Pengajar',
            'username' => 'guru.pengajar',
            'password' => bcrypt('password'),
            'created_at' => now(),
        ]);
        $teacher = User::findOrFail($teacherId);

        // Schedule is on 'Senin'
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $teacherId,
            'subject' => 'Fisika Terapan',
            'day_of_week' => 'Senin',
            'class_name' => 'XI RPL 1',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2026-11-06 is Friday (Jumat), NOT Senin!
        $this->actingAs($teacher)
            ->post(route('guru.jurnal.store'), [
                'schedule_id' => $scheduleId,
                'date' => '2026-11-06',
                'topic_description' => 'Membahas bab kinematika',
            ])
            ->assertSessionHasErrors('schedule_id');

        $this->assertDatabaseMissing('journals', [
            'schedule_id' => $scheduleId,
            'topic_description' => 'Membahas bab kinematika',
        ]);
    }

    public function test_can_save_journal_when_schedule_day_matches_date(): void
    {
        $teacherId = DB::table('users')->insertGetId([
            'role_id' => 2,
            'fullname' => 'Guru Pengajar',
            'username' => 'guru.pengajar',
            'password' => bcrypt('password'),
            'created_at' => now(),
        ]);
        $teacher = User::findOrFail($teacherId);

        // Schedule is on 'Jumat'
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $teacherId,
            'subject' => 'Pemrograman Web',
            'day_of_week' => 'Jumat',
            'class_name' => 'XII RPL 2',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2026-11-06 is Friday (Jumat)
        $this->actingAs($teacher)
            ->post(route('guru.jurnal.store'), [
                'schedule_id' => $scheduleId,
                'date' => '2026-11-06',
                'topic_description' => 'Praktikum Laravel Controller dan Blade',
            ])
            ->assertRedirect(route('guru.jurnal'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('journals', [
            'schedule_id' => $scheduleId,
            'date' => '2026-11-06',
            'topic_description' => 'Praktikum Laravel Controller dan Blade',
        ]);
    }
}
