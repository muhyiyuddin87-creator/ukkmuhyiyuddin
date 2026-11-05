<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class WakaTeacherImportAndPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_waka_can_download_teacher_excel_template_and_example(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        // 1. Template kosong
        $resTemplate = $this->actingAs($waka)
            ->get(route('waka.guru.template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pathTpl = tempnam(sys_get_temp_dir(), 'tpl-guru-');
        file_put_contents($pathTpl, $resTemplate->streamedContent());
        $rowsTpl = IOFactory::load($pathTpl)->getActiveSheet()->toArray(null, true, true, false);
        unlink($pathTpl);

        $this->assertCount(1, $rowsTpl);
        $this->assertSame('Nama Lengkap', $rowsTpl[0][0]);
        $this->assertSame('Username', $rowsTpl[0][1]);
        $this->assertSame('NIP', $rowsTpl[0][2]);

        // 2. Contoh berisi 5 guru
        $resExample = $this->actingAs($waka)
            ->get(route('waka.guru.example'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pathEx = tempnam(sys_get_temp_dir(), 'ex-guru-');
        file_put_contents($pathEx, $resExample->streamedContent());
        $rowsEx = IOFactory::load($pathEx)->getActiveSheet()->toArray(null, true, true, false);
        unlink($pathEx);

        $this->assertCount(6, $rowsEx);
        $this->assertSame('Nama Lengkap', $rowsEx[0][0]);
        $this->assertSame('Dra. Siti Aminah, M.Pd.', $rowsEx[1][0]);
        $this->assertSame('siti.aminah', $rowsEx[1][1]);
    }

    public function test_waka_can_import_teachers_from_an_excel_file(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        // Siapkan kelas untuk penugasan wali kelas
        DB::table('school_classes')->insertOrIgnore([
            ['id' => 101, 'name' => 'X RPL 1', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 102, 'name' => 'XI TKJ 1', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $teachers = [
            ['Guru Hebat Satu', 'guru.hebat1', '198101012005011001', '081211110001', 'guru1@sekolah.sch.id', 'Pass12345', 'X RPL 1'],
            ['Guru Hebat Dua', 'guru.hebat2', '198202022006022002', '081211110002', 'guru2@sekolah.sch.id', 'Pass12345', 'XI TKJ 1'],
            ['Guru Hebat Tiga', 'guru.hebat3', '198303032007031003', '081211110003', 'guru3@sekolah.sch.id', 'Pass12345', ''],
        ];

        $this->actingAs($waka)
            ->post(route('waka.guru.import'), [
                'file' => $this->makeTeacherExcelUpload($teachers),
            ])
            ->assertRedirect(route('waka.guru'))
            ->assertSessionHas('success', '3 data guru berhasil diimpor dari Excel.');

        $this->assertDatabaseHas('users', [
            'fullname' => 'Guru Hebat Satu',
            'username' => 'guru.hebat1',
            'nip' => '198101012005011001',
            'role_id' => 2,
        ]);

        $this->assertDatabaseHas('users', [
            'fullname' => 'Guru Hebat Dua',
            'username' => 'guru.hebat2',
            'nip' => '198202022006022002',
            'role_id' => 2,
        ]);

        $this->assertDatabaseHas('users', [
            'fullname' => 'Guru Hebat Tiga',
            'username' => 'guru.hebat3',
            'nip' => '198303032007031003',
            'role_id' => 2,
        ]);

        // Verifikasi penugasan wali kelas
        $teacher1Id = (int) DB::table('users')->where('username', 'guru.hebat1')->value('id');
        $this->assertDatabaseHas('user_school_classes', [
            'user_id' => $teacher1Id,
            'school_class_id' => 101,
        ]);
    }

    public function test_teacher_import_validates_and_rejects_duplicate_nip_or_username(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        // Buat guru awal
        DB::table('users')->insert([
            'fullname' => 'Guru Awal',
            'username' => 'guru.kembar',
            'nip' => '199999999999999999',
            'password' => 'secret1234',
            'role_id' => 2,
            'created_at' => now(),
        ]);

        // Coba impor dengan username dan NIP yang sama
        $teachers = [
            ['Guru Duplikat', 'guru.kembar', '199999999999999999', '081299990001', 'dup@sekolah.sch.id', 'Pass12345', ''],
        ];

        $this->actingAs($waka)
            ->from(route('waka.guru'))
            ->post(route('waka.guru.import'), [
                'file' => $this->makeTeacherExcelUpload($teachers),
            ])
            ->assertRedirect(route('waka.guru'))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('users', ['fullname' => 'Guru Duplikat']);
    }

    public function test_waka_can_import_teachers_from_pdf_file(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $pdfFile = $this->makeTeacherPdfUpload([
            'Nama Lengkap | Username | NIP | Nomor HP | Email | Password | Wali Kelas',
            'Guru Dari PDF | guru.pdf | 198505052010011005 | 081299991111 | gurupdf@sekolah.sch.id | Pass12345 | X RPL 1',
        ]);

        $this->actingAs($waka)
            ->post(route('waka.guru.import'), [
                'file' => $pdfFile,
            ])
            ->assertRedirect(route('waka.guru'))
            ->assertSessionHas('success', '1 data guru berhasil diimpor dari PDF.');

        $this->assertDatabaseHas('users', [
            'fullname' => 'Guru Dari PDF',
            'username' => 'guru.pdf',
            'nip' => '198505052010011005',
            'role_id' => 2,
        ]);
    }

    public function test_waka_can_import_teachers_from_fet_pdf_format(): void
    {
        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $pdfFile = $this->makeTeacherPdfUpload([
            'SMK NEGERI 1 BANTUL',
            'Daftar Isi',
            '1. RAHARJO, S.IP., M.Pd',
            '2. SRI MARTINI, S.Pd',
            '3. Drs. SUBARDI',
        ]);

        $this->actingAs($waka)
            ->post(route('waka.guru.import'), [
                'file' => $pdfFile,
            ])
            ->assertRedirect(route('waka.guru'))
            ->assertSessionHas('success', '3 data guru berhasil diimpor dari PDF.');

        $this->assertDatabaseHas('users', [
            'fullname' => 'RAHARJO, S.IP., M.Pd',
            'role_id' => 2,
        ]);
        $this->assertDatabaseHas('users', [
            'fullname' => 'SRI MARTINI, S.Pd',
            'role_id' => 2,
        ]);
        $this->assertDatabaseHas('users', [
            'fullname' => 'Drs. SUBARDI',
            'role_id' => 2,
        ]);
    }

    public function test_waka_can_import_teachers_from_uploaded_real_fet_pdf_file(): void
    {
        $realPdfPath = 'C:/Users/Asus/.gemini/antigravity/brain/67684dc2-af9a-4fd7-89e5-dd74f6e04d99/.user_uploaded/media_1793754272845_c86ac056.pdf';
        if (!file_exists($realPdfPath)) {
            $this->markTestSkipped('Real FET PDF file not found in test environment.');
        }

        $this->seed(DatabaseSeeder::class);
        $waka = User::where('username', 'waka')->firstOrFail();

        $uploadedFile = new UploadedFile(
            $realPdfPath,
            'JADWAL_GURU_PUT_2_SEM_GANJIL_26_27_REVISI.pdf',
            'application/pdf',
            null,
            true
        );

        $this->actingAs($waka)
            ->post(route('waka.guru.import'), [
                'file' => $uploadedFile,
            ])
            ->assertRedirect(route('waka.guru'))
            ->assertSessionHas('success', '91 data guru berhasil diimpor dari PDF.');

        $this->assertDatabaseHas('users', [
            'fullname' => 'RAHARJO, S.IP., M.Pd',
            'role_id' => 2,
        ]);
        $this->assertDatabaseHas('users', [
            'fullname' => 'ALIVI SAZKIA HAWIE, S.Pd',
            'role_id' => 2,
        ]);
    }

    private function makeTeacherPdfUpload(array $lines): UploadedFile
    {
        $stream = "BT\n/F1 12 Tf\n50 750 Td\n";
        foreach ($lines as $line) {
            $safeLine = str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $line);
            $stream .= "({$safeLine}) Tj\n0 -20 Td\n";
        }
        $stream .= "ET\n";
        $streamLen = strlen($stream);

        $body = "%PDF-1.4\n";
        $offsets = [];

        $offsets[1] = strlen($body);
        $body .= "1 0 obj\n<</Type /Catalog /Pages 2 0 R>>\nendobj\n";

        $offsets[2] = strlen($body);
        $body .= "2 0 obj\n<</Type /Pages /Kids [3 0 R] /Count 1>>\nendobj\n";

        $offsets[3] = strlen($body);
        $body .= "3 0 obj\n<</Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources <</Font <</F1 5 0 R>>>>>>\nendobj\n";

        $offsets[4] = strlen($body);
        $body .= "4 0 obj\n<</Length {$streamLen}>>\nstream\n{$stream}endstream\nendobj\n";

        $offsets[5] = strlen($body);
        $body .= "5 0 obj\n<</Type /Font /Subtype /Type1 /BaseFont /Helvetica>>\nendobj\n";

        $xrefStart = strlen($body);
        $body .= "xref\n0 6\n";
        $body .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $body .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $body .= "trailer\n<</Size 6 /Root 1 0 R>>\nstartxref\n{$xrefStart}\n%%EOF\n";

        return UploadedFile::fake()->createWithContent('teachers.pdf', $body);
    }

    private function makeTeacherExcelUpload(array $teachers): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray(
            array_merge([['Nama Lengkap', 'Username', 'NIP', 'Nomor HP', 'Email Pemulihan', 'Password', 'Wali Kelas']], $teachers),
            null,
            'A1'
        );
        $path = tempnam(sys_get_temp_dir(), 'teacher-import-');
        (new Xlsx($spreadsheet))->save($path);
        $contents = file_get_contents($path);
        unlink($path);

        return UploadedFile::fake()->createWithContent('teachers.xlsx', $contents);
    }
}
