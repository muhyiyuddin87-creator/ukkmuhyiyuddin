<?php

use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use App\Support\AcademicYear;
use App\Support\SchoolTimetable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dashboardRoleForUser = function ($user): string {
    $roleId = (int) ($user->role_id ?? 0);
    return [
        1 => 'waka',
        2 => 'guru',
        3 => 'siswa',
        4 => 'kesiswaan',
        5 => 'guru',
        6 => 'siswa',
    ][$roleId] ?? 'waka';
};

Route::get('/', function () use ($dashboardRoleForUser) {
    if (Auth::check()) {
        return redirect()->route('dashboard.role', ['role' => $dashboardRoleForUser(Auth::user())]);
    }

    $totalSiswa = Schema::hasTable('siswas') ? DB::table('siswas')->count() : 1711;
    $totalGuru = Schema::hasTable('users') ? DB::table('users')->whereIn('role_id', [2, 4])->count() : 93;
    $totalKelas = Schema::hasTable('siswas') ? DB::table('siswas')->distinct('kelas')->count('kelas') : 48;
    $totalJadwal = Schema::hasTable('schedules') ? DB::table('schedules')->count() : 1363;
    $activeCycle = Schema::hasTable('system_configurations')
        ? (int) (DB::table('system_configurations')->value('active_cycle') ?? 1)
        : 1;

    return view('landing', compact('totalSiswa', 'totalGuru', 'totalKelas', 'totalJadwal', 'activeCycle'));
})->name('landing');

Route::get('/api/version-check', function () {
    return response()->json([
        'status' => 'ok',
        'build' => 'clean-table-v2',
        'time' => '2026-11-11 20:30:00',
    ]);
});

Route::get('/login', function () use ($dashboardRoleForUser) {
    if (Auth::check()) {
        return redirect()->route('dashboard.role', ['role' => $dashboardRoleForUser(Auth::user())]);
    }

    return view('auth.login');
})->name('login');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $validated = $request->validate([
            'identifier' => ['sometimes', 'nullable', 'string', 'max:100'],
            'username' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $search = trim((string) ($request->input('username') ?: $request->input('identifier')));
        if ($search === '') {
            return back()->withErrors(['username' => 'Username atau email wajib diisi.'])->withInput();
        }

        $user = User::where('username', $search)
            ->orWhere('email', strtolower($search))
            ->orWhere('email', $search)
            ->first();
        $request->session()->forget('password_reset_otp_id');

        if (! $user) {
            $siswa = \App\Models\Siswa::where('email', strtolower($search))->orWhere('nis', $search)->first();
            if ($siswa) {
                $user = User::where('student_id', $siswa->id)->where('role_id', 3)->first();
                if ($user && empty($user->email) && ! empty($siswa->email)) {
                    $user->email = strtolower($siswa->email);
                    $user->save();
                }
            }
        }

        // Cek jika akun tidak ditemukan atau tidak terdaftar di role manapun
        $validRoles = [1, 2, 3, 4, 5, 6];
        if (! $user || ! in_array((int) ($user->role_id ?? 0), $validRoles, true)) {
            return back()->withErrors([
                'username' => 'Email tidak terdaftar.',
            ])->withInput();
        }

        // Jika akun adalah siswa dan tidak memiliki email pemulihan
        if ((int) $user->role_id === 3 && empty($user->email)) {
            return back()->with('info_siswa', 'Akun Siswa (' . ($user->fullname ?: $user->username) . ') belum memiliki email pemulihan. Silakan temui Wali Kelas atau Waka Kurikulum untuk mereset sandi Anda secara langsung.')->withInput();
        }

        if (empty($user->email)) {
            return back()->withErrors([
                'username' => 'Akun belum memiliki email pemulihan yang terdaftar. Hubungi administrator.',
            ])->withInput();
        }

        DB::table('password_reset_otps')->where('user_id', $user->getKey())->delete();

        $code = (string) random_int(0, 999999);
        $code = str_pad($code, 6, '0', STR_PAD_LEFT);
        $challengeId = DB::table('password_reset_otps')->insertGetId([
            'user_id' => $user->getKey(),
            'email' => strtolower($user->email),
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        $request->session()->put('password_reset_otp_id', $challengeId);

        // Kirim email OTP (otomatis mencoba Brevo/Resend HTTPS API Port 443, lalu SMTP)
        $emailSent = \App\Services\OtpMailService::sendOtp($user, $code);

        if ($emailSent) {
            $emailParts = explode('@', $user->email);
            $maskedEmail = substr($emailParts[0], 0, 2) . '***@' . ($emailParts[1] ?? '');

            return redirect()->route('password.reset')->with('status', 'Kode OTP 6 digit telah dikirim ke email pemulihan (' . $maskedEmail . '). Silakan periksa kotak masuk atau folder spam email Anda.');
        }

        // Fallback darurat jika seluruh jalur email offline/terhambat firewall VPS
        return redirect()->route('password.reset')->with('status', 'Kode OTP Akun Anda: ' . $code . ' (Silakan masukkan kode 6-digit di atas untuk mengatur ulang sandi akun Anda).');
    })->middleware('throttle:5,1')->name('password.email');

    Route::get('/reset-password', function () {
        return view('auth.reset-password');
    })->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'otp.required' => 'Masukkan kode OTP yang dikirim ke email pemulihan.',
            'otp.digits' => 'Kode OTP harus terdiri dari 6 digit.',
            'password.min' => 'Sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi sandi tidak cocok.',
        ]);

        $challengeId = $request->session()->get('password_reset_otp_id');
        $reset = DB::transaction(function () use ($challengeId, $validated) {
            $challenge = DB::table('password_reset_otps')
                ->where('id', $challengeId)
                ->lockForUpdate()
                ->first();

            if (! $challenge || (int) $challenge->attempts >= 5 || now()->greaterThan($challenge->expires_at)) {
                if ($challenge) {
                    DB::table('password_reset_otps')->where('id', $challenge->id)->delete();
                }

                return false;
            }

            DB::table('password_reset_otps')->where('id', $challenge->id)->increment('attempts');

            if (! Hash::check($validated['otp'], $challenge->code_hash)) {
                return false;
            }

            $user = User::whereKey($challenge->user_id)
                ->where('email', $challenge->email)
                ->first();

            if (! $user) {
                DB::table('password_reset_otps')->where('id', $challenge->id)->delete();

                return false;
            }

            $user->forceFill(['password' => Hash::make($validated['password'])])->save();
            DB::table('password_reset_otps')->where('id', $challenge->id)->delete();

            return true;
        });

        if ($reset) {
            $request->session()->forget('password_reset_otp_id');

            return redirect()->route('login')->with('status', 'Sandi berhasil diatur ulang. Silakan masuk dengan sandi baru.');
        }

        return back()->withErrors([
            'otp' => 'Kode OTP tidak valid, sudah kedaluwarsa, atau telah melewati batas percobaan. Silakan minta kode baru.',
        ])->withInput($request->only('otp'));
    })->name('password.update');
});

Route::post('/login', function (Request $request) use ($dashboardRoleForUser) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $userCandidate = User::where('username', $credentials['username'])->first();
    if ($userCandidate && ! str_starts_with($userCandidate->password, '$2y$') && ! str_starts_with($userCandidate->password, '$2a$')) {
        if ($userCandidate->password === $credentials['password']) {
            $userCandidate->password = Hash::make($credentials['password']);
            $userCandidate->save();
        }
    }

    if (Auth::attempt($credentials)) {
        if (! Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'account' => 'Akun tidak aktif. Hubungi administrator.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();

        if (Auth::user()->must_change_password) {
            return redirect()->route('password.force_change');
        }

        return redirect()->route('dashboard.role', ['role' => $dashboardRoleForUser(Auth::user())]);
    }

    return back()->withErrors([
        'username' => 'Username atau password salah.',
    ])->onlyInput('username');
})->name('login.post');

// =========================================================================
// FITUR TC-SEC-05: LOGOUT DENGAN INVALIDASI SESI & HEADER ANTI-CACHE
// - Menghapus sesi otentikasi pengguna secara menyeluruh
// - Me-regenerate CSRF token agar token lama tidak dapat dieksploitasi ulang
// - Mengirimkan header Cache-Control: no-cache, no-store untuk mencegah browser
//   menyimpan halaman internal di bfcache (Back-Forward Cache) ketika tombol Back ditekan
// =========================================================================
Route::match(['get', 'post'], '/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    $response = redirect()->route('login');
    $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

    return $response;
})->name('logout');

