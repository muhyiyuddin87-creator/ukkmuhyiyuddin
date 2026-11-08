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

            <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Hari Ini</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Jadwal</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_jadwal'] }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Izin</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Izin</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total_izin'] }}</p>
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
            </section>

            <section class="mb-8">
                <div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 p-6 text-white shadow-lg">
                    <h3 class="text-xl font-bold mb-4">{{ (int) (Auth::user()->role_id ?? 0) === 6 ? 'Catatan Siswa (Putra/Putri Anda)' : 'Catatan Siswa' }}</h3>
                    <ul class="space-y-3 text-sm text-emerald-50">
                        <li class="flex items-center justify-between"><span>Kehadiran Jurnal</span><span class="font-semibold">{{ $stats['total_hadir'] ?? 0 }} Hadir / {{ $stats['total_kehadiran'] ?? 0 }} Pertemuan</span></li>
                        <li class="flex items-center justify-between"><span>Izin</span><span class="font-semibold">{{ $stats['total_izin'] ?? 0 }} Pengajuan</span></li>
                        <li class="flex items-center justify-between"><span>Jadwal</span><span class="font-semibold">Aktif</span></li>
                        <li class="flex items-center justify-between"><span>Status Akun</span><span class="font-semibold">Aktif</span></li>
                    </ul>
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
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <p class="text-xs font-bold uppercase tracking-widest text-emerald-700">Agenda Belajar</p>
                        </div>
                        <h3 class="mt-1 text-xl font-bold text-slate-800">Jadwal Pelajaran Tahun Ajaran {{ $academicYear }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar mata pelajaran aktif untuk kelas <span class="font-semibold text-slate-700">{{ $studentClass ?? 'Belum terhubung' }}</span></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">
                            {{ $studentClass ? 'Kelas '.$studentClass : 'Kelas belum terhubung' }}
                        </span>
                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-700">
                            Putaran {{ $activeCycle }}
                        </span>
                        <a href="{{ route('siswa.jadwal') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800 transition active:scale-95">
                            Lihat Semua & Cetak PDF
                            <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Filter Hari Interaktif -->
                <div class="mb-6 flex flex-wrap items-center gap-1.5 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                    <button type="button" onclick="filterDashboardDay('all')" class="dashboard-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-emerald-600 text-white shadow-xs transition" data-day="all">
                        <span>Semua Hari</span>
                        <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $jadwalList->count() }}</span>
                    </button>
                    @foreach($daysList as $d)
                        @php
                            $countDay = $jadwalList->where('day_of_week', $d)->count();
                        @endphp
                        <button type="button" onclick="filterDashboardDay('{{ $d }}')" class="dashboard-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition" data-day="{{ $d }}">
                            <span>{{ $d }}</span>
                            <span class="rounded-full bg-slate-100 text-slate-700 px-1.5 py-0.2 text-[10px] font-bold">{{ $countDay }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Grid Kartu Jadwal Modern -->
                <div id="dashboard-schedule-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @forelse ($jadwalList as $jadwal)
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
                            $teacherName = $jadwal->guru_name ?? ('Guru #' . ($jadwal->teacher_id ?? '-'));
                            $initial = strtoupper(substr(trim($teacherName), 0, 1));
                        @endphp

                        <div class="dashboard-schedule-card group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 {{ $theme['border'] }}" data-day="{{ $jadwal->day_of_week }}">
                            <div>
                                <!-- Header Atas: Badge Hari & Badge Jam Digital Senada Warna Hari -->
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

                                <!-- Sub-info Sesi Jam & Siklus -->
                                <div class="mb-3 flex items-center justify-between text-[11px] font-semibold text-slate-500">
                                    <span class="inline-flex items-center gap-1 text-slate-600 font-mono">
                                        <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ $formattedPeriod }}
                                    </span>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                        {{ $jadwal->cycle_type ?? 'Reguler' }}
                                    </span>
                                </div>

                                <!-- Judul Mata Pelajaran -->
                                <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-snug tracking-tight group-hover:text-emerald-700 transition">
                                    {{ $jadwal->subject }}
                                </h3>

                                <!-- Profil Guru Pengampu (Cardlet) -->
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

                            <!-- Footer Card: Ruangan & Putaran -->
                            <div class="mt-auto border-t border-slate-100 pt-3 flex items-center justify-between text-xs">
                                <div class="inline-flex items-center gap-1.5 font-medium text-slate-600 truncate bg-slate-100/70 px-2.5 py-1 rounded-lg">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="truncate">{{ $jadwal->nama_ruangan ?? ($jadwal->room_id ? 'Ruang ' . $jadwal->room_id : 'Ruang Kelas') }}</span>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200/80">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $jadwal->cycle_number ? 'Putaran ' . $jadwal->cycle_number : ($jadwal->cycle_type ?? 'Reguler') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                            <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <p class="font-semibold text-slate-700">Tidak ada jadwal ditemukan</p>
                            <p class="text-xs text-slate-400 mt-1">
                                @if(!$studentClass)
                                    Akun siswa belum terhubung ke data siswa yang memiliki kelas.
                                @else
                                    Belum ada jadwal pelajaran untuk kelas {{ $studentClass }} pada putaran ini.
                                @endif
                            </p>
                        </div>
                    @endforelse
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
    function filterDashboardDay(day) {
        const cards = document.querySelectorAll('.dashboard-schedule-card');
        const buttons = document.querySelectorAll('.dashboard-day-btn');

        buttons.forEach(btn => {
            if (btn.getAttribute('data-day') === day) {
                btn.className = 'dashboard-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-emerald-600 text-white shadow-xs transition';
            } else {
                btn.className = 'dashboard-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition';
            }
        });

        cards.forEach(card => {
            if (day === 'all' || card.getAttribute('data-day') === day) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
