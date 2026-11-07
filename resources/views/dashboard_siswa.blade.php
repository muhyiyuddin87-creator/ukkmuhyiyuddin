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

            <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <h3 class="text-xl font-bold text-slate-800">Jadwal Pelajaran Tahun Ajaran {{ $academicYear }}</h3>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">
                        {{ $studentClass ? 'Kelas '.$studentClass : 'Kelas belum terhubung' }}
                    </span>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-700">Hari</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Mata Pelajaran</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Jam</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Ruangan</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Siklus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($jadwalList as $jadwal)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-600">{{ $jadwal->day_of_week }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $jadwal->subject }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ \App\Support\SchoolTimetable::labelFor($jadwal->day_of_week, $jadwal->start_time, $jadwal->end_time) ?? 'Waktu tersimpan' }} · {{ \Carbon\Carbon::parse($jadwal->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->end_time)->format('H:i') }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $jadwal->room_id ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ $jadwal->cycle_type }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                        @if(!$studentClass)
                                            Akun siswa belum terhubung ke data siswa yang memiliki kelas.
                                        @else
                                            Belum ada jadwal pelajaran untuk kelas {{ $studentClass }} pada putaran ini.
                                        @endif
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
