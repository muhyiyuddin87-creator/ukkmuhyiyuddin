@extends('layouts.guru')

@section('title', 'Riwayat Absensi Siswa - School System')

@section('content')
<div class="space-y-6">
    <!-- Header Page Banner -->
    <header class="rounded-3xl border border-indigo-100 bg-white p-6 md:p-8 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-indigo-700 border border-indigo-100">
                        <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Presensi Siswa
                    </span>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-100">
                        Wali Kelas
                    </span>
                </div>
                <h1 class="mt-2 text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Riwayat Absensi Siswa</h1>
                <p class="mt-1 text-xs md:text-sm text-slate-500">
                    Rekapitulasi lengkap kehadiran seluruh siswa kelas perwalian Anda (jumlah hadir, sakit, izin, dan alpa).
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 self-start md:self-auto">
                <a href="{{ route('guru.jurnal') }}" class="btn-standard border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 gap-2">
                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Isi Jurnal
                </a>
                <a href="{{ route('wali-kelas.izin') }}" class="btn-standard bg-indigo-600 text-white hover:bg-indigo-500 shadow-indigo-200 shadow-md gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Verifikasi Izin
                </a>
            </div>
        </div>

        @if($classNames->isNotEmpty())
        <!-- Class Switcher Tabs -->
        <div class="mt-6 pt-5 border-t border-slate-100 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Kelas Perwalian:</span>
            @foreach($classNames as $cName)
                <a href="{{ route('guru.presensi', array_merge(request()->except('kelas'), ['kelas' => $cName])) }}" 
                   class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ ($selectedClass === $cName) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Kelas {{ $cName }}
                </a>
            @endforeach
            @if($classNames->count() > 1)
                <a href="{{ route('guru.presensi', array_merge(request()->except('kelas'), ['kelas' => 'all'])) }}" 
                   class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition {{ ($selectedClass === 'all') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Kelas
                </a>
            @endif
        </div>
        @endif
    </header>

    @if(session('success'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 font-semibold flex items-center gap-3">
        <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-800 font-semibold space-y-1">
        @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
    @endif

    <!-- 5 KPI Summary Cards -->
    <section class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Total Siswa -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition hover:shadow">
            <div class="flex items-center justify-between">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 border border-indigo-100">Siswa</span>
            </div>
            <p class="mt-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Siswa</p>
            <p class="mt-1 text-2xl md:text-3xl font-extrabold text-slate-800">{{ $totalSiswa }} <span class="text-xs font-semibold text-slate-400">Orang</span></p>
        </div>

        <!-- Total Hadir -->
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/30 p-5 shadow-xs transition hover:shadow">
            <div class="flex items-center justify-between">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">{{ $avgAttendanceRate }}%</span>
            </div>
            <p class="mt-3 text-xs font-semibold text-emerald-800 uppercase tracking-wide">Total Hadir</p>
            <p class="mt-1 text-2xl md:text-3xl font-extrabold text-emerald-700">{{ $totalHadir }} <span class="text-xs font-semibold text-emerald-600/70">Kali</span></p>
        </div>

        <!-- Total Sakit -->
        <div class="rounded-2xl border border-sky-100 bg-sky-50/30 p-5 shadow-xs transition hover:shadow">
            <div class="flex items-center justify-between">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </span>
                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-700">Sakit</span>
            </div>
            <p class="mt-3 text-xs font-semibold text-sky-800 uppercase tracking-wide">Total Sakit</p>
            <p class="mt-1 text-2xl md:text-3xl font-extrabold text-sky-700">{{ $totalSakit }} <span class="text-xs font-semibold text-sky-600/70">Kali</span></p>
        </div>

        <!-- Total Izin -->
        <div class="rounded-2xl border border-amber-100 bg-amber-50/30 p-5 shadow-xs transition hover:shadow">
            <div class="flex items-center justify-between">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Izin</span>
            </div>
            <p class="mt-3 text-xs font-semibold text-amber-800 uppercase tracking-wide">Total Izin</p>
            <p class="mt-1 text-2xl md:text-3xl font-extrabold text-amber-700">{{ $totalIzin }} <span class="text-xs font-semibold text-amber-600/70">Kali</span></p>
        </div>

        <!-- Total Alpa -->
        <div class="col-span-2 md:col-span-1 rounded-2xl border border-rose-100 bg-rose-50/30 p-5 shadow-xs transition hover:shadow">
            <div class="flex items-center justify-between">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700">Alpa</span>
            </div>
            <p class="mt-3 text-xs font-semibold text-rose-800 uppercase tracking-wide">Total Alpa</p>
            <p class="mt-1 text-2xl md:text-3xl font-extrabold text-rose-700">{{ $totalAlpha }} <span class="text-xs font-semibold text-rose-600/70">Kali</span></p>
        </div>
    </section>

    <!-- Search & Filter Controls -->
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
        <form method="GET" action="{{ route('guru.presensi') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            @if($selectedClass)
                <input type="hidden" name="kelas" value="{{ $selectedClass }}">
            @endif

            <!-- Search by Name / NISN -->
            <div class="md:col-span-4">
                <label for="q" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Cari Nama Siswa / NISN</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="q" name="q" value="{{ $search }}" placeholder="Ketik nama siswa..." class="form-control-standard w-full pl-9">
                </div>
            </div>

            <!-- Status Filter Dropdown -->
            <div class="md:col-span-3">
                <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Filter Kondisi</label>
                <select id="status" name="status" class="form-control-standard w-full">
                    <option value="all" @selected($statusFilter === 'all')>Semua Siswa</option>
                    <option value="alpha" @selected($statusFilter === 'alpha')>Pernah Alpa (Alpa > 0)</option>
                    <option value="izin" @selected($statusFilter === 'izin')>Pernah Izin (Izin > 0)</option>
                    <option value="sakit" @selected($statusFilter === 'sakit')>Pernah Sakit (Sakit > 0)</option>
                    <option value="hadir_kurang" @selected($statusFilter === 'hadir_kurang')>Kehadiran &lt; 85%</option>
                </select>
            </div>

            <!-- Date Range Filter -->
            <div class="md:col-span-3 grid grid-cols-2 gap-2">
                <div>
                    <label for="start_date" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control-standard w-full text-xs">
                </div>
                <div>
                    <label for="end_date" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control-standard w-full text-xs">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="md:col-span-2 flex items-center gap-2">
                <button type="submit" class="btn-standard w-full bg-indigo-600 text-white hover:bg-indigo-500 font-bold">
                    Filter
                </button>
                @if($search !== '' || $statusFilter !== 'all' || $startDate || $endDate)
                    <a href="{{ route('guru.presensi', ['kelas' => $selectedClass]) }}" class="btn-standard bg-slate-100 text-slate-600 hover:bg-slate-200" title="Reset filter">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </section>

    <!-- Main Table: Rekapitulasi Presensi Seluruh Siswa -->
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xs">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Daftar Siswa Kelas {{ $selectedClass === 'all' ? 'Semua Perwalian' : $selectedClass }}</h3>
                <p class="text-xs text-slate-500">Menampilkan {{ $studentStats->count() }} siswa dengan rincian akumulasi kehadiran.</p>
            </div>
            @if($studentStats->where('alpha', '>', 0)->count() > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                    <span class="h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span>
                    {{ $studentStats->where('alpha', '>', 0)->count() }} Siswa tercatat Alpa
                </span>
            @endif
        </div>

        <div class="overflow-x-auto table-scroll-container rounded-2xl border border-slate-100">
            <table class="w-full min-w-[800px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                        <th class="px-4 py-3.5">Nama Siswa</th>
                        <th class="px-4 py-3.5 w-24">Kelas</th>
                        <th class="px-4 py-3.5 w-24 text-center">Hadir</th>
                        <th class="px-4 py-3.5 w-24 text-center">Sakit</th>
                        <th class="px-4 py-3.5 w-24 text-center">Izin</th>
                        <th class="px-4 py-3.5 w-24 text-center">Alpa</th>
                        <th class="px-4 py-3.5 w-36 text-center">% Kehadiran</th>
                        <th class="px-4 py-3.5 w-28 text-center">Riwayat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($studentStats as $index => $s)
                    <tr class="hover:bg-slate-50/80 transition {{ $s->alpha > 0 ? 'bg-rose-50/20' : '' }}">
                        <td class="px-4 py-3 text-center text-xs font-semibold text-slate-400">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 font-bold text-slate-700 text-xs">
                                    {{ strtoupper(substr($s->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 leading-snug">{{ $s->name }}</div>
                                    <div class="text-[11px] text-slate-400">NISN: {{ $s->nisn ?: '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-600">
                            <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                {{ $s->kelas }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                {{ $s->hadir }}x
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($s->sakit > 0)
                                <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700 border border-sky-200">
                                    {{ $s->sakit }}x
                                </span>
                            @else
                                <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($s->izin > 0)
                                <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">
                                    {{ $s->izin }}x
                                </span>
                            @else
                                <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($s->alpha > 0)
                                <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-extrabold text-rose-700 border border-rose-300 animate-pulse">
                                    {{ $s->alpha }}x
                                </span>
                            @else
                                <span class="text-xs text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-extrabold {{ $s->rate >= 85 ? 'text-emerald-700' : ($s->rate >= 75 ? 'text-amber-700' : 'text-rose-700') }}">
                                    {{ $s->rate }}%
                                </span>
                                <div class="w-20 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $s->rate >= 85 ? 'bg-emerald-500' : ($s->rate >= 75 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $s->rate }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" onclick="openStudentModal({{ json_encode($s) }})" 
                                    class="inline-flex items-center gap-1 rounded-xl bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 border border-indigo-200/80 hover:bg-indigo-600 hover:text-white transition active:scale-95">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-2">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada data siswa yang cocok dengan kriteria pencarian.</p>
                                <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci lain atau reset filter tanggal & kondisi.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Optional Section: Catat Presensi per Jurnal Mengajar -->
    @if($journals->isNotEmpty())
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xs">
        <details class="group">
            <summary class="flex cursor-pointer items-center justify-between font-bold text-slate-800 list-none select-none">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </span>
                    <span class="text-base font-bold">Input Presensi Berdasarkan Jurnal (Form Manual)</span>
                </div>
                <span class="rounded-full bg-slate-100 p-1 text-slate-500 group-open:rotate-180 transition-transform">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </summary>

            <form method="POST" action="{{ route('guru.presensi.store') }}" class="mt-6 pt-5 border-t border-slate-100 space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="journal_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pilih Jurnal Pertemuan</label>
                        <select name="journal_id" id="journal_id" class="form-control-standard w-full" required>
                            <option value="">-- Pilih Jurnal Mengajar --</option>
                            @foreach($journals as $j)
                                <option value="{{ $j->id }}" @selected(old('journal_id') == $j->id)>{{ $j->date }} - {{ $j->subject }} ({{ $j->class_name }}): {{ $j->topic_description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="form_date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal Presensi</label>
                        <input type="date" name="date" id="form_date" value="{{ old('date', date('Y-m-d')) }}" class="form-control-standard w-full" required>
                    </div>
                </div>

                <div class="overflow-x-auto table-scroll-container rounded-xl border border-slate-200">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                            <tr>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3 w-48">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($siswas as $s)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-slate-800">{{ $s->name }}</td>
                                <td class="px-4 py-2.5">
                                    <input type="hidden" name="student_ids[]" value="{{ $s->id }}">
                                    <select name="statuses[]" class="form-control-standard w-full py-1 text-xs">
                                        <option value="Hadir" @selected(old('statuses.' . $loop->index, 'Hadir') === 'Hadir')>Hadir</option>
                                        <option value="Sakit" @selected(old('statuses.' . $loop->index) === 'Sakit')>Sakit</option>
                                        <option value="Izin" @selected(old('statuses.' . $loop->index) === 'Izin')>Izin</option>
                                        <option value="Alpha" @selected(old('statuses.' . $loop->index) === 'Alpha')>Alpha</option>
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-standard bg-emerald-600 text-white hover:bg-emerald-500 font-bold px-6">
                        Simpan Presensi ke Database
                    </button>
                </div>
            </form>
        </details>
    </section>
    @endif
</div>

<!-- Modal Pop-up Log Detail Kehadiran Siswa -->
<div id="studentDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 flex items-center justify-center">
    <div class="relative w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="flex items-start justify-between pb-4 border-b border-slate-100">
            <div>
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 border border-indigo-100" id="modalStudentClass">Kelas</span>
                <h3 class="mt-2 text-xl font-bold text-slate-800" id="modalStudentName">Nama Siswa</h3>
                <p class="text-xs text-slate-400 mt-0.5" id="modalStudentNisn">NISN: -</p>
            </div>
            <button type="button" onclick="closeStudentModal()" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Modal Summary Badges -->
        <div class="grid grid-cols-4 gap-2 my-4">
            <div class="rounded-xl bg-emerald-50 p-2.5 text-center border border-emerald-100">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">Hadir</p>
                <p class="text-base font-extrabold text-emerald-700" id="modalHadir">0</p>
            </div>
            <div class="rounded-xl bg-sky-50 p-2.5 text-center border border-sky-100">
                <p class="text-[10px] font-bold uppercase tracking-wider text-sky-800">Sakit</p>
                <p class="text-base font-extrabold text-sky-700" id="modalSakit">0</p>
            </div>
            <div class="rounded-xl bg-amber-50 p-2.5 text-center border border-amber-100">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Izin</p>
                <p class="text-base font-extrabold text-amber-700" id="modalIzin">0</p>
            </div>
            <div class="rounded-xl bg-rose-50 p-2.5 text-center border border-rose-100">
                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-800">Alpa</p>
                <p class="text-base font-extrabold text-rose-700" id="modalAlpha">0</p>
            </div>
        </div>

        <!-- Modal Table: Records Log -->
        <div class="overflow-y-auto flex-1 border border-slate-100 rounded-2xl">
            <table class="w-full text-left text-xs">
                <thead class="sticky top-0 bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-2.5">Tanggal</th>
                        <th class="px-3 py-2.5">Status</th>
                        <th class="px-3 py-2.5">Mapel / Jurnal</th>
                        <th class="px-3 py-2.5">Catatan</th>
                    </tr>
                </thead>
                <tbody id="modalRecordsBody" class="divide-y divide-slate-100">
                    <!-- Injected dynamically by JS -->
                </tbody>
            </table>
        </div>

        <!-- Modal Footer -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeStudentModal()" class="btn-standard bg-slate-100 text-slate-700 hover:bg-slate-200 px-5">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openStudentModal(student) {
        document.getElementById('modalStudentName').textContent = student.name;
        document.getElementById('modalStudentClass').textContent = 'Kelas ' + student.kelas;
        document.getElementById('modalStudentNisn').textContent = 'NISN: ' + (student.nisn ? student.nisn : '-');
        document.getElementById('modalHadir').textContent = student.hadir + 'x';
        document.getElementById('modalSakit').textContent = student.sakit + 'x';
        document.getElementById('modalIzin').textContent = student.izin + 'x';
        document.getElementById('modalAlpha').textContent = student.alpha + 'x';

        const tbody = document.getElementById('modalRecordsBody');
        tbody.innerHTML = '';

        if (!student.records || student.records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-3 py-6 text-center text-slate-400 italic">Belum ada riwayat absensi tercatat untuk siswa ini.</td></tr>';
        } else {
            student.records.forEach(function(rec) {
                let badgeClass = 'bg-slate-100 text-slate-700';
                if (rec.status === 'Hadir') badgeClass = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                else if (rec.status === 'Sakit') badgeClass = 'bg-sky-100 text-sky-800 border border-sky-200';
                else if (rec.status === 'Izin') badgeClass = 'bg-amber-100 text-amber-800 border border-amber-200';
                else if (rec.status === 'Alpha') badgeClass = 'bg-rose-100 text-rose-800 border border-rose-300 font-bold';

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50';
                tr.innerHTML = `
                    <td class="px-3 py-2 font-medium text-slate-600 whitespace-nowrap">${rec.date || '-'}</td>
                    <td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${badgeClass}">${rec.status}</span></td>
                    <td class="px-3 py-2 text-slate-700 font-semibold">${rec.subject ? rec.subject : 'Jurnal KBM'}<div class="text-[10px] font-normal text-slate-400">${rec.topic_description ? rec.topic_description : '-'}</div></td>
                    <td class="px-3 py-2 text-slate-500">${rec.notes ? rec.notes : '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        const modal = document.getElementById('studentDetailModal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeStudentModal() {
        const modal = document.getElementById('studentDetailModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Close on backdrop click
    document.getElementById('studentDetailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStudentModal();
        }
    });
</script>
@endsection
