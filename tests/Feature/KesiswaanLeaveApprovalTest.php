<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KesiswaanLeaveApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_kesiswaan_can_view_leave_history_and_approve_or_reject(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('username', 'kesiswaan')->firstOrFail();
        $studentId = $this->createStudent();
        $pendingId = $this->createLeaveRequest($studentId, $user->getKey(), 'Pending', 'Surat dokter');
        $historyId = $this->createLeaveRequest($studentId, $user->getKey(), 'Approved', 'Izin sebelumnya', $user->getKey());

        $this->actingAs($user);
        $this->get('/dashboard/kesiswaan')
            ->assertOk()
            ->assertSee('Izin Menunggu Persetujuan')
            ->assertSee('Riwayat Selesai Diproses')
            ->assertDontSee('Presensi Siswa');
        $this->get(route('kesiswaan.izin'))
            ->assertOk()
            ->assertSee('Surat dokter')
            ->assertSee('Riwayat Izin')
            ->assertSee('Statistik Keputusan Izin')
            ->assertSee('id="leave-history-chart"', false)
            ->assertSee('Izin sebelumnya')
            ->assertSee('Menunggu Persetujuan');

        // Kesiswaan can approve
        $this->patch(route('kesiswaan.izin.approval', $pendingId), [
            'status_approval' => 'Approved',
        ])->assertRedirect(route('kesiswaan.izin'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $pendingId,
            'status_approval' => 'Approved',
            'verified_by' => $user->getKey(),
        ]);

        // Waka also sees the leave requests in Laporan
        $waka = User::where('username', 'waka')->firstOrFail();
        $this->actingAs($waka)
            ->get(route('waka.laporan'))
            ->assertOk()
            ->assertSee('Rekap Izin Siswa')
            ->assertSee('Surat dokter')
            ->assertSee('Izin sebelumnya');
    }

    public function test_kesiswaan_has_no_presensi_or_leave_request_crud_routes(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('username', 'kesiswaan')->firstOrFail();
        $teacher = User::where('username', 'guru')->firstOrFail();
        $scheduleId = DB::table('schedules')->insertGetId([
            'teacher_id' => $teacher->getKey(),
            'subject' => 'Matematika',
            'class_name' => 'X RPL',
            'day_of_week' => 'Kamis',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'cycle_type' => 'Regular',
            'cycle_number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);
        $this->get('/dashboard/kesiswaan')
            ->assertOk()
            ->assertSee('Isi Jurnal');
        $this->get(route('guru.jurnal'))
            ->assertOk()
            ->assertSee('Ruang kerja kesiswaan')
            ->assertSee('Matematika')
            ->assertSee('bg-amber-700')
            ->assertSee('bg-amber-600')
            ->assertDontSee('bg-indigo-900')
            ->assertSee('Riwayat Izin Siswa', false);
        $this->post(route('guru.jurnal.store'), [
            'schedule_id' => $scheduleId,
            'date' => '2026-10-22',
            'topic_description' => 'Jurnal dari Kesiswaan',
        ])->assertRedirect(route('guru.jurnal'))
            ->assertSessionHas('success');
        $journal = DB::table('journals')
            ->where('topic_description', 'Jurnal dari Kesiswaan')
            ->first();
        $this->assertNotNull($journal);
        $this->assertSame($user->getKey(), (int) $journal->created_by);

        $this->get('/kesiswaan/presensi')->assertNotFound();
        $this->post('/kesiswaan/izin')->assertMethodNotAllowed();
        $this->delete('/kesiswaan/izin/1')->assertNotFound();
    }

    public function test_other_roles_cannot_access_kesiswaan_approval(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('username', 'guru')->firstOrFail());

        $this->get(route('kesiswaan.izin'))->assertForbidden();
        $this->patch(route('kesiswaan.izin.approval', 1), [
            'status_approval' => 'Approved',
        ])->assertForbidden();
    }

    public function test_student_can_submit_and_track_their_own_leave_request(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');
        $student = User::where('username', 'siswa')->firstOrFail();
        $studentId = DB::table('siswas')->insertGetId([
            'name' => 'Siswa',
            'kelas' => 'X-1',
            'jurusan' => 'IPA',
            'alamat' => 'Alamat Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $student->student_id = $studentId;
        $student->save();
        $otherStudentId = $this->createStudent();

        $this->actingAs($student);
        $this->get(route('siswa.izin'))
            ->assertOk()
            ->assertSee('Buat Pengajuan Izin')
            ->assertSee('leave-selfie-camera', false)
            ->assertDontSee('Tanggal Mulai')
            ->assertDontSee('Tanggal Selesai')
            ->assertSee('Pengajuan untuk Siswa — X-1')
            ->assertSee('Nama Siswa')
            ->assertSee('Kelas')
            ->assertSee('Diproses Oleh')
            ->assertSee('sinkron otomatis dengan Kesiswaan');

        $response = $this->postJson(route('siswa.izin.store'), [
            'student_id' => $otherStudentId,
            'reason' => 'Sakit',
            'photo' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.student_id', $studentId)
            ->assertJsonPath('data.applicant_id', $student->getKey())
            ->assertJsonPath('data.status_approval', 'Pending')
            ->assertJsonPath('data.start_date', now()->toDateString())
            ->assertJsonPath('data.end_date', now()->toDateString());
        $photoPath = $response->json('data.document_path');
        $this->assertNotEmpty($photoPath);
        Storage::disk('public')->assertExists($photoPath);
        $this->assertDatabaseHas('leave_requests', [
            'student_id' => $studentId,
            'applicant_id' => $student->getKey(),
            'reason' => 'Sakit',
            'document_path' => $photoPath,
            'status_approval' => 'Pending',
        ]);
        $this->assertDatabaseMissing('leave_requests', [
            'student_id' => $otherStudentId,
            'reason' => 'Sakit',
        ]);

        $this->getJson(route('siswa.izin.updates'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.reason', 'Sakit')
            ->assertJsonPath('data.0.document_path', $photoPath);

        $kesiswaan = User::where('username', 'kesiswaan')->firstOrFail();
        $this->actingAs($kesiswaan)
            ->getJson(route('kesiswaan.izin.updates'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.student_name', 'Siswa')
            ->assertJsonPath('data.0.document_path', $photoPath);

        // Kesiswaan can approve
        $this->patch(route('kesiswaan.izin.approval', $response->json('data.id')), [
            'status_approval' => 'Approved',
        ])->assertRedirect(route('kesiswaan.izin'));

        $this->actingAs($student)
            ->getJson(route('siswa.izin.updates'))
            ->assertOk()
            ->assertJsonPath('data.0.student_name', 'Siswa')
            ->assertJsonPath('data.0.kelas', 'X-1')
            ->assertJsonPath('data.0.status_approval', 'Approved')
            ->assertJsonPath('data.0.verifier_name', $kesiswaan->fullname);
    }

    public function test_student_can_select_leave_type_sakit_or_izin_and_photo_is_required(): void
    {
        $this->seed(DatabaseSeeder::class);
        $studentUser = User::where('username', 'siswa')->firstOrFail();
        $studentId = $this->createStudent();
        $studentUser->student_id = $studentId;
        $studentUser->save();

        // 0. Submit without photo fails validation
        $this->actingAs($studentUser)->post(route('siswa.izin.store'), [
            'leave_type' => 'Sakit',
            'reason' => 'Sakit tanpa foto',
        ])->assertSessionHasErrors('photo');

        // 1. Submit leave request with leave_type = Sakit and photo
        $this->actingAs($studentUser)->post(route('siswa.izin.store'), [
            'leave_type' => 'Sakit',
            'reason' => 'Demam tinggi dan flu',
            'photo' => UploadedFile::fake()->image('surat_dokter.jpg'),
        ])->assertRedirect(route('siswa.izin'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'student_id' => $studentId,
            'leave_type' => 'Sakit',
            'reason' => 'Demam tinggi dan flu',
        ]);

        // 2. Submit leave request with leave_type = Izin and photo
        $this->actingAs($studentUser)->post(route('siswa.izin.store'), [
            'leave_type' => 'Izin',
            'reason' => 'Menghadiri acara keluarga di luar kota',
            'photo' => UploadedFile::fake()->image('surat_izin.jpg'),
        ])->assertRedirect(route('siswa.izin'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'student_id' => $studentId,
            'leave_type' => 'Izin',
            'reason' => 'Menghadiri acara keluarga di luar kota',
        ]);

        // 3. View /siswa/izin and see both badges
        $this->get(route('siswa.izin'))
            ->assertOk()
            ->assertSee('Jenis Pengajuan')
            ->assertSee('value="Sakit"', false)
            ->assertSee('value="Izin"', false)
            ->assertSee('Sakit')
            ->assertSee('Izin');
    }

    private function createStudent(): int
    {
        return DB::table('siswas')->insertGetId([
            'name' => 'Siswa Uji',
            'kelas' => 'X-1',
            'jurusan' => 'IPA',
            'alamat' => 'Alamat Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createLeaveRequest(
        int $studentId,
        int|string $applicantId,
        string $status,
        string $reason,
        int|string|null $verifierId = null,
    ): int {
        return DB::table('leave_requests')->insertGetId([
            'student_id' => $studentId,
            'applicant_id' => $applicantId,
            'verified_by' => $verifierId,
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-15',
            'reason' => $reason,
            'status_approval' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
