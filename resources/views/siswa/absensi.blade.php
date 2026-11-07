@extends('layouts.siswa')

@php
    $isParentAccess = (int) (Auth::user()->role_id ?? 0) === 6 || str_ends_with(strtolower(Auth::user()->username ?? ''), 'p');
@endphp

@section('title', ($isParentAccess ? 'Riwayat Kehadiran Siswa (Orang Tua) - School System' : 'Riwayat Kehadiran Siswa - School System'))

@section('content')
    <!-- Header Banner -->
    <header class="mb-6 rounded-3xl border border-emerald-200/80 bg-white p-6 md:p-8 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-7 items-center rounded-full bg-emerald-100 px-3 text-xs font-bold uppercase tracking-wider text-emerald-800">
                        Presensi KBM
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Tahun Ajaran {{ $academicYear ?? '-' }}</span>
                </div>
                <h2 class="mt-2 text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">
                    {{ $isParentAccess ? 'Riwayat Kehadiran Putra/Putri Anda' : 'Riwayat Kehadiran Siswa' }}
                </h2>
                <p class="mt-1 text-xs md:text-sm text-slate-500">
                    Catatan kehadiran otomatis terekam setiap kali bapak/ibu guru mengisi jurnal mengajar di kelas.
                </p>
                @if($student)
                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-semibold text-emerald-800">
                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-1.5 border border-emerald-200/70">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $student->name }} — <span class="font-bold">{{ $student->kelas }}</span>
                        </span>
                        @if(!empty($student->nis))
                            <span class="inline-flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-1.5 text-slate-700 font-mono">
                                NIS: {{ $student->nis }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex flex-row md:flex-col items-start md:items-end justify-between gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="text-right">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Tingkat Kehadiran</span>
                        <span class="text-2xl font-black text-emerald-600">{{ $percentageHadir }}%</span>
                    </div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </header>

    @if(!$student)
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-800">
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h3 class="font-bold">Akun Belum Terhubung dengan Data Siswa</h3>
                    <p class="mt-1 text-sm">Akun ini belum ditautkan dengan data siswa di database. Silakan hubungi admin atau Waka Kurikulum untuk menautkan akun dengan data siswa yang valid.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Stat Cards Row -->
    <section class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 mb-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total KBM</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-slate-800">{{ $totalAttendances }}</span>
                <span class="text-xs font-semibold text-slate-500">Pertemuan</span>
            </div>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Hadir</span>
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-emerald-800">{{ $totalHadir }}</span>
                <span class="text-xs font-bold text-emerald-600">{{ $totalAttendances > 0 ? round(($totalHadir / $totalAttendances) * 100) : 0 }}%</span>
            </div>
        </div>
        <div class="rounded-2xl border border-yellow-100 bg-yellow-50/60 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-yellow-700">Sakit</span>
                <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-yellow-800">{{ $totalSakit }}</span>
                <span class="text-xs font-semibold text-yellow-600">Kali</span>
            </div>
        </div>
        <div class="rounded-2xl border border-sky-100 bg-sky-50/60 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-sky-700">Izin</span>
                <span class="h-2 w-2 rounded-full bg-sky-500"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-sky-800">{{ $totalIzin }}</span>
                <span class="text-xs font-semibold text-sky-600">Kali</span>
            </div>
        </div>
        <div class="col-span-2 md:col-span-1 rounded-2xl border border-rose-100 bg-rose-50/60 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Alpha</span>
                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-black text-rose-800">{{ $totalAlpha }}</span>
                <span class="text-xs font-semibold text-rose-600">Kali</span>
            </div>
        </div>
    </section>

    <!-- Main Card Container -->
    <section class="rounded-3xl border border-slate-200 bg-white p-5 md:p-7 shadow-sm">
        <!-- Filter & Search Controls -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-6 border-b border-slate-100">
            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-1.5" id="statusTabs">
                <button type="button" onclick="filterByStatus('all')" data-status="all" class="status-tab active-tab px-3.5 py-2 rounded-xl text-xs font-bold transition">
                    Semua (<span id="count-all">{{ $totalAttendances }}</span>)
                </button>
                <button type="button" onclick="filterByStatus('Hadir')" data-status="Hadir" class="status-tab px-3.5 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:bg-slate-100">
                    Hadir (<span id="count-hadir">{{ $totalHadir }}</span>)
                </button>
                <button type="button" onclick="filterByStatus('Sakit')" data-status="Sakit" class="status-tab px-3.5 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:bg-slate-100">
                    Sakit (<span id="count-sakit">{{ $totalSakit }}</span>)
                </button>
                <button type="button" onclick="filterByStatus('Izin')" data-status="Izin" class="status-tab px-3.5 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:bg-slate-100">
                    Izin (<span id="count-izin">{{ $totalIzin }}</span>)
                </button>
                <button type="button" onclick="filterByStatus('Alpha')" data-status="Alpha" class="status-tab px-3.5 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:bg-slate-100">
                    Alpha (<span id="count-alpha">{{ $totalAlpha }}</span>)
                </button>
            </div>

            <!-- Search and Month Select -->
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input 
                        type="text" 
                        id="searchInput" 
                        oninput="applyFilters()" 
                        placeholder="Cari mapel, guru, materi..." 
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 pl-9 pr-3 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                </div>

                <select id="monthSelect" onchange="applyFilters()" class="w-full sm:w-auto rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition">
                    <option value="all">Semua Bulan</option>
                    @php
                        $months = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                    @endphp
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Attendance List -->
        <div class="mt-6 space-y-4" id="attendanceContainer">
            @php
                $dayMap = [
                    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
                    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
                ];
                $monthMap = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
            @endphp

            @forelse ($attendances as $item)
                @php
                    $carbonDate = $item->date ? \Carbon\Carbon::parse($item->date) : null;
                    $dayName = $carbonDate ? ($dayMap[$carbonDate->format('l')] ?? $carbonDate->format('l')) : '-';
                    $formattedDate = $carbonDate ? $carbonDate->format('d') . ' ' . ($monthMap[(int) $carbonDate->format('m')] ?? '') . ' ' . $carbonDate->format('Y') : '-';
                    $monthNum = $carbonDate ? $carbonDate->format('m') : '';

                    $status = $item->status ?? 'Hadir';
                    $statusStyles = [
                        'Hadir' => ['badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-500'],
                        'Sakit' => ['badge' => 'bg-yellow-100 text-yellow-800 border-yellow-200', 'dot' => 'bg-yellow-500'],
                        'Izin' => ['badge' => 'bg-sky-100 text-sky-800 border-sky-200', 'dot' => 'bg-sky-500'],
                        'Alpha' => ['badge' => 'bg-rose-100 text-rose-800 border-rose-200', 'dot' => 'bg-rose-500'],
                    ];
                    $currentStyle = $statusStyles[$status] ?? ['badge' => 'bg-slate-100 text-slate-800 border-slate-200', 'dot' => 'bg-slate-500'];
                @endphp

                <div 
                    class="attendance-card rounded-2xl border border-slate-200/90 bg-white p-5 hover:border-emerald-300 hover:shadow-md transition duration-200"
                    data-status="{{ $status }}"
                    data-month="{{ $monthNum }}"
                    data-search="{{ strtolower(($item->subject ?? '') . ' ' . ($item->teacher_name ?? '') . ' ' . ($item->topic_description ?? '') . ' ' . $dayName . ' ' . ($item->room_name ?? '')) }}"
                >
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <!-- Left Details -->
                        <div class="space-y-2.5 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $dayName }}, {{ $formattedDate }}
                                </span>

                                @if($item->start_time && $item->end_time)
                                    <span class="inline-flex items-center gap-1 rounded-xl bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-500 border border-slate-200/60 font-mono">
                                        <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time, 0, 5) }}
                                    </span>
                                @endif

                                @if($item->room_name)
                                    <span class="inline-flex items-center gap-1 rounded-xl bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-500 border border-slate-200/60">
                                        <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        {{ $item->room_name }}
                                    </span>
                                @endif
                            </div>

                            <!-- Subject Title -->
                            <h4 class="text-base md:text-lg font-bold text-slate-900 leading-snug">
                                {{ $item->subject ?? 'Presensi Kegiatan Belajar Mengajar' }}
                            </h4>

                            <!-- Teacher Info -->
                            @if($item->teacher_name)
                                <div class="flex items-center gap-2 text-xs text-slate-600">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                                        {{ strtoupper(substr($item->teacher_name, 0, 1)) }}
                                    </span>
                                    <span>Guru Pengajar: <strong class="text-slate-800 font-semibold">{{ $item->teacher_name }}</strong></span>
                                </div>
                            @endif

                            <!-- Journal Topic / Materi Pembelajaran -->
                            @if($item->topic_description)
                                <div class="mt-2 rounded-xl bg-slate-50/80 p-3.5 border border-slate-200/70 text-xs">
                                    <div class="flex items-center gap-1.5 font-bold uppercase tracking-wider text-slate-500 mb-1">
                                        <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Materi / Topik Jurnal:
                                    </div>
                                    <p class="text-slate-700 font-medium leading-relaxed whitespace-pre-line">{{ $item->topic_description }}</p>
                                </div>
                            @endif

                            @if($item->notes)
                                <p class="text-xs text-slate-500 italic mt-1">Catatan: "{{ $item->notes }}"</p>
                            @endif
                        </div>

                        <!-- Right Badges & Photo Preview -->
                        <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-start gap-3 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <!-- Status Badge -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $currentStyle['badge'] }}">
                                <span class="h-2 w-2 rounded-full {{ $currentStyle['dot'] }}"></span>
                                {{ $status }}
                            </span>

                            <!-- KBM Documentation Photo -->
                            @if(!empty($item->journal_photo))
                                <a 
                                    href="{{ asset('storage/' . $item->journal_photo) }}" 
                                    target="_blank" 
                                    rel="noopener"
                                    class="group inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 transition"
                                    title="Lihat foto kegiatan belajar mengajar"
                                >
                                    <img src="{{ asset('storage/' . $item->journal_photo) }}" alt="Foto KBM" class="h-7 w-7 rounded-lg object-cover border border-slate-200">
                                    <span class="text-[11px]">Foto KBM</span>
                                    <svg class="h-3.5 w-3.5 text-slate-400 group-hover:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 p-12 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Riwayat Kehadiran</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                        Data kehadiran akan otomatis muncul setelah bapak/ibu guru mengisi jurnal mengajar dan mencatat presensi di kelas.
                    </p>
                </div>
            @endforelse

            <!-- Filter Empty State (Hidden by default) -->
            <div id="filterEmptyState" class="hidden rounded-2xl border border-dashed border-slate-200 p-10 text-center">
                <p class="text-sm font-semibold text-slate-600">Tidak ada riwayat kehadiran yang sesuai dengan filter pencarian.</p>
                <button type="button" onclick="resetFilters()" class="mt-2 text-xs font-bold text-emerald-600 hover:text-emerald-700 underline">
                    Reset Filter
                </button>
            </div>
        </div>
    </section>

    <!-- Client-side Search and Filter Script -->
    <script>
        let currentStatusFilter = 'all';

        function filterByStatus(status) {
            currentStatusFilter = status;
            
            // Update Tab styles
            document.querySelectorAll('.status-tab').forEach(tab => {
                if (tab.getAttribute('data-status') === status) {
                    tab.classList.add('active-tab', 'bg-emerald-600', 'text-white', 'shadow');
                    tab.classList.remove('text-slate-600', 'hover:bg-slate-100');
                } else {
                    tab.classList.remove('active-tab', 'bg-emerald-600', 'text-white', 'shadow');
                    tab.classList.add('text-slate-600', 'hover:bg-slate-100');
                }
            });

            applyFilters();
        }

        function applyFilters() {
            const searchTerm = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
            const monthTerm = document.getElementById('monthSelect')?.value || 'all';
            const cards = document.querySelectorAll('.attendance-card');
            const emptyState = document.getElementById('filterEmptyState');
            
            let visibleCount = 0;

            cards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                const cardMonth = card.getAttribute('data-month');
                const cardSearch = card.getAttribute('data-search') || '';

                const matchesStatus = (currentStatusFilter === 'all' || cardStatus === currentStatusFilter);
                const matchesMonth = (monthTerm === 'all' || cardMonth === monthTerm);
                const matchesSearch = (!searchTerm || cardSearch.includes(searchTerm));

                if (matchesStatus && matchesMonth && matchesSearch) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (emptyState) {
                if (visibleCount === 0 && cards.length > 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('monthSelect').value = 'all';
            filterByStatus('all');
        }

        // Initialize active tab style on load
        document.addEventListener('DOMContentLoaded', function() {
            filterByStatus('all');
        });
    </script>
@endsection
