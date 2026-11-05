@extends('layouts.siswa')

@section('title', 'Absensi Siswa - School System')

@section('content')
            <header class="mb-8 rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                        <h2 class="mt-2 text-3xl font-bold text-slate-800">Absensi Siswa</h2>
                    </div>
                    <div class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                        Rekap kehadiran Anda
                    </div>
                </div>
            </header>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <h3 class="text-xl font-bold text-slate-800">Data Absensi</h3>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">{{ count($attendances ?? []) }} data</span>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-700">Tanggal</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Nama Siswa</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Kelas</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Topik</th>
                                <th class="px-4 py-3 font-semibold text-slate-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse (($attendances ?? []) as $attendance)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-600">{{ $attendance->date }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $attendance->student_name ?? 'Siswa' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $attendance->kelas ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $attendance->topic_description ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $status = $attendance->status ?? 'Belum ada';
                                        @endphp

                                        @if ($status === 'Hadir')
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Hadir</span>
                                        @elseif ($status === 'Sakit')
                                            <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-bold text-yellow-700">Sakit</span>
                                        @elseif ($status === 'Izin')
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Izin</span>
                                        @elseif ($status === 'Alpha')
                                            <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700">Alpha</span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Belum ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
@endsection
