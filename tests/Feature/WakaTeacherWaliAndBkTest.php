<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\WaliKelasAndBkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WakaTeacherWaliAndBkTest extends TestCase
{
    use RefreshDatabase;

    private User $waka;
    private User $teacher;
    private int $class1Id;
    private int $class2Id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waka = User::create([
            'role_id' => 1,
            'username' => 'waka_user',
            'fullname' => 'Waka Kurikulum',
            'password' => Hash::make('password123'),
            'is_active' => 1,
        ]);

        $this->teacher = User::create([
            'role_id' => 2,
            'username' => 'guru_bk_test',
            'fullname' => 'CANDRA RAHADIAN, S.Pd',
            'password' => Hash::make('password123'),
            'is_active' => 1,
        ]);

        $this->class1Id = DB::table('school_classes')->insertGetId([
            'name' => 'X AK 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->class2Id = DB::table('school_classes')->insertGetId([
            'name' => 'X AK 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_waka_can_view_data_guru_with_wali_and_bk_columns(): void
    {
        $response = $this->actingAs($this->waka)->get(route('waka.guru'));
        $response->assertOk();
        $response->assertSee('Wali Kelas');
        $response->assertSee('Guru BK (Kelas)');
    }

    public function test_waka_can_assign_bk_classes_and_updates_role_to_kesiswaan(): void
    {
        $response = $this->actingAs($this->waka)->patch(route('waka.guru.update', ['id' => $this->teacher->id]), [
            'fullname' => $this->teacher->fullname,
            'username' => $this->teacher->username,
            'bk_class_ids' => [$this->class1Id, $this->class2Id],
        ]);

        $response->assertRedirect(route('waka.guru'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_bk_classes', [
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class1Id,
        ]);
        $this->assertDatabaseHas('user_bk_classes', [
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class2Id,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $this->teacher->id,
            'role_id' => 4,
        ]);
    }

    public function test_waka_can_create_teacher_with_wali_and_bk_classes(): void
    {
        $response = $this->actingAs($this->waka)->post(route('waka.guru.store'), [
            'fullname' => 'Guru Baru S.Pd',
            'username' => 'guru_baru',
            'password' => 'password123',
            'class_ids' => [$this->class1Id],
            'bk_class_ids' => [$this->class2Id],
        ]);

        $response->assertRedirect(route('waka.guru'));
        $response->assertSessionHas('success');

        $newTeacher = DB::table('users')->where('username', 'guru_baru')->first();
        $this->assertNotNull($newTeacher);

        $this->assertDatabaseHas('user_school_classes', [
            'user_id' => $newTeacher->id,
            'school_class_id' => $this->class1Id,
        ]);
        $this->assertDatabaseHas('user_bk_classes', [
            'user_id' => $newTeacher->id,
            'school_class_id' => $this->class2Id,
        ]);
        // BK teacher has role_id = 4 (Kesiswaan)
        $this->assertEquals(4, (int) $newTeacher->role_id);
    }

    public function test_teacher_dashboard_shows_wali_and_bk_classes(): void
    {
        DB::table('user_school_classes')->insert([
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class1Id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_bk_classes')->insert([
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class2Id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->teacher)->get(route('dashboard.role', ['role' => 'guru']));
        $response->assertOk();
        $response->assertSee('X AK 1');
        $response->assertSee('X AK 2');
        $response->assertSee('Kelas Bimbingan');
    }

    public function test_kesiswaan_teacher_can_teach_and_access_schedules(): void
    {
        // Set teacher role to 4 (Kesiswaan)
        $this->teacher->update(['role_id' => 4]);

        DB::table('user_bk_classes')->insert([
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class1Id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('schedules')->insert([
            'teacher_id' => $this->teacher->id,
            'cycle_number' => 1,
            'day_of_week' => 'Senin',
            'subject' => 'Bimbingan Konseling',
            'class_name' => 'X AK 1',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'cycle_type' => 'Regular',
            'academic_year' => '2026/2027',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kesiswaan teacher can access schedule page
        $responseJadwal = $this->actingAs($this->teacher)->get(route('guru.jadwal'));
        $responseJadwal->assertOk();
        $responseJadwal->assertSee('Bimbingan Konseling');

        // Kesiswaan teacher can access dashboard kesiswaan and see their schedules and BK class
        $responseDash = $this->actingAs($this->teacher)->get(route('dashboard.role', ['role' => 'kesiswaan']));
        $responseDash->assertOk();
        $responseDash->assertSee('Bimbingan Konseling');
        $responseDash->assertSee('Kelas Bimbingan BK');

        // Waka still sees Kesiswaan teacher in data-guru table
        $responseWaka = $this->actingAs($this->waka)->get(route('waka.guru'));
        $responseWaka->assertOk();
        $responseWaka->assertSee($this->teacher->fullname);
        $responseWaka->assertSee('Kesiswaan');
    }

    public function test_kesiswaan_teacher_can_take_attendance_when_creating_and_updating_journal(): void
    {
        // Set teacher role to 4 (Kesiswaan)
        $this->teacher->update(['role_id' => 4]);

        $studentId = DB::table('siswas')->insertGetId([
            'nis' => '12345678',
            'name' => 'Budi Santoso',
            'kelas' => 'X AK 1',
            'jurusan' => 'AK',
            'alamat' => 'Alamat siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $daysMap = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $scheduleDay = $daysMap[(int) now()->dayOfWeek] ?? 'Senin';

        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $this->teacher->id,
            'cycle_number' => 1,
            'day_of_week' => $scheduleDay,
            'subject' => 'Bimbingan Konseling',
            'class_name' => 'X AK 1',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'cycle_type' => 'Regular',
            'academic_year' => '2026/2027',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kesiswaan teacher accesses guru.jurnal and sees Presensi form elements
        $responseJurnal = $this->actingAs($this->teacher)->get(route('guru.jurnal'));
        $responseJurnal->assertOk();
        $responseJurnal->assertSee('Presensi Siswa');
        $responseJurnal->assertSee('Budi Santoso');

        // Accessing guru.presensi also redirects to guru.jurnal (no 403)
        $responsePresensi = $this->actingAs($this->teacher)->get(route('guru.presensi'));
        $responsePresensi->assertRedirect(route('guru.jurnal'));

        // Store journal and attendance as Kesiswaan teacher
        $storeResponse = $this->actingAs($this->teacher)->post(route('guru.jurnal.store'), [
            'schedule_id' => $scheduleId,
            'date' => now()->toDateString(),
            'topic_description' => 'Bimbingan Karir dan Studi Lanjut',
            'student_ids' => [$studentId],
            'statuses' => ['Hadir'],
        ]);
        $storeResponse->assertRedirect(route('guru.jurnal'));
        $storeResponse->assertSessionHas('success', 'Jurnal dan presensi siswa berhasil disimpan.');

        $this->assertDatabaseHas('journals', [
            'schedule_id' => $scheduleId,
            'topic_description' => 'Bimbingan Karir dan Studi Lanjut',
            'created_by' => $this->teacher->id,
        ]);
        $journal = DB::table('journals')->where('schedule_id', $scheduleId)->first();
        $this->assertNotNull($journal);
        $this->assertDatabaseHas('attendances', [
            'journal_id' => $journal->id,
            'student_id' => $studentId,
            'status' => 'Hadir',
        ]);

        // Update journal and attendance as Kesiswaan teacher
        $updateResponse = $this->actingAs($this->teacher)->patch(route('guru.jurnal.update', $journal->id), [
            'schedule_id' => $scheduleId,
            'date' => now()->toDateString(),
            'topic_description' => 'Bimbingan Karir dan Studi Lanjut (Revisi)',
            'student_ids' => [$studentId],
            'statuses' => ['Izin'],
        ]);
        $updateResponse->assertRedirect(route('guru.jurnal'));
        $updateResponse->assertSessionHas('success', 'Jurnal dan presensi siswa berhasil diperbarui.');

        $this->assertDatabaseHas('attendances', [
            'journal_id' => $journal->id,
            'student_id' => $studentId,
            'status' => 'Izin',
        ]);
    }
}
