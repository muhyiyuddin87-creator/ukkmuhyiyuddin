@extends('layouts.siswa')

@section('title', 'Jadwal Siswa - School System')

@section('content')
            <header class="mb-8 rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                        <h2 class="mt-2 text-3xl font-bold text-slate-800">Jadwal Siswa</h2>
                    </div>
                    <div class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                        Tahun Ajaran {{ $academicYear }} · Jadwal pembelajaran Anda
                    </div>
                </div>
            </header>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Daftar Jadwal Tahun Ajaran {{ $academicYear }}</h3>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">Kelas {{ $studentClass ?? 'belum terhubung' }}</span>
                            <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-700">Putaran {{ $activeCycle }}</span>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('siswa.jadwal.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700 active:scale-95 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ubah ke PDF / Cetak Jadwal
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-700">Hari</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Mata Pelajaran</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Guru</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Jam</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Kelas</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Jenis</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($jadwals as $jadwal)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-600">{{ $jadwal->day_of_week }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $jadwal->subject }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $jadwal->guru_name ?? 'Guru #' . $jadwal->teacher_id }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ \App\Support\SchoolTimetable::labelFor($jadwal->day_of_week, $jadwal->start_time, $jadwal->end_time) ?? 'Waktu tersimpan' }} · {{ substr($jadwal->start_time, 0, 5) }} - {{ substr($jadwal->end_time, 0, 5) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $jadwal->class_name ?? $jadwal->class_id ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ $jadwal->cycle_type }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-slate-500">
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
@endsection
