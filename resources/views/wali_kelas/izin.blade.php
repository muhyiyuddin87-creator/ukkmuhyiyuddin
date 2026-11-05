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
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-4 py-3">Siswa / Kelas</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Alasan</th><th class="px-4 py-3">Bukti</th><th class="px-4 py-3">Keputusan</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingRequests as $request)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $request->student_name }}<span class="block text-xs text-slate-500">{{ $request->kelas }}</span></td>
                            <td class="px-4 py-3">{{ $request->start_date }}@if($request->end_date && $request->end_date !== $request->start_date) – {{ $request->end_date }}@endif</td>
                            <td class="px-4 py-3 whitespace-pre-line">{{ $request->reason }}</td>
                            <td class="px-4 py-3">@if($request->document_path)<a href="{{ asset('storage/' . $request->document_path) }}" target="_blank" rel="noopener" class="font-semibold text-sky-700 hover:underline">Lihat bukti</a>@else<span class="text-slate-400">-</span>@endif</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('wali-kelas.izin.approval', $request->id) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status_approval" value="Approved">
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('wali-kelas.izin.approval', $request->id) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status_approval" value="Rejected">
                                        <button type="submit" class="rounded-lg bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-200">Tolak</button>
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
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-4 py-3">Siswa / Kelas</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Alasan</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Diverifikasi Oleh</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leaveHistory as $request)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $request->student_name }}<span class="block text-xs text-slate-500">{{ $request->kelas }}</span></td>
                            <td class="px-4 py-3">{{ $request->start_date }}</td>
                            <td class="px-4 py-3">{{ $request->reason }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $request->status_approval }}</td>
                            <td class="px-4 py-3">{{ $request->verifier_name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center italic text-slate-400">Belum ada riwayat izin untuk kelas yang ditugaskan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
