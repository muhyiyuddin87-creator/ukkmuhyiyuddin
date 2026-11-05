@extends('wali_kelas.layout')

@section('title', 'Dashboard Wali Kelas')

@section('content')
    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Ruang Kerja Wali Kelas</p>
        <h2 class="mt-2 text-3xl font-bold text-slate-800">Selamat datang, {{ $user->fullname ?? $user->username }}</h2>
        <p class="mt-2 text-sm text-slate-500">Penugasan wali kelas digunakan untuk memverifikasi izin siswa di kelas berikut. Fitur mengajar tersedia di Dashboard Guru.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            @forelse($classNames as $className)
                <span class="rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-800">{{ $className }}</span>
            @empty
                <span class="text-sm font-medium text-amber-700">Belum ada kelas yang ditugaskan. Hubungi Waka.</span>
            @endforelse
        </div>
    </header>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <a href="{{ route('wali-kelas.izin') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
            <p class="text-sm font-semibold text-slate-500">Izin Menunggu Verifikasi</p>
            <p class="mt-2 text-3xl font-bold text-amber-600">{{ $pendingLeaveCount }}</p>
            <p class="mt-2 text-sm text-slate-500">Tinjau pengajuan dari siswa kelas Anda</p>
        </a>
        <a href="{{ route('dashboard.role', ['role' => 'guru']) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
            <p class="text-sm font-semibold text-slate-500">Fitur Guru</p>
            <p class="mt-2 text-3xl font-bold text-indigo-700">Jurnal & Presensi</p>
            <p class="mt-2 text-sm text-slate-500">Kelola pembelajaran sesuai semua jadwal mengajar Anda</p>
        </a>
    </section>
@endsection
