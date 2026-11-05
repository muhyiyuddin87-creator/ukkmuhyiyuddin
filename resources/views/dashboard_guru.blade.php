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

        <!-- Table 1: Jadwal dari DB -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Jadwal Mengajar Saya</h3>
                    <p class="mt-1 text-sm text-slate-500">Tahun Ajaran {{ $academicYear }} · Daftar jadwal mengajar yang telah ditetapkan untuk Anda.</p>
                </div>
            </div>

            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Hari</th>
                            <th class="px-4 py-3">Mata Pelajaran</th>
                            <th class="px-4 py-3">Waktu Mengajar</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3 rounded-r-lg">Status Jurnal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jadwals as $j)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3.5 font-bold text-slate-800">{{ $j->day_of_week }}</td>
                            <td class="px-4 py-3.5 font-medium text-slate-700">{{ $j->subject }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time) ?? 'Waktu tersimpan' }} · {{ substr($j->start_time,0,5) }} - {{ substr($j->end_time,0,5) }}</td>
                            <td class="px-4 py-3.5 font-semibold text-indigo-700">{{ $j->class_name ?? $j->class_id ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                <span class="rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 px-3 py-1 text-xs font-bold">{{ $j->cycle_type }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if(isset($schedulesWithJournals) && $schedulesWithJournals->contains($j->id))
                                    <span class="rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 px-2.5 py-1 text-xs font-semibold inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Sudah diisi
                                    </span>
                                @else
                                    <a href="{{ route('guru.jurnal') }}" class="rounded-full bg-amber-100 text-amber-800 border border-amber-300 px-2.5 py-1 text-xs font-bold inline-flex items-center gap-1 hover:bg-amber-200 transition">
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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Jurnal Mengajar (Tersimpan)</h3>
                    <p class="mt-1 text-sm text-slate-500">Catatan materi dan kegiatan pembelajaran yang telah disimpan.</p>
                </div>
                <a href="{{ route('guru.jurnal') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700 transition shadow">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Tulis Jurnal</span>
                </a>
            </div>

            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Tanggal</th>
                            <th class="px-4 py-3">Mata Pelajaran</th>
                            <th class="px-4 py-3">Materi / Kegiatan</th>
                            <th class="px-4 py-3">Bukti Foto</th>
                            <th class="px-4 py-3 rounded-r-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($journals as $j)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3.5 font-semibold text-slate-800">{{ $j->date }}</td>
                            <td class="px-4 py-3.5 font-medium text-indigo-700">{{ $j->subject ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-700">{{ $j->topic_description }}</td>
                            <td class="px-4 py-3.5">
                                @if($j->photo_path)
                                    <a href="{{ asset('storage/' . $j->photo_path) }}" target="_blank" rel="noopener" class="inline-flex flex-col items-start gap-1 text-xs font-semibold text-indigo-700 hover:underline">
                                        <img src="{{ asset('storage/' . $j->photo_path) }}" alt="Bukti foto jurnal tanggal {{ $j->date }}" class="h-12 w-16 rounded object-cover">
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
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
