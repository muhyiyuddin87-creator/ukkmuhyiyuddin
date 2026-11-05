@extends('layouts.waka')

@section('title', 'Laporan - Waka')

@section('content')
    <div class="space-y-6">
        <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Waka Kurikulum</p>
            <h2 class="mt-2 text-3xl font-bold text-slate-800">Laporan Ringkasan</h2>
            <p class="mt-1 text-slate-500 text-sm">Ringkasan operasional sekolah dari seluruh data yang tersimpan.</p>
        </header>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-sky-600">{{ $totalSiswa }}</p>
                <p class="text-xs text-slate-500 mt-2 uppercase tracking-wide font-semibold">Total Siswa</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-indigo-600">{{ $totalGuru }}</p>
                <p class="text-xs text-slate-500 mt-2 uppercase tracking-wide font-semibold">Total Guru</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-amber-600">{{ $totalJadwal }}</p>
                <p class="text-xs text-slate-500 mt-2 uppercase tracking-wide font-semibold">Jadwal Aktif</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm text-center">
                <p class="text-3xl font-bold text-emerald-600">{{ $totalPresensi }}</p>
                <p class="text-xs text-slate-500 mt-2 uppercase tracking-wide font-semibold">Data Presensi - {{ ucfirst($period) }}</p>
            </div>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('waka.laporan') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end flex-wrap">
                <label class="text-sm font-semibold text-slate-700 w-full sm:w-auto">Periode laporan
                    <select name="period" class="mt-1 block w-full sm:w-auto rounded-lg border border-slate-300 px-3 py-2">
                        <option value="hari" @selected($period === 'hari')>Per hari</option>
                        <option value="minggu" @selected($period === 'minggu')>Per minggu</option>
                        <option value="bulan" @selected($period === 'bulan')>Per bulan</option>
                    </select>
                </label>
                <label class="text-sm font-semibold text-slate-700 w-full sm:w-auto">Pilih tanggal
                    <input type="date" name="date" value="{{ $referenceDate->toDateString() }}" min="{{ today()->toDateString() }}" class="mt-1 block w-full sm:w-auto rounded-lg border border-slate-300 px-3 py-2">
                </label>
                <button type="submit" class="w-full sm:w-auto rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sky-500">Tampilkan</button>
                <p class="text-sm text-slate-500 sm:ml-auto">Rentang: <span class="font-semibold text-slate-700">{{ $reportStart->format("d/m/Y") }} - {{ $reportEnd->format("d/m/Y") }}</span></p>
            </form>
        </section>

        <!-- Laporan Jurnal per Guru -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-xl font-bold text-slate-800 mb-5">Rekap Jurnal per Guru</h3>
            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Nama Guru</th>
                            <th class="px-4 py-3">Jadwal Dimiliki</th>
                            <th class="px-4 py-3 rounded-r-lg">Jurnal Ditulis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($gurus as $g)
                        @php
                            $guruJadwal = $guruJadwalCounts[$g->id] ?? $jadwals->where('teacher_id', $g->id)->count();
                            $guruJurnal = $guruJurnalCounts[$g->id] ?? $journals->whereIn('schedule_id', $jadwals->where('teacher_id', $g->id)->pluck('id'))->count();
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3.5 font-bold">
                                <a href="{{ route('waka.laporan.guru', $g->id) }}" class="text-sky-700 hover:text-sky-900 hover:underline">
                                    {{ $g->fullname ?? $g->username }}
                                </a>
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-indigo-700">{{ $guruJadwal }}</td>
                            <td class="px-4 py-3.5 font-semibold text-emerald-700">
                                <a href="{{ route('waka.laporan.guru', $g->id) }}" class="hover:underline" aria-label="Lihat {{ $guruJurnal }} jurnal {{ $g->fullname ?? $g->username }}">
                                    {{ $guruJurnal }}
                                </a>
                                @if($guruJadwal > $guruJurnal)
                                    <span title="Jurnal belum lengkap" aria-label="Jurnal belum lengkap" class="ml-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700">!</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400 italic">Belum ada data guru.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Laporan Presensi per Siswa -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-xl font-bold text-slate-800 mb-5">Rekap Kehadiran per Siswa - {{ ['hari' => 'Harian', 'minggu' => 'Mingguan', 'bulan' => 'Bulanan'][$period] }}</h3>
            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Nama Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3 text-emerald-700">Hadir</th>
                            <th class="px-4 py-3 text-amber-700">Izin</th>
                            <th class="px-4 py-3 text-sky-700">Sakit</th>
                            <th class="px-4 py-3 text-rose-700">Alpha</th>
                            <th class="px-4 py-3 rounded-r-lg">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($siswas as $s)
                        @php
                            if (isset($studentAttendanceCounts[$s->id])) {
                                $att = $studentAttendanceCounts[$s->id];
                                $hadir = $att['hadir'];
                                $izin = $att['izin'];
                                $sakit = $att['sakit'];
                                $alpha = $att['alpha'];
                                $total = $att['total'];
                            } else {
                                $siswaAtt = $attendances->where('student_id', $s->id);
                                $hadir = $siswaAtt->where('status', 'Hadir')->count();
                                $izin = $siswaAtt->where('status', 'Izin')->count();
                                $sakit = $siswaAtt->where('status', 'Sakit')->count();
                                $alpha = $siswaAtt->where('status', 'Alpha')->count();
                                $total = $siswaAtt->count();
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3.5 font-bold text-slate-800">{{ $s->name }}</td>
                            <td class="px-4 py-3.5 font-semibold text-sky-700">{{ $s->kelas ?? '-' }}</td>
                            <td class="px-4 py-3.5 font-bold text-emerald-600">{{ $hadir }}</td>
                            <td class="px-4 py-3.5 font-bold text-amber-600">{{ $izin }}</td>
                            <td class="px-4 py-3.5 font-bold text-sky-600">{{ $sakit }}</td>
                            <td class="px-4 py-3.5 font-bold text-rose-600">{{ $alpha }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $total }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">Belum ada data siswa.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Rekap Izin Siswa -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Rekap Izin Siswa</h3>
                    <p class="mt-1 text-sm text-slate-500">Data permohonan izin siswa yang diproses oleh Wali Kelas.</p>
                </div>
                <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-700">{{ $leaveRequests->count() }} total izin</span>
            </div>
            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="rounded-l-lg px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Tanggal Pengajuan</th>
                            <th class="px-4 py-3">Alasan</th>
                            <th class="px-4 py-3">Foto Bukti</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="rounded-r-lg px-4 py-3">Diverifikasi Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($leaveRequests as $req)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3.5 font-bold text-slate-800">{{ $req->student_name ?? ('Siswa #' . $req->student_id) }}</td>
                                <td class="px-4 py-3.5 font-semibold text-sky-700">{{ $req->kelas ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-slate-600">{{ $req->start_date ?? '-' }}</td>
                                <td class="max-w-xs px-4 py-3.5 text-slate-700">{{ $req->reason ?? '-' }}</td>
                                <td class="px-4 py-3.5">
                                    @if($req->document_path)
                                        <a href="{{ asset('storage/' . $req->document_path) }}" target="_blank" rel="noopener" class="font-semibold text-sky-700 hover:underline">Lihat foto</a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($req->status_approval === 'Approved')
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Disetujui</span>
                                    @elseif($req->status_approval === 'Rejected')
                                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Ditolak</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Menunggu Wali Kelas</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-slate-600">{{ $req->verifier_name ?? ($req->status_approval === 'Pending' ? 'Menunggu' : '-') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center italic text-slate-400">Belum ada data permohonan izin siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
