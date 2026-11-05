<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class WakaStudentExcelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_waka_can_import_students_from_an_excel_file(): void
    {
        $this->seed(DatabaseSeeder::class);
        $students = [
            ['Siswa Excel 1', 'TEST26001', 'X RPL 1', 'RPL', '081234567801', 'Alamat 1'],
            ['Siswa Excel 2', 'TEST26002', 'X TKJ 1', 'TKJ', '081234567802', 'Alamat 2'],
            ['Siswa Excel 3', 'TEST26003', 'XI DKV 1', 'DKV', '081234567803', 'Alamat 3'],
            ['Siswa Excel 4', 'TEST26004', 'XI AKL 1', 'AKL', '081234567804', 'Alamat 4'],
            ['Siswa Excel 5', 'TEST26005', 'XII MP 1', 'MP', '081234567805', 'Alamat 5'],
        ];

        $this->actingAs(User::where('username', 'waka')->firstOrFail())
            ->post(route('waka.siswa.import'), [
                'file' => $this->makeExcelUpload($students),
            ])
            ->assertRedirect(route('waka.siswa'))
            ->assertSessionHas('success', '5 data siswa berhasil diimpor dari Excel.');

        foreach ($students as [$name, $nis, $class, $major, $phone, $address]) {
            $this->assertDatabaseHas('siswas', [
                'name' => $name,
                'nis' => $nis,
                'kelas' => $class,
                'jurusan' => $major,
                'phone' => $phone,
                'alamat' => $address,
            ]);
        }
    }

    public function test_invalid_excel_rows_prevent_the_entire_import(): void
    {
        $this->seed(DatabaseSeeder::class);
        $students = [
            ['Siswa Valid', 'TEST26101', 'X RPL 1', 'RPL', '081234567811', 'Alamat valid'],
            ['Siswa Jurusan Salah', 'TEST26102', 'X ABC 1', 'ABC', '081234567812', 'Alamat invalid'],
        ];

        $this->actingAs(User::where('username', 'waka')->firstOrFail())
            ->from(route('waka.siswa'))
            ->post(route('waka.siswa.import'), [
                'file' => $this->makeExcelUpload($students),
            ])
            ->assertRedirect(route('waka.siswa'))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('siswas', ['nis' => 'TEST26101']);
        $this->assertDatabaseMissing('siswas', ['nis' => 'TEST26102']);
    }

    public function test_waka_can_import_students_from_daftar_hadir_format(): void
    {
        $this->seed(DatabaseSeeder::class);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('X');
        $sheet->fromArray([
            ['DAFTAR HADIR SISWA'],
            ['KELAS : X RPL 1'],
            ['TAHUN PELAJARAN 2026 - 2027'],
            [],
            ['MATA PELAJARAN : Pemrograman'],
            ['GURU : Guru Test'],
            [],
            ['NOMOR', '', 'NAMA SISWA', 'L / P'],
            [],
            ['Urt', 'NIS'],
            ['1', '19001', 'Budi Santoso', 'L'],
            ['2', '19002', 'Siti Rahma', 'P'],
        ], null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'student-ledger-');
        (new Xlsx($spreadsheet))->save($path);
        $contents = file_get_contents($path);
        unlink($path);

        $file = UploadedFile::fake()->createWithContent('daftar_hadir.xlsx', $contents);

        $this->actingAs(User::where('username', 'waka')->firstOrFail())
            ->post(route('waka.siswa.import'), ['file' => $file])
            ->assertRedirect(route('waka.siswa'))
            ->assertSessionHas('success', '2 data siswa berhasil diimpor dari Excel.');

        $this->assertDatabaseHas('siswas', [
            'name' => 'Budi Santoso',
            'nis' => '19001',
            'kelas' => 'X RPL 1',
            'jurusan' => 'RPL',
        ]);
        $this->assertDatabaseHas('siswas', [
            'name' => 'Siti Rahma',
            'nis' => '19002',
            'kelas' => 'X RPL 1',
            'jurusan' => 'RPL',
        ]);
    }

    public function test_waka_can_import_real_absen_file_if_present(): void
    {
        $realFile = 'C:/Users/Asus/.gemini/antigravity/brain/67684dc2-af9a-4fd7-89e5-dd74f6e04d99/.user_uploaded/media_1793751035416_a3e84530.xlsx';
        if (! file_exists($realFile)) {
            $this->markTestSkipped('Real file not present.');
        }

        $this->seed(DatabaseSeeder::class);
        $contents = file_get_contents($realFile);
        $file = UploadedFile::fake()->createWithContent('Absen_2026-2027.xlsx', $contents);

        $this->actingAs(User::where('username', 'waka')->firstOrFail())
            ->post(route('waka.siswa.import'), ['file' => $file])
            ->assertRedirect(route('waka.siswa'))
            ->assertSessionHas('success', '1711 data siswa berhasil diimpor dari Excel.');

        $this->assertDatabaseHas('siswas', [
            'name' => 'ALLIF MUKHLIS SETYAWAN',
            'nis' => '18910',
            'kelas' => 'X RPL 1',
            'jurusan' => 'RPL',
        ]);
        $this->assertDatabaseHas('siswas', [
            'name' => 'WAHYU AGUNG RIZQI PAMBUDI',
            'nis' => '18945',
            'kelas' => 'X RPL 1',
            'jurusan' => 'RPL',
        ]);
    }

    public function test_example_download_contains_five_student_rows(): void
    {
        $this->seed(DatabaseSeeder::class);
        $response = $this->actingAs(User::where('username', 'waka')->firstOrFail())
            ->get(route('waka.siswa.example'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $path = tempnam(sys_get_temp_dir(), 'student-example-');
        file_put_contents($path, $response->streamedContent());
        $rows = IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);
        unlink($path);

        $this->assertCount(6, $rows);
        $this->assertSame('Nama', $rows[0][0]);
        $this->assertSame('Dinda Safitri', $rows[1][0]);
        $this->assertSame('Nabila Putri', $rows[5][0]);
    }

    private function makeExcelUpload(array $students): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray(
            array_merge([['Nama', 'NIS', 'Kelas', 'Jurusan', 'No HP', 'Alamat']], $students),
            null,
            'A1'
        );
        $path = tempnam(sys_get_temp_dir(), 'student-import-');
        (new Xlsx($spreadsheet))->save($path);
        $contents = file_get_contents($path);
        unlink($path);

        return UploadedFile::fake()->createWithContent('students.xlsx', $contents);
    }
}
