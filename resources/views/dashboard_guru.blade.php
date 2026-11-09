@extends('layouts.guru')

@section('title', 'Dashboard Guru - School System')

@section('content')
    <div class="space-y-8">
        <header class="rounded-2xl border border-indigo-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-800">Dashboard Guru</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="rounded-full bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700">
                        Ruang kerja mengajar mandiri
                    </div>
                    @if(!empty($waliClassNames) && count($waliClassNames) > 0)
                        <div class="rounded-full bg-indigo-50 border border-indigo-200 px-3.5 py-1.5 text-xs font-bold text-indigo-800">
                            Wali Kelas: {{ $waliClassNames->implode(', ') }}
                        </div>
                    @endif
                    @if(!empty($bkClassNames) && count($bkClassNames) > 0)
                        <div class="rounded-full bg-emerald-50 border border-emerald-200 px-3.5 py-1.5 text-xs font-bold text-emerald-800">
                            Guru BK ({{ count($bkClassNames) }} Kelas Bimbingan)
                        </div>
                    @endif
                    <div class="rounded-full bg-violet-100 px-4 py-2 text-sm font-semibold text-violet-700">
                        Saat ini putaran ke-{{ $activeCycle }}
                    </div>
                </div>
            </div>
        </header>

        @php
            $hasSchedules = ($totalJadwal ?? 0) > 0 && isset($jadwals) && $jadwals->isNotEmpty();
            $hasTodayMissing = $hasSchedules && isset($missingJournalsToday) && $missingJournalsToday->isNotEmpty();
            $alertSchedules = $hasTodayMissing ? $missingJournalsToday : collect();
            $dayThemes = [
                'Senin' => [
                    'border' => 'border-t-4 border-t-emerald-500',
                    'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'time_pill' => 'bg-emerald-600 text-white',
                    'time_icon' => 'text-emerald-200',
                    'accent' => 'text-emerald-600',
                    'avatar' => 'bg-emerald-600 text-white',
                ],
                'Selasa' => [
                    'border' => 'border-t-4 border-t-indigo-500',
                    'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'time_pill' => 'bg-indigo-600 text-white',
                    'time_icon' => 'text-indigo-200',
                    'accent' => 'text-indigo-600',
                    'avatar' => 'bg-indigo-600 text-white',
                ],
                'Rabu' => [
                    'border' => 'border-t-4 border-t-purple-500',
                    'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'time_pill' => 'bg-purple-600 text-white',
                    'time_icon' => 'text-purple-200',
                    'accent' => 'text-purple-600',
                    'avatar' => 'bg-purple-600 text-white',
                ],
                'Kamis' => [
                    'border' => 'border-t-4 border-t-amber-500',
                    'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'time_pill' => 'bg-amber-600 text-white',
                    'time_icon' => 'text-amber-200',
                    'accent' => 'text-amber-600',
                    'avatar' => 'bg-amber-600 text-white',
                ],
                'Jumat' => [
                    'border' => 'border-t-4 border-t-sky-500',
                    'badge' => 'bg-sky-50 text-sky-700 border-sky-200',
                    'time_pill' => 'bg-sky-600 text-white',
                    'time_icon' => 'text-sky-200',
                    'accent' => 'text-sky-600',
                    'avatar' => 'bg-sky-600 text-white',
                ],
            ];
            $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        @endphp

        @if($hasTodayMissing)
            <section role="alert" class="rounded-2xl border border-amber-300 bg-amber-50 p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-amber-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <h3 class="font-bold text-amber-900 text-base">Jurnal belum diisi</h3>
                            <span class="rounded-full bg-amber-200/90 px-2.5 py-0.5 text-xs font-bold text-amber-900">
                                {{ $alertSchedules->count() }} jadwal
                            </span>
                        </div>
                        <p class="mt-1.5 text-sm text-amber-800">
                            Anda memiliki jadwal mengajar <strong>hari ini</strong> yang belum memiliki catatan jurnal:
                        </p>
                        <ul class="mt-3 list-inside list-disc space-y-1.5 text-sm text-amber-900">
                            @foreach($alertSchedules as $schedule)
                                <li>
                                    <span class="font-semibold">{{ $schedule->subject }}</span>
                                    ({{ $schedule->day_of_week }}, {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }} WIB)
                                    @if(!empty($schedule->class_name)) · Kelas <span class="font-semibold">{{ $schedule->class_name }}</span> @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('guru.jurnal') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700 shadow-sm active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Isi Jurnal Sekarang</span>
                    </a>
                </div>
            </section>
        @endif

        <!-- Stat Cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="rounded-full bg-indigo-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-indigo-700">Jadwal Aktif</span>
                </div>
                <p class="mt-4 text-sm text-slate-500">Rencana mengajar</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $totalJadwal }}</p>
                <p class="mt-2 text-xs text-slate-500">Jadwal yang Anda ampu</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </span>
                    <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700">Tersimpan</span>
                </div>
                <p class="mt-4 text-sm text-slate-500">Jurnal Mengajar</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $totalJurnal }}</p>
                <p class="mt-2 text-xs text-slate-500">Catatan jurnal kegiatan</p>
            </div>
        </section>

        <!-- Section Jadwal Mengajar Guru Modern (Sama Persis seperti di Siswa) -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        <p class="text-xs font-bold uppercase tracking-widest text-indigo-700">Agenda Mengajar</p>
                    </div>
                    <h3 class="mt-1 text-xl font-bold text-slate-800">Jadwal Mengajar Saya</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Tahun Ajaran {{ $academicYear }} · Daftar jadwal mengajar yang telah ditetapkan untuk Anda.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-indigo-700 border border-indigo-200">
                        Putaran {{ $activeCycle }}
                    </span>
                    <a href="{{ route('guru.jadwal') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition active:scale-95">
                        Kelola Jadwal
                    </a>
                    <a href="{{ route('guru.jadwal.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition active:scale-95">
                        <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Cetak PDF</span>
                    </a>
                </div>
            </div>

            <!-- Filter Hari Interaktif + Switch View -->
            <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                <div class="flex flex-wrap items-center gap-1.5" id="guru-day-filter-container">
                    <button type="button" onclick="filterGuruDay('all')" class="guru-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold bg-indigo-600 text-white shadow-xs transition" data-day="all">
                        <span>Semua Hari</span>
                        <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $jadwals->count() }}</span>
                    </button>
                    @foreach($daysList as $d)
                        @php
                            $countDay = $jadwals->where('day_of_week', $d)->count();
                        @endphp
                        <button type="button" onclick="filterGuruDay('{{ $d }}')" class="guru-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition" data-day="{{ $d }}">
                            <span>{{ $d }}</span>
                            <span class="rounded-full bg-slate-100 text-slate-700 px-1.5 py-0.2 text-[10px] font-bold">{{ $countDay }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="inline-flex items-center rounded-xl bg-white p-1 border border-slate-200">
                    <button type="button" onclick="switchGuruViewMode('grid')" id="guru-view-grid" class="guru-toggle-btn active inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Kartu</span>
                    </button>
                    <button type="button" onclick="switchGuruViewMode('table')" id="guru-view-table" class="guru-toggle-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span>Tabel</span>
                    </button>
                </div>
            </div>

            <!-- Grid Kartu Jadwal Modern (Sama Persis seperti di Siswa) -->
            <div id="guru-schedule-card-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($jadwals as $jadwal)
                    @php
                        $theme = $dayThemes[$jadwal->day_of_week] ?? [
                            'border' => 'border-t-4 border-t-slate-400',
                            'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
                            'time_pill' => 'bg-slate-700 text-white',
                            'time_icon' => 'text-slate-300',
                            'accent' => 'text-slate-600',
                            'avatar' => 'bg-slate-700 text-white',
                        ];

                        $periodLabel = \App\Support\SchoolTimetable::labelFor($jadwal->day_of_week, $jadwal->start_time, $jadwal->end_time);
                        if ($periodLabel) {
                            $formattedPeriod = str_replace(
                                ['Jam ke-', ' sampai ', ' - '],
                                ['Jam ke - ', ' s/d ', ' s/d '],
                                $periodLabel
                            );
                        } else {
                            $formattedPeriod = 'Jam Pembelajaran';
                        }
                        $startTime = substr($jadwal->start_time, 0, 5);
                        $endTime = substr($jadwal->end_time, 0, 5);
                        $className = $jadwal->class_name ?? ($jadwal->class_id ? 'Kelas ' . $jadwal->class_id : 'Semua Kelas');
                        $classInitial = strtoupper(substr(trim(str_replace('Kelas', '', $className)), 0, 2)) ?: 'KL';
                        $isJournalFilled = isset($schedulesWithJournals) && $schedulesWithJournals->contains($jadwal->id);
                    @endphp

                    <div class="guru-schedule-card group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 {{ $theme['border'] }}" data-day="{{ $jadwal->day_of_week }}">
                        <div>
                            <!-- Header: Badge Hari & Badge Jam Digital Senada Warna Hari -->
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <span class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wider {{ $theme['badge'] }}">
                                    {{ $jadwal->day_of_week }}
                                </span>
                                
                                <div class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold shadow-2xs {{ $theme['time_pill'] }}">
                                    <svg class="h-3 w-3 {{ $theme['time_icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $startTime }} - {{ $endTime }}</span>
                                </div>
                            </div>

                            <!-- Sub-info Sesi Jam -->
                            <div class="mb-3 flex items-center justify-between text-[11px] font-semibold text-slate-500">
                                <span class="inline-flex items-center gap-1 text-slate-600 font-mono">
                                    <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $formattedPeriod }}
                                </span>
                                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    {{ $jadwal->cycle_type ?? 'Normada' }}
                                </span>
                            </div>

                            <!-- Judul Mata Pelajaran -->
                            <h4 class="text-base font-bold text-slate-800 leading-snug tracking-tight group-hover:text-indigo-700 transition">
                                {{ $jadwal->subject }}
                            </h4>

                            <!-- Target Kelas Siswa (Cardlet sama seperti di Siswa) -->
                            <div class="mt-3.5 mb-4 flex items-center gap-3 rounded-xl bg-slate-50 p-2.5 border border-slate-100">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $theme['avatar'] }} text-xs font-bold shadow-2xs">
                                    {{ $classInitial }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Kelas</p>
                                    <p class="truncate text-xs font-bold text-indigo-700" title="{{ $className }}">{{ $className }}</p>
                                </div>
                                @if(!empty($jadwal->nama_ruangan))
                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                        {{ $jadwal->nama_ruangan }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Footer Card: Ruangan & Status Jurnal -->
                        <div class="mt-auto border-t border-slate-100 pt-3 flex items-center justify-between text-xs">
                            <div class="inline-flex items-center gap-1.5 font-medium text-slate-600 truncate bg-slate-100/70 px-2.5 py-1 rounded-lg">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="truncate">{{ $jadwal->nama_ruangan ?? 'Ruang Kelas' }}</span>
                            </div>

                            @if($isJournalFilled)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Sudah diisi
                                </span>
                            @else
                                <a href="{{ route('guru.jurnal') }}" class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-800 border border-amber-300 hover:bg-amber-200 transition">
                                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Isi Jurnal</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="font-semibold text-slate-700">Belum ada jadwal yang ditetapkan untuk Anda</p>
                        <p class="text-xs text-slate-400 mt-1">Jadwal mengajar resmi ditetapkan oleh Waka Kurikulum.</p>
                    </div>
                @endforelse
            </div>

            <!-- Tampilan Tabel Alternatif (Tanpa Text Patah) -->
            <div id="guru-schedule-table-view" class="hidden overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[750px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 rounded-l-lg">Hari</th>
                            <th class="whitespace-nowrap px-4 py-3">Mata Pelajaran</th>
                            <th class="whitespace-nowrap px-4 py-3">Waktu Mengajar</th>
                            <th class="whitespace-nowrap px-4 py-3">Kelas</th>
                            <th class="whitespace-nowrap px-4 py-3">Jenis</th>
                            <th class="whitespace-nowrap px-4 py-3 rounded-r-lg text-center">Status Jurnal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jadwals as $j)
                            <tr class="guru-schedule-row hover:bg-slate-50 transition" data-day="{{ $j->day_of_week }}">
                                <td class="whitespace-nowrap px-4 py-3.5 font-bold text-slate-800">{{ $j->day_of_week }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-700">{{ $j->subject }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">{{ \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time) ?? 'Waktu tersimpan' }} · {{ substr($j->start_time,0,5) }} - {{ substr($j->end_time,0,5) }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    <span class="inline-block whitespace-nowrap rounded-md bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 border border-indigo-100">
                                        {{ $j->class_name ?? ($j->class_id ? 'Kelas ' . $j->class_id : '-') }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 px-3 py-1 text-xs font-bold">{{ $j->cycle_type ?? 'Normada' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                    @if(isset($schedulesWithJournals) && $schedulesWithJournals->contains($j->id))
                                        <span class="inline-flex whitespace-nowrap items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 px-2.5 py-1 text-xs font-semibold">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            Sudah diisi
                                        </span>
                                    @else
                                        <a href="{{ route('guru.jurnal') }}" class="inline-flex whitespace-nowrap items-center gap-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 px-2.5 py-1 text-xs font-bold hover:bg-amber-200 transition">
                                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Belum diisi
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-400 italic">
                                    Belum ada jadwal yang ditetapkan untuk Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Table 2: Jurnal dari DB -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-xl font-bold text-slate-800">Jurnal Mengajar (Tersimpan)</h3>
                        <span id="dash-journal-count-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            <span id="dash-journal-visible-count">{{ $journals->count() }}</span> Data
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">Catatan materi dan kegiatan pembelajaran yang telah Anda simpan.</p>
                </div>
                <a href="{{ route('guru.jurnal') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700 transition shadow">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Tulis Jurnal</span>
                </a>
            </div>

            <!-- Filter Tanggal Jurnal Dashboard -->
            <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                    <div class="lg:col-span-3">
                        <label for="dash-filter-start-date" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Dari Tanggal
                        </label>
                        <input type="date" id="dash-filter-start-date" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 outline-none transition">
                    </div>
                    <div class="lg:col-span-3">
                        <label for="dash-filter-end-date" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Sampai Tanggal
                        </label>
                        <input type="date" id="dash-filter-end-date" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 outline-none transition">
                    </div>
                    <div class="lg:col-span-4">
                        <label for="dash-filter-search" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Cari Mapel / Materi
                        </label>
                        <input type="text" id="dash-filter-search" placeholder="Cari materi..." class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-100 outline-none transition">
                    </div>
                    <div class="lg:col-span-2 flex items-center gap-1.5">
                        <button type="button" onclick="resetDashJournalFilter()" class="h-8 w-full inline-flex items-center justify-center px-3 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Presets -->
                <div class="flex flex-wrap items-center gap-1.5 pt-2 mt-2 border-t border-slate-200/70 text-xs">
                    <span class="text-slate-500 font-semibold mr-1">Pintasan:</span>
                    <button type="button" onclick="setDashJournalPreset('today')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        Hari Ini
                    </button>
                    <button type="button" onclick="setDashJournalPreset('7days')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        7 Hari Terakhir
                    </button>
                    <button type="button" onclick="setDashJournalPreset('this_month')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        Bulan Ini
                    </button>
                    <button type="button" onclick="setDashJournalPreset('all')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        Semua
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[700px] text-left text-sm" id="dash-journal-table">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Tanggal</th>
                            <th class="px-4 py-3">Mata Pelajaran</th>
                            <th class="px-4 py-3">Materi / Kegiatan</th>
                            <th class="px-4 py-3">Bukti Foto</th>
                            <th class="px-4 py-3 rounded-r-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="dash-journal-table-body">
                        @forelse($journals as $j)
                        <tr class="dash-journal-row hover:bg-slate-50 transition" data-date="{{ $j->date }}" data-search="{{ strtolower(($j->subject ?? '') . ' ' . ($j->topic_description ?? '') . ' ' . $j->date) }}">
                            <td class="px-4 py-3.5 font-semibold text-slate-800 whitespace-nowrap">{{ $j->date }}</td>
                            <td class="px-4 py-3.5 font-medium text-indigo-700">{{ $j->subject ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-700 max-w-xs break-words">{{ $j->topic_description }}</td>
                            <td class="px-4 py-3.5">
                                @if($j->photo_path)
                                    <a href="{{ asset('storage/' . $j->photo_path) }}" target="_blank" rel="noopener" class="inline-flex flex-col items-start gap-1 text-xs font-semibold text-indigo-700 hover:underline">
                                        <img src="{{ asset('storage/' . $j->photo_path) }}" alt="Bukti foto jurnal tanggal {{ $j->date }}" class="h-12 w-16 rounded object-cover shadow-2xs">
                                        Lihat foto
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">Tanpa Foto</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Tersimpan</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400 italic">
                                Belum ada jurnal yang disimpan. Silakan klik "Tulis Jurnal" untuk mencatat kegiatan pembelajaran.
                            </td>
                        </tr>
                        @endforelse
                        <tr id="dash-no-matching-journals" class="hidden">
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400 italic">
                                Tidak ada data jurnal yang sesuai dengan filter tanggal atau pencarian yang dipilih.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    function filterGuruDay(day) {
        const cards = document.querySelectorAll('.guru-schedule-card');
        const rows = document.querySelectorAll('.guru-schedule-row');
        const buttons = document.querySelectorAll('.guru-day-btn');

        buttons.forEach(btn => {
            if (btn.getAttribute('data-day') === day) {
                btn.className = 'guru-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold bg-indigo-600 text-white shadow-xs transition';
            } else {
                btn.className = 'guru-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition';
            }
        });

        cards.forEach(card => {
            if (day === 'all' || card.getAttribute('data-day') === day) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        rows.forEach(row => {
            if (day === 'all' || row.getAttribute('data-day') === day) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function switchGuruViewMode(mode) {
        const cardGrid = document.getElementById('guru-schedule-card-grid');
        const tableView = document.getElementById('guru-schedule-table-view');
        const btnGrid = document.getElementById('guru-view-grid');
        const btnTable = document.getElementById('guru-view-table');

        if (mode === 'grid') {
            cardGrid.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnGrid.className = 'guru-toggle-btn active inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 transition';
            btnTable.className = 'guru-toggle-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
        } else {
            cardGrid.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.className = 'guru-toggle-btn active inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 transition';
            btnGrid.className = 'guru-toggle-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
        }
    }

    // Dashboard Journal Filter Functions
    const dashFilterStartDate = document.getElementById('dash-filter-start-date');
    const dashFilterEndDate = document.getElementById('dash-filter-end-date');
    const dashFilterSearch = document.getElementById('dash-filter-search');
    const dashJournalRows = document.querySelectorAll('.dash-journal-row');
    const dashNoMatching = document.getElementById('dash-no-matching-journals');
    const dashVisibleCount = document.getElementById('dash-journal-visible-count');

    function applyDashJournalFilter() {
        if (!dashJournalRows.length) return;
        const start = dashFilterStartDate ? dashFilterStartDate.value : '';
        const end = dashFilterEndDate ? dashFilterEndDate.value : '';
        const query = dashFilterSearch ? dashFilterSearch.value.toLowerCase().trim() : '';

        let visible = 0;
        dashJournalRows.forEach(row => {
            const date = row.getAttribute('data-date') || '';
            const searchContent = row.getAttribute('data-search') || '';

            let matchDate = true;
            if (start && date < start) matchDate = false;
            if (end && date > end) matchDate = false;

            let matchText = true;
            if (query && !searchContent.includes(query)) matchText = false;

            if (matchDate && matchText) {
                row.classList.remove('hidden');
                visible++;
            } else {
                row.classList.add('hidden');
            }
        });

        if (dashNoMatching) {
            if (visible === 0) {
                dashNoMatching.classList.remove('hidden');
            } else {
                dashNoMatching.classList.add('hidden');
            }
        }

        if (dashVisibleCount) {
            dashVisibleCount.textContent = visible;
        }
    }

    function setDashJournalPreset(type) {
        const today = new Date();
        const formatIso = d => {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        if (type === 'today') {
            const todayStr = formatIso(today);
            if (dashFilterStartDate) dashFilterStartDate.value = todayStr;
            if (dashFilterEndDate) dashFilterEndDate.value = todayStr;
        } else if (type === '7days') {
            const prev = new Date();
            prev.setDate(today.getDate() - 6);
            if (dashFilterStartDate) dashFilterStartDate.value = formatIso(prev);
            if (dashFilterEndDate) dashFilterEndDate.value = formatIso(today);
        } else if (type === 'this_month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            if (dashFilterStartDate) dashFilterStartDate.value = formatIso(firstDay);
            if (dashFilterEndDate) dashFilterEndDate.value = formatIso(lastDay);
        } else if (type === 'all') {
            if (dashFilterStartDate) dashFilterStartDate.value = '';
            if (dashFilterEndDate) dashFilterEndDate.value = '';
            if (dashFilterSearch) dashFilterSearch.value = '';
        }

        applyDashJournalFilter();
    }

    function resetDashJournalFilter() {
        setDashJournalPreset('all');
    }

    if (dashFilterStartDate) dashFilterStartDate.addEventListener('change', applyDashJournalFilter);
    if (dashFilterEndDate) dashFilterEndDate.addEventListener('change', applyDashJournalFilter);
    if (dashFilterSearch) dashFilterSearch.addEventListener('input', applyDashJournalFilter);
</script>
@endpush
