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

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Daftar Jadwal Pelajaran</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Jadwal pembelajaran aktif untuk kelas {{ $studentClass ?? '-' }} pada Putaran {{ $activeCycle }}.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-800">
                            Kelas {{ $studentClass ?? 'Belum Ditentukan' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">
                            Putaran {{ $activeCycle }}
                        </span>
                    </div>
                </div>

                <!-- Filter Hari Interaktif Sederhana (Pill Style) -->
                <div class="mb-5 flex flex-wrap items-center gap-1.5" id="day-filter-container">
                    <button type="button" onclick="filterScheduleDay('all')" class="day-filter-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-emerald-600 text-white shadow-xs transition" data-day="all">
                        <span>Semua Hari</span>
                        <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $jadwals->count() }}</span>
                    </button>
                    @foreach($daysList as $d)
                        @php
                            $countDay = $jadwals->where('day_of_week', $d)->count();
                        @endphp
                        <button type="button" onclick="filterScheduleDay('{{ $d }}')" class="day-filter-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-day="{{ $d }}">
                            <span>{{ $d }}</span>
                            <span class="rounded-full bg-slate-200 text-slate-700 px-1.5 py-0.2 text-[10px] font-bold">{{ $countDay }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Tabel Bersih Sesuai Konsep Gambar 2 -->
                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50/70">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3">Hari</th>
                                <th class="whitespace-nowrap px-4 py-3">Mata Pelajaran</th>
                                <th class="whitespace-nowrap px-4 py-3">Waktu Belajar</th>
                                <th class="whitespace-nowrap px-4 py-3">Guru Pengampu</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center">Kelas</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center">Ruangan</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center">Jenis</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="schedule-table-body">
                            @forelse($jadwals as $j)
                                @php
                                    $periodLabel = \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time);
                                    $startTime = substr($j->start_time, 0, 5);
                                    $endTime = substr($j->end_time, 0, 5);
                                    $teacherName = $j->guru_name ?? ('Guru #' . ($j->teacher_id ?? '-'));
                                    $className = $j->class_name ?? ($studentClass ?? '-');
                                    $roomName = $j->nama_ruangan ?? ($j->room_id ? 'Ruang ' . $j->room_id : '-');
                                    $jenis = in_array($j->cycle_type, ['Regular', 'Normada', null], true) ? 'Regular' : $j->cycle_type;
                                @endphp
                                <tr class="schedule-table-row hover:bg-slate-50/80 transition" data-day="{{ $j->day_of_week }}">
                                    <td class="whitespace-nowrap px-4 py-3.5 font-bold text-slate-800">{{ $j->day_of_week }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-800">{{ $j->subject }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-slate-600">
                                        {{ $startTime }} - {{ $endTime }}
                                        @if($periodLabel)
                                            <span class="block text-[11px] text-slate-400 font-normal">{{ $periodLabel }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-slate-700 font-medium">{{ $teacherName }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-center text-slate-600 font-medium">{{ $className }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-center text-slate-600">{{ $roomName }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                        <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold {{ $jenis === 'Jurusan' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $jenis }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">
                                        Tidak ada jadwal pelajaran yang ditemukan untuk kelas ini pada Putaran {{ $activeCycle }}.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="empty-schedule-filter-row" class="hidden">
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">
                                    Tidak ada jadwal pada hari yang dipilih.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
@endsection

@push('scripts')
<script>
    function filterScheduleDay(day) {
        const rows = document.querySelectorAll('.schedule-table-row');
        const buttons = document.querySelectorAll('.day-filter-btn');
        const emptyRow = document.getElementById('empty-schedule-filter-row');

        buttons.forEach(btn => {
            if (btn.getAttribute('data-day') === day) {
                btn.className = 'day-filter-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-emerald-600 text-white shadow-xs transition';
            } else {
                btn.className = 'day-filter-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
            }
        });

        let visibleCount = 0;
        rows.forEach(row => {
            if (day === 'all' || row.getAttribute('data-day') === day) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptyRow) {
            if (visibleCount === 0 && rows.length > 0) {
                emptyRow.classList.remove('hidden');
            } else {
                emptyRow.classList.add('hidden');
            }
        }
    }
</script>
@endpush
