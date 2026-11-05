<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GuruAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_take_and_update_attendance_for_their_own_journal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $studentIds = [
            $this->createStudent('Siswa Pertama'),
            $this->createStudent('Siswa Kedua'),
        ];
        $journalId = $this->createJournal($teacher);

        $this->actingAs($teacher)
            ->get(route('guru.presensi'))
            ->assertOk()
            ->assertSee('Siswa Pertama')
            ->assertSee('Presensi Siswa');

        $attendance = [
            'journal_id' => $journalId,
            'date' => '2026-10-16',
            'student_ids' => $studentIds,
            'statuses' => ['Hadir', 'Sakit'],
        ];

        $this->post(route('guru.presensi.store'), $attendance)
            ->assertRedirect(route('guru.presensi'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'journal_id' => $journalId,
            'student_id' => $studentIds[0],
            'status' => 'Hadir',
            'date' => '2026-10-16',
        ]);
        $this->assertDatabaseHas('attendances', [
            'journal_id' => $journalId,
            'student_id' => $studentIds[1],
            'status' => 'Sakit',
        ]);

        $attendance['statuses'] = ['Alpha', 'Izin'];
        $this->post(route('guru.presensi.store'), $attendance)
            ->assertRedirect(route('guru.presensi'));

        $this->assertDatabaseCount('attendances', 2);
        $this->assertDatabaseHas('attendances', [
            'journal_id' => $journalId,
            'student_id' => $studentIds[0],
            'status' => 'Alpha',
        ]);
    }

    public function test_guru_cannot_record_attendance_for_another_teachers_journal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::where('username', 'guru')->firstOrFail();
        $otherTeacherId = DB::table('users')->insertGetId([
            'role_id' => 2,
            'username' => 'guru.lain',
            'password' => bcrypt('password'),
            'fullname' => 'Guru Lain',
            'created_at' => now(),
        ]);
        $studentId = $this->createStudent('Siswa Uji');
        $journalId = $this->createJournal(User::findOrFail($otherTeacherId));

        $this->actingAs($teacher)
            ->post(route('guru.presensi.store'), [
                'journal_id' => $journalId,
                'date' => '2026-10-16',
                'student_ids' => [$studentId],
                'statuses' => ['Hadir'],
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_non_guru_cannot_open_teacher_attendance_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $this->actingAs($waka)
            ->get(route('guru.presensi'))
            ->assertForbidden();
    }

    private function createStudent(string $name): int
    {
        return DB::table('siswas')->insertGetId([
            'name' => $name,
            'kelas' => 'X RPL',
            'jurusan' => 'RPL',
            'alamat' => 'Bandung',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createJournal(User $teacher): int
    {
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Matematika',
            'day_of_week' => 'Jumat',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('journals')->insertGetId([
            'schedule_id' => $scheduleId,
            'date' => '2026-10-16',
            'topic_description' => 'Materi Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
