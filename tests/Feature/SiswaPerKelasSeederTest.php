<?php

namespace Tests\Feature;

use Database\Seeders\SiswaPerKelasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SiswaPerKelasSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_ten_students_for_each_grade_and_major_without_duplicates(): void
    {
        $seeder = new SiswaPerKelasSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertSame(240, DB::table('siswas')->count());
        $this->assertSame(240, DB::table('siswas')->distinct('name')->count('name'));

        $classes = DB::table('siswas')
            ->select('kelas', 'jurusan', DB::raw('COUNT(*) as total'))
            ->groupBy('kelas', 'jurusan')
            ->get();

        $this->assertCount(24, $classes);
        foreach ($classes as $class) {
            $this->assertSame(10, (int) $class->total);
        }
    }

    public function test_it_fills_existing_classes_to_ten_students_without_removing_records(): void
    {
        $studentId = DB::table('siswas')->insertGetId([
            'name' => 'Siswa Terdaftar',
            'kelas' => 'X RPL',
            'jurusan' => 'RPL',
            'alamat' => 'Alamat siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (new SiswaPerKelasSeeder)->run();

        $this->assertSame(10, DB::table('siswas')->where('kelas', 'X RPL')->where('jurusan', 'RPL')->count());
        $this->assertDatabaseHas('siswas', ['id' => $studentId, 'kelas' => 'X RPL', 'alamat' => 'Alamat siswa']);
    }

    public function test_it_fills_existing_class_sections_to_ten_students(): void
    {
        DB::table('siswas')->insert([
            'name' => 'Siswa XI RPL 2 Terdaftar',
            'kelas' => 'XI RPL 2',
            'jurusan' => 'RPL',
            'alamat' => 'Alamat siswa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (new SiswaPerKelasSeeder)->run();

        $this->assertSame(10, DB::table('siswas')->where('kelas', 'XI RPL 2')->where('jurusan', 'RPL')->count());
        $this->assertSame(10, DB::table('siswas')->where('jurusan', 'RPL')->where('kelas', 'like', 'XI %')->count());
    }
}
