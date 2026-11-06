<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoAccounts = [
            [
                'username' => 'waka',
                'role_id' => 1,
                'fullname' => 'Waka',
                'password' => 'waka123',
            ],
            [
                'username' => 'guru',
                'role_id' => 2,
                'fullname' => 'Guru',
                'password' => 'guru123',
            ],
            [
                'username' => 'siswa',
                'role_id' => 3,
                'fullname' => 'Siswa',
                'password' => 'siswa123',
            ],
            [
                'username' => 'kesiswaan',
                'role_id' => 4,
                'fullname' => 'Kesiswaan',
                'password' => 'kesiswaan123',
            ],
            [
                'username' => 'ortu',
                'role_id' => 6,
                'fullname' => 'Orang Tua Siswa',
                'password' => 'ortu123',
            ],
        ];

        foreach ($demoAccounts as $account) {
            User::updateOrCreate(
                ['username' => $account['username']],
                [
                    'role_id' => $account['role_id'],
                    'parent_id' => null,
                    'class_id' => null,
                    'username' => $account['username'],
                    'password' => Hash::make($account['password']),
                    'fullname' => $account['fullname'],
                    'is_active' => true,
                    'created_at' => now(),
                ]
            );
        }

        $this->call(SiswaPerKelasSeeder::class);
        $this->call(RuanganSeeder::class);

        // 1. Pastikan siswa MUHYIYUDDIN ada di kelas XII RPL 2
        $classRpl2 = \Illuminate\Support\Facades\DB::table('school_classes')->where('name', 'XII RPL 2')->first();
        $classId = $classRpl2 ? $classRpl2->id : 90;

        $targetStudent = \Illuminate\Support\Facades\DB::table('siswas')
            ->where('name', 'like', '%MUHYIYUDDIN%')
            ->first();

        if (! $targetStudent) {
            $studentId = \Illuminate\Support\Facades\DB::table('siswas')->insertGetId([
                'name' => 'MUHYIYUDDIN',
                'kelas' => 'XII RPL 2',
                'jurusan' => 'RPL',
                'alamat' => 'Yogyakarta',
                'nis' => '202611001',
                'phone' => '081234567890',
                'email' => 'muhyiyuddin@sekolah.sch.id',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $targetStudent = \Illuminate\Support\Facades\DB::table('siswas')->where('id', $studentId)->first();
        }

        // 2. Setup Akun Siswa & Ortu MUHYIYUDDIN
        if ($targetStudent) {
            User::updateOrCreate(
                ['username' => 'siswa'],
                [
                    'role_id' => 3,
                    'student_id' => $targetStudent->id,
                    'class_id' => $classId,
                    'fullname' => $targetStudent->name,
                    'password' => Hash::make('siswa123'),
                    'is_active' => true,
                ]
            );

            User::updateOrCreate(
                ['username' => 'muhyiyuddin'],
                [
                    'role_id' => 3,
                    'student_id' => $targetStudent->id,
                    'class_id' => $classId,
                    'fullname' => $targetStudent->name,
                    'password' => Hash::make('siswa123'),
                    'is_active' => true,
                ]
            );

            User::updateOrCreate(
                ['username' => 'ortu'],
                [
                    'role_id' => 6,
                    'student_id' => $targetStudent->id,
                    'fullname' => 'Orang Tua ' . $targetStudent->name,
                    'password' => Hash::make('ortu123'),
                    'is_active' => true,
                ]
            );

            User::updateOrCreate(
                ['username' => 'ortu.muhyiyuddin'],
                [
                    'role_id' => 6,
                    'student_id' => $targetStudent->id,
                    'fullname' => 'Orang Tua ' . $targetStudent->name,
                    'password' => Hash::make('ortu123'),
                    'is_active' => true,
                ]
            );
        }

        // 3. Setup Akun Kesiswaan 1
        User::updateOrCreate(
            ['username' => 'kesiswaan1'],
            [
                'role_id' => 4,
                'fullname' => 'Kesiswaan 1',
                'password' => Hash::make('kesiswaan123'),
                'is_active' => true,
            ]
        );

        // 4. Setup Akun Wali Kelas: ROSYIDA RAMADHANI, S.Pd (XII RPL 2)
        $wali = User::updateOrCreate(
            ['username' => 'rosyida.ramadhani.s.pd'],
            [
                'role_id' => 2,
                'class_id' => $classId,
                'fullname' => 'ROSYIDA RAMADHANI, S.Pd',
                'password' => Hash::make('guru123'),
                'is_active' => true,
            ]
        );

        \Illuminate\Support\Facades\DB::table('user_school_classes')->updateOrInsert(
            ['school_class_id' => $classId],
            [
                'user_id' => $wali->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
