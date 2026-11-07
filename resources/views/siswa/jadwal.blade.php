@extends('layouts.siswa')

@section('title', 'Jadwal Siswa - School System')

@section('content')
            <header class="mb-6 rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                        <h2 class="mt-2 text-3xl font-bold text-slate-800">Jadwal Siswa</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800">
                            Tahun Ajaran {{ $academicYear }}
                        </span>
                        <a href="{{ route('siswa.jadwal.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-rose-700 active:scale-95 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ubah ke PDF / Cetak Jadwal
                        </a>
                    </div>
                </div>
            </header>

            <section class="space-y-6">
                <!-- Bar Filter & Info Kelas -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">Kelas {{ $studentClass ?? 'belum terhubung' }}</span>
                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-700">Putaran {{ $activeCycle }}</span>
                    </div>

                    <!-- Tombol Filter Hari -->
                    <div class="flex flex-wrap items-center gap-1.5" id="day-filter-container">
                        <button type="button" onclick="filterScheduleDay('all')" class="day-filter-btn active rounded-lg px-3 py-1.5 text-xs font-semibold bg-emerald-600 text-white shadow-xs transition" data-day="all">Semua</button>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                            <button type="button" onclick="filterScheduleDay('{{ $day }}')" class="day-filter-btn rounded-lg px-3 py-1.5 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-day="{{ $day }}">{{ $day }}</button>
                        @endforeach
                    </div>
                </div>

                <!-- Grid Kartu Jadwal (Konsep Card Sesuai Gambar) -->
                <div id="schedule-card-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @forelse ($jadwals as $jadwal)
                        @php
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
                        @endphp
                        <div class="schedule-card flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-xs hover:shadow-md transition" data-day="{{ $jadwal->day_of_week }}">
                            <div>
                                <!-- Header Card: Badge Hari & Jam Monospace Biru -->
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-700 border border-slate-200/60 shadow-2xs">
                                        {{ $jadwal->day_of_week }}
                                    </span>
                                    <div class="text-right font-mono text-xs font-bold text-blue-600 leading-tight">
                                        <div>{{ $formattedPeriod }}</div>
                                        <div class="mt-0.5">({{ $startTime }} - {{ $endTime }})</div>
                                    </div>
                                </div>

                                <!-- Body: Mata Pelajaran & Nama Guru -->
                                <h4 class="text-base sm:text-lg font-bold text-slate-900 leading-snug tracking-tight mb-2">
                                    {{ $jadwal->subject }}
                                </h4>

                                <div class="flex items-center gap-2 text-xs sm:text-sm text-slate-600 mb-4">
                                    <svg class="h-4 w-4 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="truncate">Guru: <strong class="font-semibold text-slate-800">{{ $jadwal->guru_name ?? 'Guru #' . $jadwal->teacher_id }}</strong></span>
                                </div>
                            </div>

                            <!-- Footer: Ruangan & Putaran -->
                            <div class="mt-auto border-t border-slate-200/70 pt-3 flex items-center justify-between text-xs text-slate-600">
                                <div class="flex items-center gap-1.5 font-medium truncate">
                                    <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="truncate">{{ $jadwal->nama_ruangan ?? 'Ruang Kelas' }}</span>
                                </div>
                                <span class="font-bold text-slate-600 shrink-0">
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
@endsection

@push('scripts')
<script>
    function filterScheduleDay(day) {
        const cards = document.querySelectorAll('.schedule-card');
        const buttons = document.querySelectorAll('.day-filter-btn');

        buttons.forEach(btn => {
            if (btn.getAttribute('data-day') === day) {
                btn.className = 'day-filter-btn active rounded-lg px-3 py-1.5 text-xs font-semibold bg-emerald-600 text-white shadow-xs transition';
            } else {
                btn.className = 'day-filter-btn rounded-lg px-3 py-1.5 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
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
