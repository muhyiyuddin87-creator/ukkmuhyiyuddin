<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuanganSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rooms = [
            // Labs identified from FET Timetable PDF
            ['nama_ruangan' => 'Lab 1', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 1'],
            ['nama_ruangan' => 'Lab 2', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 2'],
            ['nama_ruangan' => 'Lab 3', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 3'],
            ['nama_ruangan' => 'Lab 4', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 4'],
            ['nama_ruangan' => 'Lab 5', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 5'],
            ['nama_ruangan' => 'Lab 6', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 6'],
            ['nama_ruangan' => 'Lab 7', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 7'],
            ['nama_ruangan' => 'Lab 8', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 8'],
            ['nama_ruangan' => 'Lab 9', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 9'],
            ['nama_ruangan' => 'Lab 10', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 10'],
            ['nama_ruangan' => 'Lab 11', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 11'],
            ['nama_ruangan' => 'Lab 12', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 12'],
            ['nama_ruangan' => 'Lab 13', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 13'],
            ['nama_ruangan' => 'Lab 14', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 14'],
            ['nama_ruangan' => 'Lab 16', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 16'],
            ['nama_ruangan' => 'Lab 17', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 17'],
            ['nama_ruangan' => 'Lab 18', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 18'],
            ['nama_ruangan' => 'Lab 19', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 19'],
            ['nama_ruangan' => 'Lab 20', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 20'],
            ['nama_ruangan' => 'Lab 21', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium Komputer 21'],
            ['nama_ruangan' => 'Lab IPA', 'jenis' => 'Lab', 'keterangan' => 'Laboratorium IPA'],
            // Olahraga / Ruangan from FET Timetable PDF
            ['nama_ruangan' => 'OR1', 'jenis' => 'Ruangan', 'keterangan' => 'Lapangan / Ruang Olahraga 1'],
            ['nama_ruangan' => 'OR2', 'jenis' => 'Ruangan', 'keterangan' => 'Lapangan / Ruang Olahraga 2'],
            ['nama_ruangan' => 'OR3', 'jenis' => 'Ruangan', 'keterangan' => 'Lapangan / Ruang Olahraga 3'],
        ];

        // Delete rooms not in the target list if they aren't used in schedules
        $targetNames = array_column($rooms, 'nama_ruangan');
        $unusedOldRooms = DB::table('ruangan')
            ->whereNotIn('nama_ruangan', $targetNames)
            ->pluck('id_ruangan');

        if ($unusedOldRooms->isNotEmpty()) {
            $inUseRoomIds = DB::table('schedules')->whereIn('room_id', $unusedOldRooms)->pluck('room_id');
            $deletableIds = $unusedOldRooms->diff($inUseRoomIds);
            if ($deletableIds->isNotEmpty()) {
                DB::table('ruangan')->whereIn('id_ruangan', $deletableIds)->delete();
            }
        }

        foreach ($rooms as $room) {
            $existingRoom = DB::table('ruangan')
                ->where('nama_ruangan', $room['nama_ruangan'])
                ->first(['id_ruangan']);

            if ($existingRoom) {
                DB::table('ruangan')->where('id_ruangan', $existingRoom->id_ruangan)->update([
                    'jenis' => $room['jenis'],
                    'keterangan' => $room['keterangan'],
                    'updated_at' => $now,
                ]);
                continue;
            }

            DB::table('ruangan')->insert([
                ...$room,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
