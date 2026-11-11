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

        {{-- =========================================================================
             FITUR TC-ALT-01 & TC-ALT-02: PERINGATAN KETERLAMBATAN PENGISIAN JURNAL
             - TC-ALT-01: Mendeteksi apakah jam sistem telah melewati 30 menit dari jam KBM selesai
               (now() > end_time + 30 menit). Menampilkan indikator visual khusus keterlambatan.
             - TC-ALT-02: Alert ini otomatis hilang begitu guru selesai mengisi dan menyimpan jurnal.
             ========================================================================= --}}
        @php
            $hasSchedules = ($totalJadwal ?? 0) > 0 && isset($jadwals) && $jadwals->isNotEmpty();
            $hasTodayMissing = $hasSchedules && isset($missingJournalsToday) && $missingJournalsToday->isNotEmpty();
            $alertSchedules = $hasTodayMissing ? $missingJournalsToday : collect();

            // Cek apakah ada jadwal mengajar hari ini yang belum diisi dan sudah lewat > 30 menit dari end_time
            $nowTime = now();
            $hasOverdue30 = false;
            foreach ($alertSchedules as $sched) {
                $schedEndTimeStr = $sched->end_time ? substr($sched->end_time, 0, 8) : '00:00:00';
                $schedEndCarbon = \Carbon\Carbon::createFromTimeString($schedEndTimeStr);
                // Kondisi: waktu sekarang > waktu jam selesai + 30 menit
                if ($nowTime->greaterThan($schedEndCarbon->copy()->addMinutes(30))) {
                    $hasOverdue30 = true;
                    break;
                }
            }
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

        {{-- Banner Peringatan Pengisian Jurnal (TC-ALT-01: Visual merah jika lewat >30 menit, kuning jika belum lewat) --}}
        @if($hasTodayMissing)
            <section role="alert" class="rounded-2xl border-2 {{ $hasOverdue30 ? 'border-rose-400 bg-rose-50/90 shadow-rose-100' : 'border-amber-300 bg-amber-50' }} p-5 shadow-sm transition">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($hasOverdue30)
                                {{-- Indikator Visual Darurat: Ikon berkedip dan Badge Terlambat >30 Menit --}}
                                <svg class="h-5 w-5 text-rose-600 flex-shrink-0 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <h3 class="font-bold text-rose-950 text-base">Peringatan Keterlambatan Pengisian Jurnal</h3>
                                <span class="rounded-full bg-rose-600 px-3 py-1 text-xs font-bold text-white shadow-xs animate-pulse">
                                    ⚠️ Terlambat Mengisi Jurnal (>30 Menit)
                                </span>
                            @else
                                {{-- Indikator Peringatan Normal Hari Ini (Belum lewat 30 menit) --}}
                                <svg class="h-5 w-5 text-amber-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <h3 class="font-bold text-amber-900 text-base">Jurnal belum diisi</h3>
                                <span class="rounded-full bg-amber-200/90 px-2.5 py-0.5 text-xs font-bold text-amber-900">
                                    {{ $alertSchedules->count() }} jadwal
                                </span>
                            @endif
                        </div>
                        <p class="mt-1.5 text-sm {{ $hasOverdue30 ? 'text-rose-800' : 'text-amber-800' }}">
                            @if($hasOverdue30)
                                Waktu sistem telah melewati batas <strong>30 menit</strong> setelah jam KBM selesai. Harap segera melengkapi jurnal dan presensi siswa:
                            @else
                                Anda memiliki jadwal mengajar <strong>hari ini</strong> yang belum memiliki catatan jurnal:
                            @endif
                        </p>
                        {{-- Daftar jadwal yang belum diisi berserta status ketepatan waktunya --}}
                        <ul class="mt-3 list-none space-y-2 text-sm {{ $hasOverdue30 ? 'text-rose-950' : 'text-amber-900' }}">
                            @foreach($alertSchedules as $schedule)
                                @php
                                    // Evaluasi keterlambatan per jadwal spesifik (now() > end_time + 30 menit)
                                    $sEndTimeStr = $schedule->end_time ? substr($schedule->end_time, 0, 8) : '00:00:00';
                                    $sEndCarbon = \Carbon\Carbon::createFromTimeString($sEndTimeStr);
                                    $isThisSchedOverdue = now()->greaterThan($sEndCarbon->copy()->addMinutes(30));
                                @endphp
                                <li class="flex flex-wrap items-center gap-2 rounded-xl {{ $isThisSchedOverdue ? 'bg-rose-100/70 border border-rose-300' : 'bg-amber-100/60 border border-amber-200' }} px-3 py-2">
                                    <span class="font-bold">{{ $schedule->subject }}</span>
                                    <span class="text-xs">({{ $schedule->day_of_week }}, {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }} WIB)</span>
                                    @if(!empty($schedule->class_name))
                                        <span class="text-xs font-medium">· Kelas <span class="font-semibold">{{ $schedule->class_name }}</span></span>
                                    @endif
                                    @if($isThisSchedOverdue)
                                        <span class="ml-auto inline-flex items-center gap-1 rounded-md bg-rose-600 px-2 py-0.5 text-[11px] font-bold text-white shadow-2xs">
                                            ⚠️ Terlambat Mengisi Jurnal (>30 Menit)
                                        </span>
                                    @else
                                        <span class="ml-auto inline-flex items-center rounded-md bg-amber-200/90 px-2 py-0.5 text-[11px] font-semibold text-amber-900">
                                            Belum Diisi
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    {{-- Tombol Tindakan Cepat: Mengisi Jurnal (Setelah diisi, alert otomatis hilang / TC-ALT-02) --}}
                    <a href="{{ route('guru.jurnal') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl {{ $hasOverdue30 ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-200' : 'bg-amber-600 hover:bg-amber-700' }} px-5 py-2.5 text-sm font-semibold text-white transition shadow-sm active:scale-95">
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

            <!-- Filter Hari Interaktif Sederhana (Pill Style) -->
            <div class="mb-5 flex flex-wrap items-center gap-1.5" id="guru-day-filter-container">
                <button type="button" onclick="filterGuruDay('all')" class="guru-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-indigo-600 text-white shadow-xs transition" data-day="all">
                    <span>Semua Hari</span>
                    <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $jadwals->count() }}</span>
                </button>
                @foreach($daysList as $d)
                    @php
                        $countDay = $jadwals->where('day_of_week', $d)->count();
                    @endphp
                    <button type="button" onclick="filterGuruDay('{{ $d }}')" class="guru-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-day="{{ $d }}">
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
                            <th class="whitespace-nowrap px-4 py-3">Waktu Mengajar</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center">Kelas</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center">Ruangan</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center">Jenis</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center">Status Jurnal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="guru-schedule-table-body">
                        @forelse($jadwals as $j)
                            @php
                                $periodLabel = \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time);
                                $startTime = substr($j->start_time, 0, 5);
                                $endTime = substr($j->end_time, 0, 5);
                                $className = $j->class_name ?? ($j->class_id ? 'Kelas ' . $j->class_id : '-');
                                $roomName = $j->nama_ruangan ?? ($j->room_id ? 'Ruang ' . $j->room_id : '-');
                                $jenis = in_array($j->cycle_type, ['Regular', 'Normada', null], true) ? 'Regular' : $j->cycle_type;
                                $isJournalFilled = isset($schedulesWithJournals) && $schedulesWithJournals->contains($j->id);
                            @endphp
                            <tr class="guru-schedule-row hover:bg-slate-50/80 transition" data-day="{{ $j->day_of_week }}">
                                <td class="whitespace-nowrap px-4 py-3.5 font-bold text-slate-800">{{ $j->day_of_week }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-800">{{ $j->subject }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-600">
                                    {{ $startTime }} - {{ $endTime }}
                                    @if($periodLabel)
                                        <span class="block text-[11px] text-slate-400 font-normal">{{ $periodLabel }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-center text-slate-700 font-medium">{{ $className }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-center text-slate-600">{{ $roomName }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold {{ $jenis === 'Jurusan' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $jenis }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                    @if($isJournalFilled)
                                        <span class="inline-flex whitespace-nowrap items-center gap-1 rounded-full bg-emerald-100 text-emerald-800 px-3 py-0.5 text-xs font-semibold">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            Sudah diisi
                                        </span>
                                    @else
                                        <a href="{{ route('guru.jurnal') }}" class="inline-flex whitespace-nowrap items-center gap-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 px-3 py-0.5 text-xs font-bold hover:bg-amber-200 transition">
                                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Isi Jurnal
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">
                                    Belum ada jadwal yang ditetapkan untuk Anda.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="empty-guru-schedule-filter-row" class="hidden">
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">
                                Tidak ada jadwal mengajar pada hari yang dipilih.
                            </td>
                        </tr>
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
                    <button type="button" onclick="setDashJournalPreset('all')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        Semua
                    </button>
                    <button type="button" onclick="setDashJournalPreset('today')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        Hari Ini
                    </button>
                    <button type="button" onclick="setDashJournalPreset('yesterday')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        Kemarin
                    </button>
                    <button type="button" onclick="setDashJournalPreset('7days')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        7 Hari Terakhir
                    </button>
                    <button type="button" onclick="setDashJournalPreset('this_month')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        Bulan Ini
                    </button>
                    <button type="button" onclick="setDashJournalPreset('last_month')" class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-medium transition">
                        Bulan Lalu
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
    let currentGuruDay = 'all';

    function filterGuruDay(day) {
        currentGuruDay = day;
        const rows = document.querySelectorAll('.guru-schedule-row');
        const buttons = document.querySelectorAll('.guru-day-btn');
        const emptyRow = document.getElementById('empty-guru-schedule-filter-row');

        buttons.forEach(btn => {
            if (btn.getAttribute('data-day') === day) {
                btn.className = 'guru-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-indigo-600 text-white shadow-xs transition';
            } else {
                btn.className = 'guru-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
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
        } else if (type === 'yesterday') {
            const yesterday = new Date();
            yesterday.setDate(today.getDate() - 1);
            const yestStr = formatIso(yesterday);
            if (dashFilterStartDate) dashFilterStartDate.value = yestStr;
            if (dashFilterEndDate) dashFilterEndDate.value = yestStr;
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
        } else if (type === 'last_month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
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
