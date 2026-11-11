@extends('layouts.siswa')

@section('title', 'Jadwal Pembelajaran - School System')

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

            <header class="mb-6 rounded-2xl border border-emerald-200/80 bg-white p-6 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700">Agenda Akademik</p>
                        </div>
                        <h2 class="mt-1.5 text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Jadwal Belajar Siswa</h2>
                        <p class="mt-1 text-xs sm:text-sm text-slate-500">Daftar mata pelajaran aktif untuk kelas <span class="font-semibold text-slate-700">{{ $studentClass ?? '-' }}</span></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="rounded-xl bg-slate-100 border border-slate-200/80 px-3.5 py-2 text-xs font-semibold text-slate-700">
                            TA {{ $academicYear }}
                        </div>
                        <a href="{{ route('siswa.jadwal.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800 active:scale-95 transition">
                            <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Cetak Jadwal PDF
                        </a>
                    </div>
                </div>
            </header>

            <section class="space-y-6">
                <!-- Bar Navigasi & Filter Hari Interaktif + Mode Tampilan -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-bold text-emerald-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Kelas {{ $studentClass ?? 'Belum Ditentukan' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 border border-teal-200 px-3 py-1 text-xs font-bold text-teal-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                            Putaran Aktif: {{ $activeCycle }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Filter Hari dengan Badge Jumlah -->
                        <div class="flex flex-wrap items-center gap-1.5" id="day-filter-container">
                            <button type="button" onclick="filterScheduleDay('all')" class="day-filter-btn active inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold bg-emerald-600 text-white shadow-xs transition" data-day="all">
                                <span>Semua Hari</span>
                                <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $jadwals->count() }}</span>
                            </button>
                            @foreach($daysList as $d)
                                @php
                                    $countDay = $jadwals->where('day_of_week', $d)->count();
                                @endphp
                                <button type="button" onclick="filterScheduleDay('{{ $d }}')" class="day-filter-btn inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-day="{{ $d }}">
                                    <span>{{ $d }}</span>
                                    <span class="rounded-full bg-slate-200 text-slate-700 px-1.5 py-0.2 text-[10px] font-bold">{{ $countDay }}</span>
                                </button>
                            @endforeach
                        </div>

                        <!-- Toggle View Mode: Tabel Resmi (Default) vs Kartu -->
                        <div class="inline-flex items-center rounded-xl bg-slate-100 p-1 border border-slate-200">
                            <button type="button" onclick="switchSiswaViewMode('table')" id="siswa-view-table" class="view-toggle-btn active inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-slate-800 shadow-2xs transition">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                <span>Tabel</span>
                            </button>
                            <button type="button" onclick="switchSiswaViewMode('grid')" id="siswa-view-grid" class="view-toggle-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span>Kartu</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 1. Tampilan Konsep Utama: Tabel Jadwal Resmi Bergaris -->
                <div id="schedule-table-container" class="overflow-hidden rounded-2xl bg-white shadow-xs border border-slate-700">
                    <div class="overflow-x-auto table-scroll-container">
                        <table class="w-full text-left text-xs border-collapse border border-slate-700 min-w-[850px]">
                            <thead>
                                <tr class="bg-slate-100 text-slate-900 font-bold uppercase tracking-wider border-b border-slate-700">
                                    <th class="border border-slate-700 px-3 py-3 text-center w-12">NO</th>
                                    <th class="border border-slate-700 px-4 py-3 w-28">HARI</th>
                                    <th class="border border-slate-700 px-4 py-3 w-40">WAKTU / JAM</th>
                                    <th class="border border-slate-700 px-4 py-3">MATA PELAJARAN</th>
                                    <th class="border border-slate-700 px-4 py-3">GURU PENGAMPU</th>
                                    <th class="border border-slate-700 px-4 py-3 w-24 text-center">KELAS</th>
                                    <th class="border border-slate-700 px-4 py-3 w-28 text-center">RUANGAN</th>
                                    <th class="border border-slate-700 px-4 py-3 w-24 text-center">JENIS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700" id="schedule-table-body">
                                @forelse($jadwals as $index => $j)
                                    @php
                                        $periodLabel = \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time);
                                        $startTime = substr($j->start_time, 0, 5);
                                        $endTime = substr($j->end_time, 0, 5);
                                        $teacherName = $j->guru_name ?? ('Guru #' . $j->teacher_id);
                                        $className = $j->class_name ?? ($studentClass ?? '-');
                                        $roomName = $j->nama_ruangan ?? '-';
                                        $jenis = in_array($j->cycle_type, ['Regular', 'Normada', null], true) ? 'Normada' : $j->cycle_type;
                                    @endphp
                                    <tr class="schedule-table-row transition hover:bg-emerald-50/40 {{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}" data-day="{{ $j->day_of_week }}">
                                        <td class="border border-slate-700 px-3 py-3 text-center font-bold text-slate-800 schedule-row-num">{{ $index + 1 }}</td>
                                        <td class="border border-slate-700 px-4 py-3 font-bold text-slate-900">{{ $j->day_of_week }}</td>
                                        <td class="border border-slate-700 px-4 py-3 text-slate-800 whitespace-nowrap">
                                            <div class="font-medium text-slate-900">{{ $startTime }} - {{ $endTime }} WIB</div>
                                            @if($periodLabel)
                                                <div class="text-[10px] text-slate-500 italic mt-0.5">{{ $periodLabel }}</div>
                                            @endif
                                        </td>
                                        <td class="border border-slate-700 px-4 py-3 font-bold text-slate-900">{{ $j->subject }}</td>
                                        <td class="border border-slate-700 px-4 py-3 font-medium text-slate-800">{{ $teacherName }}</td>
                                        <td class="border border-slate-700 px-4 py-3 text-center font-bold text-slate-900">{{ $className }}</td>
                                        <td class="border border-slate-700 px-4 py-3 text-center text-slate-700">{{ $roomName }}</td>
                                        <td class="border border-slate-700 px-4 py-3 text-center font-semibold text-slate-700">{{ $jenis }}</td>
                                    </tr>
                                @empty
                                    <tr id="empty-schedule-row">
                                        <td colspan="8" class="border border-slate-700 px-4 py-12 text-center text-slate-500 italic">
                                            Tidak ada jadwal pelajaran yang ditemukan untuk kelas ini pada Putaran {{ $activeCycle }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Tampilan Kartu Jadwal Alternatif -->
                <div id="schedule-card-grid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
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
                            $teacherName = $jadwal->guru_name ?? ('Guru #' . $jadwal->teacher_id);
                            $initial = strtoupper(substr(trim($teacherName), 0, 1));
                        @endphp

                        <div class="schedule-card group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 {{ $theme['border'] }}" data-day="{{ $jadwal->day_of_week }}">
                            <div>
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

                                <div class="mb-3 flex items-center justify-between text-[11px] font-semibold text-slate-500">
                                    <span class="inline-flex items-center gap-1 text-slate-600 font-mono">
                                        <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ $formattedPeriod }}
                                    </span>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                        {{ $jadwal->cycle_type ?? 'Normada' }}
                                    </span>
                                </div>

                                <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-snug tracking-tight group-hover:text-emerald-700 transition">
                                    {{ $jadwal->subject }}
                                </h3>

                                <div class="mt-3.5 mb-4 flex items-center gap-3 rounded-xl bg-slate-50 p-2.5 border border-slate-100">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $theme['avatar'] }} text-xs font-bold shadow-2xs">
                                        {{ $initial }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Guru Pengampu</p>
                                        <p class="truncate text-xs font-semibold text-slate-800" title="{{ $teacherName }}">{{ $teacherName }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto border-t border-slate-100 pt-3 flex items-center justify-between text-xs">
                                <div class="inline-flex items-center gap-1.5 font-medium text-slate-600 truncate bg-slate-100/70 px-2.5 py-1 rounded-lg">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="truncate">{{ $jadwal->nama_ruangan ?? 'Ruang Kelas' }}</span>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200/80">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $jadwal->cycle_number ? 'Putaran ' . $jadwal->cycle_number : ($jadwal->cycle_type ?? 'Normada') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                            <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <p class="font-semibold text-slate-700">Tidak ada jadwal ditemukan</p>
                            <p class="text-xs text-slate-400 mt-1">Belum ada jadwal pelajaran untuk kelas ini pada putaran ini.</p>
                        </div>
                    @endforelse
                </div>
            </section>
@endsection

@push('scripts')
<script>
    let currentFilteredDay = 'all';

    function filterScheduleDay(day) {
        currentFilteredDay = day;
        const rows = document.querySelectorAll('.schedule-table-row');
        const cards = document.querySelectorAll('.schedule-card');
        const buttons = document.querySelectorAll('.day-filter-btn');

        buttons.forEach(btn => {
            if (btn.getAttribute('data-day') === day) {
                btn.className = 'day-filter-btn active inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold bg-emerald-600 text-white shadow-xs transition';
            } else {
                btn.className = 'day-filter-btn inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
            }
        });

        // Filter tabel & perbarui nomor urut NO secara dinamis
        let visibleCount = 0;
        rows.forEach(row => {
            if (day === 'all' || row.getAttribute('data-day') === day) {
                row.style.display = '';
                visibleCount++;
                const numCell = row.querySelector('.schedule-row-num');
                if (numCell) numCell.textContent = visibleCount;
            } else {
                row.style.display = 'none';
            }
        });

        // Filter kartu (jika mode kartu aktif)
        cards.forEach(card => {
            if (day === 'all' || card.getAttribute('data-day') === day) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function switchSiswaViewMode(mode) {
        const tableContainer = document.getElementById('schedule-table-container');
        const cardGrid = document.getElementById('schedule-card-grid');
        const btnTable = document.getElementById('siswa-view-table');
        const btnGrid = document.getElementById('siswa-view-grid');

        if (mode === 'table') {
            tableContainer.classList.remove('hidden');
            cardGrid.classList.add('hidden');
            btnTable.className = 'view-toggle-btn active inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-slate-800 shadow-2xs transition';
            btnGrid.className = 'view-toggle-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
        } else {
            tableContainer.classList.add('hidden');
            cardGrid.classList.remove('hidden');
            btnGrid.className = 'view-toggle-btn active inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-slate-800 shadow-2xs transition';
            btnTable.className = 'view-toggle-btn inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
        }

        // Jalankan filter hari pada tampilan yang baru dibuka
        filterScheduleDay(currentFilteredDay);
    }
</script>
@endpush
