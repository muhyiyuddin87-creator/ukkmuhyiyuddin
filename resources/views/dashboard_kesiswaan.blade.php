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

                <!-- Filter Hari Interaktif Sederhana (Pill Style) -->
                <div class="mb-5 flex flex-wrap items-center gap-1.5" id="kesiswaan-day-filter-container">
                    <button type="button" onclick="filterKesiswaanDay('all')" class="kesiswaan-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-amber-600 text-white shadow-xs transition" data-day="all">
                        <span>Semua Hari</span>
                        <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $jadwals->count() }}</span>
                    </button>
                    @foreach($daysList as $d)
                        @php
                            $countDay = $jadwals->where('day_of_week', $d)->count();
                        @endphp
                        <button type="button" onclick="filterKesiswaanDay('{{ $d }}')" class="kesiswaan-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-day="{{ $d }}">
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
                                <th class="whitespace-nowrap px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="kesiswaan-schedule-table-body">
                            @forelse($jadwals as $jadwal)
                            @php
                                $periodLabel = \App\Support\SchoolTimetable::labelFor($jadwal->day_of_week, $jadwal->start_time, $jadwal->end_time);
                                $tableRoom = $jadwal->nama_ruangan ?? ($jadwal->room_name ?? ($jadwal->room_id ? 'Ruang ' . $jadwal->room_id : '-'));
                                $startTime = substr($jadwal->start_time, 0, 5);
                                $endTime = substr($jadwal->end_time, 0, 5);
                                $jenis = in_array($jadwal->cycle_type, ['Regular', 'Normada', null], true) ? 'Regular' : $jadwal->cycle_type;
                            @endphp
                            <tr class="kesiswaan-schedule-row hover:bg-slate-50/80 transition" data-day="{{ $jadwal->day_of_week }}">
                                <td class="whitespace-nowrap px-4 py-3.5 font-bold text-slate-800">{{ $jadwal->day_of_week }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-800">{{ $jadwal->subject }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-600">
                                    {{ $startTime }} - {{ $endTime }}
                                    @if($periodLabel)
                                        <span class="block text-[11px] text-slate-400 font-normal">{{ $periodLabel }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-center text-slate-700 font-medium">{{ $jadwal->class_name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-center text-slate-600">{{ $tableRoom }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold {{ $jenis === 'Jurusan' ? 'bg-purple-100 text-purple-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $jenis }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                    <a href="{{ route('guru.jurnal') }}" class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-300 px-3 py-0.5 text-xs font-bold text-amber-800 hover:bg-amber-200 transition">
                                        <span>Jurnal</span>
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">
                                    Belum ada jadwal mengajar pada Putaran {{ $activeCycle }}.
                                </td>
                            </tr>
                            @endforelse
                            <tr id="empty-kesiswaan-schedule-filter-row" class="hidden">
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">
                                    Tidak ada jadwal mengajar pada hari yang dipilih.
                                </td>
                            </tr>
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
    function filterKesiswaanDay(day) {
        const rows = document.querySelectorAll('.kesiswaan-schedule-row');
        const buttons = document.querySelectorAll('.kesiswaan-day-btn');
        const emptyRow = document.getElementById('empty-kesiswaan-schedule-filter-row');

        buttons.forEach(btn => {
            if (btn.getAttribute('data-day') === day) {
                btn.className = 'kesiswaan-day-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold bg-amber-600 text-white shadow-xs transition';
            } else {
                btn.className = 'kesiswaan-day-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
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
