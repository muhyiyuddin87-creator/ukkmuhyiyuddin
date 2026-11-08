@extends('layouts.kesiswaan')

@section('title', 'Riwayat Izin Siswa - Kesiswaan')

@section('content')
            <header class="mb-8 rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Ruang Kerja Kesiswaan</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-800">Riwayat & Persetujuan Izin Siswa</h2>
                <p class="mt-2 text-slate-600">Verifikasi pengajuan izin siswa sekolah dan pantau statistik riwayat izin.</p>
            </header>

            @if(session('success'))
                <div class="mb-5 rounded-xl bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-xl bg-rose-100 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <!-- Ringkasan Statistik -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm font-medium text-emerald-700">Total Disetujui</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-800">{{ $leaveStatistics['approved'] }}</p>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                    <p class="text-sm font-medium text-rose-700">Total Ditolak</p>
                    <p class="mt-1 text-2xl font-bold text-rose-800">{{ $leaveStatistics['rejected'] }}</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-medium text-amber-700">Menunggu Persetujuan</p>
                    <p class="mt-1 text-2xl font-bold text-amber-800">{{ $leaveStatistics['pending'] }}</p>
                </div>
            </div>

            <!-- Tabel Menunggu Persetujuan -->
            <section class="mb-8 rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Menunggu Persetujuan</h3>
                        <p class="mt-1 text-sm text-slate-500">Daftar permohonan izin siswa yang membutuhkan tindakan persetujuan atau penolakan.</p>
                    </div>
                    <span class="w-fit rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">{{ $pendingRequests->count() }} menunggu</span>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[1000px] text-left text-sm">
                        <thead class="border-b border-amber-200 bg-amber-50/70 text-xs uppercase tracking-wide text-amber-900">
                            <tr>
                                <th class="whitespace-nowrap rounded-l-lg px-4 py-3">Nama Siswa</th>
                                <th class="whitespace-nowrap px-4 py-3">Kelas</th>
                                <th class="whitespace-nowrap px-4 py-3">Tanggal Izin</th>
                                <th class="min-w-[200px] max-w-[320px] px-4 py-3">Alasan</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center">Foto Bukti</th>
                                <th class="whitespace-nowrap rounded-r-lg px-4 py-3 text-center">Keputusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-100">
                            @forelse($pendingRequests as $pending)
                                <tr class="hover:bg-amber-50/40 transition">
                                    <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-800">{{ $pending->student_name ?? ('Siswa #' . $pending->student_id) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <span class="inline-block whitespace-nowrap rounded-md bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 border border-amber-200">{{ $pending->kelas ?? '-' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">
                                        {{ $pending->start_date }}@if($pending->end_date && $pending->end_date !== $pending->start_date) – {{ $pending->end_date }}@endif
                                    </td>
                                    <td class="min-w-[200px] max-w-[320px] px-4 py-3.5 text-slate-700 break-words leading-relaxed">{{ $pending->reason ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                        @if($pending->document_path)
                                            <a href="{{ asset('storage/' . $pending->document_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 whitespace-nowrap font-semibold text-amber-700 hover:underline">
                                                <span>Lihat foto</span>
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                        <div class="inline-flex items-center justify-center gap-2 whitespace-nowrap">
                                            <form method="POST" action="{{ route('kesiswaan.izin.approval', $pending->id) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status_approval" value="Approved">
                                                <button type="submit" class="h-8 inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3.5 text-xs font-semibold text-white hover:bg-emerald-700 transition active:scale-95 shadow-xs">Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('kesiswaan.izin.approval', $pending->id) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status_approval" value="Rejected">
                                                <button type="submit" class="h-8 inline-flex items-center justify-center rounded-lg bg-rose-100 px-3.5 text-xs font-semibold text-rose-700 hover:bg-rose-200 transition active:scale-95">Tolak</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center italic text-slate-400">Tidak ada pengajuan izin yang menunggu persetujuan saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Section Riwayat & Statistik Izin -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Riwayat Izin Siswa</h3>
                        <p class="mt-1 text-sm text-slate-500">Seluruh riwayat permohonan izin siswa yang telah diproses.</p>
                    </div>
                    <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-700">{{ $leaveRequests->count() }} total izin</span>
                </div>

                <div class="mb-8 rounded-xl border border-slate-200 p-4 sm:p-6">
                    <div class="mb-4">
                        <h4 class="font-bold text-slate-800">Statistik Keputusan Izin</h4>
                        <p class="mt-1 text-sm text-slate-500">Jumlah izin disetujui dan ditolak per bulan selama 6 bulan terakhir.</p>
                    </div>
                    <div class="relative h-72 w-full">
                        <canvas id="leave-history-chart" aria-label="Grafik statistik keputusan izin enam bulan terakhir" role="img"></canvas>
                    </div>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[1200px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="whitespace-nowrap rounded-l-lg px-4 py-3">Nama Siswa</th>
                                <th class="whitespace-nowrap px-4 py-3">Kelas</th>
                                <th class="whitespace-nowrap px-4 py-3">Jenis Izin</th>
                                <th class="whitespace-nowrap px-4 py-3">Tanggal Pengajuan</th>
                                <th class="min-w-[180px] max-w-[280px] px-4 py-3">Alasan</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center">Foto Bukti</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center">Status</th>
                                <th class="whitespace-nowrap px-4 py-3">Diproses Oleh</th>
                                <th class="whitespace-nowrap rounded-r-lg px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($leaveRequests as $request)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-800">{{ $request->student_name ?? ('Siswa #' . $request->student_id) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        <span class="inline-block whitespace-nowrap rounded-md bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 border border-indigo-100">{{ $request->kelas ?? '-' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5">
                                        @php
                                            $reqType = $request->leave_type ?? (stripos($request->reason, 'sakit') !== false ? 'Sakit' : 'Izin');
                                        @endphp
                                        @if($reqType === 'Sakit')
                                            <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold text-yellow-800 border border-yellow-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
                                                Sakit
                                            </span>
                                        @else
                                            <span class="inline-flex whitespace-nowrap items-center gap-1.5 rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-800 border border-sky-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                                Izin
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">{{ $request->start_date ?? '-' }}</td>
                                    <td class="min-w-[180px] max-w-[280px] px-4 py-3.5 text-slate-700 break-words leading-relaxed">{{ $request->reason ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                        @if($request->document_path)
                                            <a href="{{ asset('storage/' . $request->document_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 whitespace-nowrap font-semibold text-amber-700 hover:underline">
                                                <span>Lihat foto</span>
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                        @if($request->status_approval === 'Approved')
                                            <span class="inline-flex whitespace-nowrap items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Disetujui</span>
                                        @elseif($request->status_approval === 'Rejected')
                                            <span class="inline-flex whitespace-nowrap items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-200">Ditolak</span>
                                        @else
                                            <span class="inline-flex whitespace-nowrap items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 border border-amber-200">Menunggu Verifikasi</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 font-medium">{{ $request->verifier_name ?? ($request->status_approval === 'Pending' ? 'Menunggu Verifikasi' : '-') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                        @if($request->status_approval === 'Pending')
                                            <div class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                                                <form method="POST" action="{{ route('kesiswaan.izin.approval', $request->id) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status_approval" value="Approved">
                                                    <button type="submit" class="h-7 inline-flex items-center justify-center rounded-md bg-emerald-600 px-2.5 text-xs font-semibold text-white hover:bg-emerald-700 transition active:scale-95 shadow-xs">Setujui</button>
                                                </form>
                                                <form method="POST" action="{{ route('kesiswaan.izin.approval', $request->id) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status_approval" value="Rejected">
                                                    <button type="submit" class="h-7 inline-flex items-center justify-center rounded-md bg-rose-100 px-2.5 text-xs font-semibold text-rose-700 hover:bg-rose-200 transition active:scale-95">Tolak</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="inline-block whitespace-nowrap text-xs text-slate-400 font-medium">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center italic text-slate-400">Belum ada data permohonan izin siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const leaveStatistics = @json($leaveStatistics);
        const leaveChartCanvas = document.getElementById('leave-history-chart');

        if (window.Chart && leaveChartCanvas) {
            new Chart(leaveChartCanvas, {
                type: 'bar',
                data: {
                    labels: leaveStatistics.labels,
                    datasets: [
                        {
                            label: 'Disetujui',
                            data: leaveStatistics.approvedByMonth,
                            backgroundColor: '#10b981',
                            borderRadius: 6,
                        },
                        {
                            label: 'Ditolak',
                            data: leaveStatistics.rejectedByMonth,
                            backgroundColor: '#f43f5e',
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                    },
                },
            });
        }
    </script>
@endpush
