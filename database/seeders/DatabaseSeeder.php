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

        $targetStudent = \Illuminate\Support\Facades\DB::table('siswas')
            ->where('name', 'like', '%MUHYIYUDDIN%')
            ->first() ?? \Illuminate\Support\Facades\DB::table('siswas')->first();

        if ($targetStudent) {
            User::where('username', 'siswa')->update([
                'student_id' => $targetStudent->id,
                'fullname' => $targetStudent->name,
            ]);
            User::where('username', 'ortu')->update([
                'student_id' => $targetStudent->id,
                'fullname' => 'Orang Tua ' . $targetStudent->name,
            ]);
        }
    }
}
