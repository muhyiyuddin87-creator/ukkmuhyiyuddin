@extends('layouts.waka')

@section('title', 'Jurnal Guru - Waka')

@section('content')
    <div class="space-y-6">
        <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Rekap Jurnal per Guru</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-800">{{ $guru->fullname ?? $guru->username }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $journals->count() }} jurnal tersimpan</p>
                </div>
                <div>
                    <a href="{{ route('waka.laporan') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                        ← Kembali ke Laporan
                    </a>
                </div>
            </div>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-4">
                <h3 class="text-xl font-bold text-slate-800">Daftar Jurnal</h3>
                <a href="{{ route('waka.laporan') }}" class="text-sm font-semibold text-sky-700 hover:underline">Kembali ke rekap</a>
            </div>
            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Jadwal / Mata Pelajaran</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Materi / Kegiatan</th>
                            <th class="px-4 py-3">Bukti Foto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($journals as $journal)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $journal->date }}</td>
                                <td class="px-4 py-3 font-medium text-indigo-700">{{ $journal->day_of_week }} - {{ $journal->subject }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $journal->class_id ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-700 whitespace-pre-line">{{ $journal->topic_description }}</td>
                                <td class="px-4 py-3">
                                    @if($journal->photo_path)
                                        <a href="{{ asset('storage/' . $journal->photo_path) }}" target="_blank" rel="noopener" class="inline-flex flex-col items-start gap-1 font-semibold text-sky-700 hover:underline">
                                            <img src="{{ asset('storage/' . $journal->photo_path) }}" alt="Bukti foto jurnal tanggal {{ $journal->date }}" class="h-12 w-16 rounded object-cover">
                                            Lihat foto
                                        </a>
                                    @else
                                        <span class="text-slate-400">Tanpa foto</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center italic text-slate-400">Guru ini belum memiliki jurnal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
