<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('accounts:sync-students', function () {
    $this->info('Memulai sinkronisasi akun Siswa ({nis}) dan Orang Tua ({nis}p)...');

    $siswas = DB::table('siswas')
        ->whereNotNull('nis')
        ->where('nis', '!=', '')
        ->select('id', 'name', 'nis')
        ->get();

    $existingUsernames = DB::table('users')->pluck('username')->map(fn ($u) => strtolower($u))->flip();

    $newAccounts = [];
    $studentCount = 0;
    $parentCount = 0;
    $now = now();

    foreach ($siswas as $siswa) {
        $nisClean = trim((string) $siswa->nis);
        if ($nisClean === '') {
            continue;
        }

        $studentUsername = strtolower($nisClean);
        $parentUsername = strtolower($nisClean) . 'p';

        // Akun Siswa
        if (! isset($existingUsernames[$studentUsername])) {
            $newAccounts[] = [
                'fullname' => $siswa->name,
                'username' => $nisClean,
                'password' => $nisClean,
                'role_id' => 3,
                'student_id' => $siswa->id,
                'is_active' => 1,
                'created_at' => $now,
            ];
            $existingUsernames[$studentUsername] = true;
            $studentCount++;
        }

        // Akun Orang Tua (Parent) dengan Role Siswa (role_id = 3)
        if (! isset($existingUsernames[$parentUsername])) {
            $newAccounts[] = [
                'fullname' => 'Orang Tua ' . $siswa->name,
                'username' => $nisClean . 'p',
                'password' => $nisClean . 'p',
                'role_id' => 3,
                'student_id' => $siswa->id,
                'is_active' => 1,
                'created_at' => $now,
            ];
            $existingUsernames[$parentUsername] = true;
            $parentCount++;
        }

        if (count($newAccounts) >= 500) {
            DB::table('users')->insert($newAccounts);
            $newAccounts = [];
        }
    }

    if (! empty($newAccounts)) {
        DB::table('users')->insert($newAccounts);
    }

    $this->info("Sinkronisasi selesai: {$studentCount} akun Siswa ({nis}) dan {$parentCount} akun Orang Tua ({nis}p) berhasil dibuat.");
})->purpose('Sinkronkan akun login Siswa ({nis}) dan Orang Tua ({nis}p) dengan Role Siswa');
