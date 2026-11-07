<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - {{ $roleName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body class="min-h-screen">
    @php
        $theme = match($roleSlug) {
            'waka' => [
                'sidebar_bg' => 'bg-slate-900',
                'sidebar_hover' => 'hover:bg-slate-800',
                'badge_bg' => 'bg-sky-500/20 text-sky-400 border border-sky-500/30',
                'active_link' => 'bg-sky-600 text-white shadow-md',
                'accent_btn' => 'bg-sky-600 hover:bg-sky-500 text-white shadow-sky-600/30',
                'avatar_grad' => 'from-sky-500 to-indigo-600',
                'dot_color' => 'bg-sky-400',
                'header_badge' => 'bg-sky-100 text-sky-700',
            ],
            'guru' => [
                'sidebar_bg' => 'bg-indigo-950',
                'sidebar_hover' => 'hover:bg-indigo-900',
                'badge_bg' => 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30',
                'active_link' => 'bg-indigo-600 text-white shadow-md',
                'accent_btn' => 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-indigo-600/30',
                'avatar_grad' => 'from-indigo-500 to-purple-600',
                'dot_color' => 'bg-indigo-400',
                'header_badge' => 'bg-indigo-100 text-indigo-700',
            ],
            'siswa' => [
                'sidebar_bg' => 'bg-emerald-950',
                'sidebar_hover' => 'hover:bg-emerald-900',
                'badge_bg' => 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
                'active_link' => 'bg-emerald-600 text-white shadow-md',
                'accent_btn' => 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/30',
                'avatar_grad' => 'from-emerald-500 to-teal-600',
                'dot_color' => 'bg-emerald-400',
                'header_badge' => 'bg-emerald-100 text-emerald-700',
            ],
            'kesiswaan' => [
                'sidebar_bg' => 'bg-amber-950',
                'sidebar_hover' => 'hover:bg-amber-900',
                'badge_bg' => 'bg-amber-500/20 text-amber-300 border border-amber-500/30',
                'active_link' => 'bg-amber-600 text-white shadow-md',
                'accent_btn' => 'bg-amber-600 hover:bg-amber-500 text-white shadow-amber-600/30',
                'avatar_grad' => 'from-amber-500 to-orange-600',
                'dot_color' => 'bg-amber-400',
                'header_badge' => 'bg-amber-100 text-amber-700',
            ],
            default => [
                'sidebar_bg' => 'bg-slate-900',
                'sidebar_hover' => 'hover:bg-slate-800',
                'badge_bg' => 'bg-slate-500/20 text-slate-300 border border-slate-500/30',
                'active_link' => 'bg-blue-600 text-white shadow-md',
                'accent_btn' => 'bg-blue-600 hover:bg-blue-500 text-white shadow-blue-600/30',
                'avatar_grad' => 'from-blue-500 to-indigo-600',
                'dot_color' => 'bg-blue-400',
                'header_badge' => 'bg-blue-100 text-blue-700',
            ]
        };
    @endphp

    <!-- Mobile Top Navigation Bar -->
    <header class="lg:hidden sticky top-0 z-40 flex items-center justify-between {{ $theme['sidebar_bg'] }} px-4 py-3 text-white shadow-md">
        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleSidebar()" aria-label="Buka Menu" class="rounded-xl bg-white/10 p-2 text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/40">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-300">School System</span>
                <h1 class="text-sm font-bold leading-tight text-white">{{ $roleName }}</h1>
            </div>
        </div>
        <a href="{{ route('dashboard.role', ['role' => $dashboardSlug]) }}" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20 transition">
            Dashboard
        </a>
    </header>

    <div class="flex min-h-screen">
        <!-- Backdrop for mobile drawer -->
        <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-xs transition-opacity lg:hidden"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 shrink-0 -translate-x-full flex-col justify-between overflow-y-auto {{ $theme['sidebar_bg'] }} p-6 text-white transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:min-h-screen">
            <div>
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-[0.3em] text-slate-400">School System</div>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            <span>{{ $roleName }}</span>
                        </h2>
                    </div>
                    <button type="button" onclick="toggleSidebar()" aria-label="Tutup Menu" class="rounded-lg p-1.5 text-slate-300 hover:bg-white/10 hover:text-white lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Sesuai Role -->
                <nav class="space-y-1.5 text-sm font-medium">
                    <a href="{{ route('dashboard.role', ['role' => $dashboardSlug]) }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                        @include('partials.waka_nav_icon', ['icon' => 'dashboard'])
                        Dashboard
                    </a>

                    @if($roleSlug === 'waka')
                        <a href="{{ route('waka.siswa') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'students'])
                            Data Siswa
                        </a>
                        <a href="{{ route('waka.guru') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'teachers'])
                            Data Guru
                        </a>
                        <a href="{{ route('waka.accounts') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'accounts'])
                            Kelola Akun & Role
                        </a>
                        <a href="{{ route('waka.jadwal') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'schedule'])
                            Jadwal
                        </a>
                        <a href="{{ route('waka.laporan') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'reports'])
                            Laporan
                        </a>
                    @elseif($roleSlug === 'guru' && $isWaliKelas)
                        <a href="{{ route('wali-kelas.izin') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'verify'])
                            Verifikasi Izin
                        </a>
                        <a href="{{ route('guru.jurnal') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'journal'])
                            Isi Jurnal
                        </a>
                    @elseif($roleSlug === 'guru')
                        <a href="{{ route('guru.jurnal') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'journal'])
                            Isi Jurnal
                        </a>
                    @elseif($roleSlug === 'siswa')
                        <a href="{{ route('siswa.jadwal') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'schedule'])
                            Jadwal
                        </a>
                        <a href="{{ route('siswa.izin') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'leave'])
                            Izin
                        </a>
                    @elseif($roleSlug === 'kesiswaan')
                        <a href="{{ route('kesiswaan.izin') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 {{ $theme['sidebar_hover'] }} transition">
                            @include('partials.waka_nav_icon', ['icon' => 'leave'])
                            Riwayat Izin Siswa
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Box Akun & Logout -->
            <div class="mt-8 rounded-2xl bg-white/10 backdrop-blur p-4 border border-white/10">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Login Sebagai</p>
                <div class="mt-2 text-base font-bold text-white truncate">{{ $user->fullname ?? $user->username }}</div>
                <div class="text-xs text-slate-300 mt-0.5">@<span>{{ $user->username }}</span></div>
                
                <div class="mt-4 pt-3 border-t border-white/10">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-rose-500/90 hover:bg-rose-500 px-3 py-2 text-xs font-bold text-white transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-10 space-y-8 overflow-y-auto max-w-7xl mx-auto">
            <!-- Header Bar -->
            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 md:p-8 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <a href="{{ route('dashboard.role', ['role' => $dashboardSlug]) }}" class="hover:text-slate-600 transition">Dashboard</a>
                        <span>/</span>
                        <span class="text-slate-600">Pengaturan Akun</span>
                    </div>
                    <h2 class="mt-2 text-3xl font-extrabold text-slate-900">Profil & Akun</h2>
                    <p class="text-sm text-slate-500 mt-1">Kelola data profil, nama lengkap, dan kredensial login akun Anda.</p>
                </div>

                <div>
                    <a href="{{ route('dashboard.role', ['role' => $dashboardSlug]) }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

            <!-- Notifikasi Sukses -->
            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 shadow-sm flex items-start gap-3">
                    <div class="rounded-full bg-emerald-500 text-white p-1 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-emerald-900">Pembaruan Berhasil</h4>
                        <p class="text-sm text-emerald-700 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Notifikasi Error Validasi -->
            @if($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50/90 p-4 shadow-sm flex items-start gap-3">
                    <div class="rounded-full bg-rose-500 text-white p-1 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-rose-900">Terdapat Kesalahan</h4>
                        <ul class="mt-1 list-disc list-inside text-sm text-rose-700 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Profile Summary Card -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 md:p-8 shadow-sm">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <!-- Avatar Inisial -->
                    <div class="h-24 w-24 rounded-3xl bg-gradient-to-br {{ $theme['avatar_grad'] }} flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-indigo-500/10 shrink-0">
                        {{ strtoupper(substr($user->fullname ?? $user->username, 0, 2)) }}
                    </div>

                    <div class="flex-1 text-center sm:text-left space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 justify-center sm:justify-start">
                            <h3 class="text-2xl font-bold text-slate-900">{{ $user->fullname }}</h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $theme['header_badge'] }}">
                                {{ $roleName }}
                            </span>
                            @if($isWaliKelas)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-sky-100 text-sky-700 text-xs font-bold uppercase tracking-wider">
                                    Wali Kelas
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 font-medium">Username: <span class="font-mono text-slate-700 font-semibold">@<span>{{ $user->username }}</span></span></p>

                        <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-slate-500">
                            <div class="flex items-center gap-1.5 rounded-xl border px-3 py-1.5 {{ $user->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                                <span class="h-2 w-2 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                Status Akun: <strong>{{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}</strong>
                            </div>
                            <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/60">
                                ID Pengguna: <strong class="text-slate-700 font-mono">#{{ $user->id }}</strong>
                            </div>
                            @if(!empty($studentProfile?->nis))
                                <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/60">
                                    NIS: <strong class="text-slate-700 font-mono">{{ $studentProfile->nis }}</strong>
                                </div>
                            @endif
                            @if($user->created_at)
                                <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/60">
                                    Bergabung: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d F Y') }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Edit Profil & Ganti Password -->
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Bagian 1: Data Pribadi -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 md:p-8 shadow-sm space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Informasi Pengguna
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Ubah nama dan identitas akun yang ditampilkan pada sistem.</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="fullname" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Nama Lengkap <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="fullname" 
                                    name="fullname" 
                                    value="{{ old('fullname', $user->fullname) }}" 
                                    required 
                                    class="w-full rounded-2xl border border-slate-300 bg-slate-50/50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition"
                                    placeholder="Contoh: Budi Santoso, M.Pd"
                                >
                            </div>

                            <div>
                                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Username Login <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 font-mono text-sm">@</span>
                                    <input 
                                        type="text" 
                                        id="username" 
                                        name="username" 
                                        value="{{ old('username', $user->username) }}" 
                                        required 
                                        class="w-full rounded-2xl border border-slate-300 bg-slate-50/50 pl-8 pr-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition font-mono"
                                        placeholder="username_anda"
                                    >
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Username digunakan untuk masuk (login) ke aplikasi.</p>
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Email Pemulihan Sandi
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $user->email) }}"
                                    maxlength="255"
                                    autocomplete="email"
                                    class="w-full rounded-2xl border border-slate-300 bg-slate-50/50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition"
                                    placeholder="nama@email.com"
                                >
                                <p class="mt-1 text-xs text-slate-400">Email ini digunakan untuk mengirim kode OTP saat lupa sandi.</p>
                            </div>

                            <div>
                                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Nomor HP
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone', $studentProfile->phone ?? $user->phone) }}"
                                    maxlength="20"
                                    autocomplete="tel"
                                    class="w-full rounded-2xl border border-slate-300 bg-slate-50/50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition"
                                    placeholder="Contoh: 081234567890"
                                >
                            </div>

                            @if($roleSlug === 'guru')
                                <div>
                                    <label for="nip" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">NIP</label>
                                    <input
                                        type="text"
                                        id="nip"
                                        name="nip"
                                        value="{{ old('nip', $user->nip) }}"
                                        maxlength="30"
                                        class="w-full rounded-2xl border border-slate-300 bg-slate-50/50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition"
                                        placeholder="Masukkan NIP"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mata Pelajaran</label>
                                    <div class="min-h-12 w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-medium text-slate-700">
                                        @forelse($teacherSubjects as $subject)
                                            <span class="mb-1 mr-1 inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $subject }}</span>
                                        @empty
                                            Belum ada mata pelajaran pada jadwal mengajar.
                                        @endforelse
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">Mata pelajaran mengikuti jadwal yang ditetapkan.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Bagian 2: Ganti Password -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 md:p-8 shadow-sm space-y-6">
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Keamanan & Password
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Kosongkan kolom di bawah jika Anda <strong>tidak ingin</strong> mengubah password.</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Password Saat Ini
                                </label>
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    class="w-full rounded-2xl border border-slate-300 bg-slate-50/50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition"
                                    placeholder="Masukkan password saat ini (jika ganti password)"
                                >
                            </div>

                            <div>
                                <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Password Baru
                                </label>
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    class="w-full rounded-2xl border border-slate-300 bg-slate-50/50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition"
                                    placeholder="Minimal 6 karakter"
                                >
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Ulangi Password Baru
                                </label>
                                <input 
                                    type="password" 
                                    id="new_password_confirmation" 
                                    name="new_password_confirmation" 
                                    class="w-full rounded-2xl border border-slate-300 bg-slate-50/50 px-4 py-3 text-sm font-medium text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition"
                                    placeholder="Ketik ulang password baru Anda"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button Save -->
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500 text-center sm:text-left">
                        Pastikan data yang Anda masukkan sudah benar sebelum menekan tombol simpan.
                    </p>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ route('dashboard.role', ['role' => $dashboardSlug]) }}" class="w-full sm:w-auto text-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto rounded-2xl {{ $theme['accent_btn'] }} px-6 py-3 text-sm font-bold transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar || !backdrop) return;
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
            }
        }
    </script>
@include('partials.confirm_crud')
</body>
</html>
