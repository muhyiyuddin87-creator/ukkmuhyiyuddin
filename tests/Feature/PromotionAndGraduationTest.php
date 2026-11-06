<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PromotionAndGraduationTest extends TestCase
{
    use RefreshDatabase;

    private function createWakaUser(): User
    {
        return User::create([
            'username' => 'waka_' . uniqid(),
            'password' => bcrypt('password'),
            'fullname' => 'Waka Test',
            'role_id' => 1,
            'is_active' => 1,
        ]);
    }

    public function test_waka_can_access_kenaikan_kelas_and_alumni_pages(): void
    {
        $waka = $this->createWakaUser();

        $response = $this->actingAs($waka)->get(route('waka.kenaikan_kelas'));
        $response->assertStatus(200);
        $response->assertSee('Kenaikan Kelas');

        $responseAlumni = $this->actingAs($waka)->get(route('waka.alumni'));
        $responseAlumni->assertStatus(200);
        $responseAlumni->assertSee('Data Alumni');
    }

    public function test_promotion_and_graduation_process_works_correctly(): void
    {
        $waka = $this->createWakaUser();

        // Setup test students
        $now = now();
        $studentXId = DB::table('siswas')->insertGetId([
            'nis' => 'TEST_X_01',
            'name' => 'Siswa Test Kelas X',
            'kelas' => 'X RPL 1',
            'jurusan' => 'RPL',
            'alamat' => 'Bantul',
            'status' => 'aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $studentXIId = DB::table('siswas')->insertGetId([
            'nis' => 'TEST_XI_01',
            'name' => 'Siswa Test Kelas XI',
            'kelas' => 'XI RPL 1',
            'jurusan' => 'RPL',
            'alamat' => 'Bantul',
            'status' => 'aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $studentXIIId = DB::table('siswas')->insertGetId([
            'nis' => 'TEST_XII_01',
            'name' => 'Siswa Test Kelas XII',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'RPL',
            'alamat' => 'Bantul',
            'status' => 'aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Student staying back in XII
        $studentXIIStayId = DB::table('siswas')->insertGetId([
            'nis' => 'TEST_XII_STAY',
            'name' => 'Siswa Tinggal Kelas XII',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'RPL',
            'alamat' => 'Bantul',
            'status' => 'aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create user accounts for testing account deactivation
        $userStudentXII = User::create([
            'username' => 'test_xii_acc',
            'password' => bcrypt('password'),
            'fullname' => 'Siswa Test Kelas XII',
            'role_id' => 3,
            'student_id' => $studentXIIId,
            'is_active' => 1,
        ]);

        $userParentXII = User::create([
            'username' => 'test_xii_ortu',
            'password' => bcrypt('password'),
            'fullname' => 'Orang Tua Siswa Test XII',
            'role_id' => 6,
            'student_id' => $studentXIIId,
            'is_active' => 1,
        ]);

        // Execute promotion
        $response = $this->actingAs($waka)->post(route('waka.kenaikan_kelas.proses'), [
            'tahun_lulus' => '2026/2027',
            'tanggal_lulus' => '2026-06-20',
            'keterangan' => 'Lulus Uji Kompetensi Keahlian',
            'excluded_students' => [$studentXIIStayId],
        ]);

        $response->assertRedirect(route('waka.kenaikan_kelas'));
        $response->assertSessionHas('success');

        // Verify Student X promoted to XI
        $studentX = DB::table('siswas')->where('id', $studentXId)->first();
        $this->assertEquals('XI RPL 1', $studentX->kelas);
        $this->assertEquals('aktif', $studentX->status);

        // Verify Student XI promoted to XII
        $studentXI = DB::table('siswas')->where('id', $studentXIId)->first();
        $this->assertEquals('XII RPL 1', $studentXI->kelas);
        $this->assertEquals('aktif', $studentXI->status);

        // Verify Student XII graduated
        $studentXII = DB::table('siswas')->where('id', $studentXIIId)->first();
        $this->assertEquals('lulus', $studentXII->status);
        $this->assertEquals('2026/2027', $studentXII->tahun_lulus);

        // Verify Alumni record created
        $alumni = DB::table('alumnis')->where('student_id', $studentXIIId)->first();
        $this->assertNotNull($alumni);
        $this->assertEquals('TEST_XII_01', $alumni->nis);
        $this->assertEquals('XII RPL 1', $alumni->kelas_terakhir);
        $this->assertEquals('2026/2027', $alumni->tahun_lulus);

        // Verify user accounts deactivated
        $this->assertEquals(0, $userStudentXII->fresh()->is_active);
        $this->assertEquals(0, $userParentXII->fresh()->is_active);

        // Verify Student staying back was not graduated
        $studentStay = DB::table('siswas')->where('id', $studentXIIStayId)->first();
        $this->assertEquals('XII RPL 1', $studentStay->kelas);
        $this->assertEquals('aktif', $studentStay->status);

        // Clean up test data
        DB::table('alumnis')->where('student_id', $studentXIIId)->delete();
        $userStudentXII->delete();
        $userParentXII->delete();
        DB::table('siswas')->whereIn('id', [$studentXId, $studentXIId, $studentXIIId, $studentXIIStayId])->delete();
    }
}
