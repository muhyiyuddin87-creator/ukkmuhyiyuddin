@extends('layouts.kesiswaan')

@section('title', 'Dashboard Kesiswaan - School System')

@section('content')
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
            <!-- Tabel Jadwal Mengajar Kesiswaan -->
            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Jadwal Mengajar Anda (Putaran {{ $activeCycle }})</h3>
                        <p class="text-xs text-slate-500">Jadwal mengajar dan bimbingan aktif untuk tahun ajaran {{ $academicYear }}.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('guru.jadwal') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            Kelola Jadwal
                        </a>
                        <a href="{{ route('guru.jurnal') }}" class="rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                            Isi Jurnal & Presensi
                        </a>
                        <a href="{{ route('guru.jadwal.pdf') }}" target="_blank" rel="noopener" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700">
                            PDF Jadwal
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[650px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Hari</th>
                                <th class="px-4 py-3">Waktu / Sesi</th>
                                <th class="px-4 py-3">Mata Pelajaran</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Ruangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($jadwals as $jadwal)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $jadwal->day_of_week }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $jadwal->start_period_code ?? $jadwal->start_time ?? '-' }} - {{ $jadwal->end_period_code ?? $jadwal->end_time ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-amber-800">{{ $jadwal->subject }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-sky-50 px-2 py-0.5 text-xs font-semibold text-sky-700 border border-sky-200">{{ $jadwal->class_name ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $jadwal->room_name ?? $jadwal->room_id ?? '-' }}</td>
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
