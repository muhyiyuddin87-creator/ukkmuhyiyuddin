@extends('wali_kelas.layout')

@section('title', 'Verifikasi Izin Kelas')

@section('content')
    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm uppercase tracking-[0.3em] text-indigo-600">Guru</p>
        <h2 class="mt-2 text-3xl font-bold text-slate-800">Verifikasi Izin</h2>
        <p class="mt-2 text-sm text-slate-500">Pengajuan yang tampil hanya berasal dari siswa kelas Anda.</p>
    </header>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-xl font-bold text-slate-800">Menunggu Verifikasi</h3>
        <div class="overflow-x-auto table-scroll-container">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3">Siswa / Kelas</th>
                        <th class="whitespace-nowrap px-4 py-3">Tanggal</th>
                        <th class="min-w-[180px] max-w-[300px] px-4 py-3">Alasan</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Bukti</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Keputusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingRequests as $request)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-800">
                                <div>{{ $request->student_name }}</div>
                                <span class="inline-block mt-0.5 rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">{{ $request->kelas }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">
                                {{ $request->start_date }}@if($request->end_date && $request->end_date !== $request->start_date) – {{ $request->end_date }}@endif
                            </td>
                            <td class="min-w-[180px] max-w-[300px] px-4 py-3.5 text-slate-700 break-words leading-relaxed">{{ $request->reason }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                @if($request->document_path)
                                    <a href="{{ asset('storage/' . $request->document_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 whitespace-nowrap font-semibold text-sky-700 hover:underline">
                                        <span>Lihat bukti</span>
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                <div class="inline-flex items-center justify-center gap-2 whitespace-nowrap">
                                    <form method="POST" action="{{ route('wali-kelas.izin.approval', $request->id) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status_approval" value="Approved">
                                        <button type="submit" class="h-8 inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3.5 text-xs font-semibold text-white hover:bg-emerald-700 transition active:scale-95 shadow-xs">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('wali-kelas.izin.approval', $request->id) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status_approval" value="Rejected">
                                        <button type="submit" class="h-8 inline-flex items-center justify-center rounded-lg bg-rose-100 px-3.5 text-xs font-semibold text-rose-700 hover:bg-rose-200 transition active:scale-95">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center italic text-slate-400">Tidak ada pengajuan izin yang menunggu verifikasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-xl font-bold text-slate-800">Riwayat Izin</h3>
        <div class="overflow-x-auto table-scroll-container">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3">Siswa / Kelas</th>
                        <th class="whitespace-nowrap px-4 py-3">Tanggal</th>
                        <th class="min-w-[180px] max-w-[300px] px-4 py-3">Alasan</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Status</th>
                        <th class="whitespace-nowrap px-4 py-3">Diverifikasi Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leaveHistory as $request)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-800">
                                <div>{{ $request->student_name }}</div>
                                <span class="inline-block mt-0.5 rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">{{ $request->kelas }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">{{ $request->start_date }}</td>
                            <td class="min-w-[180px] max-w-[300px] px-4 py-3.5 text-slate-700 break-words leading-relaxed">{{ $request->reason }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                @if($request->status_approval === 'Approved')
                                    <span class="inline-flex whitespace-nowrap items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Disetujui</span>
                                @elseif($request->status_approval === 'Rejected')
                                    <span class="inline-flex whitespace-nowrap items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-200">Ditolak</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 border border-amber-200">Menunggu Verifikasi</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">{{ $request->verifier_name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center italic text-slate-400">Belum ada riwayat izin untuk kelas yang ditugaskan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
