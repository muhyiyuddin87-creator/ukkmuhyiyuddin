@extends((int)(Auth::user()->role_id ?? 0) === 4 ? 'layouts.kesiswaan' : 'layouts.guru')

@section('title', 'Jadwal Mengajar - School System')

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
        $isKesiswaan = (int)(Auth::user()->role_id ?? 0) === 4;
        $primaryColor = $isKesiswaan ? 'amber' : 'indigo';
    @endphp

    <!-- Header Agenda Guru Modern (Senada dengan Siswa) -->
    <header class="mb-6 rounded-2xl border {{ $isKesiswaan ? 'border-amber-200/80' : 'border-indigo-200/80' }} bg-white p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2 w-2 rounded-full {{ $isKesiswaan ? 'bg-amber-500' : 'bg-indigo-500' }} animate-pulse"></span>
                    <p class="text-xs font-bold uppercase tracking-widest {{ $isKesiswaan ? 'text-amber-700' : 'text-indigo-700' }}">{{ $isKesiswaan ? 'Ruang Kerja Kesiswaan' : 'Agenda Akademik Guru' }}</p>
                </div>
                <h2 class="mt-1.5 text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Jadwal Mengajar Guru</h2>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">Daftar agenda mengajar aktif tahun ajaran <span class="font-semibold text-slate-700">{{ $academicYear }}</span> (Putaran ke-{{ $activeCycle }})</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-xl bg-slate-100 border border-slate-200/80 px-3.5 py-2 text-xs font-semibold text-slate-700">
                    TA {{ $academicYear }}
                </div>
                <a href="{{ route('guru.jadwal.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800 active:scale-95 transition">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Ubah ke PDF / Cetak Jadwal</span>
                </a>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-5 rounded-xl bg-emerald-100 border border-emerald-300 px-5 py-3 text-emerald-800 font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl bg-rose-100 border border-rose-300 px-5 py-3 text-rose-800 font-semibold space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if($scheduleLocked)
        <div class="mb-6 rounded-xl border border-rose-300 bg-rose-50 px-5 py-4 font-semibold text-rose-800 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Putaran {{ $activeCycle }} sedang dikunci oleh Waka Kurikulum. Jadwal resmi tersimpan aktif untuk KBM.</span>
        </div>
    @endif

    <section class="space-y-6 mb-8">
        <!-- Bar Navigasi & Filter Hari Interaktif + Switch Tampilan -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full {{ $isKesiswaan ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-indigo-50 border-indigo-200 text-indigo-700' }} border px-3 py-1 text-xs font-bold">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    {{ Auth::user()->fullname ?? Auth::user()->username }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 border border-teal-200 px-3 py-1 text-xs font-bold text-teal-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                    Putaran Aktif: {{ $activeCycle }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-bold text-slate-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $jadwals->count() }} Jadwal Mengajar
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Filter Hari dengan Badge Jumlah -->
                <div class="flex flex-wrap items-center gap-1.5" id="day-filter-container">
                    <button type="button" onclick="filterScheduleDay('all')" class="day-filter-btn active inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold {{ $isKesiswaan ? 'bg-amber-600' : 'bg-indigo-600' }} text-white shadow-xs transition" data-day="all">
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

                <!-- Toggle View Mode: Kartu vs Tabel -->
                <div class="inline-flex items-center rounded-xl bg-slate-100 p-1 border border-slate-200">
                    <button type="button" onclick="switchViewMode('grid')" id="view-mode-grid" class="view-toggle-btn active inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-slate-800 shadow-2xs transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Kartu</span>
                    </button>
                    <button type="button" onclick="switchViewMode('table')" id="view-mode-table" class="view-toggle-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span>Tabel</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid Kartu Jadwal Modern (Sama Persis Seperti di Siswa) -->
        <div id="schedule-card-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
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
                @endphp

                <div class="schedule-card group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 {{ $theme['border'] }}" data-day="{{ $jadwal->day_of_week }}">
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
                        <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-snug tracking-tight group-hover:text-indigo-700 transition">
                            {{ $jadwal->subject }}
                        </h3>

                        <!-- Target Kelas Siswa (Cardlet Sama Persis Seperti Guru Pengampu di Siswa) -->
                        <div class="mt-3.5 mb-4 flex items-center gap-3 rounded-xl bg-slate-50 p-2.5 border border-slate-100">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $theme['avatar'] }} text-xs font-bold shadow-2xs">
                                {{ $classInitial }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Kelas Siswa</p>
                                <p class="truncate text-xs font-bold text-indigo-700" title="{{ $className }}">{{ $className }}</p>
                            </div>
                            @if(!empty($jadwal->nama_ruangan))
                                <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                    {{ $jadwal->nama_ruangan }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Card: Ruangan, Putaran, & Link Cepat Jurnal -->
                    <div class="mt-auto border-t border-slate-100 pt-3 flex items-center justify-between text-xs">
                        <div class="inline-flex items-center gap-1.5 font-medium text-slate-600 truncate bg-slate-100/70 px-2.5 py-1 rounded-lg">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="truncate">{{ $jadwal->nama_ruangan ?? 'Ruang Kelas' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 border border-emerald-200/80">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                {{ $jadwal->cycle_number ? 'Putaran ' . $jadwal->cycle_number : ($jadwal->cycle_type ?? 'Normada') }}
                            </span>
                            <a href="{{ route('guru.jurnal') }}" class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 border border-indigo-200 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100 transition" title="Buka form isi jurnal untuk jadwal ini">
                                <span>Jurnal</span>
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                    <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="font-semibold text-slate-700">Tidak ada jadwal ditemukan</p>
                    <p class="text-xs text-slate-400 mt-1">Belum ada agenda mengajar aktif untuk akun ini pada Putaran {{ $activeCycle }}.</p>
                </div>
            @endforelse
        </div>

        <!-- Tampilan Tabel Alternatif (Dengan whitespace-nowrap tanpa teks patah) -->
        <div id="schedule-table-view" class="hidden rounded-2xl bg-white p-6 shadow-xs border border-slate-200/80">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Tabel Rincian Jadwal Mengajar</h3>
                    <p class="text-xs text-slate-500">Tampilan matriks lengkap jadwal mengajar guru.</p>
                </div>
            </div>
            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[950px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap rounded-l-lg px-4 py-3">Hari</th>
                            <th class="whitespace-nowrap px-4 py-3">Mata Pelajaran</th>
                            <th class="whitespace-nowrap px-4 py-3">Waktu Mengajar</th>
                            <th class="whitespace-nowrap px-4 py-3">Kelas</th>
                            <th class="whitespace-nowrap px-4 py-3">Ruangan</th>
                            <th class="whitespace-nowrap px-4 py-3">Putaran</th>
                            <th class="whitespace-nowrap rounded-r-lg px-4 py-3">Jenis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jadwals as $j)
                            <tr class="schedule-table-row hover:bg-slate-50 transition" data-day="{{ $j->day_of_week }}">
                                <td class="whitespace-nowrap px-4 py-3.5 font-bold text-slate-800">{{ $j->day_of_week }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-700">{{ $j->subject }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">
                                    {{ \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time) ?? 'Waktu tersimpan' }} · {{ substr($j->start_time, 0, 5) }} - {{ substr($j->end_time, 0, 5) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    <span class="inline-block whitespace-nowrap rounded-md bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 border border-indigo-100">
                                        {{ $j->class_name ?? ($j->class_id ? 'Kelas ' . $j->class_id : '-') }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    @if(!empty($j->nama_ruangan))
                                        <span class="inline-flex whitespace-nowrap items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $j->nama_ruangan }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-xs">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">Putaran {{ $j->cycle_number }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5">
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1 text-xs font-bold">{{ $j->cycle_type ?? 'Normada' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">Belum ada jadwal tersimpan untuk putaran ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Form Tambah Jadwal Baru (Jika tidak dikunci) -->
    @if(!$scheduleLocked)
        <section class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-800">Tambah Jadwal Baru</h3>
                <p class="text-xs text-slate-500">Input rencana mengajar secara mandiri untuk Putaran {{ $activeCycle }}.</p>
            </div>
            <form method="POST" action="{{ route('guru.jadwal.store') }}">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="border-b text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="whitespace-nowrap px-3 py-3">Hari</th>
                                <th class="whitespace-nowrap px-3 py-3">Mata Pelajaran</th>
                                <th class="whitespace-nowrap px-3 py-3">Mulai</th>
                                <th class="whitespace-nowrap px-3 py-3">Selesai</th>
                                <th class="whitespace-nowrap px-3 py-3">Kelas</th>
                                <th class="whitespace-nowrap px-3 py-3">Jenis</th>
                            </tr>
                        </thead>
                        <tbody id="jadwal-tbody" class="divide-y divide-slate-100">
                            <tr class="jadwal-row">
                                <td class="px-3 py-4">
                                    <select name="day_of_week[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                                        <option>Senin</option>
                                        <option>Selasa</option>
                                        <option>Rabu</option>
                                        <option>Kamis</option>
                                        <option>Jumat</option>
                                    </select>
                                </td>
                                <td class="px-3 py-4">
                                    <input type="text" name="subject[]" placeholder="Mata pelajaran" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                                </td>
                                <td class="px-3 py-4">
                                    <input type="time" name="start_time[]" value="07:30" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                                </td>
                                <td class="px-3 py-4">
                                    <input type="time" name="end_time[]" value="09:00" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                                </td>
                                <td class="px-3 py-4">
                                    <input type="number" name="class_id[]" min="1" placeholder="ID kelas" class="h-9 w-28 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                                </td>
                                <td class="px-3 py-4">
                                    <select name="cycle_type[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                                        <option value="Normada">Normada</option>
                                        <option value="Jurusan">Jurusan</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" id="btn-tambah" onclick="addRow()" class="h-10 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition active:scale-95 shadow-xs">
                        + Tambah baris
                    </button>
                    <button type="submit" class="h-10 inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 text-sm font-semibold text-white hover:bg-indigo-500 transition shadow-xs hover:shadow active:scale-95">
                        Simpan Jadwal ke Database
                    </button>
                </div>
            </form>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        function filterScheduleDay(day) {
            const cards = document.querySelectorAll('.schedule-card');
            const rows = document.querySelectorAll('.schedule-table-row');
            const buttons = document.querySelectorAll('.day-filter-btn');

            buttons.forEach(btn => {
                if (btn.getAttribute('data-day') === day) {
                    btn.className = 'day-filter-btn active inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold {{ $isKesiswaan ? 'bg-amber-600' : 'bg-indigo-600' }} text-white shadow-xs transition';
                } else {
                    btn.className = 'day-filter-btn inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
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

        function switchViewMode(mode) {
            const cardGrid = document.getElementById('schedule-card-grid');
            const tableView = document.getElementById('schedule-table-view');
            const btnGrid = document.getElementById('view-mode-grid');
            const btnTable = document.getElementById('view-mode-table');

            if (mode === 'grid') {
                cardGrid.classList.remove('hidden');
                tableView.classList.add('hidden');
                btnGrid.className = 'view-toggle-btn active inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-slate-800 shadow-2xs transition';
                btnTable.className = 'view-toggle-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
            } else {
                cardGrid.classList.add('hidden');
                tableView.classList.remove('hidden');
                btnTable.className = 'view-toggle-btn active inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-slate-800 shadow-2xs transition';
                btnGrid.className = 'view-toggle-btn inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
            }
        }

        function addRow() {
            const tbody = document.getElementById('jadwal-tbody');
            const tr = document.createElement('tr');
            tr.className = 'jadwal-row';
            tr.innerHTML = `
                <td class="px-3 py-4">
                    <select name="day_of_week[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                        <option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option>
                    </select>
                </td>
                <td class="px-3 py-4">
                    <input type="text" name="subject[]" placeholder="Mata pelajaran" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                </td>
                <td class="px-3 py-4">
                    <input type="time" name="start_time[]" value="07:30" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                </td>
                <td class="px-3 py-4">
                    <input type="time" name="end_time[]" value="09:00" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                </td>
                <td class="px-3 py-4">
                    <input type="number" name="class_id[]" min="1" placeholder="ID kelas" class="h-9 w-28 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                </td>
                <td class="px-3 py-4">
                    <select name="cycle_type[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                        <option value="Normada">Normada</option>
                        <option value="Jurusan">Jurusan</option>
                    </select>
                </td>
            `;
            tbody.appendChild(tr);
        }
    </script>
@endpush
