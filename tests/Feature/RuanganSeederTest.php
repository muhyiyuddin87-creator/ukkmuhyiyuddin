<?php

namespace Tests\Feature;

use Database\Seeders\RuanganSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RuanganSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_rooms_matching_the_pdf_timetable_idempotently(): void
    {
        $seeder = new RuanganSeeder;
        $seeder->run();
        $seeder->run();

        // 21 Labs + 3 OR = 24 rooms
        $this->assertSame(24, DB::table('ruangan')->count());
        $this->assertSame(21, DB::table('ruangan')->where('jenis', 'Lab')->count());
        $this->assertSame(3, DB::table('ruangan')->where('jenis', 'Ruangan')->count());

        $expectedLabs = [
            'Lab 1', 'Lab 2', 'Lab 3', 'Lab 4', 'Lab 5', 'Lab 6', 'Lab 7', 'Lab 8', 'Lab 9',
            'Lab 10', 'Lab 11', 'Lab 12', 'Lab 13', 'Lab 14', 'Lab 16', 'Lab 17', 'Lab 18',
            'Lab 19', 'Lab 20', 'Lab 21', 'Lab IPA'
        ];

        foreach ($expectedLabs as $lab) {
            $this->assertDatabaseHas('ruangan', [
                'nama_ruangan' => $lab,
                'jenis' => 'Lab',
            ]);
        }

        foreach (['OR1', 'OR2', 'OR3'] as $or) {
            $this->assertDatabaseHas('ruangan', [
                'nama_ruangan' => $or,
                'jenis' => 'Ruangan',
            ]);
        }
    }
}
