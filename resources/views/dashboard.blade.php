<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard {{ $roleInfo['label'] }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-badge.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #eef6ff 0%, #f8fafc 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Batas tinggi tabel maks ~10 baris data dengan scroll internal & sticky header */
        .overflow-x-auto:has(table),
        .table-scroll-container {
            max-height: 520px;
            overflow-y: auto;
            overflow-x: auto;
            position: relative;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        .overflow-x-auto:has(table) table,
        .table-scroll-container table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .overflow-x-auto:has(table) thead,
        .table-scroll-container thead {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #f8fafc;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .overflow-x-auto:has(table) thead th,
        .table-scroll-container thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: inherit;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Scrollbar kustom halus */
        .overflow-x-auto:has(table)::-webkit-scrollbar,
        .table-scroll-container::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .overflow-x-auto:has(table)::-webkit-scrollbar-track,
        .table-scroll-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .overflow-x-auto:has(table)::-webkit-scrollbar-thumb,
        .table-scroll-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .overflow-x-auto:has(table)::-webkit-scrollbar-thumb:hover,
        .table-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="min-h-screen">
    @if ($roleId === 1)
        <div class="flex min-h-screen">
            <aside class="w-72 bg-slate-900 text-white p-6">
                <div class="mb-10">
                    <div class="text-xs uppercase tracking-[0.3em] text-sky-300">School System</div>
                    <h1 class="mt-3 text-2xl font-bold">Waka</h1>
                </div>

                <nav class="space-y-2">
                    <a href="#" class="flex items-center gap-3 rounded-xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-200 hover:bg-slate-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-sky-400"></span>
                        Data Siswa
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-200 hover:bg-slate-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-sky-400"></span>
                        Data Guru
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-200 hover:bg-slate-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-sky-400"></span>
                        Jadwal
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-200 hover:bg-slate-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-sky-400"></span>
                        Presensi
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-200 hover:bg-slate-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-sky-400"></span>
                        Laporan
                    </a>
                </nav>

                <div class="mt-10 rounded-2xl bg-slate-800 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Akun</p>
                    <div class="mt-3 text-lg font-semibold">{{ $user->fullname ?? $user->username }}</div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-red-500 px-3 py-2 text-sm font-semibold text-white hover:bg-red-400 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="flex-1 p-6 md:p-8">
                <header class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                            <h2 class="mt-2 text-3xl font-bold text-slate-800">Dashboard Waka</h2>
                        </div>
                        <div class="rounded-full bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700">
                            Ringkasan operasional sekolah
                        </div>
                    </div>
                </header>

                <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
                    <!-- Data Siswa -->
                    <a href="{{ route('waka.siswa') }}" class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-sky-400 hover:shadow-md hover:-translate-y-0.5">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100 text-sky-700 transition group-hover:bg-sky-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Live</span>
                                <span class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-sky-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500 group-hover:text-sky-700 transition">Data Siswa</p>
                        <div class="mt-1 flex items-baseline justify-between">
                            <p class="text-3xl font-extrabold text-slate-800">{{ $stats['total_siswa'] }}</p>
                            <span class="text-xs font-semibold text-sky-600 opacity-0 group-hover:opacity-100 transition">Buka menu &rarr;</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Kelola dan impor data siswa</p>
                    </a>

                    <!-- Data Guru -->
                    <a href="{{ route('waka.guru') }}" class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-indigo-400 hover:shadow-md hover:-translate-y-0.5">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 transition group-hover:bg-indigo-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Live</span>
                                <span class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-indigo-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500 group-hover:text-indigo-700 transition">Data Guru</p>
                        <div class="mt-1 flex items-baseline justify-between">
                            <p class="text-3xl font-extrabold text-slate-800">{{ $stats['total_guru'] }}</p>
                            <span class="text-xs font-semibold text-indigo-600 opacity-0 group-hover:opacity-100 transition">Buka menu &rarr;</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Kelola guru dan penugasan</p>
                    </a>

                    <!-- Jadwal Mengajar -->
                    <a href="{{ route('waka.jadwal') }}" class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-amber-400 hover:shadow-md hover:-translate-y-0.5">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-700 transition group-hover:bg-amber-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Live</span>
                                <span class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-amber-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500 group-hover:text-amber-700 transition">Jadwal Mengajar</p>
                        <div class="mt-1 flex items-baseline justify-between">
                            <p class="text-3xl font-extrabold text-slate-800">{{ $stats['total_jadwal'] }}</p>
                            <span class="text-xs font-semibold text-amber-600 opacity-0 group-hover:opacity-100 transition">Buka menu &rarr;</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Atur putaran dan ruangan</p>
                    </a>

                    <!-- Laporan Kehadiran -->
                    <a href="{{ route('waka.laporan') }}" class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-emerald-400 hover:shadow-md hover:-translate-y-0.5">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 transition group-hover:bg-emerald-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Hari Ini</span>
                                <span class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-500 group-hover:text-emerald-700 transition">Laporan Kehadiran</p>
                        <div class="mt-1 flex items-baseline justify-between">
                            <p class="text-3xl font-extrabold text-slate-800">{{ $stats['total_kehadiran'] }}</p>
                            <span class="text-xs font-semibold text-emerald-600 opacity-0 group-hover:opacity-100 transition">Buka menu &rarr;</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Rekap jurnal dan presensi</p>
                    </a>
                </section>

                <section class="rounded-2xl bg-gradient-to-br from-sky-600 to-indigo-700 p-6 text-white shadow-lg">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-bold">Status Hari Ini</h3>
                        <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white">Ringkasan Sistem</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <a href="{{ route('waka.jadwal') }}" class="group block rounded-xl bg-white/10 backdrop-blur-sm p-4 transition hover:bg-white/20">
                            <p class="text-xs text-sky-200 font-medium">Jadwal Aktif</p>
                            <div class="mt-2 flex items-baseline justify-between">
                                <p class="text-2xl font-bold text-white">{{ $stats['total_jadwal'] }}</p>
                                <span class="text-xs text-sky-200 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
                            </div>
                        </a>
                        <a href="{{ route('waka.laporan') }}" class="group block rounded-xl bg-white/10 backdrop-blur-sm p-4 transition hover:bg-white/20">
                            <p class="text-xs text-sky-200 font-medium">Kehadiran Siswa</p>
                            <div class="mt-2 flex items-baseline justify-between">
                                <p class="text-2xl font-bold text-white">{{ $stats['total_kehadiran'] }}</p>
                                <span class="text-xs text-sky-200 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
                            </div>
                        </a>
                        <a href="{{ route('waka.laporan') }}" class="group block rounded-xl bg-white/10 backdrop-blur-sm p-4 transition hover:bg-white/20">
                            <p class="text-xs text-sky-200 font-medium">Izin Disetujui</p>
                            <div class="mt-2 flex items-baseline justify-between">
                                <p class="text-2xl font-bold text-white">{{ $stats['total_izin'] }}</p>
                                <span class="text-xs text-sky-200 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
                            </div>
                        </a>
                        <a href="{{ route('waka.accounts') }}" class="group block rounded-xl bg-white/10 backdrop-blur-sm p-4 transition hover:bg-white/20">
                            <p class="text-xs text-sky-200 font-medium">Total Akun</p>
                            <div class="mt-2 flex items-baseline justify-between">
                                <p class="text-2xl font-bold text-white">{{ $stats['total_akun'] }}</p>
                                <span class="text-xs text-sky-200 opacity-0 group-hover:opacity-100 transition">&rarr;</span>
                            </div>
                        </a>
                    </div>
                </section>
            </main>
        </div>
    @elseif ($roleId === 2)
        <div class="flex min-h-screen">
            <aside class="w-72 bg-indigo-900 text-white p-6">
                <div class="mb-10">
                    <div class="text-xs uppercase tracking-[0.3em] text-indigo-200">School System</div>
                    <h1 class="mt-3 text-2xl font-bold">Guru</h1>
                </div>

                <nav class="space-y-2">
                    <a href="#" class="flex items-center gap-3 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-300"></span>
                        Kelas Saya
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-300"></span>
                        Jadwal
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-300"></span>
                        Presensi
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-300"></span>
                        Materi
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-indigo-100 hover:bg-indigo-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-300"></span>
                        Nilai
                    </a>
                </nav>

                <div class="mt-10 rounded-2xl bg-indigo-800 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-indigo-200">Akun</p>
                    <div class="mt-3 text-lg font-semibold">{{ $user->fullname ?? $user->username }}</div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-rose-500 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-400 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="flex-1 p-6 md:p-8">
                <header class="mb-8 rounded-2xl border border-indigo-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                            <h2 class="mt-2 text-3xl font-bold text-slate-800">Dashboard Guru</h2>
                        </div>
                        <div class="rounded-full bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700">
                            Informasi pembelajaran hari ini
                        </div>
                    </div>
                </header>

                <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Hari Ini</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Jadwal</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_jadwal'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </span>
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Kelas</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Kelas</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">5</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Presensi</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Presensi</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_kehadiran'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Izin</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Izin</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_izin'] }}</p>
                    </div>
                </section>

                <section class="grid grid-cols-1 xl:grid-cols-[1.4fr_0.9fr] gap-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-xl font-bold text-slate-800">Jadwal Mengajar</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                                <div>
                                    <div class="font-semibold text-slate-800">Matematika</div>
                                    <div class="text-sm text-slate-500">07.30 - 09.00 | Kelas XI</div>
                                </div>
                                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">Senin</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                                <div>
                                    <div class="font-semibold text-slate-800">Bahasa Indonesia</div>
                                    <div class="text-sm text-slate-500">09.15 - 10.45 | Kelas X</div>
                                </div>
                                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">Selasa</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                                <div>
                                    <div class="font-semibold text-slate-800">IPA</div>
                                    <div class="text-sm text-slate-500">11.00 - 12.30 | Kelas XII</div>
                                </div>
                                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">Rabu</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 p-6 text-white shadow-lg">
                        <h3 class="text-xl font-bold mb-4">Aktivitas Guru</h3>
                        <ul class="space-y-3 text-sm text-indigo-50">
                            <li class="flex items-center justify-between"><span>Rencana belajar</span><span class="font-semibold">4</span></li>
                            <li class="flex items-center justify-between"><span>Materi dibagikan</span><span class="font-semibold">3</span></li>
                            <li class="flex items-center justify-between"><span>Presensi belum valid</span><span class="font-semibold">2</span></li>
                            <li class="flex items-center justify-between"><span>Agenda kelas</span><span class="font-semibold">6</span></li>
                        </ul>
                    </div>
                </section>
            </main>
        </div>
    @elseif ($roleId === 3)
        <div class="flex min-h-screen">
            <aside class="w-72 bg-emerald-900 text-white p-6">
                <div class="mb-10">
                    <div class="text-xs uppercase tracking-[0.3em] text-emerald-200">School System</div>
                    <h1 class="mt-3 text-2xl font-bold">Siswa</h1>
                </div>

                <nav class="space-y-2">
                    <a href="{{ route('dashboard.role', ['role' => 'siswa']) }}" class="flex items-center gap-3 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                        Dashboard
                    </a>
                    <a href="{{ route('siswa.jadwal') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-emerald-100 hover:bg-emerald-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                        Jadwal
                    </a>
                    <a href="{{ route('siswa.izin') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-emerald-100 hover:bg-emerald-700 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                        Izin
                    </a>
                </nav>

                <div class="mt-10 rounded-2xl bg-emerald-800 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-emerald-200">Akun</p>
                    <div class="mt-3 text-lg font-semibold">{{ $user->fullname ?? $user->username }}</div>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('profile') }}" class="w-1/2 text-center rounded-lg bg-emerald-600 px-2 py-2 text-xs font-semibold text-white hover:bg-emerald-500 transition">
                            Edit Profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="w-1/2">
                            @csrf
                            <button type="submit" class="w-full rounded-lg bg-rose-500 px-2 py-2 text-xs font-semibold text-white hover:bg-rose-400 transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <main class="flex-1 p-6 md:p-8">
                <header class="mb-8 rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                            <h2 class="mt-2 text-3xl font-bold text-slate-800">Dashboard Siswa</h2>
                        </div>
                        <div class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                            Aktivitas belajar Anda
                        </div>
                    </div>
                </header>

                <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </span>
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Hari Ini</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Jadwal</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_jadwal'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </span>
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Izin</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Izin</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_izin'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Akun</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Akun</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_akun'] }}</p>
                    </div>
                </section>

                <section class="mb-8">
                    <div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 p-6 text-white shadow-lg">
                        <h3 class="text-xl font-bold mb-4">Catatan Siswa</h3>
                        <ul class="space-y-3 text-sm text-emerald-50">
                            <li class="flex items-center justify-between"><span>Izin</span><span class="font-semibold">{{ $stats['total_izin'] ?? 0 }} Pengajuan</span></li>
                            <li class="flex items-center justify-between"><span>Jadwal</span><span class="font-semibold">Aktif</span></li>
                            <li class="flex items-center justify-between"><span>Status Akun</span><span class="font-semibold">Aktif</span></li>
                        </ul>
                    </div>
                </section>
            </main>
        </div>
    @elseif ($roleId === 4)
        <div class="flex min-h-screen">
            <aside class="w-72 bg-amber-700 text-white p-6">
                <div class="mb-10">
                    <div class="text-xs uppercase tracking-[0.3em] text-amber-100">School System</div>
                    <h1 class="mt-3 text-2xl font-bold">Kesiswaan</h1>
                </div>

                <nav class="space-y-2">
                    <a href="#" class="flex items-center gap-3 rounded-xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                        Dashboard
                    </a>
                    <a href="{{ route('kesiswaan.izin') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-amber-100 hover:bg-amber-600 hover:text-white">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-200"></span>
                        Persetujuan & Riwayat Izin
                    </a>
                </nav>

                <div class="mt-10 rounded-2xl bg-amber-800 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-amber-100">Akun</p>
                    <div class="mt-3 text-lg font-semibold">{{ $user->fullname ?? $user->username }}</div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-rose-500 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-400 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="flex-1 p-6 md:p-8">
                <header class="mb-8 rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                            <h2 class="mt-2 text-3xl font-bold text-slate-800">Dashboard Kesiswaan</h2>
                        </div>
                        <div class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700">
                            Persetujuan permohonan dan riwayat izin
                        </div>
                    </div>
                </header>

                <section class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700">Menunggu</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Menunggu persetujuan</p>
                        <p class="mt-2 text-3xl font-bold text-amber-700">{{ $stats['pending_izin'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-slate-700">Riwayat</span>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Keputusan izin</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['riwayat_izin'] }}</p>
                    </div>
                </section>

                <section>
                    <a href="{{ route('kesiswaan.izin') }}" class="inline-flex rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white shadow transition hover:bg-amber-700">
                        Tinjau pengajuan dan riwayat izin
                    </a>
                </section>
            </main>
        </div>
    @else
        <div class="flex min-h-screen">
            <aside class="w-72 bg-slate-900 text-white p-6">
                <div class="mb-10">
                    <div class="text-xs uppercase tracking-[0.2em] text-sky-300">School System</div>
                    <h1 class="mt-2 text-2xl font-bold">{{ $roleInfo['label'] }}</h1>
                </div>

                <nav class="space-y-2">
                    @foreach ($roleInfo['sidebar'] as $menu)
                        <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-200 hover:bg-slate-700 hover:text-white transition {{ $loop->first ? 'bg-sky-600 text-white' : '' }}">
                            <span class="inline-block h-2.5 w-2.5 rounded-full {{ $loop->first ? 'bg-white' : 'bg-sky-400' }}"></span>
                            {{ $menu }}
                        </a>
                    @endforeach
                </nav>

                <div class="mt-10 rounded-2xl bg-slate-800 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Akun</p>
                    <div class="mt-3 font-semibold">{{ $user->fullname ?? $user->username }}</div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-red-500 px-3 py-2 text-sm font-semibold text-white hover:bg-red-400 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="flex-1 p-6 md:p-8">
                <header class="mb-8 rounded-2xl bg-white shadow-sm border border-slate-200 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.2em] text-slate-500">Selamat datang</p>
                            <h2 class="mt-1 text-3xl font-bold text-slate-800">Dashboard {{ $roleInfo['label'] }}</h2>
                        </div>
                        <div class="rounded-full bg-sky-100 px-4 py-2 text-sm font-medium text-sky-700">
                            {{ $roleInfo['summary'] }}
                        </div>
                    </div>
                </header>

                <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
                    @foreach ($roleInfo['cards'] as $card)
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl">{{ $card['icon'] }}</span>
                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Live</span>
                            </div>
                            <p class="text-sm text-slate-500">{{ $card['title'] }}</p>
                            <p class="mt-3 text-3xl font-bold text-slate-800">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </section>

                <section class="grid grid-cols-1 xl:grid-cols-[1.5fr_0.9fr] gap-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-xl font-bold text-slate-800">Menu cepat</h3>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($roleInfo['quick_actions'] as $action)
                                <button type="button" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-left text-sm font-semibold text-slate-700 hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700 transition">
                                    {{ $action }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl bg-gradient-to-br from-sky-600 to-indigo-700 p-6 text-white shadow-lg">
                        <h3 class="text-xl font-bold mb-4">Status hari ini</h3>
                        <ul class="space-y-3 text-sm text-sky-50">
                            <li class="flex items-center justify-between"><span>Jadwal aktif</span><span class="font-semibold">{{ $stats['total_jadwal'] }}</span></li>
                            <li class="flex items-center justify-between"><span>Kehadiran</span><span class="font-semibold">{{ $stats['total_kehadiran'] }}</span></li>
                            <li class="flex items-center justify-between"><span>Persetujuan</span><span class="font-semibold">{{ $stats['total_izin'] }}</span></li>
                            <li class="flex items-center justify-between"><span>Pengguna</span><span class="font-semibold">{{ $stats['total_akun'] }}</span></li>
                        </ul>
                    </div>
                </section>
            </main>
        </div>
    @endif
</body>
</html>
