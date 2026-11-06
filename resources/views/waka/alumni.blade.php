@extends('layouts.waka')

@section('title', 'Data Alumni - Waka Kurikulum')

@section('content')
    <header class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Waka Kurikulum</p>
                <h2 class="mt-1 text-3xl font-bold text-slate-800">Data Alumni & Siswa Lulus</h2>
                <p class="mt-1 text-slate-500 text-sm">Arsip permanen siswa yang telah lulus beserta riwayat kelas dan status akun login.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('waka.alumni.export', request()->query()) }}" class="btn-standard border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 transition shadow-xs">
                    📥 Ekspor CSV
                </a>
                <a href="{{ route('waka.kenaikan_kelas') }}" class="btn-standard border border-sky-300 bg-sky-50 text-sky-800 hover:bg-sky-100 transition shadow-xs">
                    ⚡ Kenaikan Kelas Serentak
                </a>
                <a href="{{ route('waka.siswa') }}" class="btn-standard border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition shadow-xs">
                    ← Data Siswa Aktif
                </a>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-5 rounded-xl bg-emerald-100 border border-emerald-300 px-5 py-4 text-sm font-semibold text-emerald-900 shadow-sm flex items-start gap-3">
            <svg class="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Filter & Pencarian Alumni -->
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-5">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Daftar Arsip Alumni</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Menampilkan <span class="font-bold text-sky-700">{{ $alumnis->total() }}</span> alumni terdaftar
                </p>
            </div>
            <form method="GET" action="{{ route('waka.alumni') }}" class="flex flex-col sm:flex-row flex-wrap gap-2.5 w-full lg:w-auto items-stretch sm:items-center">
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, NIS, kontak..." class="form-control-standard min-w-56">

                <select name="tahun_lulus" class="form-control-standard">
                    <option value="">Semua Tahun Lulus</option>
                    @foreach($availableYears as $th)
                        <option value="{{ $th }}" @selected($selectedYear === $th)>Tahun {{ $th }}</option>
                    @endforeach
                </select>

                <select name="jurusan" class="form-control-standard">
                    <option value="">Semua Jurusan</option>
                    @foreach(['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BR', 'BD', 'LPS'] as $j)
                        <option value="{{ $j }}" @selected($selectedJurusan === $j)>{{ $j }}</option>
                    @endforeach
                </select>

                <select name="status_akun" class="form-control-standard">
                    <option value="">Semua Status Akun</option>
                    <option value="nonaktif" @selected($selectedStatusAkun === 'nonaktif')>Nonaktif (Terkunci)</option>
                    <option value="aktif" @selected($selectedStatusAkun === 'aktif')>Aktif</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="btn-standard bg-slate-900 text-white hover:bg-slate-800">Filter</button>
                    @if($search !== '' || $selectedYear !== '' || $selectedJurusan !== '' || $selectedStatusAkun !== '')
                        <a href="{{ route('waka.alumni') }}" class="btn-standard border border-slate-300 text-slate-700 hover:bg-slate-100">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto table-scroll-container">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-4">NIS</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Jurusan</th>
                        <th class="py-3 px-4">Kelas Terakhir</th>
                        <th class="py-3 px-4">Tahun Lulus</th>
                        <th class="py-3 px-4">Tanggal Lulus</th>
                        <th class="py-3 px-4 text-center">Status Akun</th>
                        <th class="py-3 px-4 text-center">Aksi Akun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($alumnis as $index => $alumni)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 text-slate-400 text-xs">{{ $alumnis->firstItem() + $index }}</td>
                            <td class="py-3 px-4 font-mono text-xs font-semibold text-slate-600">{{ $alumni->nis }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                {{ $alumni->name }}
                                @if($alumni->phone || $alumni->email)
                                    <div class="text-[11px] font-normal text-slate-400 mt-0.5">
                                        {{ $alumni->phone ? '📞 ' . $alumni->phone : '' }}
                                        {{ $alumni->phone && $alumni->email ? ' • ' : '' }}
                                        {{ $alumni->email ? '✉️ ' . $alumni->email : '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">{{ $alumni->jurusan }}</span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-600">{{ $alumni->kelas_terakhir }}</td>
                            <td class="py-3 px-4 font-semibold text-sky-800">{{ $alumni->tahun_lulus }}</td>
                            <td class="py-3 px-4 text-xs text-slate-500">{{ date('d M Y', strtotime($alumni->tanggal_lulus)) }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($alumni->account_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <form method="POST" action="{{ route('waka.alumni.toggle_status', $alumni->id) }}" class="inline-block">
                                    @csrf
                                    @if($alumni->account_active)
                                        <button type="submit" class="btn-table border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 transition shadow-2xs" title="Kunci akun agar tidak bisa login">
                                            🔒 Kunci Akun
                                        </button>
                                    @else
                                        <button type="submit" class="btn-table border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition shadow-2xs" title="Buka akses akun sementara">
                                            🔓 Buka Akses
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="text-4xl mb-2">🎓</div>
                                <p class="text-base font-semibold text-slate-600">Belum ada data alumni di arsip.</p>
                                <p class="text-xs text-slate-400 mt-1">Data alumni akan otomatis terisi saat Anda memproses kelulusan di menu <a href="{{ route('waka.kenaikan_kelas') }}" class="text-sky-600 hover:underline">Kenaikan Kelas & Kelulusan</a>.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alumnis->hasPages())
            <div class="mt-4 px-2">
                {{ $alumnis->links() }}
            </div>
        @endif
    </section>
@endsection
