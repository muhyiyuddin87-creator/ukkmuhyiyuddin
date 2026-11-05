<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GuruJournalReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_reminds_teacher_when_today_schedule_has_no_journal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $scheduleId = $this->createTodaySchedule($teacher);

        $this->actingAs($teacher)
            ->get(route('dashboard.role', ['role' => 'guru']))
            ->assertOk()
            ->assertSee('Jurnal belum diisi')
            ->assertSee('Matematika')
            ->assertSee(route('guru.jurnal'));

        DB::table('journals')->insert([
            'schedule_id' => $scheduleId,
            'date' => now()->toDateString(),
            'topic_description' => 'Materi hari ini',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('dashboard.role', ['role' => 'guru']))
            ->assertOk()
            ->assertDontSee('Jurnal belum diisi');
    }

    public function test_dashboard_does_not_remind_teacher_without_a_schedule_today(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $weekdayNames = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $differentDay = $weekdayNames[now()->addDay()->format('l')];

        DB::table('schedules')->insert([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Matematika',
            'day_of_week' => $differentDay,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('dashboard.role', ['role' => 'guru']))
            ->assertOk()
            ->assertDontSee('Jurnal belum diisi');
    }

    public function test_kesiswaan_does_not_see_journal_reminder_in_isi_jurnal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $kesiswaan = User::where('username', 'kesiswaan')->firstOrFail();

        // Create a schedule in the school (belonging to guru, not kesiswaan)
        $teacher = User::where('username', 'guru')->firstOrFail();
        $this->createTodaySchedule($teacher);

        $this->actingAs($kesiswaan)
            ->get(route('guru.jurnal'))
            ->assertOk()
            ->assertDontSee('Jadwal Belum Diisi Jurnal')
            ->assertDontSee('Pengingat:');
    }

    public function test_teacher_without_schedules_does_not_see_journal_reminder(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('dashboard.role', ['role' => 'guru']))
            ->assertOk()
            ->assertDontSee('Jurnal belum diisi');

        $this->get(route('guru.jurnal'))
            ->assertOk()
            ->assertDontSee('Jadwal Belum Diisi Jurnal')
            ->assertDontSee('Pengingat:');
    }

    public function test_teacher_with_unfilled_schedule_sees_reminder_in_isi_jurnal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $this->createTodaySchedule($teacher);

        $this->actingAs($teacher)
            ->get(route('guru.jurnal'))
            ->assertOk()
            ->assertSee('Jadwal Belum Diisi Jurnal')
            ->assertSee('Pengingat:');
    }

    private function createTodaySchedule(User $teacher): int
    {
        $weekdayNames = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        return DB::table('schedules')->insertGetId([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Matematika',
            'day_of_week' => $weekdayNames[now()->format('l')],
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
