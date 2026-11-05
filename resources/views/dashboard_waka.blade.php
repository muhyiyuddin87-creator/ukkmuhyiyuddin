@extends('layouts.waka')

@section('title', 'Dashboard Waka - School System')

@section('content')
    <header class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-800">Dashboard Waka</h2>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="rounded-full bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700">
                    Ringkasan operasional sekolah
                </div>
                <div class="rounded-full bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700">
                    Saat ini putaran ke-{{ $activeCycle }}
                </div>
                <div class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                    Tahun ajaran {{ $academicYear }}
                </div>
            </div>
        </div>
    </header>

    <!-- Kartu Statistik Live yang Sekaligus Menjadi Menu Navigasi Cepat -->
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

    <!-- Ringkasan Status Hari Ini -->
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
@endsection