<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RuanganSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class WakaScheduleImportTest extends TestCase
{
    use RefreshDatabase;

    private User $waka;
    private User $guru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RuanganSeeder::class);

        $this->waka = User::create([
            'role_id' => 1,
            'username' => 'waka_kurikulum',
            'fullname' => 'Waka Kurikulum',
            'password' => Hash::make('password123'),
            'is_active' => 1,
        ]);

        $this->guru = User::create([
            'role_id' => 2,
            'username' => 'aris.purwantinah.s.pd',
            'fullname' => 'ARIS PURWANTINAH, S.Pd',
            'password' => Hash::make('password123'),
            'is_active' => 1,
        ]);

        DB::table('siswas')->insert([
            'name' => 'Siswa Test',
            'nis' => '12345',
            'kelas' => 'XII BR 1',
            'jurusan' => 'BR',
            'alamat' => 'Jl. Test No. 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_waka_can_download_schedule_import_template_and_example(): void
    {
        $responseTemplate = $this->actingAs($this->waka)->get(route('waka.jadwal.template'));
        $responseTemplate->assertOk();
        $this->assertStringContainsString('template_import_jadwal.xlsx', (string) $responseTemplate->headers->get('content-disposition'));

        $responseExample = $this->actingAs($this->waka)->get(route('waka.jadwal.example'));
        $responseExample->assertOk();
        $this->assertStringContainsString('contoh_import_jadwal_5_data.xlsx', (string) $responseExample->headers->get('content-disposition'));
    }

    public function test_waka_can_import_schedules_from_excel(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            'Hari', 'Guru', 'Mata Pelajaran', 'Kelas', 'Ruangan', 'Sesi Mulai', 'Sesi Selesai', 'Putaran', 'Jenis'
        ], null, 'A1');
        $sheet->fromArray([
            ['Senin', 'ARIS PURWANTINAH, S.Pd', 'KK BR (Bisnis Retail)', 'XII BR 1', 'Lab 6', 'Jam ke-7', 'Jam ke-8', '2', 'Jurusan'],
        ], null, 'A2');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_');
        (new Xlsx($spreadsheet))->save($tempPath);

        $file = new UploadedFile($tempPath, 'jadwal_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->waka)->post(route('waka.jadwal.import'), [
            'file' => $file,
            'cycle_number' => '2',
        ]);

        @unlink($tempPath);

        $response->assertRedirect(route('waka.jadwal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $this->guru->id,
            'cycle_number' => '2',
            'day_of_week' => 'Senin',
            'subject' => 'KK BR (Bisnis Retail)',
            'class_name' => 'XII BR 1',
        ]);
    }

    public function test_waka_cannot_import_when_target_cycle_is_locked(): void
    {
        DB::table('system_configurations')->insert([
            'active_cycle' => 1,
            'cycle_one_locked' => true,
            'cycle_two_locked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Hari', 'Guru', 'Mata Pelajaran', 'Kelas'], null, 'A1');
        $sheet->fromArray([['Senin', 'ARIS PURWANTINAH, S.Pd', 'Pelajaran', 'XII BR 1']], null, 'A2');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_locked_');
        (new Xlsx($spreadsheet))->save($tempPath);

        $file = new UploadedFile($tempPath, 'jadwal.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->waka)->post(route('waka.jadwal.import'), [
            'file' => $file,
            'cycle_number' => '1',
        ]);

        @unlink($tempPath);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_waka_can_import_from_fet_pdf(): void
    {
        $pdfPath = 'C:\\Users\\Asus\\.gemini\\antigravity\\brain\\67684dc2-af9a-4fd7-89e5-dd74f6e04d99\\.user_uploaded\\media_1793754272845_c86ac056.pdf';
        if (! file_exists($pdfPath)) {
            $this->markTestSkipped('PDF test file does not exist.');
        }

        $file = new UploadedFile($pdfPath, 'JADWAL_GURU_PUT_2_SEM_GANJIL_26_27_REVISI.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($this->waka)->post(route('waka.jadwal.import'), [
            'file' => $file,
            'cycle_number' => 'auto',
        ]);

        $response->assertRedirect(route('waka.jadwal'));
        $response->assertSessionHas('success');

        // Check that schedules for Aris were imported into cycle 2
        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $this->guru->id,
            'cycle_number' => '2',
            'day_of_week' => 'Senin',
            'class_name' => 'XII BR 1',
        ]);
    }

    public function test_waka_can_import_from_class_fet_pdf(): void
    {
        $pdfPath = 'C:\\Users\\Asus\\.gemini\\antigravity\\brain\\67684dc2-af9a-4fd7-89e5-dd74f6e04d99\\.user_uploaded\\media_1793786473777_ef367c71.pdf';
        if (! file_exists($pdfPath)) {
            $this->markTestSkipped('Class PDF test file does not exist.');
        }

        $teacherYoga = User::create([
            'role_id' => 2,
            'username' => 'rahmawati.yoga.pamungkas.m.pd',
            'fullname' => 'RAHMAWATI YOGA PAMUNGKAS, M.Pd',
            'password' => Hash::make('password123'),
            'is_active' => 1,
        ]);

        DB::table('siswas')->insert([
            'name' => 'Siswa RPL',
            'nis' => '54321',
            'kelas' => 'X RPL 1',
            'jurusan' => 'RPL',
            'alamat' => 'Jl. Test No. 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $file = new UploadedFile($pdfPath, 'JADWAL_KELAS_PUT_1.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($this->waka)->post(route('waka.jadwal.import'), [
            'file' => $file,
            'cycle_number' => '1',
        ]);

        $response->assertRedirect(route('waka.jadwal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', [
            'teacher_id' => $teacherYoga->id,
            'cycle_number' => '1',
            'day_of_week' => 'Senin',
            'class_name' => 'X RPL 1',
        ]);
    }
}
