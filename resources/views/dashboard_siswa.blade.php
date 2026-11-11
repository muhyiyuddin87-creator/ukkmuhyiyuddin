@extends('layouts.siswa')

@php
    $isParentAccess = (int) (Auth::user()->role_id ?? 0) === 6 || str_ends_with(strtolower(Auth::user()->username ?? ''), 'p');
@endphp

@section('title', ($isParentAccess ? 'Dashboard Orang Tua - School System' : 'Dashboard Siswa - School System'))

@section('content')
            <header class="mb-8 rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                        <h2 class="mt-2 text-3xl font-bold text-slate-800">{{ $isParentAccess ? 'Dashboard Orang Tua' : 'Dashboard Siswa' }}</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($isParentAccess && isset($student) && $student)
                            <div class="rounded-full bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700">
                                Siswa: {{ $student->name }} ({{ $student->kelas }})
                            </div>
                        @endif
                        <div class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                            {{ $isParentAccess ? 'Aktivitas belajar putra/putri Anda' : 'Aktivitas belajar Anda' }}
                        </div>
                        <div class="rounded-full bg-teal-100 px-4 py-2 text-sm font-semibold text-teal-700">
                            Saat ini putaran ke-{{ $activeCycle }}
                        </div>
                    </div>
                </div>
            </header>

            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <span class="rounded-full bg-teal-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-teal-700">Putaran {{ $activeCycle }}</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Jadwal Belajar</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_jadwal'] }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Presensi</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Kehadiran Jurnal</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_kehadiran'] ?? 0 }} <span class="text-xs font-semibold text-slate-400">Pertemuan</span></p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <span class="rounded-full bg-indigo-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-indigo-700">Izin / Sakit</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Pengajuan Izin</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_izin'] }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </span>
                        <span class="rounded-full bg-sky-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-sky-700">Capaian</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Tingkat Hadir</p>
                    @php
                        $attendanceRate = ($stats['total_kehadiran'] ?? 0) > 0 ? round((($stats['total_hadir'] ?? 0) / $stats['total_kehadiran']) * 100) : 100;
                    @endphp
                    <p class="mt-2 text-3xl font-bold text-slate-800">{{ $attendanceRate }}% <span class="text-xs font-semibold text-slate-400">({{ $stats['total_hadir'] ?? 0 }} Hadir)</span></p>
                </div>
            </section>

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

            <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Jadwal Pelajaran Saya</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar jadwal pelajaran yang telah ditetapkan untuk kelas {{ $studentClass ?? '-' }}.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">
                            {{ $studentClass ? 'Kelas '.$studentClass : 'Kelas belum terhubung' }}
                        </span>
                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-700">
                            Putaran {{ $activeCycle }}
                        </span>
                        <a href="{{ route('siswa.jadwal') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition active:scale-95">
                            Lihat Halaman Jadwal
                            <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Filter Hari Interaktif (Pill style sederhana) -->
                <div class="mb-5 flex flex-wrap items-center gap-1.5">
                    <button type="button" onclick="filterDashboardDay('all')" class="dashboard-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-emerald-600 text-white shadow-xs transition" data-day="all">
                        <span>Semua Hari</span>
                        <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $jadwalList->count() }}</span>
                    </button>
                    @foreach($daysList as $d)
                        @php
                            $countDay = $jadwalList->where('day_of_week', $d)->count();
                        @endphp
                        <button type="button" onclick="filterDashboardDay('{{ $d }}')" class="dashboard-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-day="{{ $d }}">
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
                        <tbody class="divide-y divide-slate-100">
                            @forelse($jadwalList as $j)
                                @php
                                    $periodLabel = \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time);
                                    $startTime = substr($j->start_time, 0, 5);
                                    $endTime = substr($j->end_time, 0, 5);
                                    $teacherName = $j->guru_name ?? ('Guru #' . ($j->teacher_id ?? '-'));
                                    $className = $j->class_name ?? ($studentClass ?? '-');
                                    $roomName = $j->nama_ruangan ?? ($j->room_id ? 'Ruang ' . $j->room_id : '-');
                                    $jenis = in_array($j->cycle_type, ['Regular', 'Normada', null], true) ? 'Regular' : $j->cycle_type;
                                @endphp
                                <tr class="dashboard-schedule-row hover:bg-slate-50/80 transition" data-day="{{ $j->day_of_week }}">
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
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Riwayat Kehadiran Terkini -->
            <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Riwayat Kehadiran Terkini</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Catatan presensi dari jurnal mengajar guru</p>
                    </div>
                    <a href="{{ route('siswa.absensi') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                        Lihat Semua Riwayat Kehadiran
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-700">Tanggal</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Mata Pelajaran</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Guru Pengajar</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Materi / Topik Jurnal</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse (($recentAttendances ?? []) as $att)
                                @php
                                    $attStatus = $att->status ?? 'Hadir';
                                    $badgeClass = match($attStatus) {
                                        'Hadir' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'Sakit' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'Izin' => 'bg-sky-100 text-sky-800 border-sky-200',
                                        'Alpha' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        default => 'bg-slate-100 text-slate-800 border-slate-200'
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $att->date ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $att->subject ?? 'Presensi KBM' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $att->teacher_name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600 text-xs max-w-xs truncate" title="{{ $att->topic_description ?? '' }}">{{ $att->topic_description ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                            {{ $attStatus }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                        Belum ada riwayat kehadiran dari jurnal mengajar guru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

@endsection

@push('scripts')
<script>
    let currentDashboardDay = 'all';

    function filterDashboardDay(day) {
        currentDashboardDay = day;
        const rows = document.querySelectorAll('.dashboard-schedule-row');
        const cards = document.querySelectorAll('.dashboard-schedule-card');
        const buttons = document.querySelectorAll('.dashboard-day-btn');

        buttons.forEach(btn => {
            if (btn.getAttribute('data-day') === day) {
                btn.className = 'dashboard-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-emerald-600 text-white shadow-xs transition';
            } else {
                btn.className = 'dashboard-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition';
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
    }
</script>
@endpush
