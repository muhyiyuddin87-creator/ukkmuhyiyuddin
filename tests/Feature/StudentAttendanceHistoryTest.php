<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentAttendanceHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_student_attendance(): void
    {
        $response = $this->get(route('siswa.absensi'));
        $response->assertRedirect('/login');
    }

    public function test_student_can_view_attendance_history_recorded_from_teacher_journal(): void
    {
        $this->seed(DatabaseSeeder::class);

        $studentUser = User::where('username', 'siswa')->firstOrFail();
        $teacherUser = User::where('username', 'guru')->firstOrFail();

        // Ensure student has a linked siswa record
        $studentId = DB::table('siswas')->insertGetId([
            'name' => 'Budi Santoso',
            'nis' => '17817',
            'kelas' => 'XII-RPL-2',
            'jurusan' => 'RPL',
            'alamat' => 'Jl. Pendidikan No. 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentUser->student_id = $studentId;
        $studentUser->save();

        // Create a schedule
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $teacherUser->id,
            'subject' => 'Pemrograman Berorientasi Objek',
            'class_name' => 'XII-RPL-2',
            'day_of_week' => 'Senin',
            'start_time' => '07:15:00',
            'end_time' => '10:30:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Teacher records journal
        $journalId = DB::table('journals')->insertGetId([
            'schedule_id' => $scheduleId,
            'date' => '2026-11-07',
            'topic_description' => 'Implementasi Enkapsulasi dan Polimorfisme pada OOP',
            'created_by' => $teacherUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attendance recorded for student
        DB::table('attendances')->insert([
            'journal_id' => $journalId,
            'student_id' => $studentId,
            'status' => 'Hadir',
            'date' => '2026-11-07',
            'notes' => 'Sangat aktif dalam diskusi kelas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Access student attendance page
        $response = $this->actingAs($studentUser)->get(route('siswa.absensi'));
        $response->assertOk()
            ->assertSee('Riwayat Kehadiran Siswa')
            ->assertSee('Pemrograman Berorientasi Objek')
            ->assertSee('Implementasi Enkapsulasi dan Polimorfisme pada OOP')
            ->assertSee('Hadir')
            ->assertSee('100%') // 1 Hadir from 1 Pertemuan = 100%
            ->assertSee('17817');

        // Access student dashboard
        $dashboardResponse = $this->actingAs($studentUser)->get(route('dashboard.role', ['role' => 'siswa']));
        $dashboardResponse->assertOk()
            ->assertSee('Riwayat Kehadiran Terkini')
            ->assertSee('Pemrograman Berorientasi Objek')
            ->assertSee('Hadir');
    }

    public function test_student_only_sees_their_own_attendance_records(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student1 = User::where('username', 'siswa')->firstOrFail();
        $student1Id = DB::table('siswas')->insertGetId([
            'name' => 'Siswa Satu',
            'nis' => '11111',
            'kelas' => 'XII-RPL-2',
            'jurusan' => 'RPL',
            'alamat' => 'Alamat Satu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $student1->student_id = $student1Id;
        $student1->save();

        $student2Id = DB::table('siswas')->insertGetId([
            'name' => 'Siswa Dua',
            'nis' => '22222',
            'kelas' => 'XII-RPL-2',
            'jurusan' => 'RPL',
            'alamat' => 'Alamat Dua',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $teacher = User::where('username', 'guru')->firstOrFail();
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $teacher->id,
            'subject' => 'Basis Data Lanjutan',
            'class_name' => 'XII-RPL-2',
            'day_of_week' => 'Selasa',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $journalId = DB::table('journals')->insertGetId([
            'schedule_id' => $scheduleId,
            'date' => '2026-11-08',
            'topic_description' => 'Materi Khusus Rahasia Siswa Dua',
            'created_by' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attendance recorded ONLY for student2, NOT student1
        DB::table('attendances')->insert([
            'journal_id' => $journalId,
            'student_id' => $student2Id,
            'status' => 'Sakit',
            'date' => '2026-11-08',
            'notes' => 'Catatan khusus siswa dua',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When student1 views attendance, student2's attendance must not be present
        $response = $this->actingAs($student1)->get(route('siswa.absensi'));
        $response->assertOk()
            ->assertDontSee('Materi Khusus Rahasia Siswa Dua')
            ->assertDontSee('Catatan khusus siswa dua');
    }
}
