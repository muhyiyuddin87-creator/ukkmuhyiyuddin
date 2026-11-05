@extends('layouts.waka')

@section('title', 'Data Presensi - Waka Kurikulum')

@section('content')
    <div class="space-y-6">
        <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Waka Kurikulum</p>
            <h2 class="mt-2 text-3xl font-bold text-slate-800">Data Presensi</h2>
            <p class="mt-1 text-slate-500 text-sm">Rekap kehadiran seluruh siswa berdasarkan data yang diinput guru.</p>
        </header>

        <!-- Stat mini -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @php
                $hadirCount = $attendances->where('status','Hadir')->count();
                $izinCount  = $attendances->where('status','Izin')->count();
                $alphaCount = $attendances->where('status','Alpha')->count();
            @endphp
            <div class="rounded-2xl bg-white border border-emerald-200 p-4 shadow-sm text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ $hadirCount }}</p>
                <p class="text-xs text-slate-500 mt-1 uppercase tracking-wide font-semibold">Hadir</p>
            </div>
            <div class="rounded-2xl bg-white border border-amber-200 p-4 shadow-sm text-center">
                <p class="text-2xl font-bold text-amber-600">{{ $izinCount }}</p>
                <p class="text-xs text-slate-500 mt-1 uppercase tracking-wide font-semibold">Izin</p>
            </div>
            <div class="rounded-2xl bg-white border border-rose-200 p-4 shadow-sm text-center">
                <p class="text-2xl font-bold text-rose-600">{{ $alphaCount }}</p>
                <p class="text-xs text-slate-500 mt-1 uppercase tracking-wide font-semibold">Alpha</p>
            </div>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5">
                <h3 class="text-xl font-bold text-slate-800">Rekap Presensi Siswa</h3>
                <p class="text-sm text-slate-500 mt-1">Total: <span class="font-bold text-sky-700">{{ $attendances->count() }}</span> data kehadiran</p>
            </div>
            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Tanggal</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Topik / Sumber</th>
                            <th class="px-4 py-3">Foto Selfie</th>
                            <th class="px-4 py-3 rounded-r-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($attendances as $a)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3.5 text-slate-600">{{ $a->date }}</td>
                            <td class="px-4 py-3.5 font-bold text-slate-800">{{ $a->student_name ?? 'Siswa #' . $a->student_id }}</td>
                            <td class="px-4 py-3.5 font-semibold text-sky-700">{{ $a->kelas ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-600 text-xs">
                                @if(!empty($a->topic_description))
                                    {{ $a->topic_description }}
                                @elseif(empty($a->journal_id))
                                    <span class="text-emerald-700 font-semibold">Presensi Mandiri</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if(!empty($a->photo_path))
                                    <a href="{{ asset('storage/' . $a->photo_path) }}" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:underline text-xs inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat foto
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @php
                                    $color = match($a->status) {
                                        'Hadir' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'Izin'  => 'bg-amber-100 text-amber-700 border-amber-200',
                                        default => 'bg-rose-100 text-rose-700 border-rose-200',
                                    };
                                @endphp
                                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $color }}">{{ $a->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Belum ada data presensi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
