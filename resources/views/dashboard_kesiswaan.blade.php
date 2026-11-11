@extends('layouts.kesiswaan')

@section('title', 'Dashboard Kesiswaan - School System')

@section('content')
    @php
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
            <header class="mb-8 rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang, {{ $user->fullname ?? $user->username }}</p>
                        <h2 class="mt-2 text-3xl font-bold text-slate-800">Dashboard Kesiswaan</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700">
                            Verifikasi & Riwayat Izin Siswa
                        </div>
                        <div class="rounded-full bg-orange-100 px-4 py-2 text-sm font-semibold text-orange-700">
                            Saat ini putaran ke-{{ $activeCycle }}
                        </div>
                        @if(isset($bkClassNames) && $bkClassNames->isNotEmpty())
                            <div class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700 border border-emerald-200">
                                {{ count($bkClassNames) }} Kelas Bimbingan BK
                            </div>
                        @endif
                        @if(isset($jadwals) && $jadwals->isNotEmpty())
                            <div class="rounded-full bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700 border border-sky-200">
                                {{ count($jadwals) }} Jam Jadwal Mengajar
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ (isset($jadwals) && $jadwals->isNotEmpty()) || (isset($bkClassNames) && $bkClassNames->isNotEmpty()) ? 'lg:grid-cols-4' : '' }}">
                <div class="rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Izin Menunggu Persetujuan</p>
                    <p class="mt-2 text-3xl font-bold text-amber-700">{{ $stats['pending_izin'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Riwayat Selesai Diproses</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['riwayat_izin'] ?? 0 }}</p>
                </div>
                @if(isset($jadwals) && $jadwals->isNotEmpty())
                <div class="rounded-2xl border border-sky-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Jadwal Mengajar (Putaran {{ $activeCycle }})</p>
                    <p class="mt-2 text-3xl font-bold text-sky-700">{{ $jadwals->count() }}</p>
                </div>
                @endif
                @if(isset($bkClassNames) && $bkClassNames->isNotEmpty())
                <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Kelas Bimbingan BK</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $bkClassNames->count() }}</p>
                </div>
                @endif
            </section>

            @if(isset($jadwals) && $jadwals->isNotEmpty())
            <!-- Section Jadwal Mengajar Kesiswaan Modern (Sama Persis seperti di Siswa) -->
            <section class="mt-6 rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <p class="text-xs font-bold uppercase tracking-widest text-amber-700">Agenda Mengajar & Bimbingan</p>
                        </div>
                        <h3 class="mt-1 text-xl font-bold text-slate-800">Jadwal Mengajar Anda</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Tahun Ajaran {{ $academicYear }} · Putaran {{ $activeCycle }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-amber-800 border border-amber-200">
                            Putaran {{ $activeCycle }}
                        </span>
                        <a href="{{ route('guru.jadwal') }}" class="rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition active:scale-95 shadow-2xs">
                            Kelola Jadwal
                        </a>
                        <a href="{{ route('guru.jurnal') }}" class="rounded-xl bg-amber-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-700 transition active:scale-95 shadow-xs">
                            Isi Jurnal
                        </a>
                        <a href="{{ route('guru.jadwal.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition active:scale-95">
                            <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>PDF</span>
                        </a>
                    </div>
                </div>

                <!-- Filter Hari Interaktif + Switch View -->
                <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-amber-50/50 p-2.5 rounded-xl border border-amber-200/80">
                    <div class="flex flex-wrap items-center gap-1.5" id="kesiswaan-day-filter-container">
                        <button type="button" onclick="filterKesiswaanDay('all')" class="kesiswaan-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold bg-amber-600 text-white shadow-xs transition" data-day="all">
                            <span>Semua Hari</span>
                            <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $jadwals->count() }}</span>
                        </button>
                        @foreach($daysList as $d)
                            @php
                                $countDay = $jadwals->where('day_of_week', $d)->count();
                            @endphp
                            <button type="button" onclick="filterKesiswaanDay('{{ $d }}')" class="kesiswaan-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition" data-day="{{ $d }}">
                                <span>{{ $d }}</span>
                                <span class="rounded-full bg-slate-100 text-slate-700 px-1.5 py-0.2 text-[10px] font-bold">{{ $countDay }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="inline-flex items-center rounded-xl bg-white p-1 border border-slate-200">
                        <button type="button" onclick="switchKesiswaanViewMode('table')" id="kesiswaan-view-table" class="kesiswaan-toggle-btn active inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 transition">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            <span>Tabel</span>
                        </button>
                        <button type="button" onclick="switchKesiswaanViewMode('grid')" id="kesiswaan-view-grid" class="kesiswaan-toggle-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Kartu</span>
                        </button>
                    </div>
                </div>

                <!-- 1. Tampilan Konsep Utama: Tabel Jadwal Resmi Bergaris -->
                <div id="kesiswaan-schedule-table-view" class="overflow-hidden rounded-2xl bg-white shadow-xs border border-slate-700">
                    <div class="overflow-x-auto table-scroll-container">
                        <table class="official-schedule-table w-full text-left text-xs border-collapse border border-slate-700 min-w-[950px]">
                            <thead>
                                <tr class="bg-slate-100 text-slate-900 font-bold uppercase tracking-wider border-b border-slate-700">
                                    <th class="border border-slate-700 px-3 py-3 text-center w-12">NO</th>
                                    <th class="border border-slate-700 px-4 py-3 w-28">HARI</th>
                                    <th class="border border-slate-700 px-4 py-3 w-40">WAKTU / JAM</th>
                                    <th class="border border-slate-700 px-4 py-3">MATA PELAJARAN</th>
                                    <th class="border border-slate-700 px-4 py-3">GURU PENGAMPU</th>
                                    <th class="border border-slate-700 px-4 py-3 w-28 text-center">KELAS</th>
                                    <th class="border border-slate-700 px-4 py-3 w-28 text-center">RUANGAN</th>
                                    <th class="border border-slate-700 px-4 py-3 w-24 text-center">JENIS</th>
                                    <th class="border border-slate-700 px-4 py-3 w-24 text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700" id="kesiswaan-schedule-table-body">
                                @forelse($jadwals as $index => $jadwal)
                                @php
                                    $periodLabel = \App\Support\SchoolTimetable::labelFor($jadwal->day_of_week, $jadwal->start_time, $jadwal->end_time);
                                    $tableRoom = $jadwal->nama_ruangan ?? ($jadwal->room_name ?? ($jadwal->room_id ? 'Ruang ' . $jadwal->room_id : '-'));
                                    $teacherName = $jadwal->guru_name ?? (Auth::user()->fullname ?? Auth::user()->username);
                                    $jenis = in_array($jadwal->cycle_type, ['Regular', 'Normada', null], true) ? 'Normada' : $jadwal->cycle_type;
                                @endphp
                                <tr class="kesiswaan-schedule-row {{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }} hover:bg-slate-100/70 transition" data-day="{{ $jadwal->day_of_week }}">
                                    <td class="kesiswaan-schedule-row-num border border-slate-700 px-3 py-2.5 text-center font-bold text-slate-800">{{ $index + 1 }}</td>
                                    <td class="border border-slate-700 px-4 py-2.5 font-bold text-slate-900 whitespace-nowrap">{{ $jadwal->day_of_week }}</td>
                                    <td class="border border-slate-700 px-4 py-2.5 text-slate-800 whitespace-nowrap">
                                        <div class="font-bold">{{ substr($jadwal->start_time, 0, 5) }} - {{ substr($jadwal->end_time, 0, 5) }} WIB</div>
                                        @if($periodLabel)
                                            <div class="text-[10px] text-slate-500 font-medium">{{ $periodLabel }}</div>
                                        @endif
                                    </td>
                                    <td class="border border-slate-700 px-4 py-2.5 font-bold text-slate-900">{{ $jadwal->subject }}</td>
                                    <td class="border border-slate-700 px-4 py-2.5 font-medium text-slate-800">{{ $teacherName }}</td>
                                    <td class="border border-slate-700 px-4 py-2.5 text-center font-bold text-slate-900 whitespace-nowrap">
                                        {{ $jadwal->class_name ?? '-' }}
                                    </td>
                                    <td class="border border-slate-700 px-4 py-2.5 text-center text-slate-700 whitespace-nowrap">
                                        {{ $tableRoom }}
                                    </td>
                                    <td class="border border-slate-700 px-4 py-2.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $jenis === 'Jurusan' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $jenis }}
                                        </span>
                                    </td>
                                    <td class="border border-slate-700 px-4 py-2.5 text-center whitespace-nowrap">
                                        <a href="{{ route('guru.jurnal') }}" class="inline-flex items-center gap-1 rounded-lg bg-amber-100 border border-amber-300 px-2.5 py-1 text-xs font-bold text-amber-800 hover:bg-amber-200 transition">
                                            <span>Jurnal</span>
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="border border-slate-700 px-4 py-8 text-center text-slate-400 italic">
                                        Belum ada jadwal mengajar pada Putaran {{ $activeCycle }}.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Grid Kartu Jadwal Modern (Tampilan Alternatif) -->
                <div id="kesiswaan-schedule-card-grid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
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
                            $roomDisplay = null;
                            if (isset($jadwal->nama_ruangan) && !empty($jadwal->nama_ruangan)) {
                                $roomDisplay = $jadwal->nama_ruangan;
                            } elseif (isset($jadwal->room_name) && !empty($jadwal->room_name)) {
                                $roomDisplay = $jadwal->room_name;
                            } elseif (isset($jadwal->room_id) && !empty($jadwal->room_id)) {
                                $roomDisplay = 'Ruang ' . $jadwal->room_id;
                            }
                        @endphp

                        <div class="kesiswaan-schedule-card group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 {{ $theme['border'] }}" data-day="{{ $jadwal->day_of_week }}">
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
                                <h4 class="text-base font-bold text-slate-800 leading-snug tracking-tight group-hover:text-amber-800 transition">
                                    {{ $jadwal->subject }}
                                </h4>

                                <!-- Target Kelas Siswa (Cardlet sama seperti di Siswa) -->
                                <div class="mt-3.5 mb-4 flex items-center gap-3 rounded-xl bg-slate-50 p-2.5 border border-slate-100">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $theme['avatar'] }} text-xs font-bold shadow-2xs">
                                        {{ $classInitial }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Kelas</p>
                                        <p class="truncate text-xs font-bold text-amber-800" title="{{ $className }}">{{ $className }}</p>
                                    </div>
                                    @if($roomDisplay)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                            {{ $roomDisplay }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer Card: Ruangan, Putaran & Aksi Jurnal -->
                            <div class="mt-auto border-t border-slate-100 pt-3 flex items-center justify-between text-xs">
                                <div class="inline-flex items-center gap-1.5 font-medium text-slate-600 truncate bg-slate-100/70 px-2.5 py-1 rounded-lg">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="truncate">{{ $roomDisplay ?? 'Ruang Kelas' }}</span>
                                </div>

                                <a href="{{ route('guru.jurnal') }}" class="inline-flex items-center gap-1 rounded-lg bg-amber-100 border border-amber-200 px-2.5 py-1 text-[11px] font-semibold text-amber-800 hover:bg-amber-200 transition">
                                    <span>Jurnal</span>
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                            <p class="font-semibold text-slate-700">Belum ada jadwal mengajar pada Putaran {{ $activeCycle }}</p>
                        </div>
                    @endforelse
                </div>

                <!-- Tampilan Tabel Alternatif (Tanpa Text Patah) -->
                <div id="kesiswaan-schedule-table-view" class="hidden overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[750px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3 rounded-l-lg">Hari</th>
                                <th class="whitespace-nowrap px-4 py-3">Waktu / Sesi</th>
                                <th class="whitespace-nowrap px-4 py-3">Mata Pelajaran</th>
                                <th class="whitespace-nowrap px-4 py-3">Kelas</th>
                                <th class="whitespace-nowrap px-4 py-3 rounded-r-lg">Ruangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($jadwals as $jadwal)
                            @php
                                $tableRoom = null;
                                if (isset($jadwal->nama_ruangan) && !empty($jadwal->nama_ruangan)) {
                                    $tableRoom = $jadwal->nama_ruangan;
                                } elseif (isset($jadwal->room_name) && !empty($jadwal->room_name)) {
                                    $tableRoom = $jadwal->room_name;
                                } elseif (isset($jadwal->room_id) && !empty($jadwal->room_id)) {
                                    $tableRoom = 'Ruang ' . $jadwal->room_id;
                                }
                            @endphp
                            <tr class="kesiswaan-schedule-row hover:bg-slate-50 transition" data-day="{{ $jadwal->day_of_week }}">
                                <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-800">{{ $jadwal->day_of_week }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">{{ \App\Support\SchoolTimetable::labelFor($jadwal->day_of_week, $jadwal->start_time, $jadwal->end_time) ?? ($jadwal->start_period_code ?? $jadwal->start_time ?? '-') }} · {{ substr($jadwal->start_time,0,5) }} - {{ substr($jadwal->end_time,0,5) }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-amber-800">{{ $jadwal->subject }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    <span class="inline-block whitespace-nowrap rounded-md bg-sky-50 px-2.5 py-0.5 text-xs font-semibold text-sky-700 border border-sky-200">{{ $jadwal->class_name ?? '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">{{ $tableRoom ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            <section class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('kesiswaan.izin') }}" class="inline-flex rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white shadow transition hover:bg-amber-700">
                    Verifikasi & Riwayat Izin Siswa
                </a>
                <a href="{{ route('guru.jadwal') }}" class="inline-flex rounded-xl bg-sky-700 px-5 py-3 font-semibold text-white shadow transition hover:bg-sky-800">
                    Jadwal Mengajar
                </a>
                <a href="{{ route('guru.jurnal') }}" class="inline-flex rounded-xl bg-indigo-700 px-5 py-3 font-semibold text-white shadow transition hover:bg-indigo-800">
                    Isi Jurnal
                </a>
            </section>
@endsection

@push('scripts')
<script>
    let currentKesiswaanDay = 'all';

    function filterKesiswaanDay(day) {
        currentKesiswaanDay = day;
        const cards = document.querySelectorAll('.kesiswaan-schedule-card');
        const rows = document.querySelectorAll('.kesiswaan-schedule-row');
        const buttons = document.querySelectorAll('.kesiswaan-day-btn');

        buttons.forEach(btn => {
            if (btn.getAttribute('data-day') === day) {
                btn.className = 'kesiswaan-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold bg-amber-600 text-white shadow-xs transition';
            } else {
                btn.className = 'kesiswaan-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition';
            }
        });

        // Filter tabel & perbarui nomor urut NO secara dinamis
        let visibleCount = 0;
        rows.forEach(row => {
            if (day === 'all' || row.getAttribute('data-day') === day) {
                row.style.display = '';
                visibleCount++;
                const numCell = row.querySelector('.kesiswaan-schedule-row-num');
                if (numCell) numCell.textContent = visibleCount;
            } else {
                row.style.display = 'none';
            }
        });

        // Filter kartu
        cards.forEach(card => {
            if (day === 'all' || card.getAttribute('data-day') === day) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function switchKesiswaanViewMode(mode) {
        const cardGrid = document.getElementById('kesiswaan-schedule-card-grid');
        const tableView = document.getElementById('kesiswaan-schedule-table-view');
        const btnGrid = document.getElementById('kesiswaan-view-grid');
        const btnTable = document.getElementById('kesiswaan-view-table');

        if (mode === 'grid') {
            cardGrid.classList.remove('hidden');
            tableView.classList.add('hidden');
            btnGrid.className = 'kesiswaan-toggle-btn active inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 transition';
            btnTable.className = 'kesiswaan-toggle-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
        } else {
            cardGrid.classList.add('hidden');
            tableView.classList.remove('hidden');
            btnTable.className = 'kesiswaan-toggle-btn active inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 transition';
            btnGrid.className = 'kesiswaan-toggle-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
        }

        filterKesiswaanDay(currentKesiswaanDay);
    }
</script>
@endpush