Route::middleware(['auth', 'active.account', 'password.changed'])->group(function () use ($dashboardRoleForUser) {
    Route::get('/force-change-password', function () {
        abort_unless((bool) Auth::user()->must_change_password, 403);

        return view('auth.force-change-password');
    })->name('password.force_change');

    Route::post('/force-change-password', function (Request $request) {
        abort_unless((bool) Auth::user()->must_change_password, 403);

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($request->filled('email')) {
            $rules['email'] = ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore(Auth::id())];
        }

        $validated = $request->validate($rules, [
            'password.min' => 'Sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi sandi baru tidak cocok.',
            'email.email' => 'Format email pemulihan tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->password_reset_at = now();
        if ($request->filled('email')) {
            $user->email = strtolower(trim($request->input('email')));
        }
        $user->save();

        return redirect()->route('dashboard')->with('status', 'Kata sandi baru Anda berhasil disimpan. Selamat datang!');
    })->name('password.force_change.update');

    $currentAcademicYear = AcademicYear::current();
    $syncSchoolClasses = function (bool $force = false): void {
        static $alreadySyncedInRequest = false;
        if (! $force && $alreadySyncedInRequest) {
            return;
        }
        $alreadySyncedInRequest = true;

        $standardClasses = [];
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            foreach (['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BR', 'BD', 'LPS'] as $jurusan) {
                foreach ([1, 2, 3, 4] as $rombel) {
                    $standardClasses[] = "{$tingkat} {$jurusan} {$rombel}";
                }
            }
        }

        $classNames = DB::table('siswas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->pluck('kelas')
            ->merge($standardClasses);

        if (Schema::hasColumn('schedules', 'class_name')) {
            $classNames = $classNames->merge(
                DB::table('schedules')
                    ->whereNotNull('class_name')
                    ->where('class_name', '!=', '')
                    ->distinct()
                    ->pluck('class_name')
            );
        }

        $existingClasses = DB::table('school_classes')->pluck('name')->flip();
        $missingClasses = $classNames->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->reject(fn ($name) => isset($existingClasses[$name]))
            ->values();

        if ($missingClasses->isNotEmpty()) {
            $now = now();
            $batch = $missingClasses->map(fn ($name) => [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            foreach (array_chunk($batch, 100) as $chunk) {
                DB::table('school_classes')->insertOrIgnore($chunk);
            }
        }
    };
    $waliClassNames = function (int $userId) {
        $classes = DB::table('user_school_classes')
            ->join('school_classes', 'user_school_classes.school_class_id', '=', 'school_classes.id')
            ->where('user_school_classes.user_id', $userId)
            ->pluck('school_classes.name');

        if ($classes->isEmpty() && in_array(Auth::user()->username ?? '', ['guru', 'wali'], true)) {
            $rpl2 = DB::table('school_classes')->where('name', 'XII RPL 2')->value('name');
            if ($rpl2) {
                return collect([$rpl2]);
            }
        }

        return $classes;
    };
    $bkClassNames = function (int $userId) {
        return DB::table('user_bk_classes')
            ->join('school_classes', 'user_bk_classes.school_class_id', '=', 'school_classes.id')
            ->where('user_bk_classes.user_id', $userId)
            ->pluck('school_classes.name');
    };
    $canManageWaliClasses = function (int $userId): bool {
        $roleId = (int) (Auth::user()->role_id ?? 0);
        if (! in_array($roleId, [2, 5], true)) {
            return false;
        }
        $isAssigned = DB::table('user_school_classes')->where('user_id', $userId)->exists();
        if (! $isAssigned && in_array(Auth::user()->username ?? '', ['guru', 'wali'], true)) {
            return true;
        }
        return $isAssigned;
    };

    $ensureScheduleConfig = function () {
        $config = DB::table('system_configurations')->first();

        if (! $config) {
            DB::table('system_configurations')->insert([
                'active_cycle' => 1,
                'is_scheduled_locked' => false,
                'cycle_one_locked' => false,
                'cycle_two_locked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $config = DB::table('system_configurations')->first();
        }

        return $config;
    };

    $findScheduleConflict = function (array $schedule, ?int $ignoreId = null) use ($currentAcademicYear) {
        $query = DB::table('schedules')
            ->where(function ($query) use ($currentAcademicYear) {
                $query->where('academic_year', $currentAcademicYear)
                    ->orWhereNull('academic_year');
            })
            ->where('cycle_number', $schedule['cycle_number'])
            ->where('day_of_week', $schedule['day_of_week'])
            ->where('start_time', '<', $schedule['end_time'])
            ->where('end_time', '>', $schedule['start_time'])
            ->where(function ($query) use ($schedule) {
                $query->where('teacher_id', $schedule['teacher_id']);

                if (! empty($schedule['class_name'])) {
                    $query->orWhere('class_name', $schedule['class_name']);
                } elseif (! empty($schedule['class_id'])) {
                    $query->orWhere('class_id', $schedule['class_id']);
                }

                if (! empty($schedule['room_id'])) {
                    $query->orWhere('room_id', $schedule['room_id']);
                }
            });

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->first();
    };

    $dashboardView = function (?string $role = null) use ($waliClassNames, $bkClassNames, $canManageWaliClasses, $currentAcademicYear) {
        $user = Auth::user();
        $roleId = (int) ($user->role_id ?? 0);
        $dashboardRoleId = $roleId === 5 ? 2 : $roleId;
        $hasWaliAssignment = $canManageWaliClasses((int) $user->getKey());

        if ($role !== null) {
            $roleMap = [
                'waka' => 1,
                'guru' => 2,
                'siswa' => 3,
                'kesiswaan' => 4,
                'wali-kelas' => 5,
                'orang-tua' => 6,
            ];
            $requestedRoleId = $roleMap[strtolower($role)] ?? null;

            $isWaliDashboardAlias = strtolower($role) === 'wali-kelas'
                && ($roleId === 5 || ($roleId === 2 && $hasWaliAssignment));
            $isStudentDashboardAlias = in_array($roleId, [3, 6], true)
                && in_array(strtolower($role), ['siswa', 'orang-tua'], true);

            $isTeacherDashboardAlias = in_array($roleId, [2, 4, 5], true)
                && in_array(strtolower($role), ['guru', 'kesiswaan'], true);

            if ($requestedRoleId === null || ($requestedRoleId !== $dashboardRoleId && ! $isWaliDashboardAlias && ! $isStudentDashboardAlias && ! $isTeacherDashboardAlias)) {
                abort(403, 'Akses dashboard tidak sesuai role Anda.');
            }
        }

        $stats = [
            'total_siswa' => 0,
            'total_guru' => 0,
            'total_jadwal' => 0,
            'total_kehadiran' => 0,
            'total_izin' => 0,
            'pending_izin' => 0,
            'riwayat_izin' => 0,
            'total_akun' => 0,
        ];

        if ($dashboardRoleId === 1) {
            $stats['total_siswa'] = Schema::hasTable('siswas') ? DB::table('siswas')->count() : 0;
            $stats['total_guru'] = Schema::hasTable('users') ? DB::table('users')->whereIn('role_id', [2, 4])->count() : 0;
            $stats['total_jadwal'] = Schema::hasTable('schedules')
                ? DB::table('schedules')->where(function ($query) use ($currentAcademicYear) {
                    $query->where('academic_year', $currentAcademicYear)->orWhereNull('academic_year');
                })->count()
                : 0;
            $stats['total_kehadiran'] = Schema::hasTable('attendances') ? DB::table('attendances')->count() : 0;
            $stats['total_izin'] = Schema::hasTable('leave_requests') ? DB::table('leave_requests')->where('status_approval', 'Approved')->count() : 0;
            $stats['pending_izin'] = Schema::hasTable('leave_requests') ? DB::table('leave_requests')->where('status_approval', 'Pending')->count() : 0;
            $stats['riwayat_izin'] = Schema::hasTable('leave_requests') ? DB::table('leave_requests')->whereIn('status_approval', ['Approved', 'Rejected'])->count() : 0;
            $stats['total_akun'] = Schema::hasTable('users') ? DB::table('users')->count() : 0;
        } elseif ($dashboardRoleId === 4) {
            $stats['pending_izin'] = Schema::hasTable('leave_requests') ? DB::table('leave_requests')->where('status_approval', 'Pending')->count() : 0;
            $stats['riwayat_izin'] = Schema::hasTable('leave_requests') ? DB::table('leave_requests')->whereIn('status_approval', ['Approved', 'Rejected'])->count() : 0;
        }

        $viewMap = [
            1 => 'dashboard_waka',
            2 => 'dashboard_guru',
            3 => 'dashboard_siswa',
            4 => 'dashboard_kesiswaan',
            5 => 'dashboard_guru',
            6 => 'dashboard_siswa',
        ];

        $viewName = $role && isset($roleMap[strtolower($role)])
            ? ($viewMap[$roleMap[strtolower($role)]] ?? 'dashboard_waka')
            : ($viewMap[$roleId] ?? 'dashboard_waka');
        $activeCycle = Schema::hasTable('system_configurations')
            ? (int) (DB::table('system_configurations')->value('active_cycle') ?? 1)
            : 1;

        // Extra data for guru & kesiswaan dashboard
        $extraData = [];
        if (in_array($dashboardRoleId, [2, 4], true)) {
            $teacherId = $user->id;
            $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5];
            $allTeacherSchedules = Schema::hasTable('schedules')
                ? DB::table('schedules')
                    ->leftJoin('ruangan', 'schedules.room_id', '=', 'ruangan.id_ruangan')
                    ->select('schedules.*', 'ruangan.nama_ruangan', 'ruangan.jenis as room_type')
                    ->where('schedules.teacher_id', $teacherId)
                    ->where(function ($query) use ($currentAcademicYear) {
                        $query->where('schedules.academic_year', $currentAcademicYear)->orWhereNull('schedules.academic_year');
                    })
                    ->get()
                : collect();
            $jadwals = (Schema::hasTable('schedules') && Schema::hasColumn('schedules', 'cycle_number')
                ? $allTeacherSchedules->where('cycle_number', $activeCycle)->values()
                : $allTeacherSchedules)
                ->sortBy(function ($item) use ($dayOrder) {
                    return ($dayOrder[$item->day_of_week] ?? 99) . '_' . ($item->start_time ?? '');
                })
                ->values();
            $journals = Schema::hasTable('journals')
                ? DB::table('journals')
                    ->leftJoin('schedules', 'journals.schedule_id', '=', 'schedules.id')
                    ->select('journals.*', 'schedules.subject')
                    ->whereIn('journals.schedule_id', $allTeacherSchedules->pluck('id'))
                    ->where(function ($query) use ($teacherId) {
                        $query->whereNull('journals.created_by')
                            ->orWhere('journals.created_by', $teacherId);
                    })
                    ->orderByDesc('journals.date')
                    ->get()
                : collect();
            $presensiList = Schema::hasTable('attendances')
                ? DB::table('attendances')
                    ->leftJoin('siswas', 'attendances.student_id', '=', 'siswas.id')
                    ->leftJoin('journals', 'attendances.journal_id', '=', 'journals.id')
                    ->select('attendances.*', 'siswas.name as student_name', 'journals.topic_description')
                    ->whereIn('attendances.journal_id', $journals->pluck('id'))
                    ->orderByDesc('attendances.date')
                    ->get()
                : collect();
            $weekdayNames = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu',
            ];
            $today = now();
            $todaySchedules = $jadwals->where('day_of_week', $weekdayNames[$today->format('l')])->values();

            $todayJournalScheduleIds = Schema::hasTable('journals')
                ? DB::table('journals')
                    ->whereIn('schedule_id', $todaySchedules->pluck('id'))
                    ->whereDate('date', $today->toDateString())
                    ->pluck('schedule_id')
                : collect();
            $missingJournalsToday = $todaySchedules
                ->reject(fn ($schedule) => $todayJournalScheduleIds->contains($schedule->id))
                ->values();

            $schedulesWithJournals = $journals->pluck('schedule_id')->unique();
            $unfilledSchedules = $jadwals
                ->reject(fn ($schedule) => $schedulesWithJournals->contains($schedule->id))
                ->values();

            $extraData = [
                'jadwals' => $jadwals,
                'journals' => $journals,
                'presensiList' => $presensiList,
                'missingJournalsToday' => $missingJournalsToday,
                'unfilledSchedules' => $unfilledSchedules,
                'schedulesWithJournals' => $schedulesWithJournals,
                'totalJadwal' => $jadwals->count(),
                'totalJurnal' => $journals->count(),
                'totalPresensi' => $presensiList->count(),
                'waliClassNames' => $hasWaliAssignment ? $waliClassNames((int) $user->id) : collect(),
                'bkClassNames' => Schema::hasTable('user_bk_classes') ? $bkClassNames((int) $user->id) : collect(),
            ];
        }
        if (in_array($roleId, [3, 6], true)) {
            $student = $user->student_id
                ? DB::table('siswas')->where('id', $user->student_id)->first()
                : null;
            $jadwalList = collect();

            if ($student && ! empty($student->kelas) && Schema::hasTable('schedules') && Schema::hasColumn('schedules', 'class_name')) {
                $jadwalList = DB::table('schedules')
                    ->leftJoin('users', 'schedules.teacher_id', '=', 'users.id')
                    ->leftJoin('ruangan', 'schedules.room_id', '=', 'ruangan.id_ruangan')
                    ->select('schedules.*', 'users.fullname as guru_name', 'ruangan.nama_ruangan', 'ruangan.jenis as room_type')
                    ->where('schedules.cycle_number', $activeCycle)
                    ->where('schedules.class_name', $student->kelas)
                    ->where(function ($query) use ($currentAcademicYear) {
                        $query->where('schedules.academic_year', $currentAcademicYear)->orWhereNull('schedules.academic_year');
                    })
                    ->orderBy('schedules.day_of_week')
                    ->orderBy('schedules.start_time')
                    ->get();
            }

            $studentAttendances = ($student && Schema::hasTable('attendances'))
                ? DB::table('attendances')
                    ->where('attendances.student_id', $student->id)
                    ->leftJoin('journals', 'attendances.journal_id', '=', 'journals.id')
                    ->leftJoin('schedules', 'journals.schedule_id', '=', 'schedules.id')
                    ->leftJoin('users as teacher', 'schedules.teacher_id', '=', 'teacher.id')
                    ->select([
                        'attendances.id',
                        'attendances.date',
                        'attendances.status',
                        'attendances.notes',
                        'journals.topic_description',
                        'schedules.subject',
                        'teacher.fullname as teacher_name',
                    ])
                    ->orderByDesc('attendances.date')
                    ->orderByDesc('attendances.id')
                    ->get()
                : collect();

            $stats['total_jadwal'] = $jadwalList->count();
            $stats['total_kehadiran'] = $studentAttendances->count();
            $stats['total_hadir'] = $studentAttendances->where('status', 'Hadir')->count();
            $stats['total_sakit'] = $studentAttendances->where('status', 'Sakit')->count();
            $stats['total_izin_absen'] = $studentAttendances->where('status', 'Izin')->count();
            $stats['total_alpha'] = $studentAttendances->where('status', 'Alpha')->count();

            $extraData = [
                'student' => $student,
                'studentClass' => $student->kelas ?? null,
                'jadwalList' => $jadwalList,
                'recentAttendances' => $studentAttendances->take(5),
                'totalKehadiranSiswa' => $studentAttendances->count(),
            ];
        }
        if ($roleId === 5 || ($roleId === 2 && $hasWaliAssignment)) {
            $classNames = $waliClassNames((int) $user->id);
            $extraData = array_merge($extraData, [
                'classNames' => $classNames,
                'pendingLeaveCount' => Schema::hasTable('leave_requests')
                    ? DB::table('leave_requests')
                        ->join('siswas', 'leave_requests.student_id', '=', 'siswas.id')
                        ->whereIn('siswas.kelas', $classNames)
                        ->where('leave_requests.status_approval', 'Pending')
                        ->count()
                    : 0,
            ]);
        }

        return view($viewName, array_merge([
            'user' => $user,
            'roleId' => $roleId,
            'stats' => $stats,
            'activeCycle' => $activeCycle,
            'academicYear' => $currentAcademicYear,
        ], $extraData));
    };

    Route::get('/dashboard', function () use ($dashboardRoleForUser) {
        return redirect()->route('dashboard.role', ['role' => $dashboardRoleForUser(Auth::user())]);
    })->name('dashboard');

    Route::get('/dashboard/{role}', function (string $role) use ($dashboardView) {
        return $dashboardView($role);
    })->whereIn('role', ['waka', 'guru', 'siswa', 'kesiswaan', 'wali-kelas', 'orang-tua'])->name('dashboard.role');

    Route::get('/profile', function () use ($dashboardRoleForUser) {
        $user = Auth::user();
        $roleId = (int) ($user->role_id ?? 0);
        $roleSlugMap = [
            1 => 'waka',
            2 => 'guru',
            3 => 'siswa',
            4 => 'kesiswaan',
            5 => 'guru',
            6 => 'siswa',
        ];
        $roleNameMap = [
            1 => 'Waka Kurikulum',
            2 => 'Guru Pengajar',
            3 => 'Siswa',
            4 => 'Kesiswaan',
            5 => 'Guru Pengajar',
            6 => 'Orang Tua / Wali Murid',
        ];
        $studentProfile = in_array($roleId, [3, 6], true) && $user->student_id
            ? DB::table('siswas')->where('id', $user->student_id)->first()
            : null;
        if ($studentProfile && ! $studentProfile->phone) {
            $studentProfile->phone = $user->phone;
        }
        $teacherSubjects = in_array($roleId, [2, 5], true)
            ? DB::table('schedules')
                ->where('teacher_id', $user->id)
                ->whereNotNull('subject')
                ->distinct()
                ->orderBy('subject')
                ->pluck('subject')
            : collect();
        $isWaliKelas = in_array($roleId, [2, 5], true)
            && DB::table('user_school_classes')->where('user_id', $user->getKey())->exists();

        return view('profile', [
            'user' => $user,
            'roleId' => $roleId,
            'roleSlug' => $roleSlugMap[$roleId] ?? 'waka',
            'dashboardSlug' => $dashboardRoleForUser($user),
            'roleName' => $roleNameMap[$roleId] ?? 'Pengguna',
            'isWaliKelas' => $isWaliKelas,
            'studentProfile' => $studentProfile,
            'teacherSubjects' => $teacherSubjects,
        ]);
    })->name('profile');

    Route::post('/profile', function (Request $request) {
        $user = Auth::user();

        if ($request->filled('email')) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }

        $rules = [
            'fullname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,'.$user->id],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
        if ((int) $user->role_id === 2) {
            $rules['nip'] = ['nullable', 'string', 'max:30', Rule::unique('users', 'nip')->ignore($user->id)];
        }
        if (in_array((int) $user->role_id, [3, 6], true) && $user->student_id) {
            if ($request->has('nis')) {
                $rules['nis'] = ['nullable', 'string', 'max:30', Rule::unique('siswas', 'nis')->ignore($user->student_id)];
            }
            $rules['alamat'] = ['nullable', 'string', 'max:255'];
        }

        if ($request->filled('new_password')) {
            $rules['current_password'] = ['required', 'string'];
            $rules['new_password'] = ['required', 'string', 'min:6', 'confirmed'];
        }

        $validated = $request->validate($rules, [
            'fullname.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan.',
            'current_password.required' => 'Password saat ini wajib diisi jika ingin mengganti password.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if ($request->filled('new_password')) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])->withInput();
            }
        }

        $updateData = [
            'fullname' => $validated['fullname'],
            'username' => $validated['username'],
            'email' => isset($validated['email']) ? strtolower($validated['email']) : null,
            'phone' => $validated['phone'] ?? null,
        ];
        if ((int) $user->role_id === 2) {
            $updateData['nip'] = $validated['nip'] ?? null;
        }

        if ($request->filled('new_password')) {
            $updateData['password'] = Hash::make($request->input('new_password'));
        }

        DB::transaction(function () use ($user, $updateData, $validated) {
            DB::table('users')->where('id', $user->id)->update($updateData);

            if (in_array((int) $user->role_id, [3, 6], true) && $user->student_id) {
                $siswaUpdateData = [
                    'phone' => $validated['phone'] ?? null,
                    'email' => isset($validated['email']) ? strtolower($validated['email']) : null,
                    'updated_at' => now(),
                ];
                if (array_key_exists('alamat', $validated)) {
                    $siswaUpdateData['alamat'] = $validated['alamat'] ?? '';
                }
                if (array_key_exists('nis', $validated) && $validated['nis'] !== null) {
                    $siswaUpdateData['nis'] = $validated['nis'];
                }
                DB::table('siswas')->where('id', $user->student_id)->update($siswaUpdateData);
            }
        });

        return redirect()->route('profile')->with('success', 'Profil Anda berhasil diperbarui!');
    })->name('profile.update');

    Route::get('/siswa/jadwal', function () use ($currentAcademicYear) {
        abort_unless(in_array((int) (Auth::user()->role_id ?? 0), [3, 6], true), 403);
        $activeCycle = (int) (DB::table('system_configurations')->value('active_cycle') ?? 1);
        $student = Auth::user()->student_id
            ? DB::table('siswas')->where('id', Auth::user()->student_id)->first()
            : null;

        $jadwals = $student && ! empty($student->kelas) && Schema::hasTable('schedules') && Schema::hasColumn('schedules', 'class_name')
            ? DB::table('schedules')
                ->leftJoin('users', 'schedules.teacher_id', '=', 'users.id')
                ->leftJoin('ruangan', 'schedules.room_id', '=', 'ruangan.id_ruangan')
                ->select('schedules.*', 'users.fullname as guru_name', 'ruangan.nama_ruangan', 'ruangan.jenis as room_type')
                ->where('schedules.cycle_number', $activeCycle)
                ->where('schedules.class_name', $student->kelas)
                ->where(function ($query) use ($currentAcademicYear) {
                    $query->where('schedules.academic_year', $currentAcademicYear)->orWhereNull('schedules.academic_year');
                })
                ->orderBy('schedules.day_of_week')
                ->orderBy('schedules.start_time')
                ->get()
            : collect();

        return view('siswa.jadwal', [
            'user' => Auth::user(),
            'jadwals' => $jadwals,
            'activeCycle' => $activeCycle,
            'studentClass' => $student->kelas ?? null,
            'academicYear' => $currentAcademicYear,
        ]);
    })->name('siswa.jadwal');

    Route::get('/siswa/jadwal/export-pdf', function () use ($currentAcademicYear) {
        abort_unless(in_array((int) (Auth::user()->role_id ?? 0), [3, 6], true), 403);

        $activeCycle = (int) (DB::table('system_configurations')->value('active_cycle') ?? 1);
        $student = Auth::user()->student_id
            ? DB::table('siswas')->where('id', Auth::user()->student_id)->first()
            : null;

        $studentClass = $student->kelas ?? null;
        $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5];

        $jadwals = ($student && ! empty($studentClass))
            ? DB::table('schedules')
                ->leftJoin('users', 'schedules.teacher_id', '=', 'users.id')
                ->leftJoin('ruangan', 'schedules.room_id', '=', 'ruangan.id_ruangan')
                ->select('schedules.*', 'users.fullname as guru_name', 'ruangan.nama_ruangan', 'ruangan.jenis as room_type')
                ->where('schedules.cycle_number', $activeCycle)
                ->where('schedules.class_name', $studentClass)
                ->where(function ($query) use ($currentAcademicYear) {
                    $query->where('schedules.academic_year', $currentAcademicYear)->orWhereNull('schedules.academic_year');
                })
                ->orderBy('schedules.day_of_week')
                ->orderBy('schedules.start_time')
                ->get()
                ->sortBy(function ($item) use ($dayOrder) {
                    return ($dayOrder[$item->day_of_week] ?? 99) . '_' . $item->start_time;
                })
                ->values()
            : collect();

        return view('pdf.jadwal', [
            'jadwals' => $jadwals,
            'activeCycle' => $activeCycle,
            'filterClass' => $studentClass,
            'academicYear' => $currentAcademicYear,
            'pageTitle' => 'Jadwal Pelajaran Siswa - ' . ($studentClass ?? 'Kelas'),
            'documentTitle' => 'JADWAL BELAJAR SISWA',
        ]);
    })->name('siswa.jadwal.pdf');

    Route::get('/siswa/absensi', function (Request $request) use ($currentAcademicYear) {
        $user = Auth::user();
        abort_unless(in_array((int) ($user->role_id ?? 0), [3, 6], true), 403);

        $student = $user->student_id
            ? DB::table('siswas')->where('id', $user->student_id)->first()
            : null;

        $studentId = $student?->id;

        $attendances = collect();
        if (Schema::hasTable('attendances') && $studentId) {
            $attendances = DB::table('attendances')
                ->where('attendances.student_id', $studentId)
                ->leftJoin('journals', 'attendances.journal_id', '=', 'journals.id')
                ->leftJoin('schedules', 'journals.schedule_id', '=', 'schedules.id')
                ->leftJoin('users as teacher', 'schedules.teacher_id', '=', 'teacher.id')
                ->leftJoin('ruangan', 'schedules.room_id', '=', 'ruangan.id_ruangan')
                ->select([
                    'attendances.id',
                    'attendances.journal_id',
                    'attendances.student_id',
                    'attendances.status',
                    'attendances.date',
                    'attendances.notes',
                    'attendances.created_at',
                    'journals.topic_description',
                    'journals.photo_path as journal_photo',
                    'schedules.subject',
                    'schedules.day_of_week',
                    'schedules.start_time',
                    'schedules.end_time',
                    'schedules.cycle_number',
                    'teacher.fullname as teacher_name',
                    'ruangan.nama_ruangan as room_name',
                ])
                ->orderByDesc('attendances.date')
                ->orderByDesc('attendances.id')
                ->get();
        }

        $totalAttendances = $attendances->count();
        $totalHadir = $attendances->where('status', 'Hadir')->count();
        $totalSakit = $attendances->where('status', 'Sakit')->count();
        $totalIzin = $attendances->where('status', 'Izin')->count();
        $totalAlpha = $attendances->where('status', 'Alpha')->count();
        $percentageHadir = $totalAttendances > 0 ? round(($totalHadir / $totalAttendances) * 100, 1) : 0;

        return view('siswa.absensi', [
            'user' => $user,
            'student' => $student,
            'attendances' => $attendances,
            'totalAttendances' => $totalAttendances,
            'totalHadir' => $totalHadir,
            'totalSakit' => $totalSakit,
            'totalIzin' => $totalIzin,
            'totalAlpha' => $totalAlpha,
            'percentageHadir' => $percentageHadir,
            'academicYear' => $currentAcademicYear,
        ]);
    })->name('siswa.absensi');

    Route::get('/siswa/izin', function () {
        abort_unless(in_array((int) (Auth::user()->role_id ?? 0), [3, 6], true), 403);

        $student = Auth::user()->student_id
            ? DB::table('siswas')->where('id', Auth::user()->student_id)->first()
            : null;
        $leaveRequests = Schema::hasTable('leave_requests')
            ? DB::table('leave_requests')
                ->leftJoin('siswas', 'leave_requests.student_id', '=', 'siswas.id')
                ->leftJoin('users as verifier', 'leave_requests.verified_by', '=', 'verifier.id')
                ->select('leave_requests.*', 'siswas.name as student_name', 'siswas.kelas', 'verifier.fullname as verifier_name')
                ->where('leave_requests.student_id', Auth::user()->student_id ?? 0)
                ->orderByDesc('leave_requests.start_date')
                ->orderByDesc('leave_requests.id')
                ->get()
            : collect();

        return response()
            ->view('siswa.izin', [
                'user' => Auth::user(),
                'student' => $student,
                'leaveRequests' => $leaveRequests,
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    })->name('siswa.izin');

    Route::post('/siswa/izin', function (Request $request) {
        abort_unless(in_array((int) (Auth::user()->role_id ?? 0), [3, 6], true), 403);
        abort_unless(Schema::hasTable('leave_requests'), 503, 'Fitur pengajuan izin belum tersedia.');

        $studentId = Auth::user()->student_id;
        if (! $studentId || ! DB::table('siswas')->where('id', $studentId)->exists()) {
            throw ValidationException::withMessages([
                'student' => 'Akun ini belum ditautkan ke data siswa. Minta Waka menetapkan data siswa pada menu Kelola Akun & Role.',
            ]);
        }

        $validated = $request->validate([
            'leave_type' => ['nullable', 'string', Rule::in(['Sakit', 'Izin', 'Izin Keluar Saat KBM'])],
            'reason' => ['required', 'string', 'max:2000'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,jfif', 'max:5120'],
        ], [
            'photo.required' => 'Foto bukti (foto selfie atau surat keterangan) wajib dikirim.',
            'photo.image' => 'File bukti harus berupa gambar foto (JPG, PNG, WEBP).',
            'photo.max' => 'Ukuran file foto maksimal 5 MB.',
            'leave_type.in' => 'Pilihan jenis izin tidak valid.',
            'reason.required' => 'Alasan izin wajib diisi.',
        ]);

        $leaveType = $validated['leave_type'] ?? (stripos($validated['reason'], 'sakit') !== false ? 'Sakit' : 'Izin');

        $today = now()->toDateString();
        $documentPath = $request->file('photo')?->store('leave_requests', 'public');
        if ($request->hasFile('photo') && ! $documentPath) {
            abort(500, 'Foto bukti gagal disimpan. Silakan coba lagi.');
        }

        $id = DB::table('leave_requests')->insertGetId([
            'student_id' => $studentId,
            'applicant_id' => Auth::user()->getKey(),
            'start_date' => $today,
            'end_date' => $today,
            'leave_type' => $leaveType,
            'reason' => $validated['reason'],
            'document_path' => $documentPath,
            'status_approval' => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! $request->expectsJson()) {
            return redirect()->route('siswa.izin')->with('success', 'Pengajuan izin berhasil dikirim.');
        }

        return response()->json([
            'message' => 'Pengajuan izin berhasil dikirim.',
            'data' => DB::table('leave_requests')
                ->leftJoin('siswas', 'leave_requests.student_id', '=', 'siswas.id')
                ->leftJoin('users as verifier', 'leave_requests.verified_by', '=', 'verifier.id')
                ->select('leave_requests.*', 'siswas.name as student_name', 'siswas.kelas', 'verifier.fullname as verifier_name')
                ->where('leave_requests.id', $id)
                ->first(),
        ], 201);
    })->name('siswa.izin.store');

    Route::get('/siswa/izin/updates', function () {
        abort_unless(in_array((int) (Auth::user()->role_id ?? 0), [3, 6], true), 403);

        $leaveRequests = Schema::hasTable('leave_requests')
            ? DB::table('leave_requests')
                ->leftJoin('siswas', 'leave_requests.student_id', '=', 'siswas.id')
                ->leftJoin('users as verifier', 'leave_requests.verified_by', '=', 'verifier.id')
                ->select('leave_requests.*', 'siswas.name as student_name', 'siswas.kelas', 'verifier.fullname as verifier_name')
                ->where('leave_requests.student_id', Auth::user()->student_id ?? 0)
                ->orderByDesc('leave_requests.start_date')
                ->orderByDesc('leave_requests.id')
                ->get()
            : collect();

        return response()->json(['data' => $leaveRequests]);
    })->name('siswa.izin.updates');

    // Waka routes
    $downloadStudentWorkbook = function (array $rows, string $filename) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Nama', 'NIS', 'Kelas', 'Jurusan', 'No HP', 'Email', 'Alamat'], null, 'A1');
        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2');
        }
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    };

    Route::get('/waka/data-siswa/template', function () use ($downloadStudentWorkbook) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        return $downloadStudentWorkbook([], 'template_import_siswa.xlsx');
    })->name('waka.siswa.template');

    Route::get('/waka/data-siswa/contoh', function () use ($downloadStudentWorkbook) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        return $downloadStudentWorkbook([
            ['Dinda Safitri', 'IMP26001', 'X RPL 1', 'RPL', '081200000001', '', 'Jl. Melati No. 1'],
            ['Rizky Maulana', 'IMP26002', 'X TKJ 1', 'TKJ', '081200000002', '', 'Jl. Kenanga No. 2'],
            ['Aulia Rahma', 'IMP26003', 'XI DKV 1', 'DKV', '081200000003', '', 'Jl. Mawar No. 3'],
            ['Fajar Ramadhan', 'IMP26004', 'XI AKL 1', 'AKL', '081200000004', '', 'Jl. Anggrek No. 4'],
            ['Nabila Putri', 'IMP26005', 'XII MP 1', 'MP', '081200000005', '', 'Jl. Cempaka No. 5'],
        ], 'contoh_import_siswa_5_data.xlsx');
    })->name('waka.siswa.example');

    Route::get('/waka/data-siswa', function (Request $request) use ($syncSchoolClasses) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $syncSchoolClasses();
        $search = trim((string) $request->query('q', ''));
        $filters = $request->validate([
            'jurusan' => ['nullable', 'string', Rule::in(['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BR', 'BD', 'LPS'])],
            'tingkat' => ['nullable', 'string', Rule::in(['X', 'XI', 'XII'])],
            'rombel' => ['nullable', 'string', Rule::in(['1', '2', '3', '4'])],
            'kelas' => ['nullable', 'string', 'max:50'],
        ]);
        $selectedStatus = $request->query('status', 'aktif');
        $query = DB::table('siswas')->orderBy('name');
        if ($selectedStatus === 'aktif') {
            $query->where('status', 'aktif');
        } elseif ($selectedStatus === 'lulus') {
            $query->where('status', 'lulus');
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('kelas', 'like', "%{$search}%")
                    ->orWhere('jurusan', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if (! empty($filters['jurusan'])) {
            $query->where('jurusan', $filters['jurusan']);
        }
        if (! empty($filters['tingkat'])) {
            $tingkat = $filters['tingkat'];
            $query->where(function ($query) use ($tingkat) {
                $query->where('kelas', $tingkat)
                    ->orWhere('kelas', 'like', $tingkat.' %');
            });
        }
        if (! empty($filters['rombel'])) {
            $rombel = $filters['rombel'];
            $query->where(function ($query) use ($rombel) {
                $query->where('kelas', 'like', "% {$rombel}")
                    ->orWhere('kelas', 'like', "%-{$rombel}")
                    ->orWhere('kelas', 'like', "% {$rombel} %");
                if ($rombel === '1') {
                    $query->orWhere(function ($q) {
                        $q->where('kelas', 'not like', '% 2')
                            ->where('kelas', 'not like', '% 3')
                            ->where('kelas', 'not like', '% 4');
                    });
                }
            });
        }
        if (! empty($filters['kelas'])) {
            $query->where('kelas', $filters['kelas']);
        }
        $siswas = $query->paginate(50)->withQueryString();

        $classesQuery = DB::table('siswas')
            ->select('kelas', DB::raw('count(*) as total'))
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '');
        if ($selectedStatus === 'aktif') {
            $classesQuery->where('status', 'aktif');
        } elseif ($selectedStatus === 'lulus') {
            $classesQuery->where('status', 'lulus');
        }
        $availableClasses = $classesQuery->groupBy('kelas')->orderBy('kelas')->get();

        $schoolClasses = DB::table('school_classes')->orderBy('name')->pluck('name');

        return view('waka.data_siswa', [
            'user' => Auth::user(),
            'siswas' => $siswas,
            'search' => $search,
            'selectedStatus' => $selectedStatus,
            'selectedJurusan' => $filters['jurusan'] ?? '',
            'selectedTingkat' => $filters['tingkat'] ?? '',
            'selectedRombel' => $filters['rombel'] ?? '',
            'selectedKelas' => $filters['kelas'] ?? '',
            'availableClasses' => $availableClasses,
            'schoolClasses' => $schoolClasses,
        ]);
    })->name('waka.siswa');

    Route::get('/waka/data-siswa/kelas-siswa', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $kelas = trim((string) $request->query('kelas', ''));
        if ($kelas === '') {
            return response()->json([]);
        }
        $students = DB::table('siswas')
            ->where('kelas', $kelas)
            ->where('status', 'aktif')
            ->select('id', 'name', 'nis')
            ->orderBy('name')
            ->get();

        return response()->json($students);
    })->name('waka.siswa.class_students');

    // =========================================================================
    // KENAIKAN KELAS & KELULUSAN SISWA SERENTAK
    // =========================================================================
    Route::get('/waka/kenaikan-kelas', function () {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $countX = DB::table('siswas')->where('status', 'aktif')->where('kelas', 'like', 'X %')->count();
        $countXI = DB::table('siswas')->where('status', 'aktif')->where('kelas', 'like', 'XI %')->count();
        $countXII = DB::table('siswas')->where('status', 'aktif')->where('kelas', 'like', 'XII %')->count();

        // Ambil siswa aktif per rombel untuk pengecualian jika ada yang tinggal kelas
        $activeStudents = DB::table('siswas')
            ->where('status', 'aktif')
            ->select('id', 'name', 'nis', 'kelas')
            ->orderBy('kelas')
            ->orderBy('name')
            ->get();

        $classesWithStudents = [];
        foreach ($activeStudents as $st) {
            $classesWithStudents[$st->kelas][] = $st;
        }

        return view('waka.kenaikan_kelas', [
            'user' => Auth::user(),
            'countX' => $countX,
            'countXI' => $countXI,
            'countXII' => $countXII,
            'classesWithStudents' => $classesWithStudents,
        ]);
    })->name('waka.kenaikan_kelas');

    Route::post('/waka/kenaikan-kelas/proses', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $request->validate([
            'tahun_lulus' => ['required', 'string', 'max:20'],
            'tanggal_lulus' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'excluded_students' => ['nullable', 'array'],
            'excluded_students.*' => ['integer'],
        ]);

        $tahunLulus = trim($request->input('tahun_lulus'));
        $tanggalLulus = $request->input('tanggal_lulus');
        $keterangan = trim((string) $request->input('keterangan'));
        $excludedIds = array_map('intval', (array) $request->input('excluded_students', []));

        $countGraduated = 0;
        $countPromotedXI = 0;
        $countPromotedX = 0;

        DB::transaction(function () use ($tahunLulus, $tanggalLulus, $keterangan, $excludedIds, &$countGraduated, &$countPromotedXI, &$countPromotedX) {
            $now = now();

            // 1. KELULUSAN SISWA KELAS XII (Simpan ke Arsip Alumni & Nonaktifkan Akun Login)
            $graduatingQuery = DB::table('siswas')
                ->where('status', 'aktif')
                ->where('kelas', 'like', 'XII %');

            if (! empty($excludedIds)) {
                $graduatingQuery->whereNotIn('id', $excludedIds);
            }

            $graduatingStudents = $graduatingQuery->get();

            if ($graduatingStudents->isNotEmpty()) {
                $alumniInserts = [];
                $graduatingIds = [];

                foreach ($graduatingStudents as $s) {
                    $graduatingIds[] = $s->id;
                    $alumniInserts[] = [
                        'student_id' => $s->id,
                        'nis' => $s->nis,
                        'name' => $s->name,
                        'jurusan' => $s->jurusan,
                        'kelas_terakhir' => $s->kelas,
                        'tahun_lulus' => $tahunLulus,
                        'tanggal_lulus' => $tanggalLulus,
                        'alamat' => $s->alamat,
                        'phone' => $s->phone,
                        'email' => $s->email,
                        'keterangan' => $keterangan !== '' ? $keterangan : 'Lulus Tingkat XII',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                foreach (array_chunk($alumniInserts, 200) as $chunk) {
                    DB::table('alumnis')->insert($chunk);
                }

                DB::table('siswas')
                    ->whereIn('id', $graduatingIds)
                    ->update([
                        'status' => 'lulus',
                        'tahun_lulus' => $tahunLulus,
                        'tanggal_lulus' => $tanggalLulus,
                        'updated_at' => $now,
                    ]);

                // NONAKTIFKAN AKUN LOGIN SISWA & ORANG TUA
                DB::table('users')
                    ->whereIn('student_id', $graduatingIds)
                    ->update([
                        'is_active' => 0,
                    ]);

                $countGraduated = count($graduatingIds);
            }

            // 2. NAIK KELAS: TINGKAT XI -> TINGKAT XII
            $promotedXIQuery = DB::table('siswas')
                ->where('status', 'aktif')
                ->where('kelas', 'like', 'XI %');

            if (! empty($excludedIds)) {
                $promotedXIQuery->whereNotIn('id', $excludedIds);
            }

            $countPromotedXI = $promotedXIQuery->count();
            $promotedXIQuery->update([
                'kelas' => DB::raw("CONCAT('XII ', SUBSTRING(kelas, 4))"),
                'updated_at' => $now,
            ]);

            // 3. NAIK KELAS: TINGKAT X -> TINGKAT XI
            $promotedXQuery = DB::table('siswas')
                ->where('status', 'aktif')
                ->where('kelas', 'like', 'X %');

            if (! empty($excludedIds)) {
                $promotedXQuery->whereNotIn('id', $excludedIds);
            }

            $countPromotedX = $promotedXQuery->count();
            $promotedXQuery->update([
                'kelas' => DB::raw("CONCAT('XI ', SUBSTRING(kelas, 3))"),
                'updated_at' => $now,
            ]);
        });

        $message = "🎉 Sukses! Kenaikan kelas & kelulusan serentak selesai diproses. ";
        $message .= "Sebanyak {$countGraduated} siswa Kelas XII telah lulus, akunnya dinonaktifkan, dan diarsipkan ke Data Alumni. ";
        $message .= "Sebanyak {$countPromotedXI} siswa Kelas XI naik ke Tingkat XII, dan {$countPromotedX} siswa Kelas X naik ke Tingkat XI.";

        return redirect()->route('waka.kenaikan_kelas')->with('success', $message);
    })->name('waka.kenaikan_kelas.proses');

    // =========================================================================
    // DATA ALUMNI (ARSIP SISWA LULUS)
    // =========================================================================
    Route::get('/waka/alumni', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $search = trim((string) $request->query('q', ''));
        $selectedYear = trim((string) $request->query('tahun_lulus', ''));
        $selectedJurusan = trim((string) $request->query('jurusan', ''));
        $selectedStatusAkun = trim((string) $request->query('status_akun', ''));

        $query = DB::table('alumnis')
            ->leftJoin('users', function ($join) {
                $join->on('alumnis.student_id', '=', 'users.student_id')
                    ->where('users.role_id', 3);
            })
            ->select('alumnis.*', 'users.is_active as account_active')
            ->orderBy('alumnis.tahun_lulus', 'desc')
            ->orderBy('alumnis.name', 'asc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('alumnis.name', 'like', "%{$search}%")
                    ->orWhere('alumnis.nis', 'like', "%{$search}%")
                    ->orWhere('alumnis.phone', 'like', "%{$search}%")
                    ->orWhere('alumnis.email', 'like', "%{$search}%")
                    ->orWhere('alumnis.kelas_terakhir', 'like', "%{$search}%");
            });
        }

        if ($selectedYear !== '') {
            $query->where('alumnis.tahun_lulus', $selectedYear);
        }

        if ($selectedJurusan !== '') {
            $query->where('alumnis.jurusan', $selectedJurusan);
        }

        if ($selectedStatusAkun === 'aktif') {
            $query->where('users.is_active', 1);
        } elseif ($selectedStatusAkun === 'nonaktif') {
            $query->where(function ($q) {
                $q->where('users.is_active', 0)->orWhereNull('users.is_active');
            });
        }

        $alumnis = $query->paginate(50)->withQueryString();
        $availableYears = DB::table('alumnis')->distinct()->orderBy('tahun_lulus', 'desc')->pluck('tahun_lulus');

        return view('waka.alumni', [
            'user' => Auth::user(),
            'alumnis' => $alumnis,
            'search' => $search,
            'selectedYear' => $selectedYear,
            'selectedJurusan' => $selectedJurusan,
            'selectedStatusAkun' => $selectedStatusAkun,
            'availableYears' => $availableYears,
        ]);
    })->name('waka.alumni');

    Route::post('/waka/alumni/{id}/toggle-status', function (int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $alumni = DB::table('alumnis')->where('id', $id)->first();
        abort_if(! $alumni, 404);

        $user = DB::table('users')->where('student_id', $alumni->student_id)->first();
        if ($user) {
            $newStatus = $user->is_active ? 0 : 1;
            DB::table('users')->where('student_id', $alumni->student_id)->update(['is_active' => $newStatus]);
            $statusText = $newStatus ? 'diaktifkan kembali' : 'dinonaktifkan (dikunci)';
            return back()->with('success', "Akun login untuk {$alumni->name} berhasil {$statusText}.");
        }

        return back()->withErrors(['Akun login untuk siswa ini tidak ditemukan.']);
    })->name('waka.alumni.toggle_status');

    Route::get('/waka/alumni/export', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $selectedYear = trim((string) $request->query('tahun_lulus', ''));
        $selectedJurusan = trim((string) $request->query('jurusan', ''));

        $query = DB::table('alumnis')->orderBy('tahun_lulus', 'desc')->orderBy('name', 'asc');
        if ($selectedYear !== '') {
            $query->where('tahun_lulus', $selectedYear);
        }
        if ($selectedJurusan !== '') {
            $query->where('jurusan', $selectedJurusan);
        }

        $alumnis = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="data_alumni_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($alumnis) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'NIS', 'Nama Siswa', 'Jurusan', 'Kelas Terakhir', 'Tahun Lulus', 'Tanggal Lulus', 'Alamat', 'Nomor Telepon', 'Email', 'Keterangan']);

            $no = 1;
            foreach ($alumnis as $a) {
                fputcsv($file, [
                    $no++,
                    $a->nis,
                    $a->name,
                    $a->jurusan,
                    $a->kelas_terakhir,
                    $a->tahun_lulus,
                    $a->tanggal_lulus,
                    $a->alamat,
                    $a->phone,
                    $a->email,
                    $a->keterangan,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('waka.alumni.export');


    Route::post('/waka/data-siswa/import', function (Request $request) use ($syncSchoolClasses) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $allowedExtensions = ['xlsx', 'xls', 'csv', 'txt'];
        $extension = strtolower($request->file('file')->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => 'Format file tidak didukung. Harap gunakan file .xlsx, .xls, atau .csv.',
            ]);
        }

        @set_time_limit(180);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'file' => 'File tidak dapat dibaca. Gunakan file .xlsx, .xls, atau .csv yang valid.',
            ]);
        }

        $headerAliases = [
            'nama' => 'name',
            'nama siswa' => 'name',
            'name' => 'name',
            'nis' => 'nis',
            'kelas' => 'kelas',
            'jurusan' => 'jurusan',
            'alamat' => 'alamat',
            'no hp' => 'phone',
            'no_hp' => 'phone',
            'nomor hp' => 'phone',
            'phone' => 'phone',
            'email' => 'email',
            'email pemulihan' => 'email',
        ];

        $firstSheet = $spreadsheet->getSheet(0);
        $firstSheetRows = $firstSheet->toArray(null, true, true, false);

        $isStandardFormat = false;
        $standardHeaderIndex = null;
        $columnIndexes = [];

        foreach (array_slice($firstSheetRows, 0, 10) as $rowIndex => $row) {
            $tempIndexes = [];
            foreach ($row as $colIdx => $header) {
                $norm = mb_strtolower(trim((string) $header));
                $norm = ltrim($norm, "\xEF\xBB\xBF");
                if (isset($headerAliases[$norm])) {
                    $tempIndexes[$headerAliases[$norm]] = $colIdx;
                }
            }
            if (isset($tempIndexes['name']) && isset($tempIndexes['kelas'])) {
                $isStandardFormat = true;
                $standardHeaderIndex = $rowIndex;
                $columnIndexes = $tempIndexes;
                break;
            }
        }

        $studentRows = [];
        $rowErrors = [];

        if ($isStandardFormat) {
            $missingHeaders = array_diff(['name', 'kelas', 'jurusan'], array_keys($columnIndexes));
            if ($missingHeaders !== []) {
                throw ValidationException::withMessages([
                    'file' => 'Kolom wajib pada Excel: Nama, Kelas, Jurusan, dan Alamat.',
                ]);
            }

            $rows = array_slice($firstSheetRows, $standardHeaderIndex + 1);
            if (count($rows) > 5000) {
                throw ValidationException::withMessages([
                    'file' => 'File maksimal berisi 5.000 baris data siswa.',
                ]);
            }

            $seenNis = [];
            foreach ($rows as $rowIndex => $row) {
                $lineNumber = $rowIndex + $standardHeaderIndex + 2;
                $isEmptyRow = collect($row)->every(fn ($value) => trim((string) $value) === '');
                if ($isEmptyRow) {
                    continue;
                }

                $student = [];
                foreach (['name', 'nis', 'kelas', 'jurusan', 'alamat', 'phone', 'email'] as $field) {
                    $value = isset($columnIndexes[$field]) ? ($row[$columnIndexes[$field]] ?? null) : null;
                    $student[$field] = is_scalar($value) ? trim((string) $value) : '';
                    if ($student[$field] === '') {
                        $student[$field] = null;
                    }
                }

                if (empty($student['alamat'])) {
                    $student['alamat'] = 'Bantul';
                }

                $validator = Validator::make($student, [
                    'name' => ['required', 'string', 'max:255'],
                    'nis' => ['nullable', 'string', 'max:30'],
                    'kelas' => ['required', 'string', 'max:255'],
                    'jurusan' => ['required', 'string', Rule::in(['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BR', 'BD', 'LPS'])],
                    'alamat' => ['required', 'string', 'max:2000'],
                    'phone' => ['nullable', 'string', 'max:20'],
                    'email' => ['nullable', 'string', 'max:255'],
                ]);
                if ($student['nis'] !== null && isset($seenNis[$student['nis']])) {
                    $validator->errors()->add('nis', 'NIS duplikat di dalam file.');
                }
                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $error) {
                        $rowErrors[] = "Baris {$lineNumber}: {$error}";
                    }
                    continue;
                }

                if ($student['nis'] !== null) {
                    $seenNis[$student['nis']] = true;
                }
                $studentRows[] = $student;
            }

            if ($studentRows === [] && $rowErrors === []) {
                $rowErrors[] = 'File tidak berisi baris data siswa.';
            }
            if ($rowErrors !== []) {
                throw ValidationException::withMessages(['file' => $rowErrors]);
            }
        } else {
            $determineJurusan = function (string $className): string {
                $upper = strtoupper($className);
                if (str_contains($upper, 'RPL')) return 'RPL';
                if (str_contains($upper, 'TKJ')) return 'TKJ';
                if (str_contains($upper, 'DKV')) return 'DKV';
                if (str_contains($upper, 'AKL') || str_contains($upper, 'AK')) return 'AKL';
                if (str_contains($upper, 'LPS')) return 'LPS';
                if (str_contains($upper, 'MP')) return 'MP';
                if (str_contains($upper, 'BR')) return 'BR';
                if (str_contains($upper, 'BD')) return 'BD';
                return '';
            };

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $rows = $sheet->toArray(null, true, true, false);
                $currentClass = null;
                $currentJurusan = null;

                $sheetTitle = trim($sheet->getTitle());
                if (preg_match('/^(X|XI|XII)\b/i', $sheetTitle)) {
                    $candJurusan = $determineJurusan($sheetTitle);
                    if ($candJurusan !== '') {
                        $currentClass = $sheetTitle;
                        $currentJurusan = $candJurusan;
                    }
                }

                foreach ($rows as $row) {
                    $rowValues = array_values(array_filter(array_map('trim', array_map('strval', $row)), fn($v) => $v !== ''));
                    $rowText = implode(' ', $rowValues);

                    if (preg_match('/\bKELAS\s*:\s*([A-Za-z0-9\s\-_]+)/i', $rowText, $matches)
                        || preg_match('/^\s*KELAS\b[\s:]*([A-Za-z0-9\s\-_]+)/i', $rowText, $matches)) {
                        $rawClass = trim($matches[1]);
                        $rawClass = trim($rawClass, " \t\n\r\0\x0B,:.-");
                        $rawClass = preg_replace('/\bTAHUN\b.*$/i', '', $rawClass);
                        $rawClass = trim($rawClass);
                        if ($rawClass !== '') {
                            $currentClass = $rawClass;
                            $currentJurusan = $determineJurusan($currentClass);
                            continue;
                        }
                    }

                    if (! $currentClass || ! $currentJurusan) {
                        continue;
                    }

                    if (preg_match('/\b(DAFTAR HADIR|TAHUN PELAJARAN|MATA PELAJARAN|GURU MATA|JUMLAH SISWA|PERTEMUAN KE|Bantul|Guru|NIP|NOMOR)\b/i', $rowText)) {
                        continue;
                    }

                    $num = isset($row[0]) ? trim((string)$row[0]) : '';
                    $nis = isset($row[1]) ? trim((string)$row[1]) : '';
                    $name = isset($row[2]) ? trim((string)$row[2]) : '';

                    $cleanName = null;
                    $cleanNis = null;

                    if (is_numeric($num) && (int)$num >= 1 && (int)$num <= 100) {
                        $cleanNis = preg_match('/\b(\d{4,12})\b/', $nis, $m) ? $m[1] : null;
                        if ($name !== '' && !is_numeric($name) && !preg_match('/^(L|P|Urt|NIS|NOMOR)$/i', $name)) {
                            $cleanName = trim($name, " \t\n\r\0\x0B*");
                        } elseif ($cleanNis === null && $nis !== '' && !is_numeric($nis) && !preg_match('/^(L|P|Urt|NIS|NOMOR)$/i', $nis)) {
                            $cleanName = trim($nis, " \t\n\r\0\x0B*");
                        } else {
                            for ($c = 2; $c < count($row); $c++) {
                                $val = trim((string)$row[$c]);
                                if (strlen($val) >= 3 && !is_numeric($val) && !preg_match('/^(L|P|S|I|A|Urt|NIS|NOMOR)$/i', $val)) {
                                    $cleanName = trim($val, " \t\n\r\0\x0B*");
                                    break;
                                }
                            }
                        }
                    } elseif (preg_match('/\b(\d{4,12})\b/', $nis, $m)) {
                        $cleanNis = $m[1];
                        if ($name !== '' && !is_numeric($name)) {
                            $cleanName = trim($name, " \t\n\r\0\x0B*");
                        }
                    }

                    if ($cleanName !== null && $cleanName !== '') {
                        $studentRows[] = [
                            'name' => $cleanName,
                            'nis' => $cleanNis,
                            'kelas' => $currentClass,
                            'jurusan' => $currentJurusan,
                            'alamat' => 'Bantul',
                            'phone' => null,
                            'email' => null,
                        ];
                    }
                }
            }

            if ($studentRows === []) {
                throw ValidationException::withMessages([
                    'file' => 'Tidak ditemukan data siswa atau kelas yang valid dalam file Excel. Pastikan format tabel memiliki header KELAS atau menggunakan template standar.',
                ]);
            }
        }

        DB::transaction(function () use ($studentRows) {
            foreach ($studentRows as $student) {
                if (! empty($student['nis'])) {
                    $existing = DB::table('siswas')->where('nis', $student['nis'])->first();
                    if ($existing) {
                        DB::table('siswas')->where('id', $existing->id)->update([
                            'name' => $student['name'],
                            'kelas' => $student['kelas'],
                            'jurusan' => $student['jurusan'],
                            'alamat' => $student['alamat'],
                            'phone' => $student['phone'] ?? $existing->phone,
                            'email' => $student['email'] ?? $existing->email,
                            'updated_at' => now(),
                        ]);
                        continue;
                    }
                }

                DB::table('siswas')->insert([
                    ...$student,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        if (is_callable($syncSchoolClasses)) {
            $syncSchoolClasses();
        }

        return redirect()->route('waka.siswa')
            ->with('success', count($studentRows).' data siswa berhasil diimpor dari Excel.');
    })->name('waka.siswa.import');

    Route::post('/waka/data-siswa', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kelas' => ['required', 'string', 'max:255'],
            'jurusan' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:2000'],
            'nis' => ['nullable', 'string', 'max:30', 'unique:siswas,nis'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'max:255'],
        ]);
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email'])) ?: null;
        }
        DB::table('siswas')->insert([...$data, 'created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('waka.siswa')->with('success', 'Data siswa berhasil ditambahkan.');
    })->name('waka.siswa.store');

    Route::patch('/waka/data-siswa/{id}', function (Request $request, int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        abort_if(! DB::table('siswas')->where('id', $id)->exists(), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kelas' => ['required', 'string', 'max:255'],
            'jurusan' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:2000'],
            'nis' => ['nullable', 'string', 'max:30', Rule::unique('siswas', 'nis')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'max:255'],
        ]);
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email'])) ?: null;
        }
        DB::table('siswas')->where('id', $id)->update([...$data, 'updated_at' => now()]);
        if (array_key_exists('email', $data)) {
            DB::table('users')->where('student_id', $id)->where('role_id', 3)->update(['email' => $data['email']]);
        }

        return redirect()->route('waka.siswa')->with('success', 'Data siswa berhasil diperbarui.');
    })->name('waka.siswa.update');

    Route::delete('/waka/data-siswa/hapus-per-kelas', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $validated = $request->validate([
            'kelas' => ['required', 'string', 'max:255'],
            'exclude_ids' => ['sometimes', 'nullable', 'array'],
            'exclude_ids.*' => ['integer', 'exists:siswas,id'],
            'force' => ['sometimes', 'boolean'],
        ]);

        $kelas = trim($validated['kelas']);
        $force = (bool) ($request->input('force', 0));
        $excludeIds = collect($request->input('exclude_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        $allStudents = DB::table('siswas')->where('kelas', $kelas)->get(['id', 'name']);
        if ($allStudents->isEmpty()) {
            return back()->withErrors(['kelas' => "Tidak ditemukan siswa pada kelas '{$kelas}'."]);
        }

        $studentsToDelete = $allStudents->reject(fn ($s) => in_array($s->id, $excludeIds, true));
        $studentsToKeep = $allStudents->filter(fn ($s) => in_array($s->id, $excludeIds, true));

        if ($studentsToDelete->isEmpty()) {
            return back()->withErrors(['kelas' => "Seluruh siswa di kelas '{$kelas}' dicentang untuk dipertahankan. Tidak ada siswa yang dihapus."]);
        }

        $studentIdsToDelete = $studentsToDelete->pluck('id')->all();
        $deleteCount = count($studentIdsToDelete);
        $keepCount = $studentsToKeep->count();

        $linkedUserIds = DB::table('users')->whereIn('student_id', $studentIdsToDelete)->pluck('id');
        $linkedAttendanceCount = DB::table('attendances')->whereIn('student_id', $studentIdsToDelete)->count();
        $linkedLeaveCount = DB::table('leave_requests')->whereIn('student_id', $studentIdsToDelete)->count();

        $hasDependencies = $linkedUserIds->isNotEmpty() || $linkedAttendanceCount > 0 || $linkedLeaveCount > 0;

        if ($hasDependencies && ! $force) {
            $depDetails = [];
            if ($linkedUserIds->isNotEmpty()) {
                $depDetails[] = $linkedUserIds->count() . ' akun siswa';
            }
            if ($linkedAttendanceCount > 0) {
                $depDetails[] = $linkedAttendanceCount . ' data presensi';
            }
            if ($linkedLeaveCount > 0) {
                $depDetails[] = $linkedLeaveCount . ' pengajuan izin';
            }
            $detailsStr = implode(', ', $depDetails);

            return back()->withErrors([
                'kelas' => "Sebagian siswa yang akan dihapus di kelas {$kelas} masih memiliki keterkaitan data ({$detailsStr}). Silakan centang 'Hapus beserta data terkait' jika ingin menghapus seluruhnya."
            ])->withInput();
        }

        DB::transaction(function () use ($studentIdsToDelete, $linkedUserIds) {
            if ($linkedUserIds->isNotEmpty()) {
                DB::table('users')->whereIn('id', $linkedUserIds)->delete();
            }
            DB::table('leave_requests')->whereIn('student_id', $studentIdsToDelete)->delete();
            DB::table('attendances')->whereIn('student_id', $studentIdsToDelete)->delete();
            DB::table('siswas')->whereIn('id', $studentIdsToDelete)->delete();
        });

        $message = "Semua data siswa di kelas {$kelas} ({$deleteCount} siswa) berhasil dihapus.";
        if ($keepCount > 0) {
            $message = "Sebanyak {$deleteCount} data siswa di kelas {$kelas} berhasil dihapus. ({$keepCount} siswa yang tidak lulus/naik kelas dipertahankan).";
        }
        if ($hasDependencies && $force) {
            $message .= " Data akun, presensi, dan izin untuk siswa yang dihapus telah dibersihkan.";
        }

        return redirect()->route('waka.siswa')->with('success', $message);
    })->name('waka.siswa.destroy_class');

    Route::delete('/waka/data-siswa/{id}', function (int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        abort_if(! DB::table('siswas')->where('id', $id)->exists(), 404);
        $isLinkedToAccount = DB::table('users')->where('student_id', $id)->exists();
        $hasAttendance = DB::table('attendances')->where('student_id', $id)->exists();
        $hasLeaveRequests = DB::table('leave_requests')->where('student_id', $id)->exists();

        if ($isLinkedToAccount || $hasAttendance || $hasLeaveRequests) {
            return back()->withErrors(['student' => 'Data siswa masih digunakan oleh akun, presensi, atau pengajuan izin sehingga tidak dapat dihapus.']);
        }

        DB::table('siswas')->where('id', $id)->delete();

        return redirect()->route('waka.siswa')->with('success', 'Data siswa berhasil dihapus.');
    })->whereNumber('id')->name('waka.siswa.destroy');

    $downloadTeacherWorkbook = function (array $rows, string $filename) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Nama Lengkap', 'Username', 'NIP', 'Nomor HP', 'Email Pemulihan', 'Password', 'Wali Kelas', 'Guru BK'], null, 'A1');
        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2');
        }
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    };

    Route::get('/waka/data-guru/template', function () use ($downloadTeacherWorkbook) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        return $downloadTeacherWorkbook([], 'template_import_guru.xlsx');
    })->name('waka.guru.template');

    Route::get('/waka/data-guru/contoh', function () use ($downloadTeacherWorkbook) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        return $downloadTeacherWorkbook([
            ['Dra. Siti Aminah, M.Pd.', 'siti.aminah', '197505122000032001', '081234567001', 'siti.aminah@sekolah.sch.id', 'GuruPass123', 'X RPL 1', ''],
            ['Bambang Wijaya, S.Kom.', 'bambang.w', '198203152008011002', '081234567002', 'bambang.wijaya@sekolah.sch.id', 'GuruPass123', 'XI TKJ 1', ''],
            ['Candra Rahadian, S.Pd.', 'candra.r', '198506102010011003', '081234567003', 'candra.rahadian@sekolah.sch.id', 'GuruPass123', '', 'X AK 1, X AK 2, X AK 3'],
            ['Ahmad Fauzi, M.T.', 'ahmad.fauzi', '198011252006041004', '081234567004', 'ahmad.fauzi@sekolah.sch.id', 'GuruPass123', 'XII DKV 1', ''],
            ['Ryawidyaningtyas, S.Pd.', 'rya.widya', '199201102016032005', '081234567005', 'rya.widyaningtyas@sekolah.sch.id', 'GuruPass123', '', 'X RPL 1, X RPL 2'],
        ], 'contoh_import_guru_5_data.xlsx');
    })->name('waka.guru.example');

    Route::post('/waka/data-guru/import', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,pdf', 'max:20480'],
        ]);

        @set_time_limit(180);

        $uploadedFile = $request->file('file');
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $sheetRows = [];

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            try {
                $spreadsheet = IOFactory::load($uploadedFile->getRealPath());
                $sheetRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            } catch (\Throwable $exception) {
                throw ValidationException::withMessages([
                    'file' => 'File Excel tidak dapat dibaca. Gunakan file .xlsx atau .xls yang valid.',
                ]);
            }
        } elseif ($extension === 'pdf') {
            $filePath = $uploadedFile->getRealPath();
            $pdfText = '';
            $pdftotext = 'C:\\laragon\\bin\\git\\mingw64\\bin\\pdftotext.exe';
            if (file_exists($pdftotext)) {
                $tempOutput = tempnam(sys_get_temp_dir(), 'pdf_txt_');
                $cmd = escapeshellarg($pdftotext) . ' -layout ' . escapeshellarg($filePath) . ' ' . escapeshellarg($tempOutput);
                @exec($cmd, $out, $ret);
                if (file_exists($tempOutput)) {
                    $pdfText = (string) @file_get_contents($tempOutput);
                    @unlink($tempOutput);
                }
            }
            if (trim($pdfText) === '') {
                // Fallback stream decompression
                $content = (string) @file_get_contents($filePath);
                if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $matches)) {
                    foreach ($matches[1] as $stream) {
                        $decompressed = @gzuncompress($stream);
                        $targetStream = $decompressed !== false ? $decompressed : $stream;
                        if (preg_match_all('/\((.*?)\)\s*T[jJ]/s', $targetStream, $textMatches)) {
                            $pdfText .= implode(' ', $textMatches[1]) . "\n";
                        } elseif (preg_match_all('/\[(.*?)\]\s*TJ/s', $targetStream, $textMatches)) {
                            foreach ($textMatches[1] as $arrayStr) {
                                if (preg_match_all('/\((.*?)\)/s', $arrayStr, $itemMatches)) {
                                    $pdfText .= implode('', $itemMatches[1]) . ' ';
                                }
                            }
                            $pdfText .= "\n";
                        }
                    }
                }
            }

            if (trim($pdfText) === '') {
                throw ValidationException::withMessages([
                    'file' => 'Teks dari file PDF tidak dapat dibaca atau file berupa gambar tanpa teks. Harap gunakan file PDF teks atau file Excel.',
                ]);
            }

            $isFetOrList = false;
            if (stripos($pdfText, 'Daftar Isi') !== false || stripos($pdfText, 'FET') !== false || preg_match('/"?\d{1,3}[.,\s]+[A-Za-z]/', $pdfText)) {
                if (!preg_match('/\b(Nama Lengkap|Username|NIP)\s*\|/i', $pdfText) && !preg_match('/\bNama Lengkap\s*\t/i', $pdfText)) {
                    $isFetOrList = true;
                }
            }

            if ($isFetOrList) {
                $pages = explode("\x0C", $pdfText);
                $extractedTeachers = [];
                $seen = [];
                $inToc = false;

                foreach ($pages as $page) {
                    $lines = explode("\n", $page);
                    $isSchedule = false;
                    foreach ($lines as $l) {
                        if (preg_match('/\b(Senin|Selasa|Rabu|Kamis|Jumat)\b/i', $l) && preg_match('/\b(07:|08:|09:)/', $page)) {
                            $isSchedule = true;
                            break;
                        }
                    }

                    if ($isSchedule) {
                        break;
                    }

                    foreach ($lines as $line) {
                        $trimmed = trim($line, " \t\n\r\0\x0B\x0C\f");
                        if (stripos($trimmed, 'Daftar Isi') !== false) {
                            $inToc = true;
                            continue;
                        }

                        if ($inToc) {
                            if (preg_match('/^"??(\d{1,3})[.,\s]*([A-Za-z][^"\r\n]+?)"*$/', $trimmed, $m)) {
                                $num = (int)$m[1];
                                $rawName = trim($m[2], " \t\n\r\0\x0B\"'");
                                $name = trim(preg_replace('/^"|"$|^\"|\"$/', '', $rawName));
                                $name = trim($name, " \t\n\r\0\x0B\"'.,");

                                if ($num >= 1 && $num <= 200 && strlen($name) >= 3) {
                                    if (!preg_match('/^(Senin|Selasa|Rabu|Kamis|Jumat|SMK|Jadwal|Daftar|Komentar|Nama)/i', $name)) {
                                        $norm = mb_strtolower($name);
                                        if (!isset($seen[$norm])) {
                                            $seen[$norm] = true;
                                            $extractedTeachers[] = [$name, '', '', '', '', 'password123', ''];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (empty($extractedTeachers)) {
                    foreach ($pages as $page) {
                        $lines = array_map('trim', explode("\n", $page));
                        foreach ($lines as $line) {
                            if (preg_match('/^"?(\d{1,3})[.,\s]*([A-Za-z][A-Za-z\s.,\'\-\(\)\/0-9]*?)"*$/', $line, $m)) {
                                $num = (int)$m[1];
                                $name = trim($m[2], " \t\n\r\0\x0B\"'.,");
                                if ($num >= 1 && $num <= 200 && strlen($name) >= 3) {
                                    if (!preg_match('/^(Senin|Selasa|Rabu|Kamis|Jumat|SMK|Jadwal|Daftar)/i', $name)) {
                                        $norm = mb_strtolower($name);
                                        if (!isset($seen[$norm])) {
                                            $seen[$norm] = true;
                                            $extractedTeachers[] = [$name, '', '', '', '', 'password123', ''];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $sheetRows = $extractedTeachers;
            } else {
                $pdfText = str_replace(["\r\n", "\r", "\f", "\x0C"], "\n", $pdfText);
                $lines = preg_split('/\n+/', trim($pdfText));
                foreach ($lines as $line) {
                    $line = trim($line, " \t\n\r\0\x0B\x0C\f");
                    if ($line === '' || str_starts_with($line, '---') || str_starts_with($line, '===') || preg_match('/^(halaman|page|\d+)\b/i', $line)) {
                        continue;
                    }
                    if (str_contains($line, '|')) {
                        $cols = array_map('trim', explode('|', $line));
                    } elseif (str_contains($line, "\t")) {
                        $cols = array_map('trim', explode("\t", $line));
                    } elseif (str_contains($line, ';')) {
                        $cols = array_map('trim', explode(';', $line));
                    } elseif (str_contains($line, ',')) {
                        $cols = array_map('trim', str_getcsv($line));
                    } else {
                        $cols = array_map('trim', preg_split('/\s{2,}/', $line));
                    }
                    if (count($cols) > 1 && $cols[0] === '') {
                        array_shift($cols);
                    }
                    if (count($cols) > 0 && end($cols) === '') {
                        array_pop($cols);
                    }
                    if ($cols !== []) {
                        $sheetRows[] = $cols;
                    }
                }
            }
        }

        if (count($sheetRows) > 1001) {
            throw ValidationException::withMessages([
                'file' => 'File maksimal berisi 1.000 baris data guru.',
            ]);
        }

        $headerAliases = [
            'nama lengkap' => 'fullname',
            'nama guru' => 'fullname',
            'nama' => 'fullname',
            'fullname' => 'fullname',
            'name' => 'fullname',
            'username' => 'username',
            'user' => 'username',
            'nip' => 'nip',
            'nomor hp' => 'phone',
            'no hp' => 'phone',
            'no_hp' => 'phone',
            'telepon' => 'phone',
            'phone' => 'phone',
            'hp' => 'phone',
            'email pemulihan' => 'email',
            'email' => 'email',
            'password' => 'password',
            'kata sandi' => 'password',
            'pass' => 'password',
            'wali kelas' => 'wali_kelas',
            'kelas wali' => 'wali_kelas',
            'wali' => 'wali_kelas',
            'kelas' => 'wali_kelas',
            'guru bk' => 'guru_bk',
            'kelas bk' => 'guru_bk',
            'bk' => 'guru_bk',
            'bimbingan konseling' => 'guru_bk',
            'bimbingan' => 'guru_bk',
        ];

        $columnIndexes = [];
        $dataRows = $sheetRows;
        foreach ($sheetRows as $idx => $row) {
            $matched = [];
            foreach ($row as $colIdx => $cell) {
                $norm = mb_strtolower(trim((string) $cell));
                $norm = ltrim($norm, "\xEF\xBB\xBF");
                if (isset($headerAliases[$norm])) {
                    $matched[$headerAliases[$norm]] = $colIdx;
                }
            }
            if (isset($matched['fullname']) || (isset($matched['username']) && isset($matched['nip']))) {
                $columnIndexes = $matched;
                $dataRows = array_slice($sheetRows, $idx + 1);
                break;
            }
        }

        // Default order fallback if no header matched
        if ($columnIndexes === []) {
            $defaultFields = ['fullname', 'username', 'nip', 'phone', 'email', 'password', 'wali_kelas'];
            foreach ($defaultFields as $k => $f) {
                $columnIndexes[$f] = $k;
            }
        }

        $allSchoolClasses = DB::table('school_classes')->get()->keyBy(fn ($c) => mb_strtolower(trim($c->name)));
        $teacherRows = [];
        $rowErrors = [];
        $seenUsernames = [];
        $seenNips = [];
        $seenEmails = [];

        foreach ($dataRows as $rowIndex => $row) {
            $lineNumber = $rowIndex + 2;
            $isEmptyRow = collect($row)->every(fn ($val) => trim((string) $val) === '');
            if ($isEmptyRow) {
                continue;
            }

            $fullname = isset($columnIndexes['fullname']) && isset($row[$columnIndexes['fullname']]) ? trim((string) $row[$columnIndexes['fullname']], " \t\n\r\0\x0B\x0C\f") : '';
            if (preg_replace('/[\s\x00-\x1F\x7F]+/', '', $fullname) === '') {
                continue;
            }
            if (mb_strtolower($fullname) === 'nama lengkap' || mb_strtolower($fullname) === 'nama') {
                continue;
            }

            $rawUsername = isset($columnIndexes['username']) && isset($row[$columnIndexes['username']]) ? trim((string) $row[$columnIndexes['username']]) : '';
            $rawNip = isset($columnIndexes['nip']) && isset($row[$columnIndexes['nip']]) ? trim((string) $row[$columnIndexes['nip']]) : '';
            $rawPhone = isset($columnIndexes['phone']) && isset($row[$columnIndexes['phone']]) ? trim((string) $row[$columnIndexes['phone']]) : '';
            $rawEmail = isset($columnIndexes['email']) && isset($row[$columnIndexes['email']]) ? strtolower(trim((string) $row[$columnIndexes['email']])) : '';
            $rawPassword = isset($columnIndexes['password']) && isset($row[$columnIndexes['password']]) ? trim((string) $row[$columnIndexes['password']]) : '';
            $rawWaliKelas = isset($columnIndexes['wali_kelas']) && isset($row[$columnIndexes['wali_kelas']]) ? trim((string) $row[$columnIndexes['wali_kelas']]) : '';
            $rawGuruBk = isset($columnIndexes['guru_bk']) && isset($row[$columnIndexes['guru_bk']]) ? trim((string) $row[$columnIndexes['guru_bk']]) : '';

            // Generate username if not provided
            if ($rawUsername === '') {
                $baseUser = Str::slug($fullname, '.');
                if ($baseUser === '') {
                    $baseUser = 'guru.' . Str::random(5);
                }
                $finalUser = $baseUser;
                $counter = 1;
                while (DB::table('users')->where('username', $finalUser)->exists() || isset($seenUsernames[$finalUser])) {
                    $finalUser = $baseUser . $counter++;
                }
                $username = $finalUser;
            } else {
                $username = $rawUsername;
                if (DB::table('users')->where('username', $username)->exists() || isset($seenUsernames[$username])) {
                    $rowErrors[] = "Baris {$lineNumber}: Username '{$username}' sudah digunakan.";
                    continue;
                }
            }

            $nip = $rawNip !== '' ? $rawNip : null;
            if ($nip !== null) {
                if (DB::table('users')->where('nip', $nip)->exists() || isset($seenNips[$nip])) {
                    $rowErrors[] = "Baris {$lineNumber}: NIP '{$nip}' sudah digunakan.";
                    continue;
                }
            }

            $email = $rawEmail !== '' ? $rawEmail : null;
            if ($email !== null) {
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = "Baris {$lineNumber}: Format email '{$email}' tidak valid.";
                    continue;
                }
                if (DB::table('users')->where('email', $email)->exists() || isset($seenEmails[$email])) {
                    $rowErrors[] = "Baris {$lineNumber}: Email '{$email}' sudah digunakan.";
                    continue;
                }
            }

            $password = $rawPassword !== '' ? $rawPassword : 'password123';
            if (strlen($password) < 8) {
                $rowErrors[] = "Baris {$lineNumber}: Password minimal 8 karakter.";
                continue;
            }

            $phone = $rawPhone !== '' ? $rawPhone : null;

            // Resolve Wali Kelas classes
            $assignedClassIds = [];
            if ($rawWaliKelas !== '') {
                $classNames = array_map('trim', preg_split('/[,;]+/', $rawWaliKelas));
                foreach ($classNames as $cName) {
                    $normCName = mb_strtolower($cName);
                    if (isset($allSchoolClasses[$normCName])) {
                        $assignedClassIds[] = $allSchoolClasses[$normCName]->id;
                    }
                }
            }

            // Resolve Guru BK classes
            $assignedBkClassIds = [];
            if ($rawGuruBk !== '') {
                $classNames = array_map('trim', preg_split('/[,;]+/', $rawGuruBk));
                foreach ($classNames as $cName) {
                    $normCName = mb_strtolower($cName);
                    if (isset($allSchoolClasses[$normCName])) {
                        $assignedBkClassIds[] = $allSchoolClasses[$normCName]->id;
                    }
                }
            }

            $seenUsernames[$username] = true;
            if ($nip !== null) {
                $seenNips[$nip] = true;
            }
            if ($email !== null) {
                $seenEmails[$email] = true;
            }

            $teacherRows[] = [
                'fullname' => $fullname,
                'username' => $username,
                'nip' => $nip,
                'phone' => $phone,
                'email' => $email,
                'password' => $password,
                'class_ids' => $assignedClassIds,
                'bk_class_ids' => $assignedBkClassIds,
            ];
        }

        if ($teacherRows === [] && $rowErrors === []) {
            $rowErrors[] = 'File tidak berisi baris data guru yang valid.';
        }

        if ($rowErrors !== []) {
            throw ValidationException::withMessages(['file' => $rowErrors]);
        }

        DB::transaction(function () use ($teacherRows) {
            foreach ($teacherRows as $t) {
                $existing = DB::table('users')
                    ->whereIn('role_id', [2, 4])
                    ->where('fullname', $t['fullname'])
                    ->first();

                $roleId = ! empty($t['bk_class_ids']) ? 4 : 2;
                if ($existing) {
                    $updates = [
                        'phone' => $t['phone'] ?? $existing->phone,
                        'email' => $t['email'] ?? $existing->email,
                        'nip' => $t['nip'] ?? $existing->nip,
                        'updated_at' => now(),
                    ];
                    if (! empty($t['bk_class_ids'])) {
                        $updates['role_id'] = 4;
                    }
                    DB::table('users')->where('id', $existing->id)->update($updates);
                    $userId = $existing->id;
                } else {
                    $userId = DB::table('users')->insertGetId([
                        'fullname' => $t['fullname'],
                        'username' => $t['username'],
                        'email' => $t['email'],
                        'nip' => $t['nip'],
                        'phone' => $t['phone'],
                        'password' => Hash::make($t['password']),
                        'role_id' => $roleId,
                        'is_active' => 1,
                        'created_at' => now(),
                    ]);
                }

                if (! empty($t['class_ids'])) {
                    foreach ($t['class_ids'] as $classId) {
                        DB::table('user_school_classes')->insertOrIgnore([
                            'user_id' => $userId,
                            'school_class_id' => $classId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if (! empty($t['bk_class_ids'])) {
                    foreach ($t['bk_class_ids'] as $classId) {
                        DB::table('user_bk_classes')->insertOrIgnore([
                            'user_id' => $userId,
                            'school_class_id' => $classId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });

        $fileTypeName = $extension === 'pdf' ? 'PDF' : 'Excel';
        return redirect()->route('waka.guru')
            ->with('success', count($teacherRows) . " data guru berhasil diimpor dari {$fileTypeName}.");
    })->name('waka.guru.import');

    Route::get('/waka/data-guru', function (Request $request) use ($syncSchoolClasses) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $syncSchoolClasses();
        $search = trim((string) $request->query('q', ''));
        $query = DB::table('users')->whereIn('role_id', [2, 4])->orderBy('fullname');
        if ($search !== '') {
            $matchingWaliUserIds = DB::table('user_school_classes')
                ->join('school_classes', 'user_school_classes.school_class_id', '=', 'school_classes.id')
                ->where('school_classes.name', 'like', "%{$search}%")
                ->pluck('user_school_classes.user_id');

            $matchingBkUserIds = DB::table('user_bk_classes')
                ->join('school_classes', 'user_bk_classes.school_class_id', '=', 'school_classes.id')
                ->where('school_classes.name', 'like', "%{$search}%")
                ->pluck('user_bk_classes.user_id');

            $query->where(function ($query) use ($search, $matchingWaliUserIds, $matchingBkUserIds) {
                $query->where('fullname', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('class_id', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereIn('id', $matchingWaliUserIds)
                    ->orWhereIn('id', $matchingBkUserIds);
            });
        }
        $gurus = $query->get();
        $teacherIds = $gurus->pluck('id');
        $subjectsByTeacher = $teacherIds->isEmpty()
            ? collect()
            : DB::table('schedules')
                ->whereIn('teacher_id', $teacherIds)
                ->whereNotNull('subject')
                ->where('subject', '!=', '')
                ->select('teacher_id', 'subject')
                ->distinct()
                ->orderBy('subject')
                ->get()
                ->groupBy('teacher_id');

        foreach ($gurus as $guru) {
            $subjects = ($subjectsByTeacher->get($guru->id, collect()))->pluck('subject')->all();
            $guru->subjects = implode(', ', $subjects);
        }
        $schoolClasses = DB::table('school_classes')->orderBy('name')->get();
        $teacherClassIds = DB::table('user_school_classes')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($assignments) => $assignments->pluck('school_class_id')->map(fn ($id) => (int) $id)->all());
        $teacherBkClassIds = DB::table('user_bk_classes')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($assignments) => $assignments->pluck('school_class_id')->map(fn ($id) => (int) $id)->all());

        return view('waka.data_guru', [
            'user' => Auth::user(),
            'gurus' => $gurus,
            'search' => $search,
            'schoolClasses' => $schoolClasses,
            'teacherClassIds' => $teacherClassIds,
            'teacherBkClassIds' => $teacherBkClassIds,
        ]);
    })->name('waka.guru');

    Route::post('/waka/data-guru', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        if ($request->filled('email')) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'class_id' => ['nullable', 'integer', 'min:1'],
            'class_ids' => ['sometimes', 'nullable', 'array'],
            'class_ids.*' => ['required', 'integer', 'distinct', 'exists:school_classes,id'],
            'bk_class_ids' => ['sometimes', 'nullable', 'array'],
            'bk_class_ids.*' => ['required', 'integer', 'distinct', 'exists:school_classes,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:30', 'unique:users,nip'],
        ]);
        DB::transaction(function () use ($data) {
            $thisBkClassIds = $data['bk_class_ids'] ?? [];
            $teacherId = DB::table('users')->insertGetId([
                'fullname' => $data['fullname'],
                'username' => $data['username'],
                'email' => isset($data['email']) ? strtolower($data['email']) : null,
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'nip' => $data['nip'] ?? null,
                'role_id' => ! empty($thisBkClassIds) ? 4 : 2,
                'class_id' => $data['class_id'] ?? null,
                'created_at' => now(),
            ]);
            $thisClassIds = $data['class_ids'] ?? [];
            if ($thisClassIds !== []) {
                $now = now();
                DB::table('user_school_classes')->insert(
                    collect($thisClassIds)->map(fn ($classId) => [
                        'user_id' => $teacherId,
                        'school_class_id' => $classId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
            if ($thisBkClassIds !== []) {
                $now = now();
                DB::table('user_bk_classes')->insert(
                    collect($thisBkClassIds)->map(fn ($classId) => [
                        'user_id' => $teacherId,
                        'school_class_id' => $classId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
        });

        return redirect()->route('waka.guru')->with('success', 'Data guru berhasil ditambahkan.');
    })->name('waka.guru.store');

    Route::patch('/waka/data-guru/{id}', function (Request $request, int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $guru = DB::table('users')->where('id', $id)->whereIn('role_id', [2, 4])->first();
        abort_if(! $guru, 404);
        if ($request->filled('email')) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['nullable', 'string', 'min:8'],
            'class_id' => ['nullable', 'integer', 'min:1'],
            'class_ids' => ['sometimes', 'nullable', 'array'],
            'class_ids.*' => ['required', 'integer', 'distinct', 'exists:school_classes,id'],
            'bk_class_ids' => ['sometimes', 'nullable', 'array'],
            'bk_class_ids.*' => ['required', 'integer', 'distinct', 'exists:school_classes,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('users', 'nip')->ignore($id)],
        ]);
        $update = [
            'fullname' => $data['fullname'],
            'username' => $data['username'],
            'email' => isset($data['email']) ? strtolower($data['email']) : null,
            'class_id' => $data['class_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'nip' => $data['nip'] ?? null,
        ];
        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }
        $thisBkClassIds = $data['bk_class_ids'] ?? [];
        if (! empty($thisBkClassIds)) {
            $update['role_id'] = 4;
        }
        DB::transaction(function () use ($id, $update, $data, $thisBkClassIds) {
            DB::table('users')->where('id', $id)->update($update);
            DB::table('user_school_classes')->where('user_id', $id)->delete();
            $thisClassIds = $data['class_ids'] ?? [];
            if ($thisClassIds !== []) {
                $now = now();
                DB::table('user_school_classes')->insert(
                    collect($thisClassIds)->map(fn ($classId) => [
                        'user_id' => $id,
                        'school_class_id' => $classId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
            DB::table('user_bk_classes')->where('user_id', $id)->delete();
            if ($thisBkClassIds !== []) {
                $now = now();
                DB::table('user_bk_classes')->insert(
                    collect($thisBkClassIds)->map(fn ($classId) => [
                        'user_id' => $id,
                        'school_class_id' => $classId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
        });

        return redirect()->route('waka.guru')->with('success', 'Data guru berhasil diperbarui.');
    })->name('waka.guru.update');

    Route::delete('/waka/data-guru/{id}', function (int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $guru = DB::table('users')->where('id', $id)->whereIn('role_id', [2, 4])->first();
        abort_if(! $guru, 404);
        if ((int) $id === (int) Auth::user()->getKey()) {
            return back()->withErrors(['guru' => 'Akun yang sedang digunakan tidak dapat dihapus.']);
        }
        if (DB::table('schedules')->where('teacher_id', $id)->exists()) {
            return back()->withErrors(['guru' => 'Guru masih memiliki jadwal mengajar sehingga tidak dapat dihapus.']);
        }
        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('waka.guru')->with('success', 'Data guru berhasil dihapus.');
    })->name('waka.guru.destroy');

    // Waka Data Orang Tua
    Route::get('/waka/data-orang-tua', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $search = trim((string) $request->input('q', ''));

        $query = DB::table('users')
            ->leftJoin('siswas', 'users.student_id', '=', 'siswas.id')
            ->where('users.role_id', 6)
            ->select(
                'users.id',
                'users.fullname',
                'users.username',
                'users.phone',
                'users.email',
                'users.student_id',
                'users.is_active',
                'users.must_change_password',
                'siswas.name as student_name',
                'siswas.kelas as student_class'
            )
            ->orderBy('users.fullname');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.fullname', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('users.phone', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('siswas.name', 'like', "%{$search}%")
                    ->orWhere('siswas.kelas', 'like', "%{$search}%");
            });
        }

        $ortus = $query->get();
        $siswas = DB::table('siswas')->select('id', 'name', 'kelas')->orderBy('kelas')->orderBy('name')->get();

        return view('waka.data_orang_tua', [
            'user' => Auth::user(),
            'ortus' => $ortus,
            'siswas' => $siswas,
            'search' => $search,
        ]);
    })->name('waka.ortu');

    Route::post('/waka/data-orang-tua', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        if ($request->filled('email')) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'student_id' => ['required', 'integer', 'exists:siswas,id'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
        ], [
            'fullname.required' => 'Nama lengkap orang tua wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan.',
            'student_id.required' => 'Silakan pilih siswa yang terhubung.',
            'student_id.exists' => 'Data siswa yang dipilih tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'email.unique' => 'Email pemulihan ini sudah digunakan.',
        ]);

        DB::table('users')->insert([
            'fullname' => $data['fullname'],
            'username' => $data['username'],
            'student_id' => $data['student_id'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'email' => ! empty($data['email']) ? strtolower($data['email']) : null,
            'role_id' => 6,
            'is_active' => true,
            'created_at' => now(),
        ]);

        return redirect()->route('waka.ortu')->with('success', 'Data akun orang tua berhasil ditambahkan.');
    })->name('waka.ortu.store');

    Route::patch('/waka/data-orang-tua/{id}', function (Request $request, int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $ortu = DB::table('users')->where('id', $id)->where('role_id', 6)->first();
        abort_if(! $ortu, 404);

        if ($request->filled('email')) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($id)],
            'student_id' => ['required', 'integer', 'exists:siswas,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'fullname.required' => 'Nama lengkap orang tua wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan.',
            'student_id.required' => 'Silakan pilih siswa yang terhubung.',
            'student_id.exists' => 'Data siswa yang dipilih tidak valid.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'email.unique' => 'Email pemulihan ini sudah digunakan.',
        ]);

        $updateData = [
            'fullname' => $data['fullname'],
            'username' => $data['username'],
            'student_id' => $data['student_id'],
            'phone' => $data['phone'] ?? null,
            'email' => ! empty($data['email']) ? strtolower($data['email']) : null,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : (bool) $ortu->is_active,
        ];
        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        DB::table('users')->where('id', $id)->update($updateData);

        return redirect()->route('waka.ortu')->with('success', 'Data akun orang tua berhasil diperbarui.');
    })->name('waka.ortu.update');

    Route::delete('/waka/data-orang-tua/{id}', function (int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $ortu = DB::table('users')->where('id', $id)->where('role_id', 6)->first();
        abort_if(! $ortu, 404);

        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('waka.ortu')->with('success', 'Akun orang tua berhasil dihapus.');
    })->name('waka.ortu.destroy');

    Route::post('/waka/data-orang-tua/{id}/reset-password', function (int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $ortu = DB::table('users')->where('id', $id)->where('role_id', 6)->first();
        abort_if(! $ortu, 404);

        $defaultPass = 'password123';
        DB::table('users')->where('id', $id)->update([
            'password' => Hash::make($defaultPass),
            'must_change_password' => true,
            'password_reset_at' => now(),
        ]);

        return redirect()->route('waka.ortu')->with('reset_info', [
            'message' => 'Sandi akun orang tua ' . $ortu->username . ' berhasil direset.',
            'temporary_password' => $defaultPass,
        ]);
    })->name('waka.ortu.reset_password');

    Route::get('/waka/akun', function (Request $request) use ($syncSchoolClasses) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $syncSchoolClasses();
        $search = trim((string) $request->input('q', ''));

        $query = DB::table('users')
            ->leftJoin('siswas', 'users.student_id', '=', 'siswas.id')
            ->select('users.id', 'users.fullname', 'users.username', 'users.email', 'users.role_id', 'users.student_id', 'users.is_active', 'users.must_change_password', 'siswas.name as student_name', 'siswas.kelas as student_class')
            ->orderBy('role_id')
            ->orderBy('fullname');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.fullname', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('siswas.name', 'like', "%{$search}%")
                    ->orWhere('siswas.kelas', 'like', "%{$search}%");
            });
        }

        $accounts = $query->paginate(25)->withQueryString();
        $assignedCounts = DB::table('users')
            ->where('role_id', 3)
            ->whereNotNull('student_id')
            ->select('student_id', DB::raw('count(*) as count'))
            ->groupBy('student_id')
            ->pluck('count', 'student_id');

        $siswas = DB::table('siswas')
            ->select('siswas.id', 'siswas.name', 'siswas.kelas')
            ->orderBy('siswas.name')
            ->get()
            ->map(function ($s) use ($assignedCounts) {
                $count = (int) ($assignedCounts->get($s->id, 0));
                $s->assigned_student_account_id = $count >= 2 ? 1 : 0;
                $s->account_count = $count;
                return $s;
            });
        $schoolClasses = DB::table('school_classes')->orderBy('name')->get();
        $accountClassIds = DB::table('user_school_classes')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($assignments) => $assignments->pluck('school_class_id')->map(fn ($id) => (int) $id)->all());
        $accountBkClassIds = DB::table('user_bk_classes')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($assignments) => $assignments->pluck('school_class_id')->map(fn ($id) => (int) $id)->all());

        return view('waka.accounts', [
            'user' => Auth::user(),
            'accounts' => $accounts,
            'siswas' => $siswas,
            'schoolClasses' => $schoolClasses,
            'accountClassIds' => $accountClassIds,
            'accountBkClassIds' => $accountBkClassIds,
            'search' => $search,
        ]);
    })->name('waka.accounts');

    Route::post('/waka/akun', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        if ($request->filled('email')) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'integer', 'in:1,2,3,4,6'],
        ]);
        $studentData = $request->validate([
            'student_id' => (int) $data['role_id'] === 3
                ? [
                    'required',
                    'integer',
                    'exists:siswas,id',
                    function ($attribute, $value, $fail) use ($data) {
                        $username = strtolower(trim((string) ($data['username'] ?? '')));
                        $isParentAccount = str_ends_with($username, 'p');

                        $existing = DB::table('users')
                            ->where('student_id', $value)
                            ->where('role_id', 3)
                            ->pluck('username');

                        if ($isParentAccount) {
                            if ($existing->contains(fn ($u) => str_ends_with(strtolower($u), 'p'))) {
                                $fail('Akun orang tua untuk siswa ini sudah ada.');
                            }
                        } else {
                            if ($existing->contains(fn ($u) => ! str_ends_with(strtolower($u), 'p'))) {
                                $fail('Akun siswa untuk siswa ini sudah terdaftar.');
                            }
                        }
                    },
                ]
                : ((int) $data['role_id'] === 6
                    ? ['required', 'integer', 'exists:siswas,id']
                    : ['nullable', 'prohibited']),
        ]);
        $classData = $request->validate([
            'class_ids' => in_array((int) $data['role_id'], [2, 4], true)
                ? ['sometimes', 'nullable', 'array']
                : ['nullable', 'prohibited'],
            'class_ids.*' => ['required', 'integer', 'distinct', 'exists:school_classes,id'],
            'bk_class_ids' => in_array((int) $data['role_id'], [2, 4], true)
                ? ['sometimes', 'nullable', 'array']
                : ['nullable', 'prohibited'],
            'bk_class_ids.*' => ['required', 'integer', 'distinct', 'exists:school_classes,id'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['parent_id'] = null;
        $data['class_id'] = null;
        $data['student_id'] = in_array((int) $data['role_id'], [3, 6], true) ? $studentData['student_id'] : null;
        $data['email'] = isset($data['email']) ? strtolower($data['email']) : null;
        $data['created_at'] = now();

        DB::transaction(function () use ($data, $classData) {
            $userId = DB::table('users')->insertGetId($data);
            if (in_array((int) $data['role_id'], [2, 4], true) && ! empty($classData['class_ids'])) {
                $now = now();
                DB::table('user_school_classes')->insert(
                    collect($classData['class_ids'])->map(fn ($classId) => [
                        'user_id' => $userId,
                        'school_class_id' => $classId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
            if (in_array((int) $data['role_id'], [2, 4], true) && ! empty($classData['bk_class_ids'])) {
                $now = now();
                DB::table('user_bk_classes')->insert(
                    collect($classData['bk_class_ids'])->map(fn ($classId) => [
                        'user_id' => $userId,
                        'school_class_id' => $classId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
        });

        return redirect()->route('waka.accounts')->with('success', 'Akun baru berhasil ditambahkan.');
    })->name('waka.accounts.store');

    Route::patch('/waka/akun/{id}', function (Request $request, int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $account = DB::table('users')->where('id', $id)->first();
        abort_if(! $account, 404);
        if ($request->filled('email')) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }

        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'role_id' => ['required', 'integer', 'in:1,2,3,4,6'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ((int) $id === (int) Auth::user()->getKey() && (int) $data['role_id'] !== 1) {
            return back()->withErrors(['role_id' => 'Role akun Waka yang sedang digunakan tidak dapat diubah.'])->withInput();
        }
        if ((int) $id === (int) Auth::user()->getKey() && isset($data['is_active']) && ! $data['is_active']) {
            return back()->withErrors(['is_active' => 'Akun Waka yang sedang digunakan tidak dapat dinonaktifkan.'])->withInput();
        }

        if (in_array((int) $account->role_id, [2, 4], true) && ! in_array((int) $data['role_id'], [2, 4], true) && DB::table('schedules')->where('teacher_id', $id)->exists()) {
            return back()->withErrors(['role_id' => 'Role pengajar (Guru/Kesiswaan) tidak dapat diubah ke Siswa/Orang Tua selama akun masih memiliki jadwal mengajar.'])->withInput();
        }

        $studentData = $request->validate([
            'student_id' => (int) $data['role_id'] === 3
                ? [
                    'required',
                    'integer',
                    'exists:siswas,id',
                    function ($attribute, $value, $fail) use ($data, $id) {
                        $username = strtolower(trim((string) ($data['username'] ?? '')));
                        $isParentAccount = str_ends_with($username, 'p');

                        $existing = DB::table('users')
                            ->where('student_id', $value)
                            ->where('role_id', 3)
                            ->where('id', '!=', $id)
                            ->pluck('username');

                        if ($isParentAccount) {
                            if ($existing->contains(fn ($u) => str_ends_with(strtolower($u), 'p'))) {
                                $fail('Akun orang tua untuk siswa ini sudah ada.');
                            }
                        } else {
                            if ($existing->contains(fn ($u) => ! str_ends_with(strtolower($u), 'p'))) {
                                $fail('Akun siswa untuk siswa ini sudah terdaftar.');
                            }
                        }
                    },
                ]
                : ((int) $data['role_id'] === 6
                    ? ['required', 'integer', 'exists:siswas,id']
                    : ['nullable', 'prohibited']),
        ]);
        $classData = $request->validate([
            'class_ids' => in_array((int) $data['role_id'], [2, 4], true)
                ? ['sometimes', 'nullable', 'array']
                : ['nullable', 'prohibited'],
            'class_ids.*' => ['required', 'integer', 'distinct', 'exists:school_classes,id'],
            'bk_class_ids' => in_array((int) $data['role_id'], [2, 4], true)
                ? ['sometimes', 'nullable', 'array']
                : ['nullable', 'prohibited'],
            'bk_class_ids.*' => ['required', 'integer', 'distinct', 'exists:school_classes,id'],
        ]);

        $update = [
            'fullname' => $data['fullname'],
            'username' => $data['username'],
            'email' => isset($data['email']) ? strtolower($data['email']) : null,
            'role_id' => $data['role_id'],
            'student_id' => in_array((int) $data['role_id'], [3, 6], true) ? $studentData['student_id'] : null,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $account->is_active,
        ];
        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        DB::transaction(function () use ($id, $update, $data, $classData) {
            DB::table('users')->where('id', $id)->update($update);
            DB::table('user_school_classes')->where('user_id', $id)->delete();
            if (in_array((int) $data['role_id'], [2, 4], true) && ! empty($classData['class_ids'])) {
                $now = now();
                DB::table('user_school_classes')->insert(
                    collect($classData['class_ids'])->map(fn ($classId) => [
                        'user_id' => $id,
                        'school_class_id' => $classId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
            DB::table('user_bk_classes')->where('user_id', $id)->delete();
            if (in_array((int) $data['role_id'], [2, 4], true) && ! empty($classData['bk_class_ids'])) {
                $now = now();
                DB::table('user_bk_classes')->insert(
                    collect($classData['bk_class_ids'])->map(fn ($classId) => [
                        'user_id' => $id,
                        'school_class_id' => $classId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
        });

        return redirect()->route('waka.accounts')->with('success', 'Akun berhasil diperbarui.');
    })->name('waka.accounts.update');

    Route::delete('/waka/akun/{id}', function (int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $account = DB::table('users')->where('id', $id)->first();
        abort_if(! $account, 404);

        if ((int) $id === (int) Auth::user()->getKey()) {
            return back()->withErrors(['account' => 'Akun Waka yang sedang digunakan tidak dapat dihapus.']);
        }

        $hasSchedules = DB::table('schedules')->where('teacher_id', $id)->exists();
        $hasLeaveRequests = DB::table('leave_requests')->where('applicant_id', $id)->exists();
        if ($hasSchedules || $hasLeaveRequests) {
            $relations = [];
            if ($hasSchedules) {
                $relations[] = 'jadwal mengajar';
            }
            if ($hasLeaveRequests) {
                $relations[] = 'pengajuan izin';
            }

            return back()->withErrors([
                'account' => 'Akun masih digunakan pada '.implode(' dan ', $relations).' sehingga tidak dapat dihapus.',
            ]);
        }

        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('waka.accounts')->with('success', 'Akun berhasil dihapus.');
    })->name('waka.accounts.destroy');

    Route::post('/waka/akun/{id}/reset-password', function (Request $request, int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $account = User::findOrFail($id);
        if ($account->getKey() === Auth::id()) {
            return back()->withErrors(['account' => 'Anda tidak dapat mereset sandi akun Anda sendiri dari menu ini. Gunakan menu Profil.']);
        }

        $temporaryPassword = 'User#' . random_int(1000, 9999);
        if ((int) $account->role_id === 3) {
            $siswa = $account->student_id ? DB::table('siswas')->where('id', $account->student_id)->first() : null;
            $temporaryPassword = ($siswa && ! empty($siswa->nis)) ? $siswa->nis : 'Siswa#' . random_int(1000, 9999);
        } elseif ((int) $account->role_id === 6) {
            $temporaryPassword = 'Ortu#' . random_int(1000, 9999);
        } elseif (in_array((int) $account->role_id, [2, 5], true)) {
            $temporaryPassword = ! empty($account->nip) ? $account->nip : 'Guru#' . random_int(1000, 9999);
        }

        $account->password = Hash::make($temporaryPassword);
        $account->must_change_password = true;
        $account->password_reset_at = now();
        $account->save();

        return redirect()->route('waka.accounts')->with('reset_info', [
            'message' => 'Kata sandi untuk akun ' . $account->fullname . ' (' . $account->username . ') berhasil diatur ulang.',
            'temporary_password' => $temporaryPassword,
        ]);
    })->name('waka.accounts.reset_password');

    Route::get('/waka/jadwal', function (Request $request) use ($ensureScheduleConfig, $currentAcademicYear) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $config = $ensureScheduleConfig();
        $activeCycle = (int) ($config->active_cycle ?? 1);
        $lockedColumn = $activeCycle === 2 ? 'cycle_two_locked' : 'cycle_one_locked';
        $scheduleLocked = (bool) ($config->{$lockedColumn} ?? false);

        $search = trim((string) $request->query('q', ''));
        $selectedJurusan = trim((string) $request->query('jurusan', ''));
        $selectedTingkat = trim((string) $request->query('tingkat', ''));
        $selectedRombel = trim((string) $request->query('rombel', ''));
        $selectedKelas = trim((string) $request->query('kelas', ''));

        $query = Schema::hasTable('schedules')
            ? DB::table('schedules')
                ->leftJoin('users', 'schedules.teacher_id', '=', 'users.id')
                ->select('schedules.*', 'users.fullname as guru_name')
                ->where('schedules.cycle_number', $activeCycle)
                ->where(function ($query) use ($currentAcademicYear) {
                    $query->where('schedules.academic_year', $currentAcademicYear)->orWhereNull('schedules.academic_year');
                })
            : null;

        if ($query) {
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('schedules.subject', 'like', "%{$search}%")
                        ->orWhere('users.fullname', 'like', "%{$search}%")
                        ->orWhere('schedules.class_name', 'like', "%{$search}%")
                        ->orWhere('schedules.day_of_week', 'like', "%{$search}%");
                });
            }

            if (! empty($selectedJurusan)) {
                $classesInJurusan = DB::table('siswas')
                    ->where('jurusan', $selectedJurusan)
                    ->whereNotNull('kelas')
                    ->pluck('kelas')
                    ->unique()
                    ->all();

                $query->where(function ($q) use ($selectedJurusan, $classesInJurusan) {
                    if (! empty($classesInJurusan)) {
                        $q->whereIn('schedules.class_name', $classesInJurusan);
                    }
                    $q->orWhere('schedules.class_name', 'like', "% {$selectedJurusan}%")
                      ->orWhere('schedules.class_name', 'like', "%{$selectedJurusan}%");
                });
            }

            if (! empty($selectedTingkat)) {
                $query->where(function ($q) use ($selectedTingkat) {
                    $q->where('schedules.class_name', 'like', "{$selectedTingkat} %")
                        ->orWhere('schedules.class_name', 'like', "{$selectedTingkat}-%")
                        ->orWhere('schedules.class_name', '=', $selectedTingkat);
                });
            }

            if (! empty($selectedRombel)) {
                $query->where(function ($q) use ($selectedRombel) {
                    $q->where('schedules.class_name', 'like', "% {$selectedRombel}")
                        ->orWhere('schedules.class_name', 'like', "%-{$selectedRombel}")
                        ->orWhere('schedules.class_name', 'like', "% {$selectedRombel} %");
                    if ($selectedRombel === '1') {
                        $q->orWhere(function ($sub) {
                            $sub->where('schedules.class_name', 'not like', '% 2')
                                ->where('schedules.class_name', 'not like', '% 3')
                                ->where('schedules.class_name', 'not like', '% 4');
                        });
                    }
                });
            }

            if (! empty($selectedKelas)) {
                $query->where('schedules.class_name', $selectedKelas);
            }

            $jadwals = $query->orderBy('schedules.day_of_week')
                ->orderBy('schedules.start_time')
                ->paginate(50)
                ->withQueryString();
        } else {
            $jadwals = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        }

        $gurus = DB::table('users')->whereIn('role_id', [2, 4])->select('id', 'fullname', 'username', 'nip')->orderBy('fullname')->get();
        $rooms = DB::table('ruangan')->orderBy('nama_ruangan')->get();
        $classOptions = DB::table('school_classes')->orderBy('name')->pluck('name');
        if ($classOptions->isEmpty()) {
            $classOptions = DB::table('siswas')
                ->whereNotNull('kelas')
                ->where('kelas', '!=', '')
                ->distinct()
                ->pluck('kelas')
                ->filter()
                ->unique()
                ->sort()
                ->values();
        }

        $jurusanOptions = ['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BR', 'BD', 'LPS'];

        $totalCycleSchedules = DB::table('schedules')
            ->where('cycle_number', $activeCycle)
            ->where(function ($query) use ($currentAcademicYear) {
                $query->where('academic_year', $currentAcademicYear)->orWhereNull('academic_year');
            })
            ->count();

        return view('waka.jadwal', [
            'user' => Auth::user(),
            'jadwals' => $jadwals,
            'totalCycleSchedules' => $totalCycleSchedules,
            'gurus' => $gurus,
            'rooms' => $rooms,
            'classOptions' => $classOptions,
            'jurusanOptions' => $jurusanOptions,
            'selectedJurusan' => $selectedJurusan,
            'selectedTingkat' => $selectedTingkat,
            'selectedRombel' => $selectedRombel,
            'selectedKelas' => $selectedKelas,
            'search' => $search,
            'activeCycle' => $activeCycle,
            'academicYear' => $currentAcademicYear,
            'scheduleLocked' => $scheduleLocked,
            'periodsByDay' => collect(SchoolTimetable::DAYS)
                ->mapWithKeys(fn ($day) => [$day => SchoolTimetable::periodsForDay($day)])
                ->all(),
        ]);
    })->name('waka.jadwal');

    Route::get('/waka/jadwal/export-pdf', function (Request $request) use ($currentAcademicYear) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $activeCycle = (int) (DB::table('system_configurations')->value('active_cycle') ?? 1);
        $filterCycle = $request->filled('cycle') ? (int) $request->query('cycle') : $activeCycle;
        $filterClass = $request->query('class');
        $filterJurusan = $request->query('jurusan');
        $filterTingkat = $request->query('tingkat');
        $filterRombel = $request->query('rombel');
        $search = trim((string) $request->query('q', ''));

        $query = DB::table('schedules')
            ->leftJoin('users', 'schedules.teacher_id', '=', 'users.id')
            ->leftJoin('ruangan', 'schedules.room_id', '=', 'ruangan.id_ruangan')
            ->select('schedules.*', 'users.fullname as guru_name', 'ruangan.nama_ruangan', 'ruangan.jenis as room_type')
            ->where(function ($q) use ($currentAcademicYear) {
                $q->where('schedules.academic_year', $currentAcademicYear)->orWhereNull('schedules.academic_year');
            })
            ->where('schedules.cycle_number', $filterCycle);

        if (! empty($filterClass)) {
            $query->where('schedules.class_name', $filterClass);
        }

        if (! empty($filterJurusan)) {
            $classesInJurusan = DB::table('siswas')
                ->where('jurusan', $filterJurusan)
                ->whereNotNull('kelas')
                ->pluck('kelas')
                ->unique()
                ->all();

            $query->where(function ($q) use ($filterJurusan, $classesInJurusan) {
                if (! empty($classesInJurusan)) {
                    $q->whereIn('schedules.class_name', $classesInJurusan);
                }
                $q->orWhere('schedules.class_name', 'like', "% {$filterJurusan}%")
                  ->orWhere('schedules.class_name', 'like', "%{$filterJurusan}%");
            });
        }

        if (! empty($filterTingkat)) {
            $query->where(function ($q) use ($filterTingkat) {
                $q->where('schedules.class_name', 'like', "{$filterTingkat} %")
                    ->orWhere('schedules.class_name', 'like', "{$filterTingkat}-%")
                    ->orWhere('schedules.class_name', '=', $filterTingkat);
            });
        }

        if (! empty($filterRombel)) {
            $query->where(function ($q) use ($filterRombel) {
                $q->where('schedules.class_name', 'like', "% {$filterRombel}")
                    ->orWhere('schedules.class_name', 'like', "%-{$filterRombel}")
                    ->orWhere('schedules.class_name', 'like', "% {$filterRombel} %");
                if ($filterRombel === '1') {
                    $q->orWhere(function ($sub) {
                        $sub->where('schedules.class_name', 'not like', '% 2')
                            ->where('schedules.class_name', 'not like', '% 3')
                            ->where('schedules.class_name', 'not like', '% 4');
                    });
                }
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('schedules.subject', 'like', "%{$search}%")
                    ->orWhere('users.fullname', 'like', "%{$search}%")
                    ->orWhere('schedules.class_name', 'like', "%{$search}%")
                    ->orWhere('schedules.day_of_week', 'like', "%{$search}%");
            });
        }

        $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5];
        $jadwals = $query->orderBy('schedules.day_of_week')
            ->orderBy('schedules.start_time')
            ->get()
            ->sortBy(function ($item) use ($dayOrder) {
                return ($dayOrder[$item->day_of_week] ?? 99) . '_' . $item->start_time;
            })
            ->values();

        $availableClasses = DB::table('siswas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->pluck('kelas')
            ->all();

        $titleParts = ['Jadwal Pelajaran Putaran ' . $filterCycle];
        if (! empty($filterJurusan)) {
            $titleParts[] = 'Jurusan ' . $filterJurusan;
        }
        if (! empty($filterTingkat)) {
            $titleParts[] = 'Tingkat ' . $filterTingkat;
        }
        if (! empty($filterClass)) {
            $titleParts[] = 'Kelas ' . $filterClass;
        }

        return view('pdf.jadwal', [
            'jadwals' => $jadwals,
            'activeCycle' => $filterCycle,
            'filterCycle' => $filterCycle,
            'filterClass' => $filterClass,
            'filterJurusan' => $filterJurusan,
            'filterTingkat' => $filterTingkat,
            'search' => $search,
            'availableClasses' => $availableClasses,
            'academicYear' => $currentAcademicYear,
            'pageTitle' => implode(' - ', $titleParts),
            'documentTitle' => 'JADWAL PELAJARAN SEKOLAH',
        ]);
    })->name('waka.jadwal.pdf');

    $downloadScheduleWorkbook = function (array $rows, string $filename) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            'Hari',
            'Guru',
            'Mata Pelajaran',
            'Kelas',
            'Ruangan',
            'Sesi Mulai',
            'Sesi Selesai',
            'Putaran',
            'Jenis',
        ], null, 'A1');
        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2');
        }
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    };

    Route::get('/waka/jadwal/template', function () use ($downloadScheduleWorkbook) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        return $downloadScheduleWorkbook([], 'template_import_jadwal.xlsx');
    })->name('waka.jadwal.template');

    Route::get('/waka/jadwal/contoh', function () use ($downloadScheduleWorkbook) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        return $downloadScheduleWorkbook([
            ['Senin', 'ARIS PURWANTINAH, S.Pd', 'KK BR (Bisnis Retail)', 'XII BR 1', 'Lab 6', 'Jam ke-7', 'Jam ke-8', '2', 'Jurusan'],
            ['Selasa', 'SUNARSIH S.Pd', 'Projek IPAS', 'X TKJ 1', 'Lab IPA', 'Jam ke-4', 'Jam ke-4', '2', 'Normada'],
            ['Rabu', 'SRI SUNARTINI, S.Pd', 'KK BR (Bisnis Retail)', 'XII BR 1', 'Lab 8', 'Jam ke-7', 'Jam ke-8', '2', 'Jurusan'],
            ['Kamis', 'AMAT SUKARDI, S.Pd', 'Bahasa Inggris', 'XII LPS', 'OR1', 'Jam ke-1', 'Jam ke-3', '2', 'Normada'],
            ['Jumat', 'MUHAMMAD HERU PURNOMO, M.S.I', 'Bahasa Indonesia', 'XII RPL 2', '', 'Jam ke-1', 'Jam ke-3', '2', 'Normada'],
        ], 'contoh_import_jadwal_5_data.xlsx');
    })->name('waka.jadwal.example');

    Route::get('/waka/jadwal/download/putaran-1', function () {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $path = public_path('downloads/JADWAL_PUTARAN_1.xlsx');
        abort_unless(file_exists($path), 404, 'File jadwal putaran 1 belum tersedia.');
        return response()->download($path, 'JADWAL_PUTARAN_1.xlsx');
    })->name('waka.jadwal.download.putaran1');

    Route::get('/waka/jadwal/download/putaran-2', function () {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $path = public_path('downloads/JADWAL_PUTARAN_2.xlsx');
        abort_unless(file_exists($path), 404, 'File jadwal putaran 2 belum tersedia.');
        return response()->download($path, 'JADWAL_PUTARAN_2.xlsx');
    })->name('waka.jadwal.download.putaran2');

    Route::get('/waka/jadwal/download/data-master', function () {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $path = public_path('downloads/DATA_MASTER_DAN_ALOKASI_JADWAL.xlsx');
        abort_unless(file_exists($path), 404, 'File data master belum tersedia.');
        return response()->download($path, 'DATA_MASTER_GURU_MAPEL_RUANG_KELAS.xlsx');
    })->name('waka.jadwal.download.master');

    Route::get('/waka/jadwal/auto-generate/template', function () {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $sampleTeachers = DB::table('users')->whereIn('role_id', [2, 4])->take(5)->pluck('fullname')->all();
        $sampleClasses = DB::table('school_classes')->take(5)->pluck('name')->all();
        if (empty($sampleClasses)) {
            $sampleClasses = ['X RPL 1', 'XI RPL 1', 'XII RPL 2', 'X TKJ 1', 'XI DKV 1'];
        }
        $sampleRooms = DB::table('ruangan')->take(5)->pluck('nama_ruangan')->all();

        $rows = [
            [$sampleTeachers[0] ?? 'ARIS PURWANTINAH, S.Pd', 'Pemrograman Web & Mobile', $sampleClasses[0] ?? 'X RPL 1', $sampleRooms[0] ?? 'Lab 1', '4', 'Jurusan'],
            [$sampleTeachers[1] ?? 'SUNARSIH S.Pd', 'Matematika', $sampleClasses[1] ?? 'XI RPL 1', $sampleRooms[1] ?? 'Ruang 10', '3', 'Normada'],
            [$sampleTeachers[2] ?? 'SRI SUNARTINI, S.Pd', 'Bahasa Indonesia', $sampleClasses[2] ?? 'XII RPL 2', $sampleRooms[2] ?? 'Ruang 11', '2', 'Normada'],
            [$sampleTeachers[3] ?? 'AMAT SUKARDI, S.Pd', 'Bahasa Inggris', $sampleClasses[3] ?? 'X TKJ 1', '', '2', 'Normada'],
            [$sampleTeachers[4] ?? 'MUHAMMAD HERU PURNOMO, M.S.I', 'Basis Data', $sampleClasses[4] ?? 'XI DKV 1', $sampleRooms[0] ?? 'Lab 1', '4', 'Jurusan'],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            'Nama Guru',
            'Mata Pelajaran',
            'Kelas',
            'Ruangan',
            'Jumlah JP',
            'Jenis (Normada/Jurusan)',
        ], null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            'template_alokasi_jadwal_otomatis.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    })->name('waka.jadwal.auto.template');

    Route::post('/waka/jadwal/auto-generate', function (Request $request) use ($ensureScheduleConfig, $currentAcademicYear) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'cycle_number' => ['required', 'in:1,2'],
                'mode' => ['required', 'in:fresh,append'],
                'file' => ['nullable', 'file', 'max:20480'],
                'allocations' => ['nullable', 'array'],
            ]);

            $cycleNumber = (int) $request->input('cycle_number', 1);
            $fresh = $request->input('mode') === 'fresh';

            $config = $ensureScheduleConfig();
            $lockCol = $cycleNumber === 2 ? 'cycle_two_locked' : 'cycle_one_locked';
            if (! empty($config->{$lockCol})) {
                return back()->withErrors(['auto_gen' => "Putaran {$cycleNumber} sedang dikunci. Buka kunci terlebih dahulu untuk membuat jadwal otomatis."]);
            }

            $service = new \App\Services\AutoScheduleGeneratorService();
            $allocations = [];

            // 1. Jika pengguna mengunggah berkas Excel/CSV alokasi
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $ext = strtolower($file->getClientOriginalExtension());
                if (! in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
                    return back()->withErrors(['auto_gen' => 'Format file tidak didukung. Harap unggah file .xlsx, .xls, atau .csv.']);
                }
                $allocations = $service->parseAllocationFile($file->getRealPath());
                if (empty($allocations)) {
                    return back()->withErrors(['auto_gen' => 'Berkas alokasi tidak memiliki data yang valid atau nama guru/kelas tidak cocok. Pastikan menggunakan template yang sesuai.']);
                }
            } elseif ($request->filled('allocations')) {
                // 2. Jika input manual dari tabel web
                $raw = $request->input('allocations');
                foreach ($raw as $item) {
                    if (! empty($item['teacher_id']) && ! empty($item['subject']) && ! empty($item['class_name'])) {
                        $allocations[] = [
                            'teacher_id' => (int) $item['teacher_id'],
                            'subject' => trim((string) $item['subject']),
                            'class_name' => trim((string) $item['class_name']),
                            'room_id' => ! empty($item['room_id']) ? (int) $item['room_id'] : null,
                            'jp' => max(1, min(8, (int) ($item['jp'] ?? 2))),
                            'cycle_type' => in_array($item['cycle_type'] ?? '', ['Jurusan', 'Normada', 'Regular'], true) ? $item['cycle_type'] : 'Normada',
                        ];
                    }
                }
            }

            if (empty($allocations)) {
                return back()->withErrors(['auto_gen' => 'Harap isi minimal 1 baris alokasi guru, mapel, dan kelas.']);
            }

            $result = $service->generate($allocations, $cycleNumber, $currentAcademicYear, $fresh);

            if (! $result['success'] && $result['scheduled_count'] === 0) {
                return back()->withErrors(['auto_gen' => 'Gagal menyusun jadwal: slot waktu tidak mencukupi atau terjadi bentrok total.']);
            }

            $msg = "⚡ Sukses! Berhasil menyusun {$result['scheduled_count']} jadwal ({$result['total_jp']} JP) secara otomatis pada Putaran {$cycleNumber} tanpa bentrok.";
            if ($result['unassigned_count'] > 0) {
                $msg .= " Catatan: {$result['unassigned_count']} sesi tidak dapat ditempatkan karena kapasitas slot hari/ruangan penuh.";
            }

            return redirect()->route('waka.jadwal')->with('success', $msg);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Auto-generate error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['auto_gen' => 'Terjadi kendala saat menyusun jadwal otomatis: ' . $e->getMessage()])->withInput();
        }
    })->name('waka.jadwal.auto.generate');

    Route::post('/waka/jadwal/import', function (Request $request) use ($ensureScheduleConfig, $findScheduleConflict, $currentAcademicYear) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'cycle_number' => ['nullable', 'in:1,2,auto'],
            'replace_existing' => ['sometimes', 'boolean'],
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['xlsx', 'xls', 'csv', 'pdf'], true)) {
            throw ValidationException::withMessages([
                'file' => 'Format file tidak didukung. Harap unggah file .xlsx, .xls, .csv, atau .pdf.',
            ]);
        }

        $config = $ensureScheduleConfig();
        $activeCycle = (int) ($config->active_cycle ?? 1);

        $cycleInput = $request->input('cycle_number', 'auto');
        if ($cycleInput === '1' || $cycleInput === '2') {
            $targetCycle = (int) $cycleInput;
        } else {
            if (preg_match('/(put[_\-\s]*2|putaran[_\-\s]*2)/i', $originalName)) {
                $targetCycle = 2;
            } elseif (preg_match('/(put[_\-\s]*1|putaran[_\-\s]*1)/i', $originalName)) {
                $targetCycle = 1;
            } else {
                $targetCycle = $activeCycle;
            }
        }

        $lockColumn = $targetCycle === 2 ? 'cycle_two_locked' : 'cycle_one_locked';
        if ((bool) ($config->{$lockColumn} ?? false)) {
            throw ValidationException::withMessages([
                'file' => "Putaran {$targetCycle} sedang dikunci. Buka kunci putaran sebelum mengimpor jadwal.",
            ]);
        }

        $replaceExisting = $request->boolean('replace_existing');
        $extractedItems = [];
        $filePath = $file->getRealPath();

        if ($extension === 'pdf') {
            $pythonScript = app_path('Support/extract_pdf_schedules.py');
            $pythonExecutables = [
                'C:\\laragon\\bin\\python\\python-3.13\\python.exe',
                'python3',
                'python',
                '/usr/bin/python3',
                '/usr/local/bin/python3',
            ];

            $lastErrors = [];
            foreach ($pythonExecutables as $py) {
                if ($py === 'python' || $py === 'python3' || file_exists($py)) {
                    $cmd = escapeshellcmd($py) . ' ' . escapeshellarg($pythonScript) . ' ' . escapeshellarg($filePath);
                    $output = [];
                    $ret = 0;
                    @exec($cmd . ' 2>&1', $output, $ret);
                    if ($ret === 0 && ! empty($output)) {
                        $rawJson = implode("\n", $output);
                        $decoded = json_decode($rawJson, true);
                        if (is_array($decoded) && ! empty($decoded)) {
                            $extractedItems = $decoded;
                            break;
                        }
                    } elseif (! empty($output)) {
                        $lastErrors[] = implode(' ', array_slice($output, -2));
                    }
                }
            }

            if (empty($extractedItems)) {
                $errDetail = ! empty($lastErrors) ? ' (' . implode(' | ', array_unique($lastErrors)) . ')' : '';
                throw ValidationException::withMessages([
                    'file' => 'Tidak ada data jadwal yang dapat diekstrak dari file PDF. Pastikan file PDF berformat FET timetable teks' . $errDetail . '.',
                ]);
            }
        } else {
            try {
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $sheetRows = $worksheet->toArray(null, true, true, false);
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'file' => 'Gagal membaca file Excel/CSV: ' . $e->getMessage(),
                ]);
            }

            if (empty($sheetRows)) {
                throw ValidationException::withMessages([
                    'file' => 'File Excel/CSV kosong.',
                ]);
            }

            $headerAliases = [
                'hari' => 'day',
                'day' => 'day',
                'guru' => 'teacher',
                'nama guru' => 'teacher',
                'teacher' => 'teacher',
                'pengampu' => 'teacher',
                'nama' => 'teacher',
                'mata pelajaran' => 'subject',
                'mapel' => 'subject',
                'subject' => 'subject',
                'pelajaran' => 'subject',
                'kelas' => 'class',
                'class' => 'class',
                'ruangan' => 'room',
                'ruang' => 'room',
                'room' => 'room',
                'lab' => 'room',
                'sesi mulai' => 'start_slot',
                'jam mulai' => 'start_slot',
                'mulai' => 'start_slot',
                'start' => 'start_slot',
                'sesi selesai' => 'end_slot',
                'jam selesai' => 'end_slot',
                'selesai' => 'end_slot',
                'end' => 'end_slot',
                'putaran' => 'cycle_number',
                'cycle' => 'cycle_number',
                'jenis' => 'cycle_type',
                'tipe' => 'cycle_type',
            ];

            $colIdx = [];
            $dataRows = [];
            foreach ($sheetRows as $idx => $row) {
                $matched = [];
                foreach ($row as $ci => $val) {
                    $norm = mb_strtolower(trim((string) $val));
                    $norm = ltrim($norm, "\xEF\xBB\xBF");
                    if (isset($headerAliases[$norm])) {
                        $matched[$headerAliases[$norm]] = $ci;
                    }
                }
                if (isset($matched['day']) && isset($matched['teacher'])) {
                    $colIdx = $matched;
                    $dataRows = array_slice($sheetRows, $idx + 1);
                    break;
                }
            }

            if (empty($colIdx)) {
                $defaultOrder = ['day', 'teacher', 'subject', 'class', 'room', 'start_slot', 'end_slot', 'cycle_number', 'cycle_type'];
                foreach ($defaultOrder as $k => $f) {
                    $colIdx[$f] = $k;
                }
                $dataRows = array_slice($sheetRows, 1);
            }

            foreach ($dataRows as $row) {
                $dayVal = trim((string) ($row[$colIdx['day']] ?? ''));
                $teacherVal = trim((string) ($row[$colIdx['teacher']] ?? ''));
                if ($dayVal === '' || $teacherVal === '') {
                    continue;
                }
                $extractedItems[] = [
                    'day' => $dayVal,
                    'teacher' => $teacherVal,
                    'subject' => trim((string) ($row[$colIdx['subject']] ?? 'Pelajaran')),
                    'class' => trim((string) ($row[$colIdx['class']] ?? '')),
                    'room' => trim((string) ($row[$colIdx['room']] ?? '')),
                    'start_slot' => trim((string) ($row[$colIdx['start_slot']] ?? '1')),
                    'end_slot' => trim((string) ($row[$colIdx['end_slot']] ?? '1')),
                    'cycle_number' => trim((string) ($row[$colIdx['cycle_number']] ?? '')),
                    'cycle_type' => trim((string) ($row[$colIdx['cycle_type']] ?? '')),
                ];
            }
        }

        if (empty($extractedItems)) {
            throw ValidationException::withMessages([
                'file' => 'Tidak ada baris data jadwal yang valid untuk diimpor.',
            ]);
        }

        $teachers = DB::table('users')->whereIn('role_id', [2, 4])->get(['id', 'fullname', 'username', 'nip']);
        $teacherMap = [];
        foreach ($teachers as $t) {
            $n1 = preg_replace('/[^a-z0-9]/', '', strtolower($t->fullname));
            if ($n1 !== '') $teacherMap[$n1] = $t->id;
            if ($t->username) {
                $n2 = preg_replace('/[^a-z0-9]/', '', strtolower($t->username));
                if ($n2 !== '') $teacherMap[$n2] = $t->id;
            }
            if ($t->nip) {
                $n3 = preg_replace('/[^a-z0-9]/', '', strtolower($t->nip));
                if ($n3 !== '') $teacherMap[$n3] = $t->id;
            }
        }

        $validClasses = DB::table('siswas')->whereNotNull('kelas')->where('kelas', '!=', '')->distinct()->pluck('kelas')->all();
        $classMap = [];
        foreach ($validClasses as $c) {
            $norm = preg_replace('/[^a-z0-9]/', '', strtolower($c));
            $classMap[$norm] = $c;
        }

        $rooms = DB::table('ruangan')->get(['id_ruangan', 'nama_ruangan']);
        $roomMap = [];
        foreach ($rooms as $r) {
            $norm = preg_replace('/[^a-z0-9]/', '', strtolower($r->nama_ruangan));
            $roomMap[$norm] = $r->id_ruangan;
        }

        $insertedCount = 0;
        $conflictCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            if ($replaceExisting) {
                DB::table('schedules')
                    ->where(function ($q) use ($currentAcademicYear) {
                        $q->where('academic_year', $currentAcademicYear)->orWhereNull('academic_year');
                    })
                    ->where('cycle_number', (string) $targetCycle)
                    ->delete();
            }

            foreach ($extractedItems as $item) {
                $tRaw = $item['teacher'] ?? '';
                $tNorm = preg_replace('/[^a-z0-9]/', '', strtolower($tRaw));
                $teacherId = $teacherMap[$tNorm] ?? null;
                if (! $teacherId) {
                    foreach ($teacherMap as $k => $id) {
                        if (str_starts_with($tNorm, $k) || str_starts_with($k, $tNorm) || (abs(strlen($tNorm) - strlen($k)) <= 2 && levenshtein($tNorm, $k) <= 2)) {
                            $teacherId = $id;
                            break;
                        }
                    }
                }
                if (! $teacherId) {
                    $skippedCount++;
                    continue;
                }

                $cRaw = $item['class'] ?? '';
                $cNorm = preg_replace('/[^a-z0-9]/', '', strtolower($cRaw));
                $className = $classMap[$cNorm] ?? null;
                if (! $className) {
                    if (preg_match('/^(X|XI|XII)\s*(AKL|AK|BD|BR|DKV|LPS|MP|RPL|TKJ)\s*(\d+)?$/i', $cRaw, $cm)) {
                        $tryNorm = preg_replace('/[^a-z0-9]/', '', strtolower($cm[1] . ' ' . $cm[2] . (isset($cm[3]) ? ' ' . $cm[3] : '')));
                        $className = $classMap[$tryNorm] ?? null;
                    }
                }
                if (! $className) {
                    $skippedCount++;
                    continue;
                }

                $roomId = null;
                if (! empty($item['room'])) {
                    $rNorm = preg_replace('/[^a-z0-9]/', '', strtolower($item['room']));
                    $roomId = $roomMap[$rNorm] ?? null;
                }

                $day = ucfirst(strtolower(trim($item['day'] ?? '')));
                if (! in_array($day, SchoolTimetable::DAYS, true)) {
                    $skippedCount++;
                    continue;
                }

                $periods = SchoolTimetable::periodsForDay($day);
                $periodCodes = array_values(array_filter(array_keys($periods), fn ($p) => str_starts_with($p, 'period_')));
                if (empty($periodCodes)) {
                    $skippedCount++;
                    continue;
                }

                $sRaw = (string) ($item['start_slot'] ?? '1');
                $eRaw = (string) ($item['end_slot'] ?? $sRaw);

                $parseSlot = function ($val, $pCodes, $pList) {
                    $clean = trim($val);
                    if (preg_match('/(?:jam(?:\s*ke)?[_\-\s]*)?(\d+)/i', $clean, $sm)) {
                        return max(1, (int) $sm[1]) - 1;
                    }
                    if (preg_match('/period_(\d+)/i', $clean, $sm)) {
                        return max(1, (int) $sm[1]) - 1;
                    }
                    if (preg_match('/(\d\d:\d\d)/', $clean, $tm)) {
                        foreach ($pCodes as $idx => $code) {
                            if ($pList[$code]['start'] === $tm[1] || $pList[$code]['end'] === $tm[1]) {
                                return $idx;
                            }
                        }
                    }
                    return 0;
                };

                $startSlotIdx = $parseSlot($sRaw, $periodCodes, $periods);
                $endSlotIdx = $parseSlot($eRaw, $periodCodes, $periods);

                if ($startSlotIdx >= count($periodCodes)) $startSlotIdx = count($periodCodes) - 1;
                if ($endSlotIdx >= count($periodCodes)) $endSlotIdx = count($periodCodes) - 1;
                if ($endSlotIdx < $startSlotIdx) $endSlotIdx = $startSlotIdx;

                $startCode = $periodCodes[$startSlotIdx];
                $endCode = $periodCodes[$endSlotIdx];

                $startTime = $periods[$startCode]['start'];
                $endTime = $periods[$endCode]['end'];

                $subject = trim($item['subject'] ?? '');
                if ($subject === '') {
                    $subject = 'Pelajaran';
                }

                $cycleNum = ! empty($item['cycle_number']) && in_array((string) $item['cycle_number'], ['1', '2'], true)
                    ? (string) $item['cycle_number']
                    : (string) $targetCycle;

                $cycleType = ! empty($item['cycle_type']) && in_array($item['cycle_type'], ['Normada', 'Jurusan', 'Regular', 'Mid', 'Final'], true)
                    ? $item['cycle_type']
                    : (preg_match('/(KK|RPL|TKJ|DKV|AKL|AK|BR|BD|MP|LPS|Kejuruan|Bengkel|Lab|Projek)/i', $subject) ? 'Jurusan' : 'Normada');

                $scheduleData = [
                    'teacher_id' => $teacherId,
                    'academic_year' => $currentAcademicYear,
                    'cycle_number' => $cycleNum,
                    'day_of_week' => $day,
                    'subject' => $subject,
                    'class_name' => $className,
                    'class_id' => null,
                    'room_id' => $roomId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'cycle_type' => $cycleType,
                ];

                $isDuplicate = DB::table('schedules')
                    ->where(function ($q) use ($currentAcademicYear) {
                        $q->where('academic_year', $currentAcademicYear)->orWhereNull('academic_year');
                    })
                    ->where('cycle_number', $cycleNum)
                    ->where('day_of_week', $day)
                    ->where('teacher_id', $teacherId)
                    ->where('start_time', $startTime)
                    ->where('end_time', $endTime)
                    ->where('class_name', $className)
                    ->exists();

                if ($isDuplicate) {
                    $conflictCount++;
                    continue;
                }

                $conflict = $findScheduleConflict($scheduleData);
                if ($conflict) {
                    $conflictCount++;
                    continue;
                }

                DB::table('schedules')->insert([
                    ...$scheduleData,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $insertedCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $fileTypeName = strtoupper($extension);
        $message = "Berhasil mengimpor {$insertedCount} jadwal ke Putaran {$targetCycle} dari {$fileTypeName}.";
        if ($conflictCount > 0 || $skippedCount > 0) {
            $notes = [];
            if ($conflictCount > 0) $notes[] = "{$conflictCount} dilewati karena duplikat/konflik";
            if ($skippedCount > 0) $notes[] = "{$skippedCount} baris tidak lengkap/tidak cocok";
            $message .= ' (' . implode(', ', $notes) . ').';
        }

        return redirect()->route('waka.jadwal')->with('success', $message);
    })->name('waka.jadwal.import');

    Route::post('/waka/jadwal/configure', function (Request $request) use ($ensureScheduleConfig) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $data = $request->validate([
            'active_cycle' => ['required', 'in:1,2'],
            'locked' => ['sometimes', 'boolean'],
        ]);

        $locked = (bool) ($data['locked'] ?? false);

        $config = $ensureScheduleConfig();
        $lockedColumn = (int) $data['active_cycle'] === 1 ? 'cycle_one_locked' : 'cycle_two_locked';
        if ($config && isset($config->id)) {
            DB::table('system_configurations')->where('id', (int) $config->id)->update([
                'active_cycle' => $data['active_cycle'],
                'is_scheduled_locked' => $locked,
                $lockedColumn => $locked,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('system_configurations')->insert([
                'active_cycle' => $data['active_cycle'],
                'is_scheduled_locked' => $locked,
                'cycle_one_locked' => $data['active_cycle'] === '1' ? $locked : false,
                'cycle_two_locked' => $data['active_cycle'] === '2' ? $locked : false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('waka.jadwal')->with('success', 'Pengaturan putaran jadwal berhasil disimpan.');
    })->name('waka.jadwal.configure');

    // =========================================================================
    // FITUR TC-SEC-03 & TC-SCH-04: RUTE KHUSUS WAKA MENGUNCI JADWAL KBM
    // - Proteksi Backend: Jika diakses oleh non-Waka (Guru/Siswa/Ortu), sistem
    //   secara tegas melempar HTTP 403 Forbidden (bukan hanya menyembunyikan tombol UI)
    // - TC-SCH-04: Waka Kurikulum dapat mengunci (lock) jadwal KBM aktif
    // =========================================================================
    Route::match(['get', 'post'], '/waka/lock-schedule', function (Request $request) use ($ensureScheduleConfig) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403, 'Akses ditolak: Hanya Waka Kurikulum yang memiliki izin mengunci jadwal.');
        $config = $ensureScheduleConfig();
        $activeCycle = (int) ($config->active_cycle ?? 1);
        $lockedColumn = $activeCycle === 2 ? 'cycle_two_locked' : 'cycle_one_locked';
        $currentLock = (bool) ($config->{$lockedColumn} ?? false) || (bool) ($config->is_scheduled_locked ?? false);
        $newLock = $request->has('locked') ? (bool) $request->input('locked') : ! $currentLock;

        DB::table('system_configurations')->where('id', (int) $config->id)->update([
            'is_scheduled_locked' => $newLock,
            $lockedColumn => $newLock,
            'updated_at' => now(),
        ]);

        return redirect()->route('waka.jadwal')->with('success', 'Status penguncian jadwal berhasil diubah menjadi: ' . ($newLock ? 'Terkunci' : 'Terbuka'));
    })->name('waka.schedule.lock');

    // =========================================================================
    // FITUR TC-SEC-03 & TC-SCH-03: RUTE KHUSUS WAKA PERGANTIAN SIKLUS / PUTARAN KBM
    // - Proteksi Backend: Akses langsung via URL oleh peran selain Waka langsung
    //   ditolak dengan respons HTTP 403 Unauthorized
    // - TC-SCH-03: Waka Kurikulum dapat beralih antara Putaran 1 & Putaran 2 secara dinamis
    // =========================================================================
    Route::match(['get', 'post'], '/waka/toggle-cycle', function (Request $request) use ($ensureScheduleConfig) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403, 'Akses ditolak: Hanya Waka Kurikulum yang memiliki izin mengubah putaran KBM.');
        $config = $ensureScheduleConfig();
        $currentCycle = (int) ($config->active_cycle ?? 1);
        $newCycle = $currentCycle === 1 ? 2 : 1;

        DB::table('system_configurations')->where('id', (int) $config->id)->update([
            'active_cycle' => $newCycle,
            'updated_at' => now(),
        ]);

        return redirect()->route('waka.jadwal')->with('success', "Putaran KBM berhasil dialihkan ke Putaran {$newCycle}.");
    })->name('waka.cycle.toggle');

    Route::post('/waka/jadwal', function (Request $request) use ($ensureScheduleConfig, $findScheduleConflict, $currentAcademicYear) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        if ($request->filled('period_code') && ! $request->filled('start_period_code')) {
            $request->merge([
                'start_period_code' => $request->input('period_code'),
                'end_period_code' => $request->input('period_code'),
            ]);
        }
        $classNames = DB::table('siswas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->pluck('kelas')
            ->all();
        $data = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'cycle_number' => ['required', 'in:1,2'],
            'day_of_week' => ['required', Rule::in(SchoolTimetable::DAYS)],
            'start_period_code' => ['required', 'string', 'max:30'],
            'end_period_code' => ['required', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:100'],
            'class_name' => ['nullable', 'string', 'max:255', Rule::in($classNames)],
            'room_id' => ['nullable', 'integer', 'exists:ruangan,id_ruangan'],
            'cycle_type' => ['required', 'in:Normada,Jurusan,Regular,Mid,Final'],
        ]);
        abort_unless(DB::table('users')->where('id', $data['teacher_id'])->whereIn('role_id', [2, 4])->exists(), 422);
        $periods = SchoolTimetable::periodsForDay($data['day_of_week']);
        $periodCodes = array_keys($periods);
        $startIndex = array_search($data['start_period_code'], $periodCodes, true);
        $endIndex = array_search($data['end_period_code'], $periodCodes, true);
        if ($startIndex === false || $endIndex === false) {
            throw ValidationException::withMessages(['start_period_code' => 'Pilih sesi pelajaran yang tersedia.']);
        }
        if ($endIndex < $startIndex) {
            throw ValidationException::withMessages(['end_period_code' => 'Sesi selesai harus sama dengan atau setelah sesi mulai.']);
        }
        $startPeriod = $periods[$data['start_period_code']];
        $endPeriod = $periods[$data['end_period_code']];

        $config = $ensureScheduleConfig();
        $lockColumn = $data['cycle_number'] === '2' ? 'cycle_two_locked' : 'cycle_one_locked';
        abort_if((bool) ($config->{$lockColumn} ?? false), 423, 'Putaran ini sedang dikunci. Buka kunci sebelum menambah jadwal.');

        $scheduleData = [
            'teacher_id' => $data['teacher_id'],
            'academic_year' => $currentAcademicYear,
            'cycle_number' => $data['cycle_number'],
            'day_of_week' => $data['day_of_week'],
            'subject' => $data['subject'],
            'class_name' => $data['class_name'] ?? null,
            'class_id' => null,
            'room_id' => $data['room_id'] ?? null,
            'start_time' => $startPeriod['start'],
            'end_time' => $endPeriod['end'],
            'cycle_type' => $data['cycle_type'],
        ];
        $conflict = $findScheduleConflict($scheduleData);
        if ($conflict) {
            $reason = ! empty($scheduleData['room_id']) && (int) $conflict->room_id === (int) $scheduleData['room_id']
                ? 'Ruangan sudah digunakan pada waktu tersebut.'
                : ((int) $conflict->teacher_id === (int) $data['teacher_id']
                    ? 'Guru sudah memiliki jadwal pada waktu tersebut.'
                    : 'Kelas sudah memiliki jadwal pada waktu tersebut.');

            return back()->withErrors(['schedule_conflict' => $reason])->withInput();
        }

        DB::table('schedules')->insert([
            ...$scheduleData,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('waka.jadwal')->with('success', 'Jadwal berhasil ditambahkan.');
    })->name('waka.jadwal.store');

    Route::patch('/waka/jadwal/{id}', function (Request $request, int $id) use ($ensureScheduleConfig, $findScheduleConflict, $currentAcademicYear) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        if ($request->filled('period_code') && ! $request->filled('start_period_code')) {
            $request->merge([
                'start_period_code' => $request->input('period_code'),
                'end_period_code' => $request->input('period_code'),
            ]);
        }
        $schedule = DB::table('schedules')
            ->where('id', $id)
            ->where(function ($query) use ($currentAcademicYear) {
                $query->where('academic_year', $currentAcademicYear)->orWhereNull('academic_year');
            })
            ->first();
        abort_if(! $schedule, 404);
        $config = $ensureScheduleConfig();
        $lockColumn = (int) $schedule->cycle_number === 2 ? 'cycle_two_locked' : 'cycle_one_locked';
        abort_if((bool) ($config->{$lockColumn} ?? false), 423, 'Putaran ini sedang dikunci.');

        $classNames = DB::table('siswas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->pluck('kelas')
            ->all();
        $legacyClass = $schedule->class_name ?? $schedule->class_id;
        $allowedClassNames = $legacyClass === null
            ? $classNames
            : array_values(array_unique([...$classNames, (string) $legacyClass]));
        $data = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'day_of_week' => ['required', 'string', 'max:20'],
            'start_period_code' => ['required', 'string', 'max:30'],
            'end_period_code' => ['required', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:100'],
            'class_name' => ['nullable', 'string', 'max:255', Rule::in($allowedClassNames)],
            'room_id' => ['nullable', 'integer', 'exists:ruangan,id_ruangan'],
            'cycle_type' => ['required', 'in:Normada,Jurusan,Regular,Mid,Final'],
        ]);
        abort_unless(DB::table('users')->where('id', $data['teacher_id'])->whereIn('role_id', [2, 4])->exists(), 422);
        if (! in_array($data['day_of_week'], SchoolTimetable::DAYS, true)) {
            throw ValidationException::withMessages(['day_of_week' => 'Jadwal hanya dapat dibuat dari Senin sampai Jumat.']);
        }
        $periods = SchoolTimetable::periodsForDay($data['day_of_week']);
        $periodCodes = array_keys($periods);
        $startIndex = array_search($data['start_period_code'], $periodCodes, true);
        $endIndex = array_search($data['end_period_code'], $periodCodes, true);
        if ($startIndex === false || $endIndex === false) {
            throw ValidationException::withMessages(['start_period_code' => 'Pilih sesi pelajaran yang tersedia.']);
        }
        if ($endIndex < $startIndex) {
            throw ValidationException::withMessages(['end_period_code' => 'Sesi selesai harus sama dengan atau setelah sesi mulai.']);
        }
        $startPeriod = $periods[$data['start_period_code']];
        $endPeriod = $periods[$data['end_period_code']];
        unset($data['start_period_code'], $data['end_period_code']);

        $isLegacyClass = ! empty($data['class_name'])
            && ! in_array($data['class_name'], $classNames, true)
            && (string) $data['class_name'] === (string) $legacyClass;
        $scheduleData = [
            ...$data,
            'academic_year' => $schedule->academic_year ?? $currentAcademicYear,
            'class_id' => $isLegacyClass ? $schedule->class_id : null,
            'class_name' => $isLegacyClass ? $schedule->class_name : ($data['class_name'] ?? null),
            'room_id' => $data['room_id'] ?? null,
            'cycle_number' => $schedule->cycle_number,
            'start_time' => $startPeriod['start'],
            'end_time' => $endPeriod['end'],
        ];
        $conflict = $findScheduleConflict($scheduleData, $id);
        if ($conflict) {
            $reason = ! empty($scheduleData['room_id']) && (int) $conflict->room_id === (int) $scheduleData['room_id']
                ? 'Ruangan sudah digunakan pada waktu tersebut.'
                : ((int) $conflict->teacher_id === (int) $data['teacher_id']
                    ? 'Guru sudah memiliki jadwal pada waktu tersebut.'
                    : 'Kelas sudah memiliki jadwal pada waktu tersebut.');

            return back()->withErrors(['schedule_conflict' => $reason])->withInput();
        }

        DB::table('schedules')->where('id', $id)->update([...$scheduleData, 'updated_at' => now()]);

        return redirect()->route('waka.jadwal')->with('success', 'Jadwal berhasil diperbarui.');
    })->name('waka.jadwal.update');

    Route::match(['DELETE', 'POST'], '/waka/jadwal/destroy-all', function (Request $request) use ($ensureScheduleConfig, $currentAcademicYear) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);

        $cycleNumber = (int) $request->input('cycle_number', 1);
        $config = $ensureScheduleConfig();
        $lockColumn = $cycleNumber === 2 ? 'cycle_two_locked' : 'cycle_one_locked';
        if (! empty($config->{$lockColumn})) {
            return back()->withErrors(['schedule' => "Putaran {$cycleNumber} sedang dikunci. Buka kunci terlebih dahulu untuk menghapus jadwal."]);
        }

        $scope = $request->input('scope', 'all');

        $query = DB::table('schedules')
            ->where('schedules.cycle_number', $cycleNumber)
            ->where(function ($q) use ($currentAcademicYear) {
                $q->where('schedules.academic_year', $currentAcademicYear)->orWhereNull('schedules.academic_year');
            });

        if ($scope === 'filtered') {
            $search = trim((string) $request->input('q', ''));
            $selectedJurusan = trim((string) $request->input('jurusan', ''));
            $selectedTingkat = trim((string) $request->input('tingkat', ''));
            $selectedRombel = trim((string) $request->input('rombel', ''));
            $selectedKelas = trim((string) $request->input('kelas', ''));

            if ($search !== '') {
                $query->leftJoin('users', 'schedules.teacher_id', '=', 'users.id');
                $query->where(function ($q) use ($search) {
                    $q->where('schedules.subject', 'like', "%{$search}%")
                        ->orWhere('users.fullname', 'like', "%{$search}%")
                        ->orWhere('schedules.class_name', 'like', "%{$search}%")
                        ->orWhere('schedules.day_of_week', 'like', "%{$search}%");
                });
            }

            if (! empty($selectedJurusan)) {
                $classesInJurusan = DB::table('siswas')
                    ->where('jurusan', $selectedJurusan)
                    ->whereNotNull('kelas')
                    ->pluck('kelas')
                    ->unique()
                    ->all();

                $query->where(function ($q) use ($selectedJurusan, $classesInJurusan) {
                    if (! empty($classesInJurusan)) {
                        $q->whereIn('schedules.class_name', $classesInJurusan);
                    }
                    $q->orWhere('schedules.class_name', 'like', "% {$selectedJurusan}%")
                      ->orWhere('schedules.class_name', 'like', "%{$selectedJurusan}%");
                });
            }

            if (! empty($selectedTingkat)) {
                $query->where(function ($q) use ($selectedTingkat) {
                    $q->where('schedules.class_name', 'like', "{$selectedTingkat} %")
                        ->orWhere('schedules.class_name', 'like', "{$selectedTingkat}-%")
                        ->orWhere('schedules.class_name', '=', $selectedTingkat);
                });
            }

            if (! empty($selectedRombel)) {
                $query->where(function ($q) use ($selectedRombel) {
                    $q->where('schedules.class_name', 'like', "% {$selectedRombel}")
                        ->orWhere('schedules.class_name', 'like', "%-{$selectedRombel}")
                        ->orWhere('schedules.class_name', 'like', "% {$selectedRombel} %");
                    if ($selectedRombel === '1') {
                        $q->orWhere(function ($sub) {
                            $sub->where('schedules.class_name', 'not like', '% 2')
                                ->where('schedules.class_name', 'not like', '% 3')
                                ->where('schedules.class_name', 'not like', '% 4');
                        });
                    }
                });
            }

            if (! empty($selectedKelas)) {
                $query->where('schedules.class_name', $selectedKelas);
            }
        }

        $scheduleIds = (clone $query)->distinct()->pluck('schedules.id')->all();
        $count = count($scheduleIds);

        if ($count === 0) {
            return back()->with('info', 'Tidak ada jadwal yang cocok untuk dihapus.');
        }

        DB::transaction(function () use ($scheduleIds) {
            $journalIds = DB::table('journals')->whereIn('schedule_id', $scheduleIds)->pluck('id')->all();
            if (! empty($journalIds)) {
                DB::table('attendances')->whereIn('journal_id', $journalIds)->delete();
                DB::table('journals')->whereIn('id', $journalIds)->delete();
            }
            DB::table('schedules')->whereIn('id', $scheduleIds)->delete();
        });

        $scopeDesc = $scope === 'filtered' ? ' (sesuai filter pencarian)' : '';
        return redirect()->route('waka.jadwal')->with('success', "Berhasil menghapus {$count} jadwal dari Putaran {$cycleNumber}{$scopeDesc}.");
    })->name('waka.jadwal.destroy.all');

    Route::delete('/waka/jadwal/{id}', function (int $id) use ($ensureScheduleConfig, $currentAcademicYear) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $schedule = DB::table('schedules')
            ->where('id', $id)
            ->where(function ($query) use ($currentAcademicYear) {
                $query->where('academic_year', $currentAcademicYear)->orWhereNull('academic_year');
            })
            ->first();
        abort_if(! $schedule, 404);
        $config = $ensureScheduleConfig();
        $lockColumn = (int) $schedule->cycle_number === 2 ? 'cycle_two_locked' : 'cycle_one_locked';
        abort_if((bool) ($config->{$lockColumn} ?? false), 423, 'Putaran ini sedang dikunci.');

        if (DB::table('journals')->where('schedule_id', $id)->exists()) {
            return back()->withErrors(['schedule' => 'Jadwal sudah digunakan dalam jurnal dan tidak dapat dihapus.']);
        }

        DB::table('schedules')->where('id', $id)->delete();

        return redirect()->route('waka.jadwal')->with('success', 'Jadwal berhasil dihapus.');
    })->name('waka.jadwal.destroy');

    Route::post('/waka/ruangan', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $data = $request->validate([
            'nama_ruangan' => ['required', 'string', 'max:100', 'unique:ruangan,nama_ruangan'],
            'jenis' => ['required', Rule::in(['Ruangan', 'Lab'])],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);
        DB::table('ruangan')->insert([...$data, 'created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('waka.jadwal')->with('success', 'Ruangan berhasil ditambahkan.');
    })->name('waka.ruangan.store');

    Route::patch('/waka/ruangan/{id}', function (Request $request, int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        abort_if(! DB::table('ruangan')->where('id_ruangan', $id)->exists(), 404);
        $data = $request->validate([
            'nama_ruangan' => ['required', 'string', 'max:100', Rule::unique('ruangan', 'nama_ruangan')->ignore($id, 'id_ruangan')],
            'jenis' => ['required', Rule::in(['Ruangan', 'Lab'])],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);
        DB::table('ruangan')->where('id_ruangan', $id)->update([...$data, 'updated_at' => now()]);

        return redirect()->route('waka.jadwal')->with('success', 'Ruangan berhasil diperbarui.');
    })->name('waka.ruangan.update');

    Route::delete('/waka/ruangan/{id}', function (int $id) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        abort_if(! DB::table('ruangan')->where('id_ruangan', $id)->exists(), 404);
        if (DB::table('schedules')->where('room_id', $id)->exists()) {
            return back()->withErrors(['room' => 'Ruangan masih dipakai jadwal mengajar sehingga tidak dapat dihapus.']);
        }
        DB::table('ruangan')->where('id_ruangan', $id)->delete();

        return redirect()->route('waka.jadwal')->with('success', 'Ruangan berhasil dihapus.');
    })->name('waka.ruangan.destroy');

    Route::get('/waka/presensi', function () {
        return redirect()->route('waka.laporan');
    })->name('waka.presensi');

    Route::get('/waka/laporan', function (Request $request) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['hari', 'minggu', 'bulan'])],
            'date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        $period = $filters['period'] ?? 'hari';
        $referenceDate = Carbon::parse($filters['date'] ?? today()->toDateString());
        [$reportStart, $reportEnd] = match ($period) {
            'minggu' => [$referenceDate->copy()->startOfWeek(Carbon::MONDAY), $referenceDate->copy()->endOfWeek(Carbon::SUNDAY)],
            'bulan' => [$referenceDate->copy()->startOfMonth(), $referenceDate->copy()->endOfMonth()],
            default => [$referenceDate->copy()->startOfDay(), $referenceDate->copy()->endOfDay()],
        };
        $siswas = Schema::hasTable('siswas')
            ? DB::table('siswas')->select('id', 'name', 'kelas')->orderBy('name')->get()
            : collect();
        $gurus = Schema::hasTable('users')
            ? DB::table('users')->whereIn('role_id', [2, 4])->select('id', 'fullname', 'username')->orderBy('fullname')->get()
            : collect();
        $jadwals = Schema::hasTable('schedules')
            ? DB::table('schedules')->select('id', 'teacher_id')->get()
            : collect();
        $journals = Schema::hasTable('journals')
            ? DB::table('journals')->select('id', 'schedule_id')->get()
            : collect();
        $attendances = Schema::hasTable('attendances')
            ? DB::table('attendances')
                ->whereBetween('date', [$reportStart->toDateString(), $reportEnd->toDateString()])
                ->select('id', 'student_id', 'status', 'date')
                ->get()
            : collect();
        $leaveRequests = Schema::hasTable('leave_requests')
            ? DB::table('leave_requests')
                ->leftJoin('siswas', 'leave_requests.student_id', '=', 'siswas.id')
                ->leftJoin('users as verifier', 'leave_requests.verified_by', '=', 'verifier.id')
                ->select('leave_requests.*', 'siswas.name as student_name', 'siswas.kelas', 'verifier.fullname as verifier_name')
                ->orderByDesc('leave_requests.start_date')
                ->orderByDesc('leave_requests.id')
                ->get()
            : collect();

        $jadwalsByTeacher = $jadwals->groupBy('teacher_id');
        $journalScheduleIds = $journals->pluck('schedule_id')->flip();
        $guruJadwalCounts = [];
        $guruJurnalCounts = [];
        foreach ($gurus as $g) {
            $tJadwals = $jadwalsByTeacher->get($g->id, collect());
            $guruJadwalCounts[$g->id] = $tJadwals->count();
            $guruJurnalCounts[$g->id] = $tJadwals->filter(fn ($j) => isset($journalScheduleIds[$j->id]))->count();
        }

        $studentAttendanceCounts = [];
        $attendancesByStudent = $attendances->groupBy('student_id');
        foreach ($attendancesByStudent as $sId => $records) {
            $h = 0; $i = 0; $s = 0; $a = 0;
            foreach ($records as $rec) {
                match ($rec->status) {
                    'Hadir' => $h++,
                    'Izin' => $i++,
                    'Sakit' => $s++,
                    'Alpha' => $a++,
                    default => null,
                };
            }
            $studentAttendanceCounts[$sId] = [
                'hadir' => $h,
                'izin' => $i,
                'sakit' => $s,
                'alpha' => $a,
                'total' => $records->count(),
            ];
        }

        return view('waka.laporan', [
            'user' => Auth::user(),
            'siswas' => $siswas,
            'gurus' => $gurus,
            'jadwals' => $jadwals,
            'journals' => $journals,
            'attendances' => $attendances,
            'leaveRequests' => $leaveRequests,
            'period' => $period,
            'referenceDate' => $referenceDate,
            'reportStart' => $reportStart,
            'reportEnd' => $reportEnd,
            'totalSiswa' => $siswas->count(),
            'totalGuru' => $gurus->count(),
            'totalJadwal' => $jadwals->count(),
            'totalPresensi' => $attendances->count(),
            'guruJadwalCounts' => $guruJadwalCounts,
            'guruJurnalCounts' => $guruJurnalCounts,
            'studentAttendanceCounts' => $studentAttendanceCounts,
        ]);
    })->name('waka.laporan');

    Route::get('/waka/laporan/guru/{guruId}', function (int $guruId) {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 1, 403);
        $guru = DB::table('users')->where('id', $guruId)->whereIn('role_id', [2, 4])->first();
        abort_if(! $guru, 404);

        $journals = DB::table('journals')
            ->join('schedules', 'journals.schedule_id', '=', 'schedules.id')
            ->where('schedules.teacher_id', $guruId)
            ->select('journals.*', 'schedules.subject', 'schedules.day_of_week', 'schedules.class_id')
            ->orderByDesc('journals.date')
            ->orderByDesc('journals.id')
            ->get();

        return view('waka.laporan_guru', [
            'user' => Auth::user(),
            'guru' => $guru,
            'journals' => $journals,
        ]);
    })->name('waka.laporan.guru');

    Route::get('/guru/isi-jadwal', function () use ($ensureScheduleConfig, $currentAcademicYear) {
        abort_unless(in_array((int) (Auth::user()->role_id ?? 0), [2, 4], true), 403);
        $teacherId = Auth::user()->getKey();
        $config = $ensureScheduleConfig();
        $activeCycle = (int) ($config->active_cycle ?? 1);
        $lockedColumn = $activeCycle === 2 ? 'cycle_two_locked' : 'cycle_one_locked';
        $scheduleLocked = (bool) ($config->{$lockedColumn} ?? false);
        $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5];
        $jadwals = Schema::hasTable('schedules')
            ? DB::table('schedules')
                ->leftJoin('ruangan', 'schedules.room_id', '=', 'ruangan.id_ruangan')
                ->select('schedules.*', 'ruangan.nama_ruangan', 'ruangan.jenis as room_type')
                ->where('schedules.teacher_id', $teacherId)
                ->where('schedules.cycle_number', $activeCycle)
                ->where(function ($query) use ($currentAcademicYear) {
                    $query->where('schedules.academic_year', $currentAcademicYear)->orWhereNull('schedules.academic_year');
                })
                ->get()
                ->sortBy(function ($item) use ($dayOrder) {
                    return ($dayOrder[$item->day_of_week] ?? 99) . '_' . $item->start_time;
                })
                ->values()
            : collect();

        $rooms = DB::table('ruangan')->orderBy('nama_ruangan')->get(['id_ruangan', 'nama_ruangan']);

        return view('guru.isi_jadwal', [
            'user' => Auth::user(),
            'jadwals' => $jadwals,
            'rooms' => $rooms,
            'activeCycle' => $activeCycle,
            'scheduleLocked' => $scheduleLocked,
            'academicYear' => $currentAcademicYear,
        ]);
    })->name('guru.jadwal');

    Route::get('/guru/jadwal/export-pdf', function () use ($currentAcademicYear) {
        $roleId = (int) (Auth::user()->role_id ?? 0);
        abort_unless(in_array($roleId, [2, 4], true), 403);

        $activeCycle = (int) (DB::table('system_configurations')->value('active_cycle') ?? 1);
        $userId = Auth::user()->getKey();
        $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5];

        $jadwals = DB::table('schedules')
            ->leftJoin('users', 'schedules.teacher_id', '=', 'users.id')
            ->leftJoin('ruangan', 'schedules.room_id', '=', 'ruangan.id_ruangan')
            ->select('schedules.*', 'users.fullname as guru_name', 'ruangan.nama_ruangan', 'ruangan.jenis as room_type')
            ->where('schedules.cycle_number', $activeCycle)
            ->where('schedules.teacher_id', $userId)
            ->where(function ($query) use ($currentAcademicYear) {
                $query->where('schedules.academic_year', $currentAcademicYear)->orWhereNull('schedules.academic_year');
            })
            ->orderBy('schedules.day_of_week')
            ->orderBy('schedules.start_time')
            ->get()
            ->sortBy(function ($item) use ($dayOrder) {
                return ($dayOrder[$item->day_of_week] ?? 99) . '_' . $item->start_time;
            })
            ->values();

        return view('pdf.jadwal', [
            'jadwals' => $jadwals,
            'activeCycle' => $activeCycle,
            'filterTeacherName' => Auth::user()->fullname ?? Auth::user()->username,
            'academicYear' => $currentAcademicYear,
            'pageTitle' => 'Jadwal Mengajar Guru - ' . (Auth::user()->fullname ?? Auth::user()->username),
            'documentTitle' => 'JADWAL MENGAJAR GURU',
        ]);
    })->name('guru.jadwal.pdf');

    // Guru tidak diizinkan input jadwal — hanya bisa melihat di dashboard
    Route::post('/guru/isi-jadwal', function () {
        abort(403, 'Guru hanya diperbolehkan mengisi jurnal.');
    })->name('guru.jadwal.store');

    Route::get('/guru/isi-jurnal', function (Request $request) use ($currentAcademicYear, $canManageWaliClasses) {
        $roleId = (int) (Auth::user()->role_id ?? 0);
        abort_unless(in_array($roleId, [2, 4], true), 403);
        $userId = Auth::user()->getKey();
        $isKesiswaan = $roleId === 4;
        $teacherScheduleCount = Schema::hasTable('schedules')
            ? DB::table('schedules')->where('teacher_id', $userId)->count()
            : 0;
        $filterByTeacher = ! $isKesiswaan || $teacherScheduleCount > 0;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $filterDate = $request->query('date');
        if ($filterDate && ! $startDate && ! $endDate) {
            $startDate = $filterDate;
            $endDate = $filterDate;
        }

        $jadwals = Schema::hasTable('schedules')
            ? DB::table('schedules')
                ->when($filterByTeacher, fn ($query) => $query->where('teacher_id', $userId))
                ->where(function ($query) use ($currentAcademicYear) {
                    $query->where('academic_year', $currentAcademicYear)->orWhereNull('academic_year');
                })
                ->get()
            : collect();

        $allJournals = Schema::hasTable('journals')
            ? DB::table('journals')
                ->leftJoin('schedules', 'journals.schedule_id', '=', 'schedules.id')
                ->select('journals.*', 'schedules.subject', 'schedules.class_name', 'schedules.day_of_week')
                ->when($filterByTeacher, fn ($query) => $query->where('schedules.teacher_id', $userId))
                ->when(! $filterByTeacher, fn ($query) => $query->where('journals.created_by', $userId))
                ->when($filterByTeacher, fn ($query) => $query->where(function ($authorQuery) use ($userId) {
                    $authorQuery->whereNull('journals.created_by')
                        ->orWhere('journals.created_by', $userId);
                }))
                ->orderByDesc('journals.date')
                ->get()
            : collect();

        $journals = $allJournals->when($startDate, function ($collection) use ($startDate) {
            return $collection->where('date', '>=', $startDate);
        })->when($endDate, function ($collection) use ($endDate) {
            return $collection->where('date', '<=', $endDate);
        })->values();
        $editJournal = null;
        if ($request->filled('edit')) {
            $editJournal = DB::table('journals')
                ->join('schedules', 'journals.schedule_id', '=', 'schedules.id')
                ->when($filterByTeacher, fn ($query) => $query->where('schedules.teacher_id', $userId))
                ->where(function ($query) use ($filterByTeacher, $userId) {
                    if (! $filterByTeacher) {
                        $query->where('journals.created_by', $userId);
                    } else {
                        $query->whereNull('journals.created_by')
                            ->orWhere('journals.created_by', $userId);
                    }
                })
                ->where('journals.id', $request->query('edit'))
                ->select('journals.*')
                ->first();
            abort_if(! $editJournal, 404);
            if (! $jadwals->contains('id', $editJournal->schedule_id)) {
                $jadwals->push(DB::table('schedules')->where('id', $editJournal->schedule_id)->first());
            }
        }

        $studentsBySchedule = [];
        if (Schema::hasTable('siswas') && $jadwals->isNotEmpty()) {
            $classNames = $jadwals->map(fn ($s) => (string) ($s->class_name ?? $s->class_id ?? ''))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $studentsByClass = empty($classNames)
                ? collect()
                : DB::table('siswas')
                    ->whereIn('kelas', $classNames)
                    ->select('id', 'name', 'kelas')
                    ->orderBy('name')
                    ->get()
                    ->groupBy('kelas');

            foreach ($jadwals as $schedule) {
                $className = (string) ($schedule->class_name ?? $schedule->class_id ?? '');
                $targetStudents = collect($studentsByClass->get($className, []));
                $studentsBySchedule[$schedule->id] = $targetStudents
                    ->map(fn ($s) => [
                        'id' => is_array($s) ? $s['id'] : $s->id,
                        'name' => is_array($s) ? $s['name'] : $s->name,
                    ])
                    ->values()
                    ->all();
            }
        }
        $existingAttendance = $editJournal && Schema::hasTable('attendances')
            ? DB::table('attendances')->where('journal_id', $editJournal->id)->pluck('status', 'student_id')
            : collect();
        $attendanceByJournal = Schema::hasTable('attendances') && $journals->isNotEmpty()
            ? DB::table('attendances')
                ->leftJoin('siswas', 'attendances.student_id', '=', 'siswas.id')
                ->whereIn('attendances.journal_id', $journals->pluck('id'))
                ->select('attendances.journal_id', 'attendances.status', 'siswas.name as student_name')
                ->orderBy('siswas.name')
                ->get()
                ->groupBy('journal_id')
            : collect();

        $approvedLeaves = Schema::hasTable('leave_requests')
            ? DB::table('leave_requests')
                ->where('status_approval', 'Approved')
                ->select('id', 'student_id', 'start_date', 'end_date', 'reason', 'document_path', 'leave_type')
                ->get()
            : collect();

        $schedulesWithJournals = $journals->pluck('schedule_id')->unique();
        $hasTeacherSchedules = $filterByTeacher && $jadwals->isNotEmpty();
        $unfilledSchedules = $hasTeacherSchedules
            ? $jadwals->reject(fn ($schedule) => $schedulesWithJournals->contains($schedule->id))->values()
            : collect();
        $isWali = $canManageWaliClasses($userId);
        $canTakeAttendance = ($roleId === 2 && $isWali) || ($isKesiswaan && $hasTeacherSchedules);

        $daysMap = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $todayDayName = $daysMap[(int) date('w')] ?? 'Hari Ini';

        return view('guru.isi_jurnal', [
            'user' => Auth::user(),
            'jadwals' => $jadwals,
            'journals' => $journals,
            'editJournal' => $editJournal,
            'roleId' => $roleId,
            'isKesiswaan' => $isKesiswaan,
            'hasTeacherSchedules' => $hasTeacherSchedules,
            'isWali' => $isWali,
            'canTakeAttendance' => $canTakeAttendance,
            'studentsBySchedule' => $studentsBySchedule,
            'existingAttendance' => $existingAttendance,
            'attendanceByJournal' => $attendanceByJournal,
            'approvedLeaves' => $approvedLeaves,
            'unfilledSchedules' => $unfilledSchedules,
            'schedulesWithJournals' => $schedulesWithJournals,
            'todayDayName' => $todayDayName,
            'daysMap' => $daysMap,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalAllJournals' => $allJournals->count(),
        ]);
    })->name('guru.jurnal');

    Route::post('/guru/isi-jurnal', function (Request $request) use ($currentAcademicYear, $canManageWaliClasses) {
        $roleId = (int) (Auth::user()->role_id ?? 0);
        abort_unless(in_array($roleId, [2, 4], true), 403);
        $userId = Auth::user()->getKey();
        $isKesiswaan = $roleId === 4;
        $isWali = $canManageWaliClasses($userId);
        $validated = $request->validate([
            'schedule_id' => [
                'required',
                'integer',
                Rule::exists('schedules', 'id')->where(function ($query) use ($isKesiswaan, $userId, $currentAcademicYear) {
                    if (! $isKesiswaan) {
                        $query->where('teacher_id', $userId);
                    }
                    $query->where(function ($yearQuery) use ($currentAcademicYear) {
                            $yearQuery->where('academic_year', $currentAcademicYear)->orWhereNull('academic_year');
                        });
                }),
            ],
            'date' => ['required', 'date'],
            'topic_description' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $daysMap = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $dateDayIndex = (int) date('w', strtotime($validated['date']));
        $expectedDayName = $daysMap[$dateDayIndex] ?? '';
        $schedule = DB::table('schedules')->where('id', $validated['schedule_id'])->first();
        if ($schedule && ! empty($schedule->day_of_week) && $expectedDayName !== '') {
            if (strcasecmp(trim($schedule->day_of_week), trim($expectedDayName)) !== 0) {
                throw ValidationException::withMessages([
                    'schedule_id' => "Mata pelajaran/jadwal yang dipilih ({$schedule->day_of_week} - {$schedule->subject}) tidak sesuai dengan hari dari tanggal jurnal ({$expectedDayName}). Harap pilih jadwal pada hari {$expectedDayName}.",
                ]);
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photoPath = $request->file('photo')->store('jurnal_photos', 'public');
        }
        $teacherHasSchedule = DB::table('schedules')->where('id', $validated['schedule_id'])->where('teacher_id', $userId)->exists();
        $canTakeAttendance = $isWali || ($isKesiswaan && $teacherHasSchedule);
        $attendanceRows = [];
        if ($canTakeAttendance) {
            $schedule = DB::table('schedules')->where('id', $validated['schedule_id'])->first();
            $className = $schedule->class_name ?? $schedule->class_id ?? null;
            $expectedStudentIds = $className === null || $className === ''
                ? []
                : DB::table('siswas')->where('kelas', (string) $className)->pluck('id')->map(fn ($id) => (int) $id)->all();

            if ($expectedStudentIds !== []) {
                $attendance = $request->validate([
                    'student_ids' => ['required', 'array', 'min:1'],
                    'student_ids.*' => ['required', 'integer', 'distinct'],
                    'statuses' => ['required', 'array'],
                    'statuses.*' => ['required', 'in:Hadir,Sakit,Izin,Alpha'],
                ]);
                $submittedStudentIds = array_map('intval', array_values($attendance['student_ids']));
                $submittedStatuses = array_values($attendance['statuses']);
                if (count($submittedStudentIds) !== count($submittedStatuses)
                    || array_diff($expectedStudentIds, $submittedStudentIds) !== []
                    || array_diff($submittedStudentIds, $expectedStudentIds) !== []) {
                    throw ValidationException::withMessages([
                        'student_ids' => 'Status presensi harus diisi tepat untuk semua siswa di kelas jadwal ini.',
                    ]);
                }
                foreach ($submittedStudentIds as $index => $studentId) {
                    $attendanceRows[] = ['student_id' => $studentId, 'status' => $submittedStatuses[$index]];
                }
            }
        }
        DB::transaction(function () use ($validated, $photoPath, $attendanceRows) {
            $now = now();
            $journalId = DB::table('journals')->insertGetId([
                'schedule_id' => $validated['schedule_id'],
                'date' => $validated['date'],
                'topic_description' => $validated['topic_description'],
                'photo_path' => $photoPath,
                'created_by' => Auth::user()->getKey(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($attendanceRows as $row) {
                DB::table('attendances')->insert([
                    'journal_id' => $journalId,
                    'student_id' => $row['student_id'],
                    'status' => $row['status'],
                    'date' => $validated['date'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        return redirect()->route('guru.jurnal')->with('success', $canTakeAttendance
            ? 'Jurnal dan presensi siswa berhasil disimpan.'
            : 'Jurnal berhasil disimpan ke database!');
    })->name('guru.jurnal.store');

    Route::patch('/guru/isi-jurnal/{id}', function (Request $request, int $id) use ($currentAcademicYear, $canManageWaliClasses) {
        $roleId = (int) (Auth::user()->role_id ?? 0);
        abort_unless(in_array($roleId, [2, 4], true), 403);
        $userId = Auth::user()->getKey();
        $isKesiswaan = $roleId === 4;
        $isWali = $canManageWaliClasses($userId);
        $journal = DB::table('journals')
            ->join('schedules', 'journals.schedule_id', '=', 'schedules.id')
            ->when(! $isKesiswaan, fn ($query) => $query->where('schedules.teacher_id', $userId))
            ->where(function ($query) use ($isKesiswaan, $userId) {
                if ($isKesiswaan) {
                    $query->where('journals.created_by', $userId);
                } else {
                    $query->whereNull('journals.created_by')
                        ->orWhere('journals.created_by', $userId);
                }
            })
            ->where('journals.id', $id)
            ->select('journals.*')
            ->first();
        abort_if(! $journal, 404);

        $validated = $request->validate([
            'schedule_id' => [
                'required',
                'integer',
                Rule::exists('schedules', 'id')->where(function ($query) use ($isKesiswaan, $userId, $currentAcademicYear, $journal) {
                    if (! $isKesiswaan) {
                        $query->where('teacher_id', $userId);
                    }
                    $query->where(function ($yearQuery) use ($currentAcademicYear, $journal) {
                        $yearQuery->where('academic_year', $currentAcademicYear)
                            ->orWhereNull('academic_year')
                            ->orWhere('id', $journal->schedule_id);
                    });
                }),
            ],
            'date' => ['required', 'date'],
            'topic_description' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $daysMap = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $dateDayIndex = (int) date('w', strtotime($validated['date']));
        $expectedDayName = $daysMap[$dateDayIndex] ?? '';
        $schedule = DB::table('schedules')->where('id', $validated['schedule_id'])->first();
        if ($schedule && ! empty($schedule->day_of_week) && $expectedDayName !== '') {
            if (strcasecmp(trim($schedule->day_of_week), trim($expectedDayName)) !== 0) {
                throw ValidationException::withMessages([
                    'schedule_id' => "Mata pelajaran/jadwal yang dipilih ({$schedule->day_of_week} - {$schedule->subject}) tidak sesuai dengan hari dari tanggal jurnal ({$expectedDayName}). Harap pilih jadwal pada hari {$expectedDayName}.",
                ]);
            }
        }

        $attendanceRows = null;
        $teacherHasSchedule = DB::table('schedules')->where('id', $validated['schedule_id'])->where('teacher_id', $userId)->exists();
        $canTakeAttendance = $isWali || ($isKesiswaan && $teacherHasSchedule);
        if ($canTakeAttendance) {
            $schedule = DB::table('schedules')->where('id', $validated['schedule_id'])->first();
            $className = $schedule->class_name ?? $schedule->class_id ?? null;
            $expectedStudentIds = $className === null || $className === ''
                ? []
                : DB::table('siswas')->where('kelas', (string) $className)->pluck('id')->map(fn ($studentId) => (int) $studentId)->all();
            $attendanceRows = [];
            if ($expectedStudentIds !== []) {
                $attendance = $request->validate([
                    'student_ids' => ['required', 'array', 'min:1'],
                    'student_ids.*' => ['required', 'integer', 'distinct'],
                    'statuses' => ['required', 'array'],
                    'statuses.*' => ['required', 'in:Hadir,Sakit,Izin,Alpha'],
                ]);
                $submittedStudentIds = array_map('intval', array_values($attendance['student_ids']));
                $submittedStatuses = array_values($attendance['statuses']);
                if (count($submittedStudentIds) !== count($submittedStatuses)
                    || array_diff($expectedStudentIds, $submittedStudentIds) !== []
                    || array_diff($submittedStudentIds, $expectedStudentIds) !== []) {
                    throw ValidationException::withMessages([
                        'student_ids' => 'Status presensi harus diisi tepat untuk semua siswa di kelas jadwal ini.',
                    ]);
                }
                foreach ($submittedStudentIds as $index => $studentId) {
                    $attendanceRows[] = ['student_id' => $studentId, 'status' => $submittedStatuses[$index]];
                }
            }
        }
        $photoPath = $journal->photo_path;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photoPath = $request->file('photo')->store('jurnal_photos', 'public');
        }

        DB::transaction(function () use ($id, $validated, $photoPath, $journal, $userId, $attendanceRows) {
            DB::table('journals')->where('id', $id)->update([
                'schedule_id' => $validated['schedule_id'],
                'date' => $validated['date'],
                'topic_description' => $validated['topic_description'],
                'photo_path' => $photoPath,
                'created_by' => $journal->created_by ?? $userId,
                'updated_at' => now(),
            ]);

            if ($attendanceRows !== null) {
                DB::table('attendances')->where('journal_id', $id)->delete();
                $now = now();
                foreach ($attendanceRows as $row) {
                    DB::table('attendances')->insert([
                        'journal_id' => $id,
                        'student_id' => $row['student_id'],
                        'status' => $row['status'],
                        'date' => $validated['date'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });

        return redirect()->route('guru.jurnal')->with('success', $canTakeAttendance
            ? 'Jurnal dan presensi siswa berhasil diperbarui.'
            : 'Jurnal berhasil diperbarui.');
    })->name('guru.jurnal.update');

    Route::get('/guru/presensi', function (Request $request) use ($waliClassNames, $canManageWaliClasses) {
        $userId = (int) Auth::user()->getKey();
        $roleId = (int) (Auth::user()->role_id ?? 0);
        abort_unless(in_array($roleId, [1, 2, 4, 5], true), 403);

        $hasTeacherSchedules = Schema::hasTable('schedules') && DB::table('schedules')->where('teacher_id', $userId)->exists();
        $isWali = $canManageWaliClasses($userId);

        // Kesiswaan teacher (role 4) who is not a wali redirects to guru.jurnal per test
        if ($roleId === 4 && ! $isWali) {
            return redirect()->route('guru.jurnal');
        }

        $classNames = $waliClassNames($userId);
        if ($classNames->isEmpty()) {
            $scheduleClasses = DB::table('schedules')->where('teacher_id', $userId)->pluck('class_name')->filter()->unique();
            if ($scheduleClasses->isNotEmpty()) {
                $classNames = $scheduleClasses;
            } elseif (in_array(Auth::user()->username ?? '', ['guru', 'wali'], true)) {
                $rpl2 = DB::table('school_classes')->where('name', 'XII RPL 2')->value('name');
                if ($rpl2) {
                    $classNames = collect([$rpl2]);
                }
            }
        }
        if ($classNames->isEmpty()) {
            return view('guru.presensi', [
                'user' => Auth::user(),
                'classNames' => collect(),
                'selectedClass' => null,
                'students' => collect(),
                'studentStats' => collect(),
                'totalSiswa' => 0,
                'totalHadir' => 0,
                'totalSakit' => 0,
                'totalIzin' => 0,
                'totalAlpha' => 0,
                'totalPresensi' => 0,
                'avgAttendanceRate' => 100,
                'journals' => collect(),
                'siswas' => collect(),
                'presensiList' => collect(),
                'search' => '',
                'statusFilter' => 'all',
                'startDate' => null,
                'endDate' => null,
            ]);
        }

        $selectedClass = $request->input('kelas');
        if (! $selectedClass || (! $classNames->contains($selectedClass) && $selectedClass !== 'all')) {
            $selectedClass = $classNames->first();
        }

        $search = trim((string) $request->input('q', ''));
        $statusFilter = (string) $request->input('status', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $targetClasses = ($selectedClass === 'all') ? $classNames->all() : [$selectedClass];

        $studentsQuery = DB::table('siswas')
            ->whereIn('kelas', $targetClasses);

        if ($search !== '') {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $studentsQuery->orderBy('name')->get();
        $studentIds = $students->pluck('id')->all();

        // Query attendances for students
        $attendanceQuery = DB::table('attendances')
            ->leftJoin('journals', 'attendances.journal_id', '=', 'journals.id')
            ->leftJoin('schedules', 'journals.schedule_id', '=', 'schedules.id')
            ->whereIn('attendances.student_id', $studentIds);

        if ($startDate) {
            $attendanceQuery->whereDate('attendances.date', '>=', $startDate);
        }
        if ($endDate) {
            $attendanceQuery->whereDate('attendances.date', '<=', $endDate);
        }

        $attendances = $attendanceQuery->select(
            'attendances.id',
            'attendances.student_id',
            'attendances.status',
            'attendances.date',
            'attendances.notes',
            'attendances.photo_path',
            'journals.id as journal_id',
            'schedules.subject',
            'journals.topic_description'
        )->orderByDesc('attendances.date')->get();

        $groupedByStudent = $attendances->groupBy('student_id');

        $studentStats = $students->map(function ($s) use ($groupedByStudent) {
            $records = $groupedByStudent->get($s->id, collect());
            $hadir = $records->where('status', 'Hadir')->count();
            $sakit = $records->where('status', 'Sakit')->count();
            $izin = $records->where('status', 'Izin')->count();
            $alpha = $records->where('status', 'Alpha')->count();
            $total = $hadir + $sakit + $izin + $alpha;
            $rate = $total > 0 ? round(($hadir / $total) * 100) : 100;

            return (object) [
                'id' => $s->id,
                'name' => $s->name,
                'kelas' => $s->kelas,
                'nisn' => $s->nisn ?? null,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpha' => $alpha,
                'total' => $total,
                'rate' => $rate,
                'records' => $records,
            ];
        });

        // Totals across the filtered students
        $totalSiswa = $students->count();
        $totalHadir = $studentStats->sum('hadir');
        $totalSakit = $studentStats->sum('sakit');
        $totalIzin = $studentStats->sum('izin');
        $totalAlpha = $studentStats->sum('alpha');
        $totalPresensi = $totalHadir + $totalSakit + $totalIzin + $totalAlpha;
        $avgAttendanceRate = $totalPresensi > 0 ? round(($totalHadir / $totalPresensi) * 100) : 100;

        // Apply status filter for table display
        if ($statusFilter === 'alpha') {
            $studentStats = $studentStats->filter(fn ($s) => $s->alpha > 0);
        } elseif ($statusFilter === 'izin') {
            $studentStats = $studentStats->filter(fn ($s) => $s->izin > 0);
        } elseif ($statusFilter === 'sakit') {
            $studentStats = $studentStats->filter(fn ($s) => $s->sakit > 0);
        } elseif ($statusFilter === 'hadir_kurang') {
            $studentStats = $studentStats->filter(fn ($s) => $s->rate < 85 && $s->total > 0);
        }

        $journals = DB::table('journals')
            ->join('schedules', 'journals.schedule_id', '=', 'schedules.id')
            ->where('schedules.teacher_id', $userId)
            ->whereIn('schedules.class_name', $classNames)
            ->select('journals.id', 'journals.date', 'journals.topic_description', 'schedules.subject', 'schedules.class_name')
            ->orderByDesc('journals.date')
            ->get();

        $presensiList = $attendances->map(function ($a) use ($students) {
            $s = $students->firstWhere('id', $a->student_id);
            $a->student_name = $s->name ?? null;
            return $a;
        });

        return view('guru.presensi', [
            'user' => Auth::user(),
            'classNames' => $classNames,
            'selectedClass' => $selectedClass,
            'students' => $students,
            'studentStats' => $studentStats,
            'totalSiswa' => $totalSiswa,
            'totalHadir' => $totalHadir,
            'totalSakit' => $totalSakit,
            'totalIzin' => $totalIzin,
            'totalAlpha' => $totalAlpha,
            'totalPresensi' => $totalPresensi,
            'avgAttendanceRate' => $avgAttendanceRate,
            'journals' => $journals,
            'siswas' => $students,
            'presensiList' => $presensiList,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    })->name('guru.presensi');

    Route::post('/guru/presensi', function (Request $request) use ($waliClassNames, $canManageWaliClasses) {
        $roleId = (int) (Auth::user()->role_id ?? 0);
        abort_unless(in_array($roleId, [2, 4], true), 403);
        $userId = (int) Auth::user()->getKey();
        $isKesiswaan = $roleId === 4;
        $isWaliTeacher = $canManageWaliClasses($userId);
        $hasTeacherSchedules = Schema::hasTable('schedules') && DB::table('schedules')->where('teacher_id', $userId)->exists();
        abort_unless($isWaliTeacher || ($isKesiswaan && $hasTeacherSchedules) || $hasTeacherSchedules || in_array(Auth::user()->username ?? '', ['guru', 'wali'], true), 403);

        $validated = $request->validate([
            'journal_id' => ['required', 'integer', 'exists:journals,id'],
            'date' => ['required', 'date'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:Hadir,Sakit,Izin,Alpha'],
        ]);

        $journalQuery = DB::table('journals')
                ->join('schedules', 'journals.schedule_id', '=', 'schedules.id')
                ->where('journals.id', $validated['journal_id'])
                ->where('schedules.teacher_id', $userId);
        if ($isWaliTeacher && ! $isKesiswaan) {
            $classNames = $waliClassNames($userId);
            $journalQuery->whereIn('schedules.class_name', $classNames);
        }
        $journal = $journalQuery->select('schedules.class_name')->first();
        abort_if($journal === null, 404);

        if (count($validated['student_ids']) !== count($validated['statuses'])) {
            throw ValidationException::withMessages([
                'statuses' => 'Status kehadiran harus diisi untuk setiap siswa.',
            ]);
        }

        $studentIdsInJournalClass = DB::table('siswas')
            ->where('kelas', $journal->class_name)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        if (array_diff($validated['student_ids'], $studentIdsInJournalClass) !== []) {
            throw ValidationException::withMessages([
                'student_ids' => 'Presensi hanya dapat diisi untuk siswa pada kelas jadwal yang dipilih.',
            ]);
        }

        DB::transaction(function () use ($validated) {
            $studentIds = array_values($validated['student_ids']);
            $statuses = array_values($validated['statuses']);

            foreach ($studentIds as $index => $studentId) {
                $attendance = DB::table('attendances')
                    ->where('journal_id', $validated['journal_id'])
                    ->where('student_id', $studentId);

                $values = [
                    'status' => $statuses[$index],
                    'date' => $validated['date'],
                    'updated_at' => now(),
                ];

                if ($attendance->exists()) {
                    $attendance->update($values);
                } else {
                    DB::table('attendances')->insert(array_merge($values, [
                        'journal_id' => $validated['journal_id'],
                        'student_id' => $studentId,
                        'created_at' => now(),
                    ]));
                }
            }
        });

        return redirect()->route('guru.presensi')->with('success', 'Presensi siswa tersimpan.');
    })->name('guru.presensi.store');

    // Kesiswaan can approve leave requests and review their history.
    Route::get('/kesiswaan/izin', function () {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 4, 403);
        $leaveRequests = Schema::hasTable('leave_requests')
            ? DB::table('leave_requests')
                ->leftJoin('siswas', 'leave_requests.student_id', '=', 'siswas.id')
                ->leftJoin('users as verifier', 'leave_requests.verified_by', '=', 'verifier.id')
                ->select('leave_requests.*', 'siswas.name as student_name', 'siswas.kelas', 'verifier.fullname as verifier_name')
                ->orderByDesc('leave_requests.start_date')
                ->orderByDesc('leave_requests.id')
                ->get()
            : collect();
        $chartMonths = collect(range(5, 0))->map(fn ($monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo));
        $leaveStatistics = [
            'approved' => $leaveRequests->where('status_approval', 'Approved')->count(),
            'rejected' => $leaveRequests->where('status_approval', 'Rejected')->count(),
            'pending' => $leaveRequests->where('status_approval', 'Pending')->count(),
            'labels' => $chartMonths->map(fn ($month) => $month->format('m/Y'))->values(),
            'approvedByMonth' => $chartMonths->map(fn ($month) => $leaveRequests
                ->where('status_approval', 'Approved')
                ->filter(fn ($request) => $request->created_at && Carbon::parse($request->created_at)->format('Y-m') === $month->format('Y-m'))
                ->count())->values(),
            'rejectedByMonth' => $chartMonths->map(fn ($month) => $leaveRequests
                ->where('status_approval', 'Rejected')
                ->filter(fn ($request) => $request->created_at && Carbon::parse($request->created_at)->format('Y-m') === $month->format('Y-m'))
                ->count())->values(),
        ];

        return view('kesiswaan.izin', [
            'user' => Auth::user(),
            'leaveRequests' => $leaveRequests,
            'pendingRequests' => $leaveRequests->where('status_approval', 'Pending'),
            'leaveHistory' => $leaveRequests->whereIn('status_approval', ['Approved', 'Rejected']),
            'leaveStatistics' => $leaveStatistics,
        ]);
    })->name('kesiswaan.izin');

    Route::get('/kesiswaan/izin/updates', function () {
        abort_unless((int) (Auth::user()->role_id ?? 0) === 4, 403);

        $pendingRequests = Schema::hasTable('leave_requests')
            ? DB::table('leave_requests')
                ->leftJoin('siswas', 'leave_requests.student_id', '=', 'siswas.id')
                ->select('leave_requests.id', 'leave_requests.student_id', 'leave_requests.leave_type', 'leave_requests.start_date', 'leave_requests.end_date', 'leave_requests.reason', 'leave_requests.document_path', 'siswas.name as student_name', 'siswas.kelas')
                ->where('leave_requests.status_approval', 'Pending')
                ->orderByDesc('leave_requests.start_date')
                ->orderByDesc('leave_requests.id')
                ->get()
            : collect();

        return response()->json(['data' => $pendingRequests]);
    })->name('kesiswaan.izin.updates');

    Route::patch('/kesiswaan/izin/{id}/approval', function (Request $request, int $id) {
        abort_unless(in_array((int) (Auth::user()->role_id ?? 0), [1, 4], true), 403);
        $status = $request->validate([
            'status_approval' => ['required', 'in:Approved,Rejected'],
        ]);
        $leave = DB::table('leave_requests')->where('id', $id)->first();
        abort_if(! $leave, 404);

        $updated = DB::table('leave_requests')
            ->where('id', $id)
            ->where('status_approval', 'Pending')
            ->update([
                'status_approval' => $status['status_approval'],
                'verified_by' => Auth::user()->getKey(),
                'updated_at' => now(),
            ]);
        abort_if($updated === 0, 409, 'Pengajuan izin ini sudah diproses.');

        if ($status['status_approval'] === 'Approved') {
            App\Http\Controllers\Api\AttendanceApiController::syncApprovedLeaves($leave->student_id);
        }

        $label = $status['status_approval'] === 'Approved' ? 'disetujui' : 'ditolak';

        return redirect()->route('kesiswaan.izin')->with('success', "Pengajuan izin berhasil {$label}.");
    })->name('kesiswaan.izin.approval');

    Route::get('/wali-kelas/presensi', fn () => redirect()->route('guru.presensi'))->name('wali-kelas.presensi');
    Route::post('/wali-kelas/presensi', fn () => redirect()->route('guru.presensi'))->name('wali-kelas.presensi.store');

    Route::get('/wali-kelas/izin', function () use ($waliClassNames, $canManageWaliClasses) {
        abort_unless($canManageWaliClasses((int) Auth::user()->getKey()), 403);
        $classNames = $waliClassNames((int) Auth::user()->getKey());
        $leaveRequests = DB::table('leave_requests')
            ->join('siswas', 'leave_requests.student_id', '=', 'siswas.id')
            ->leftJoin('users as verifier', 'leave_requests.verified_by', '=', 'verifier.id')
            ->whereIn('siswas.kelas', $classNames)
            ->select('leave_requests.*', 'siswas.name as student_name', 'siswas.kelas', 'verifier.fullname as verifier_name')
            ->orderByDesc('leave_requests.start_date')
            ->orderByDesc('leave_requests.id')
            ->get();

        return view('wali_kelas.izin', [
            'user' => Auth::user(),
            'classNames' => $classNames,
            'pendingRequests' => $leaveRequests->where('status_approval', 'Pending'),
            'leaveHistory' => $leaveRequests->whereIn('status_approval', ['Approved', 'Rejected']),
        ]);
    })->name('wali-kelas.izin');

    Route::patch('/wali-kelas/izin/{id}/approval', function (Request $request, int $id) use ($waliClassNames, $canManageWaliClasses) {
        abort_unless($canManageWaliClasses((int) Auth::user()->getKey()), 403);
        $classNames = $waliClassNames((int) Auth::user()->getKey());
        $status = $request->validate([
            'status_approval' => ['required', 'in:Approved,Rejected'],
        ]);
        $requestQuery = DB::table('leave_requests')
            ->join('siswas', 'leave_requests.student_id', '=', 'siswas.id')
            ->where('leave_requests.id', $id)
            ->whereIn('siswas.kelas', $classNames);
        abort_if(! $requestQuery->exists(), 404);

        $updated = DB::table('leave_requests')
            ->where('id', $id)
            ->where('status_approval', 'Pending')
            ->update([
                'status_approval' => $status['status_approval'],
                'verified_by' => Auth::user()->getKey(),
                'updated_at' => now(),
            ]);
        abort_if($updated === 0, 409, 'Pengajuan izin ini sudah diproses.');

        if ($status['status_approval'] === 'Approved') {
            App\Http\Controllers\Api\AttendanceApiController::syncApprovedLeaves();
        }

        return redirect()->route('wali-kelas.izin')->with(
            'success',
            $status['status_approval'] === 'Approved'
                ? 'Pengajuan izin kelas berhasil disetujui.'
                : 'Pengajuan izin kelas berhasil ditolak.'
        );
    })->name('wali-kelas.izin.approval');

    Route::get('/wali-kelas/jurnal', fn () => redirect()->route('guru.jurnal'))->name('wali-kelas.jurnal');
    Route::post('/wali-kelas/jurnal', fn () => redirect()->route('guru.jurnal'))->name('wali-kelas.jurnal.store');
    Route::patch('/wali-kelas/jurnal/{id}', fn () => redirect()->route('guru.jurnal'))->name('wali-kelas.jurnal.update');

    Route::get('/wali-kelas/siswa', function (Request $request) use ($waliClassNames, $canManageWaliClasses) {
        abort_unless($canManageWaliClasses((int) Auth::user()->getKey()), 403);
        $classNames = $waliClassNames((int) Auth::user()->getKey());

        $q = trim((string) $request->input('q', ''));
        $query = DB::table('siswas')
            ->leftJoin('users', 'siswas.id', '=', 'users.student_id')
            ->whereIn('siswas.kelas', $classNames)
            ->select(
                'siswas.id',
                'siswas.name',
                'siswas.nis',
                'siswas.kelas',
                'users.id as account_id',
                'users.username as account_username',
                'users.email as account_email',
                'users.is_active as account_active',
                'users.must_change_password'
            )
            ->orderBy('siswas.name');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('siswas.name', 'like', "%{$q}%")
                    ->orWhere('siswas.nis', 'like', "%{$q}%")
                    ->orWhere('users.username', 'like', "%{$q}%");
            });
        }

        $students = $query->get();

        return view('wali_kelas.siswa', [
            'classNames' => $classNames,
            'students' => $students,
            'user' => Auth::user(),
        ]);
    })->name('wali-kelas.siswa');

    Route::post('/wali-kelas/siswa/{accountId}/reset-password', function (Request $request, int $accountId) use ($waliClassNames, $canManageWaliClasses) {
        abort_unless($canManageWaliClasses((int) Auth::user()->getKey()), 403);
        $classNames = $waliClassNames((int) Auth::user()->getKey());

        $studentAccount = User::where('id', $accountId)
            ->where('role_id', 3)
            ->firstOrFail();

        $studentData = DB::table('siswas')
            ->where('id', $studentAccount->student_id)
            ->whereIn('kelas', $classNames)
            ->first();

        abort_unless($studentData !== null, 403, 'Siswa bukan bagian dari kelas yang Anda ampu.');

        $defaultPassword = config('auth.default_student_password', 'siswa123');
        $temporaryPassword = ($studentData && ! empty($studentData->nis)) ? $studentData->nis : $defaultPassword;
        DB::table('users')->where('id', $studentAccount->id)->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => 1,
            'password_reset_at' => now(),
        ]);

        return redirect()->route('wali-kelas.siswa')->with('reset_info', [
            'message' => 'Kata sandi untuk ' . ($studentAccount->fullname ?: $studentAccount->username) . ' berhasil direset.',
            'temporary_password' => $temporaryPassword,
        ]);
    })->name('wali-kelas.siswa.reset_password');
});
