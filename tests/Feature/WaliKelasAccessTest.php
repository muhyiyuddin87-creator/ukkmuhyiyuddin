<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WaliKelasAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_kelas_can_only_view_assigned_classes_and_verify_their_leave_requests(): void
    {
        $this->seed(DatabaseSeeder::class);
        $wali = $this->createWali();
        $classIds = $this->createClasses($wali);
        $ownStudentId = $this->createStudent('Siswa Kelas A', 'X-A');
        $otherStudentId = $this->createStudent('Siswa Kelas B', 'X-C');
        $ownJournalId = $this->createJournal('X-A');
        $otherJournalId = $this->createJournal('X-C');
        $this->createAttendance($ownJournalId, $ownStudentId);
        $this->createAttendance($otherJournalId, $otherStudentId);
        $ownLeaveId = $this->createLeaveRequest($ownStudentId);
        $otherLeaveId = $this->createLeaveRequest($otherStudentId);

        $this->actingAs($wali)
            ->get(route('dashboard.role', 'wali-kelas'))
            ->assertOk()
            ->assertSee('Dashboard Guru')
            ->assertSee('Verifikasi Izin Wali Kelas')
            ->assertSee('Edit Profil')
            ->assertSee(route('profile'), false)
            ->assertDontSee('>Profil</a>', false);

        $this->get(route('wali-kelas.presensi'))
            ->assertRedirect(route('guru.presensi'));

        $this->get(route('wali-kelas.izin'))
            ->assertOk()
            ->assertSee('Siswa Kelas A')
            ->assertDontSee('Siswa Kelas B')
            ->assertSee('Guru')
            ->assertSee('Verifikasi Izin Wali Kelas')
            ->assertDontSee('Ruang Kerja Wali Kelas');

        $this->patch(route('wali-kelas.izin.approval', $ownLeaveId), [
            'status_approval' => 'Approved',
        ])->assertRedirect(route('wali-kelas.izin'));
        $this->assertDatabaseHas('leave_requests', [
            'id' => $ownLeaveId,
            'status_approval' => 'Approved',
            'verified_by' => $wali->getKey(),
        ]);

        $this->patch(route('wali-kelas.izin.approval', $otherLeaveId), [
            'status_approval' => 'Approved',
        ])->assertNotFound();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $otherLeaveId,
            'status_approval' => 'Pending',
            'verified_by' => null,
        ]);

        $this->get(route('wali-kelas.jurnal'))
            ->assertRedirect(route('guru.jurnal'));
    }

    public function test_waka_can_assign_wali_classes_to_a_teacher_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $classIds = [];
        foreach (['X-A', 'X-B'] as $className) {
            $classIds[] = DB::table('school_classes')->insertGetId([
                'name' => $className,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs(User::where('username', 'waka')->firstOrFail())
            ->post(route('waka.accounts.store'), [
                'fullname' => 'Wali Uji',
                'username' => 'wali.uji',
                'password' => 'password123',
                'role_id' => 2,
                'class_ids' => $classIds,
            ])
            ->assertRedirect(route('waka.accounts'));

        $wali = User::where('username', 'wali.uji')->firstOrFail();
        $this->assertSame(2, (int) $wali->role_id);
        $this->assertSame(
            $classIds,
            DB::table('user_school_classes')
                ->where('user_id', $wali->getKey())
                ->orderBy('school_class_id')
                ->pluck('school_class_id')
                ->map(fn ($id) => (int) $id)
                ->all()
        );

        $this->patch(route('waka.accounts.update', $wali->getKey()), [
            'fullname' => 'Wali Uji',
            'username' => 'wali.uji',
            'role_id' => 2,
            'class_ids' => [$classIds[1]],
        ])->assertRedirect(route('waka.accounts'));

        $this->assertDatabaseCount('user_school_classes', 1);
        $this->assertDatabaseHas('user_school_classes', [
            'user_id' => $wali->getKey(),
            'school_class_id' => $classIds[1],
        ]);
        $this->actingAs($wali)
            ->get(route('dashboard.role', 'wali-kelas'))
            ->assertOk()
            ->assertSee('Dashboard Guru')
            ->assertSee('Verifikasi Izin Wali Kelas')
            ->assertDontSee('Ruang Kerja Wali Kelas');
    }

    public function test_non_wali_roles_cannot_open_wali_pages_or_approve_leave(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('username', 'guru')->firstOrFail())
            ->get(route('wali-kelas.presensi'))
            ->assertRedirect(route('guru.presensi'));

        $this->patch(route('wali-kelas.izin.approval', 1), [
            'status_approval' => 'Approved',
        ])->assertForbidden();
    }

    public function test_legacy_wali_role_uses_teacher_dashboard_and_can_open_wali_verification(): void
    {
        $this->seed(DatabaseSeeder::class);
        $wali = DB::table('users')->insertGetId([
            'role_id' => 5,
            'username' => 'wali.lama',
            'password' => bcrypt('password'),
            'fullname' => 'Wali Lama',
            'is_active' => true,
            'created_at' => now(),
        ]);
        $user = User::findOrFail($wali);
        $this->createClasses($user);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('dashboard.role', 'guru'));
        $this->get(route('dashboard.role', 'wali-kelas'))
            ->assertOk()
            ->assertSee('Dashboard Guru')
            ->assertSee('Verifikasi Izin Wali Kelas');
        $this->get(route('wali-kelas.izin'))
            ->assertOk()
            ->assertSee(route('dashboard.role', 'guru'), false)
            ->assertSee('Dashboard Guru');
    }

    public function test_waka_cannot_create_a_separate_wali_kelas_role(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('username', 'waka')->firstOrFail())
            ->post(route('waka.accounts.store'), [
                'fullname' => 'Akun Wali Terpisah',
                'username' => 'wali.terpisah',
                'password' => 'password123',
                'role_id' => 5,
            ])
            ->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['username' => 'wali.terpisah']);
    }

    public function test_assigned_teacher_uses_teacher_dashboard_with_wali_leave_verification_access(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = $this->createWali();
        $this->createClasses($teacher);

        $this->actingAs($teacher)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Guru Pengajar')
            ->assertSee('Wali Kelas')
            ->assertSee(route('guru.jurnal'), false)
            ->assertSee(route('guru.presensi'), false)
            ->assertSee(route('wali-kelas.izin'), false)
            ->assertDontSee(route('wali-kelas.jurnal'), false);

        $this->post(route('logout'));
        $this->post(route('login.post'), [
            'username' => 'wali',
            'password' => 'password',
        ])->assertRedirect(route('dashboard.role', 'guru'));

        $this->get(route('dashboard.role', 'guru'))
            ->assertOk()
            ->assertSee('Dashboard Guru')
            ->assertSee('Verifikasi Izin Wali Kelas');
        $this->get(route('dashboard'))
            ->assertRedirect(route('dashboard.role', 'guru'));
    }

    public function test_assigned_teacher_attendance_is_limited_to_their_wali_classes(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = $this->createWali();
        $this->createClasses($teacher);
        $waliStudentId = $this->createStudent('Siswa Kelas Wali', 'X-A');
        $otherStudentId = $this->createStudent('Siswa Kelas Lain', 'X-C');
        $assignedScheduleId = $this->createScheduleForTeacher($teacher, 'X-A');
        $otherScheduleId = $this->createScheduleForTeacher($teacher, 'X-C');
        $assignedJournalId = $this->createJournalForSchedule($assignedScheduleId, $teacher);
        $otherJournalId = $this->createJournalForSchedule($otherScheduleId, $teacher);

        $this->actingAs($teacher)
            ->get(route('guru.presensi'))
            ->assertOk()
            ->assertSee('Siswa Kelas Wali')
            ->assertDontSee('Siswa Kelas Lain');

        $this->post(route('guru.presensi.store'), [
            'journal_id' => $otherJournalId,
            'date' => '2026-10-22',
            'student_ids' => [$otherStudentId],
            'statuses' => ['Hadir'],
        ])->assertNotFound();

        $this->post(route('guru.presensi.store'), [
            'journal_id' => $assignedJournalId,
            'date' => '2026-10-22',
            'student_ids' => [$waliStudentId],
            'statuses' => ['Hadir'],
        ])->assertRedirect(route('guru.presensi'));

        $this->assertDatabaseHas('attendances', [
            'journal_id' => $assignedJournalId,
            'student_id' => $waliStudentId,
            'status' => 'Hadir',
        ]);
        $this->assertDatabaseMissing('attendances', [
            'journal_id' => $otherJournalId,
            'student_id' => $otherStudentId,
        ]);
    }

    private function createWali(): User
    {
        $waliId = DB::table('users')->insertGetId([
            'role_id' => 2,
            'username' => 'wali',
            'password' => bcrypt('password'),
            'fullname' => 'Wali Kelas Uji',
            'is_active' => true,
            'created_at' => now(),
        ]);

        return User::findOrFail($waliId);
    }

    private function createClasses(User $wali): array
    {
        $classIds = [];
        foreach (['X-A', 'X-B'] as $className) {
            $classIds[] = DB::table('school_classes')->insertGetId([
                'name' => $className,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($classIds as $classId) {
            DB::table('user_school_classes')->insert([
                'user_id' => $wali->getKey(),
                'school_class_id' => $classId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $classIds;
    }

    private function createStudent(string $name, string $className): int
    {
        return DB::table('siswas')->insertGetId([
            'name' => $name,
            'kelas' => $className,
            'jurusan' => 'IPA',
            'alamat' => 'Alamat uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createJournal(string $className): int
    {
        $scheduleId = $this->createSchedule($className);

        return DB::table('journals')->insertGetId([
            'schedule_id' => $scheduleId,
            'date' => '2026-10-20',
            'topic_description' => 'Jurnal kelas '.$className,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSchedule(string $className): int
    {
        $teacher = User::where('username', 'guru')->firstOrFail();

        return DB::table('schedules')->insertGetId([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Matematika',
            'class_name' => $className,
            'day_of_week' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createScheduleForTeacher(User $teacher, string $className): int
    {
        return DB::table('schedules')->insertGetId([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Bahasa Indonesia',
            'class_name' => $className,
            'day_of_week' => 'Kamis',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createJournalForSchedule(int $scheduleId, User $teacher): int
    {
        return DB::table('journals')->insertGetId([
            'schedule_id' => $scheduleId,
            'date' => '2026-10-22',
            'topic_description' => 'Pembelajaran',
            'created_by' => $teacher->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAttendance(int $journalId, int $studentId): void
    {
        DB::table('attendances')->insert([
            'journal_id' => $journalId,
            'student_id' => $studentId,
            'status' => 'Hadir',
            'date' => '2026-10-20',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createLeaveRequest(int $studentId): int
    {
        $studentUserId = DB::table('users')->insertGetId([
            'role_id' => 3,
            'username' => 'siswa.'.uniqid(),
            'password' => bcrypt('password'),
            'fullname' => 'Siswa',
            'student_id' => $studentId,
            'is_active' => true,
            'created_at' => now(),
        ]);

        return DB::table('leave_requests')->insertGetId([
            'student_id' => $studentId,
            'applicant_id' => $studentUserId,
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-20',
            'reason' => 'Izin uji',
            'status_approval' => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
