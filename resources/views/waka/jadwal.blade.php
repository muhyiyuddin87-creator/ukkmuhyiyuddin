@extends('layouts.waka')

@section('title', 'Jadwal - Waka')

@section('content')
        <header class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Waka Kurikulum</p>
            <h2 class="mt-2 text-3xl font-bold text-slate-800">Jadwal Mengajar</h2>
            <p class="mt-1 text-slate-500 text-sm">Tahun ajaran {{ $academicYear }} · Atur dua putaran jadwal, input jadwal, lalu kunci putaran yang sudah final.</p>
        </header>

        @if(session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 text-rose-800">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-800">Pengaturan Putaran</h3>
                <p class="mt-1 text-sm text-slate-500">Status kunci disimpan terpisah untuk masing-masing putaran.</p>
            </div>
            <form method="POST" action="{{ route('waka.jadwal.configure') }}" class="flex flex-col gap-4 md:flex-row md:items-end">
                @csrf
                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Putaran aktif</span>
                    <select name="active_cycle" class="h-10 min-w-48 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                        <option value="1" @selected($activeCycle === 1)>Putaran 1</option>
                        <option value="2" @selected($activeCycle === 2)>Putaran 2</option>
                    </select>
                </label>
                <label class="flex h-10 items-center gap-2.5 rounded-lg border border-slate-300 bg-white px-4 cursor-pointer hover:bg-slate-50 transition">
                    <input type="checkbox" name="locked" value="1" @checked($scheduleLocked) class="h-4 w-4 accent-sky-600 rounded cursor-pointer">
                    <span class="text-sm font-semibold text-slate-700">Kunci putaran aktif</span>
                </label>
                <button type="submit" class="h-10 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-700 transition inline-flex items-center justify-center shadow-sm">Simpan Pengaturan</button>
                <div class="flex h-10 items-center">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold {{ $scheduleLocked ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $scheduleLocked ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                        {{ $scheduleLocked ? 'Putaran terkunci' : 'Putaran terbuka' }}
                    </span>
                </div>
            </form>
        </section>

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <h3 class="text-lg font-bold text-slate-800">Impor Jadwal dari Excel & PDF</h3>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        Unggah file .xlsx, .xls, .csv, atau file .pdf FET Timetable. Guru, kelas, ruangan, dan sesi jam otomatis dipetakan ke putaran target.
                    </p>
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                        <a href="{{ route('waka.jadwal.template') }}" class="font-medium text-sky-600 hover:underline">Unduh template kosong (.xlsx)</a>
                        <span class="text-slate-300">·</span>
                        <a href="{{ route('waka.jadwal.example') }}" class="font-medium text-sky-600 hover:underline">Unduh contoh 5 data (.xlsx)</a>
                        <span class="text-slate-300">·</span>
                        <a href="{{ route('waka.jadwal.download.putaran1') }}" class="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Hasil Konversi Putaran 1 (.xlsx)
                        </a>
                        <a href="{{ route('waka.jadwal.download.putaran2') }}" class="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Hasil Konversi Putaran 2 (.xlsx)
                        </a>
                    </div>
                </div>
                <form method="POST" action="{{ route('waka.jadwal.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="flex flex-col">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Target Putaran</label>
                        <select name="cycle_number" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                            <option value="auto">Otomatis / Ikuti Putaran Aktif (Putaran {{ $activeCycle }})</option>
                            <option value="1">Putaran 1</option>
                            <option value="2">Putaran 2</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Pilih File (.xlsx, .xls, .csv, .pdf)</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv,.pdf" required class="h-10 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-600 file:mr-3 file:h-7 file:rounded-md file:border-0 file:bg-slate-100 file:px-2.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:outline-none">
                    </div>
                    <div>
                        <button type="submit" class="h-10 rounded-lg bg-sky-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-sky-500 transition inline-flex items-center justify-center">
                            Impor Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Section: Generator Jadwal Otomatis (1-Klik) -->
        <section class="mb-6 rounded-2xl border-2 border-indigo-200 bg-gradient-to-br from-indigo-50/40 via-white to-sky-50/30 p-6 shadow-sm overflow-hidden relative">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-4 border-b border-indigo-100">
                <div class="flex items-start gap-3.5">
                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-sky-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-200">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-800 uppercase tracking-wide">Fitur Cerdas</span>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">1-Klik Bebas Bentrok</span>
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-800 mt-1">Pembuat Jadwal Otomatis (Auto Generator)</h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                            Cukup masukkan data Guru, Mapel, Kelas, dan Ruangan. Algoritma sistem akan memetakan jam belajar secara otomatis ke slot hari Senin–Jumat tanpa ada tabrakan jadwal.
                        </p>
                    </div>
                </div>

                <!-- Toggle Mode: Form Dinamis vs Upload Excel -->
                <div class="flex items-center rounded-xl bg-slate-100 p-1 border border-slate-200 shrink-0 self-start md:self-center">
                    <button type="button" onclick="switchAutoGenMode('form')" id="btn-tab-form" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold bg-white text-indigo-700 shadow-xs transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Input Form Web
                    </button>
                    <button type="button" onclick="switchAutoGenMode('excel')" id="btn-tab-excel" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Unggah File Excel
                    </button>
                </div>
            </div>

            <!-- Panel 1: Input Form Web Dinamis -->
            <div id="autogen-panel-form">
                <form method="POST" action="{{ route('waka.jadwal.auto.generate') }}" id="form-auto-generate" onsubmit="return confirmAutoGen(this)">
                    @csrf
                    <!-- Bar Opsi Target & Pengaturan -->
                    <div class="mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Target Putaran</label>
                            <select name="cycle_number" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                                <option value="1" @selected($activeCycle === 1)>Putaran 1 (Aktif: {{ $activeCycle === 1 ? 'Ya' : 'Tidak' }})</option>
                                <option value="2" @selected($activeCycle === 2)>Putaran 2 (Aktif: {{ $activeCycle === 2 ? 'Ya' : 'Tidak' }})</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Mode Penyusunan</label>
                            <select name="mode" id="autogen-mode-select" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                                <option value="fresh">Bersihkan & Buat Ulang (Reset jadwal di putaran ini)</option>
                                <option value="append">Isi Slot Kosong Saja (Pertahankan jadwal lama)</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <div class="h-10 w-full rounded-lg bg-white border border-indigo-200 px-3 flex items-center justify-between text-xs text-slate-600">
                                <span>Total Alokasi: <strong id="total-alloc-count" class="text-indigo-700">0</strong> baris</span>
                                <span>Total Beban: <strong id="total-alloc-jp" class="text-indigo-700">0</strong> JP</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Input Baris Alokasi -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white mb-4">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 font-bold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="px-3 py-3 text-center w-10">#</th>
                                    <th class="px-3 py-3 min-w-[220px]">Guru Pengampu <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-3 min-w-[200px]">Mata Pelajaran <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-3 min-w-[140px]">Kelas Sasaran <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-3 min-w-[160px]">Ruangan (Opsional)</th>
                                    <th class="px-3 py-3 text-center w-24">Beban JP <span class="text-rose-500">*</span></th>
                                    <th class="px-3 py-3 text-center w-28">Jenis</th>
                                    <th class="px-3 py-3 text-center w-12">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="allocation-table-body" class="divide-y divide-slate-100">
                                <!-- Baris alokasi diisi via Javascript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Tombol Tambah Baris & Aksi Cepat -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" onclick="addAllocationRow()" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 border border-indigo-200 px-3.5 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition shadow-2xs">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Baris
                            </button>
                            <button type="button" onclick="loadDemoAllocations()" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 border border-amber-200 px-3.5 py-2 text-xs font-bold text-amber-800 hover:bg-amber-100 transition shadow-2xs">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                📋 Muat 5 Data Demo
                            </button>
                            <button type="button" onclick="clearAllAllocationRows()" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                                Bersihkan
                            </button>
                        </div>
                        <div class="text-xs text-slate-500">
                            Setiap baris alokasi akan otomatis ditempatkan pada slot jam kosong tanpa bentrok guru, kelas, maupun ruangan.
                        </div>
                    </div>

                    <!-- Tombol Eksekusi Utama -->
                    <div class="flex items-center justify-end">
                        <button type="submit" id="btn-submit-autogen" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-sky-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-indigo-600/25 hover:from-indigo-700 hover:to-sky-700 hover:shadow-lg transition active:scale-95">
                            <svg class="h-5 w-5 text-amber-300 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>⚡ Generate Jadwal Sekarang (1-Klik)</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Panel 2: Unggah Berkas Excel Alokasi -->
            <div id="autogen-panel-excel" class="hidden">
                <form method="POST" action="{{ route('waka.jadwal.auto.generate') }}" enctype="multipart/form-data" onsubmit="return confirmAutoGenExcel(this)">
                    @csrf
                    <div class="mb-5 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Target Putaran</label>
                            <select name="cycle_number" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                                <option value="1" @selected($activeCycle === 1)>Putaran 1 (Aktif: {{ $activeCycle === 1 ? 'Ya' : 'Tidak' }})</option>
                                <option value="2" @selected($activeCycle === 2)>Putaran 2 (Aktif: {{ $activeCycle === 2 ? 'Ya' : 'Tidak' }})</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Mode Penyusunan</label>
                            <select name="mode" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                                <option value="fresh">Bersihkan & Buat Ulang (Reset jadwal di putaran ini)</option>
                                <option value="append">Isi Slot Kosong Saja (Pertahankan jadwal lama)</option>
                            </select>
                        </div>
                    </div>

                    <div class="rounded-xl border-2 border-dashed border-indigo-200 bg-white p-6 text-center mb-5">
                        <svg class="mx-auto h-12 w-12 text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h4 class="text-sm font-bold text-slate-800">Pilih Berkas Alokasi Jadwal (.xlsx, .xls, .csv)</h4>
                        <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                            Format berkas berisi kolom: <strong>Nama Guru</strong>, <strong>Mata Pelajaran</strong>, <strong>Kelas</strong>, <strong>Ruangan</strong>, <strong>Jumlah JP</strong>, dan <strong>Jenis</strong>.
                        </p>
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="text-xs text-slate-600 file:mr-3 file:h-9 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-700 focus:outline-none">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('waka.jadwal.download.master') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-100 transition shadow-2xs" title="Unduh data gabungan Guru, Mapel, Ruangan, Kelas yang ada di project">
                                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Unduh Data Master (.xlsx)
                                </a>
                                <a href="{{ route('waka.jadwal.auto.template') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition shadow-2xs">
                                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Template Kosong
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                            <svg class="h-5 w-5 text-amber-300" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span>⚡ Proses & Generate dari Excel (1-Klik)</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-800">Input Jadwal Waka</h3>
                <p class="mt-1 text-sm text-slate-500">Jadwal baru akan masuk ke Tahun Ajaran {{ $academicYear }}, Putaran {{ $activeCycle }}.</p>
            </div>
            @if($scheduleLocked)
                <p class="rounded-lg bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">Putaran ini terkunci. Buka kunci untuk menambahkan jadwal.</p>
            @else
                <form method="POST" action="{{ route('waka.jadwal.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end schedule-period-row" data-period-row data-default-period="period_1">
                    @csrf
                    <input type="hidden" name="cycle_number" value="{{ $activeCycle }}">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Guru</label>
                        <select name="teacher_id" required class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                            <option value="">Pilih guru</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->fullname ?? $guru->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Hari</label>
                        <select name="day_of_week" required data-day-select class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                            @foreach(\App\Support\SchoolTimetable::DAYS as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Mata Pelajaran</label>
                        <input name="subject" list="mapel-list" placeholder="Contoh: Bahasa Indonesia / Matematika" required maxlength="100" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                        <datalist id="mapel-list">
                            <option value="Bahasa Indonesia">B INDO (Bahasa Indonesia)</option>
                            <option value="Matematika">MTK (Matematika)</option>
                            <option value="B JAWA">B JAWA (Bahasa Jawa)</option>
                            <option value="Bahasa Inggris">B INGRIS (Bahasa Inggris)</option>
                            <option value="PPKN">PPKN (Pendidikan Pancasila dan Kewarganegaraan)</option>
                        </datalist>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Kelas</label>
                        <select name="class_name" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                            <option value="">Pilih kelas</option>
                            @foreach($classOptions as $className)
                                <option value="{{ $className }}" @selected(old('class_name') === $className)>{{ $className }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Ruangan</label>
                        <select name="room_id" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                            <option value="">Belum ditentukan</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id_ruangan }}">{{ $room->nama_ruangan }}{{ $room->jenis ? ' — ' . $room->jenis : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Sesi Jam</label>
                            <span data-period-preview class="text-[11px] font-semibold text-sky-600 truncate max-w-36"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="start_period_code" required data-start-period class="h-10 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none" aria-label="Sesi Mulai"></select>
                            <select name="end_period_code" required data-end-period class="h-10 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none" aria-label="Sesi Selesai"></select>
                        </div>
                        <input type="hidden" value="08:00" data-start-time>
                        <input type="hidden" value="08:35" data-end-time>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Jenis Jadwal</label>
                        <select name="cycle_type" required class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                            <option value="Normada" @selected(old('cycle_type') === 'Normada')>Normada</option>
                            <option value="Jurusan" @selected(old('cycle_type') === 'Jurusan')>Jurusan</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="h-10 w-full rounded-lg bg-sky-600 px-5 text-sm font-semibold text-white hover:bg-sky-500 transition inline-flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Jadwal
                        </button>
                    </div>
                </form>
                <p class="mt-3 text-xs text-slate-500">Waktu otomatis mengikuti hari dan jam pada jadwal sekolah. Waktu istirahat tidak dapat dipilih; kegiatan khusus tersedia sebagai pilihan tersendiri.</p>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Jadwal Putaran {{ $activeCycle }}</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Menampilkan <span class="font-bold text-sky-700">{{ method_exists($jadwals, 'total') ? $jadwals->total() : $jadwals->count() }}</span> jadwal
                        @if(($selectedJurusan ?? '') !== '' || ($selectedTingkat ?? '') !== '' || ($search ?? '') !== '')
                            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800 ml-1">Hasil Pencarian</span>
                        @endif
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5">
                    <form method="GET" action="{{ route('waka.jadwal') }}" class="flex flex-col sm:flex-row flex-wrap gap-2.5 w-full lg:w-auto items-stretch sm:items-center">
                        <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Cari mapel, guru, kelas..." class="h-10 w-full sm:w-auto min-w-44 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                        
                        <select name="jurusan" class="h-10 w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                            <option value="">Semua jurusan</option>
                            @foreach(($jurusanOptions ?? ['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BR', 'BD', 'LPS']) as $jurusan)
                                <option value="{{ $jurusan }}" @selected(($selectedJurusan ?? '') === $jurusan)>{{ $jurusan }}</option>
                            @endforeach
                        </select>

                        <select name="tingkat" class="h-10 w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                            <option value="">Semua tingkat</option>
                            @foreach(['X', 'XI', 'XII'] as $tingkat)
                                <option value="{{ $tingkat }}" @selected(($selectedTingkat ?? '') === $tingkat)>{{ $tingkat }}</option>
                            @endforeach
                        </select>

                        <select name="rombel" class="h-10 w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                            <option value="">Semua rombel</option>
                            @foreach(['1', '2', '3', '4'] as $rombel)
                                <option value="{{ $rombel }}" @selected(($selectedRombel ?? '') === $rombel)>Rombel {{ $rombel }}</option>
                            @endforeach
                        </select>

                        <div class="flex gap-2">
                            <button type="submit" class="h-10 flex-1 sm:flex-none rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-700 transition inline-flex items-center justify-center shadow-sm">
                                Cari
                            </button>
                            @if(($search ?? '') !== '' || ($selectedJurusan ?? '') !== '' || ($selectedTingkat ?? '') !== '' || ($selectedRombel ?? '') !== '')
                                <a href="{{ route('waka.jadwal') }}" class="h-10 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition inline-flex items-center justify-center">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('waka.jadwal.pdf', ['cycle' => $activeCycle, 'jurusan' => $selectedJurusan ?? '', 'tingkat' => $selectedTingkat ?? '', 'rombel' => $selectedRombel ?? '', 'q' => $search ?? '']) }}" target="_blank" rel="noopener" class="h-10 inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 text-sm font-bold text-white shadow-sm hover:bg-rose-700 active:scale-95 transition whitespace-nowrap">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ubah ke PDF
                        </a>

                        @if(!$scheduleLocked)
                            <button type="button" onclick="openBulkDeleteModal()" class="h-10 inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-bold text-white shadow-sm hover:bg-red-700 active:scale-95 transition whitespace-nowrap" title="Hapus semua jadwal pada putaran ini">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus Semua Jadwal
                            </button>
                        @else
                            <button type="button" disabled title="Putaran sedang dikunci" class="h-10 inline-flex items-center justify-center gap-2 rounded-lg bg-slate-100 border border-slate-200 px-3.5 text-sm font-semibold text-slate-400 cursor-not-allowed whitespace-nowrap">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Terkunci
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="overflow-hidden rounded-2xl bg-white shadow-xs border border-slate-200">
                <div class="overflow-x-auto table-scroll-container">
                    @foreach($jadwals as $j)
                        <form id="update-schedule-{{ $j->id }}" method="POST" action="{{ route('waka.jadwal.update', $j->id) }}">@csrf @method('PATCH')</form>
                    @endforeach
                    <table class="w-full min-w-[1300px] text-left text-xs">
                        <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50/70">
                            <tr>
                                <th class="px-3 py-3 text-center w-12">NO</th>
                                <th class="px-3 py-3 w-28">HARI</th>
                                <th class="px-3 py-3 w-56">WAKTU / JAM</th>
                                <th class="px-3 py-3">MATA PELAJARAN</th>
                                <th class="px-3 py-3">GURU PENGAMPU</th>
                                <th class="px-3 py-3 w-36 text-center">KELAS</th>
                                <th class="px-3 py-3 w-36 text-center">RUANGAN</th>
                                <th class="px-3 py-3 w-28 text-center">JENIS</th>
                                <th class="px-3 py-3 w-32 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($jadwals as $index => $j)
                            @php
                                $currentPeriodCode = \App\Support\SchoolTimetable::codeFor($j->day_of_week, $j->start_time, $j->end_time);
                                $periodLabel = \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time);
                                $rowNumber = ($jadwals instanceof \Illuminate\Pagination\LengthAwarePaginator) 
                                    ? $jadwals->firstItem() + $index 
                                    : $index + 1;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition" data-period-row data-original-day="{{ $j->day_of_week }}" data-legacy-start="{{ substr($j->start_time, 0, 5) }}" data-legacy-end="{{ substr($j->end_time, 0, 5) }}" data-legacy-code="{{ $currentPeriodCode ?? 'legacy' }}">
                                <td class="px-3 py-3 text-center font-bold text-slate-800">{{ $rowNumber }}</td>
                                <td class="px-2.5 py-3">
                                    <select form="update-schedule-{{ $j->id }}" name="day_of_week" required data-day-select class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs font-bold text-slate-900 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                        @foreach(\App\Support\SchoolTimetable::DAYS as $day)<option value="{{ $day }}" @selected($j->day_of_week === $day)>{{ $day }}</option>@endforeach
                                        @if(!in_array($j->day_of_week, \App\Support\SchoolTimetable::DAYS, true))
                                            <option value="{{ $j->day_of_week }}" selected>{{ $j->day_of_week }} (jadwal lama)</option>
                                        @endif
                                    </select>
                                </td>
                                <td class="px-2.5 py-3">
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <label class="text-[10px] font-medium text-slate-500">Sesi Mulai<select form="update-schedule-{{ $j->id }}" name="start_period_code" required data-start-period class="mt-0.5 h-8 w-full rounded border border-slate-300 bg-white px-1.5 text-xs focus:border-sky-500 focus:outline-none"></select></label>
                                        <label class="text-[10px] font-medium text-slate-500">Sesi Selesai<select form="update-schedule-{{ $j->id }}" name="end_period_code" required data-end-period class="mt-0.5 h-8 w-full rounded border border-slate-300 bg-white px-1.5 text-xs focus:border-sky-500 focus:outline-none"></select></label>
                                    </div>
                                    <input type="hidden" value="{{ substr($j->start_time, 0, 5) }}" data-start-time>
                                    <input type="hidden" value="{{ substr($j->end_time, 0, 5) }}" data-end-time>
                                    <div class="mt-1 flex items-center justify-between text-[11px] text-slate-800 font-bold">
                                        <span>{{ substr($j->start_time, 0, 5) }} - {{ substr($j->end_time, 0, 5) }} WIB</span>
                                    </div>
                                    <span data-period-preview class="mt-0.5 block text-[10px] text-slate-500">{{ $periodLabel ?? '' }}</span>
                                </td>
                                <td class="px-2.5 py-3">
                                    <input form="update-schedule-{{ $j->id }}" name="subject" list="mapel-list" required maxlength="100" value="{{ $j->subject }}" class="h-9 w-full min-w-32 rounded-lg border border-slate-300 bg-white px-2.5 text-xs font-bold text-slate-900 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                </td>
                                <td class="px-2.5 py-3">
                                    <select form="update-schedule-{{ $j->id }}" name="teacher_id" required class="h-9 w-full min-w-36 rounded-lg border border-slate-300 bg-white px-2.5 text-xs font-medium text-slate-800 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                        @foreach($gurus as $guru)<option value="{{ $guru->id }}" @selected((int) $j->teacher_id === (int) $guru->id)>{{ $guru->fullname ?? $guru->username }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="px-2.5 py-3 text-center">
                                    @php
                                        $scheduleClass = $j->class_name ?? ($j->class_id !== null ? (string) $j->class_id : '');
                                    @endphp
                                    <select form="update-schedule-{{ $j->id }}" name="class_name" class="h-9 w-full min-w-28 rounded-lg border border-slate-300 bg-white px-2 text-xs font-bold text-slate-900 text-center focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                        <option value="">Pilih kelas</option>
                                        @foreach($classOptions as $className)
                                            <option value="{{ $className }}" @selected($scheduleClass === $className)>{{ $className }}</option>
                                        @endforeach
                                        @if($scheduleClass !== '' && ! $classOptions->contains($scheduleClass))
                                            <option value="{{ $scheduleClass }}" selected>Kelas lama: {{ $scheduleClass }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td class="px-2.5 py-3 text-center">
                                    <select form="update-schedule-{{ $j->id }}" name="room_id" class="h-9 w-full min-w-28 rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-700 text-center focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                        <option value="">-</option>
                                        @foreach($rooms as $room)<option value="{{ $room->id_ruangan }}" @selected((int) $j->room_id === (int) $room->id_ruangan)>{{ $room->nama_ruangan }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="px-2.5 py-3 text-center">
                                    @if(!$scheduleLocked)
                                        <select form="update-schedule-{{ $j->id }}" name="cycle_type" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs font-bold text-center focus:border-sky-500 focus:outline-none {{ $j->cycle_type === 'Jurusan' ? 'text-purple-700' : 'text-slate-700' }}">
                                            <option value="Normada" @selected(in_array($j->cycle_type, ['Normada', 'Regular']))>Normada</option>
                                            <option value="Jurusan" @selected($j->cycle_type === 'Jurusan')>Jurusan</option>
                                        </select>
                                    @else
                                        <input form="update-schedule-{{ $j->id }}" type="hidden" name="cycle_type" value="{{ $j->cycle_type }}">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $j->cycle_type === 'Jurusan' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700' }}">{{ $j->cycle_type === 'Regular' ? 'Normada' : ($j->cycle_type ?? 'Normada') }}</span>
                                    @endif
                                </td>
                                <td class="px-2.5 py-3 text-center">
                                    @if(!$scheduleLocked)
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button form="update-schedule-{{ $j->id }}" type="submit" class="h-8 rounded-lg bg-emerald-600 px-2.5 text-xs font-bold text-white hover:bg-emerald-700 transition inline-flex items-center justify-center shadow-xs">Simpan</button>
                                            <form method="POST" action="{{ route('waka.jadwal.destroy', $j->id) }}" onsubmit="return confirm('Hapus jadwal {{ $j->subject }}?')">
                                                 @csrf @method('DELETE')
                                                <button type="submit" class="h-8 rounded-lg bg-rose-100 px-2.5 text-xs font-bold text-rose-700 hover:bg-rose-200 transition inline-flex items-center justify-center">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">Terkunci</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-400 italic">
                                    Belum ada jadwal tersimpan{{ (($selectedJurusan ?? '') !== '' || ($selectedTingkat ?? '') !== '' || ($search ?? '') !== '') ? ' yang sesuai dengan kriteria pencarian.' : '.' }}
                                    @if(($selectedJurusan ?? '') !== '' || ($selectedTingkat ?? '') !== '' || ($search ?? '') !== '')
                                        <div class="mt-2">
                                            <a href="{{ route('waka.jadwal') }}" class="inline-flex items-center rounded-lg bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-800 hover:bg-sky-200 transition">
                                                Reset Pencarian
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($jadwals, 'hasPages') && $jadwals->hasPages())
                <div class="mt-4 px-2">
                    {{ $jadwals->links() }}
                </div>
            @endif
        </section>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5">
                <h3 class="text-xl font-bold text-slate-800">Kelola Ruangan</h3>
                <p class="mt-1 text-sm text-slate-500">Ruangan yang sudah digunakan jadwal tidak dapat dihapus.</p>
            </div>
            <div class="overflow-x-auto table-scroll-container">
                <form id="create-room" method="POST" action="{{ route('waka.ruangan.store') }}">@csrf</form>
                @foreach($rooms as $room)
                    <form id="update-room-{{ $room->id_ruangan }}" method="POST" action="{{ route('waka.ruangan.update', $room->id_ruangan) }}">@csrf @method('PATCH')</form>
                @endforeach
                <table class="w-full min-w-[750px] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr><th class="px-4 py-3 rounded-l-lg">Nama Ruangan</th><th class="px-4 py-3">Jenis</th><th class="px-4 py-3">Keterangan</th><th class="px-4 py-3 rounded-r-lg">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="bg-sky-50/70">
                            <td class="px-3 py-2.5"><input form="create-room" name="nama_ruangan" required maxlength="100" placeholder="Contoh: Lab IPA" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none"></td>
                            <td class="px-3 py-2.5">
                                <select form="create-room" name="jenis" required class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                    <option value="">Pilih jenis</option>
                                    <option value="Ruangan">Ruangan</option>
                                    <option value="Lab">Lab</option>
                                </select>
                            </td>
                            <td class="px-3 py-2.5"><input form="create-room" name="keterangan" maxlength="2000" placeholder="Keterangan ruangan" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none"></td>
                            <td class="px-3 py-2.5"><button form="create-room" type="submit" class="h-9 whitespace-nowrap rounded-lg bg-sky-600 px-4 text-xs font-semibold text-white hover:bg-sky-700 transition inline-flex items-center justify-center shadow-sm">Tambah Ruangan</button></td>
                        </tr>
                        @forelse($rooms as $room)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-3 py-2.5"><input form="update-room-{{ $room->id_ruangan }}" name="nama_ruangan" required maxlength="100" value="{{ $room->nama_ruangan }}" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none"></td>
                                <td class="px-3 py-2.5">
                                    <select form="update-room-{{ $room->id_ruangan }}" name="jenis" required class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                        @if(!in_array($room->jenis, ['Ruangan', 'Lab'], true))
                                            <option value="{{ $room->jenis }}" selected>{{ $room->jenis }} (data lama)</option>
                                        @endif
                                        <option value="Ruangan" @selected($room->jenis === 'Ruangan')>Ruangan</option>
                                        <option value="Lab" @selected($room->jenis === 'Lab')>Lab</option>
                                    </select>
                                </td>
                                <td class="px-3 py-2.5"><input form="update-room-{{ $room->id_ruangan }}" name="keterangan" maxlength="2000" value="{{ $room->keterangan }}" placeholder="Keterangan" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none"></td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-1.5">
                                        <button form="update-room-{{ $room->id_ruangan }}" type="submit" class="h-9 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-700 transition inline-flex items-center justify-center">Simpan</button>
                                        <form method="POST" action="{{ route('waka.ruangan.destroy', $room->id_ruangan) }}" onsubmit="return confirm('Hapus ruangan {{ $room->nama_ruangan }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="h-9 rounded-lg bg-rose-100 px-3 text-xs font-semibold text-rose-700 hover:bg-rose-200 transition inline-flex items-center justify-center">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center italic text-slate-400">Belum ada ruangan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Modal Konfirmasi Hapus Semua Jadwal -->
        <div id="bulkDeleteModal" onclick="if(event.target === this) closeBulkDeleteModal()" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
                <form method="POST" action="{{ route('waka.jadwal.destroy.all') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="cycle_number" value="{{ $activeCycle }}">
                    <input type="hidden" name="q" value="{{ $search ?? '' }}">
                    <input type="hidden" name="jurusan" value="{{ $selectedJurusan ?? '' }}">
                    <input type="hidden" name="tingkat" value="{{ $selectedTingkat ?? '' }}">
                    <input type="hidden" name="rombel" value="{{ $selectedRombel ?? '' }}">
                    <input type="hidden" name="kelas" value="{{ $selectedKelas ?? '' }}">

                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-800">Hapus Semua Jadwal</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Tindakan ini akan menghapus jadwal secara massal dari sistem untuk <strong>Tahun Ajaran {{ $academicYear }}</strong>.
                            </p>
                        </div>
                        <button type="button" onclick="closeBulkDeleteModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="mt-5 space-y-3">
                        @php
                            $isFiltered = (($selectedJurusan ?? '') !== '' || ($selectedTingkat ?? '') !== '' || ($selectedRombel ?? '') !== '' || ($search ?? '') !== '');
                            $filteredCount = method_exists($jadwals, 'total') ? $jadwals->total() : $jadwals->count();
                            $totalInCycle = $totalCycleSchedules ?? $filteredCount;
                        @endphp

                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pilih Cakupan Jadwal yang Ingin Dihapus</label>
                        
                        @if($isFiltered)
                            <div class="rounded-xl border border-rose-200 bg-rose-50/40 p-3.5 hover:bg-rose-50 transition cursor-pointer flex items-start gap-3">
                                <input type="radio" id="scope_all" name="scope" value="all" checked class="mt-1 h-4 w-4 text-rose-600 focus:ring-rose-500">
                                <label for="scope_all" class="cursor-pointer text-xs flex-1">
                                    <span class="font-bold text-rose-700 block text-sm">Hapus Seluruh Jadwal Putaran {{ $activeCycle }}</span>
                                    <span class="text-slate-600 block mt-0.5">Menghapus total <strong>{{ $totalInCycle }} jadwal</strong> di Putaran {{ $activeCycle }} secara menyeluruh.</span>
                                </label>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-3.5 hover:border-rose-300 hover:bg-slate-50 transition cursor-pointer flex items-start gap-3">
                                <input type="radio" id="scope_filtered" name="scope" value="filtered" class="mt-1 h-4 w-4 text-rose-600 focus:ring-rose-500">
                                <label for="scope_filtered" class="cursor-pointer text-xs flex-1">
                                    <span class="font-bold text-slate-800 block text-sm">Hanya Hapus Hasil Filter / Pencarian Saat Ini</span>
                                    <span class="text-slate-500 block mt-0.5">Hanya menghapus <strong>{{ $filteredCount }} jadwal</strong> yang sedang tampil pada filter pencarian aktif.</span>
                                </label>
                            </div>
                        @else
                            <input type="hidden" name="scope" value="all">
                            <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-900">
                                <p class="font-bold text-sm mb-1">Konfirmasi Penghapusan Total Putaran {{ $activeCycle }}</p>
                                <p>Akan menghapus seluruh <strong>{{ $totalInCycle }} jadwal</strong> yang terdaftar pada Putaran {{ $activeCycle }} (Tahun Ajaran {{ $academicYear }}).</p>
                            </div>
                        @endif

                        <div class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800 flex items-start gap-2">
                            <svg class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span><strong>Peringatan:</strong> Jadwal yang dihapus tidak dapat dipulihkan. Jurnal atau presensi yang terhubung ke jadwal ini juga akan otomatis dibersihkan agar integritas database tetap terjaga.</span>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeBulkDeleteModal()" class="rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700 active:scale-95 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Ya, Hapus Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
@endsection

@push('scripts')
<script>
    const periodsByDay = @json($periodsByDay);

    document.querySelectorAll('[data-period-row]').forEach((row) => {
        const daySelect = row.querySelector('[name="day_of_week"]');
        const startSelect = row.querySelector('[data-start-period]');
        const endSelect = row.querySelector('[data-end-period]');
        const startTime = row.querySelector('[data-start-time]');
        const endTime = row.querySelector('[data-end-time]');
        const preview = row.querySelector('[data-period-preview]');

        const updatePreview = () => {
            const periods = periodsByDay[daySelect.value] || {};
            const start = periods[startSelect.value];
            const end = periods[endSelect.value];
            if (!start || !end) return;
            startTime.value = start.start;
            endTime.value = end.end;
            preview.textContent = `Rentang waktu: ${start.start}?${end.end}`;
        };

        const updatePeriodOptions = (initial = false) => {
            const entries = Object.entries(periodsByDay[daySelect.value] || {});
            const oldStart = initial ? row.dataset.legacyStart : startSelect.value;
            const oldEnd = initial ? row.dataset.legacyEnd : endSelect.value;
            startSelect.replaceChildren();
            endSelect.replaceChildren();
            entries.forEach(([code, period]) => {
                startSelect.add(new Option(`${period.label} (${period.start})`, code));
                endSelect.add(new Option(`${period.label} (${period.end})`, code));
                if (initial && period.start === oldStart) startSelect.value = code;
                if (initial && period.end === oldEnd) endSelect.value = code;
            });
            if (!startSelect.value && entries.length) startSelect.selectedIndex = 0;
            if (!endSelect.value && entries.length) endSelect.selectedIndex = Math.min(2, entries.length - 1);
            updatePreview();
        };

        daySelect.addEventListener('change', () => updatePeriodOptions(false));
        startSelect.addEventListener('change', updatePreview);
        endSelect.addEventListener('change', updatePreview);
        updatePeriodOptions(row.hasAttribute('data-original-day'));
    });

    // ==========================================
    // LOGIKA GENERATOR JADWAL OTOMATIS (1-KLIK)
    // ==========================================
    const autogenGurus = @json($gurus);
    const autogenRooms = @json($rooms);
    const autogenClasses = @json($classOptions);
    let allocRowIndex = 0;

    function switchAutoGenMode(mode) {
        const btnForm = document.getElementById('btn-tab-form');
        const btnExcel = document.getElementById('btn-tab-excel');
        const panelForm = document.getElementById('autogen-panel-form');
        const panelExcel = document.getElementById('autogen-panel-excel');

        if (mode === 'form') {
            panelForm.classList.remove('hidden');
            panelExcel.classList.add('hidden');
            btnForm.className = 'inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold bg-white text-indigo-700 shadow-xs transition';
            btnExcel.className = 'inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
        } else {
            panelForm.classList.add('hidden');
            panelExcel.classList.remove('hidden');
            btnExcel.className = 'inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold bg-white text-indigo-700 shadow-xs transition';
            btnForm.className = 'inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 transition';
        }
    }

    function addAllocationRow(data = null) {
        allocRowIndex++;
        const tbody = document.getElementById('allocation-table-body');
        const tr = document.createElement('tr');
        tr.className = 'alloc-row hover:bg-slate-50/70 transition';
        tr.id = `alloc-row-${allocRowIndex}`;

        // Options Guru
        let guruOptions = '<option value="">-- Pilih Guru --</option>';
        autogenGurus.forEach(g => {
            const name = g.fullname || g.username;
            const isSel = (data && (data.teacher_id == g.id || data.teacher_name == name)) ? 'selected' : '';
            guruOptions += `<option value="${g.id}" ${isSel}>${name}</option>`;
        });

        // Options Kelas
        let classOptions = '<option value="">-- Pilih Kelas --</option>';
        autogenClasses.forEach(c => {
            const isSel = (data && data.class_name == c) ? 'selected' : '';
            classOptions += `<option value="${c}" ${isSel}>${c}</option>`;
        });

        // Options Ruangan
        let roomOptions = '<option value="">(Tanpa Ruangan / Teori)</option>';
        autogenRooms.forEach(r => {
            const isSel = (data && data.room_id == r.id_ruangan) ? 'selected' : '';
            roomOptions += `<option value="${r.id_ruangan}" ${isSel}>${r.nama_ruangan}</option>`;
        });

        const subjectVal = data ? (data.subject || '') : '';
        const jpVal = data ? (data.jp || 2) : 2;
        const typeNormada = (!data || data.cycle_type !== 'Jurusan') ? 'selected' : '';
        const typeJurusan = (data && data.cycle_type === 'Jurusan') ? 'selected' : '';

        tr.innerHTML = `
            <td class="px-3 py-2.5 text-center font-bold text-slate-400 row-num">1</td>
            <td class="px-3 py-2.5">
                <select name="allocations[${allocRowIndex}][teacher_id]" required class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-indigo-500 focus:outline-none">
                    ${guruOptions}
                </select>
            </td>
            <td class="px-3 py-2.5">
                <input type="text" name="allocations[${allocRowIndex}][subject]" value="${subjectVal}" list="mapel-list" placeholder="Nama Mapel" required class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-indigo-500 focus:outline-none">
            </td>
            <td class="px-3 py-2.5">
                <select name="allocations[${allocRowIndex}][class_name]" required class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-indigo-500 focus:outline-none">
                    ${classOptions}
                </select>
            </td>
            <td class="px-3 py-2.5">
                <select name="allocations[${allocRowIndex}][room_id]" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs focus:border-indigo-500 focus:outline-none">
                    ${roomOptions}
                </select>
            </td>
            <td class="px-3 py-2.5 text-center">
                <input type="number" name="allocations[${allocRowIndex}][jp]" value="${jpVal}" min="1" max="8" required oninput="recalcAllocSummary()" class="h-9 w-20 mx-auto rounded-lg border border-slate-300 bg-white px-2 text-center text-xs font-bold text-indigo-700 focus:border-indigo-500 focus:outline-none">
            </td>
            <td class="px-3 py-2.5 text-center">
                <select name="allocations[${allocRowIndex}][cycle_type]" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs focus:border-indigo-500 focus:outline-none">
                    <option value="Normada" ${typeNormada}>Normada</option>
                    <option value="Jurusan" ${typeJurusan}>Jurusan</option>
                </select>
            </td>
            <td class="px-3 py-2.5 text-center">
                <button type="button" onclick="removeAllocationRow(${allocRowIndex})" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition" title="Hapus baris ini">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        recalcAllocSummary();
    }

    function removeAllocationRow(index) {
        const row = document.getElementById(`alloc-row-${index}`);
        if (row) {
            row.remove();
            recalcAllocSummary();
        }
    }

    function clearAllAllocationRows() {
        const tbody = document.getElementById('allocation-table-body');
        if (tbody) {
            tbody.innerHTML = '';
            recalcAllocSummary();
        }
    }

    function recalcAllocSummary() {
        const rows = document.querySelectorAll('#allocation-table-body tr.alloc-row');
        let totalJp = 0;
        rows.forEach((r, idx) => {
            const numEl = r.querySelector('.row-num');
            if (numEl) numEl.textContent = idx + 1;
            const jpInput = r.querySelector('input[type="number"]');
            if (jpInput) {
                totalJp += parseInt(jpInput.value || 0, 10);
            }
        });

        const countEl = document.getElementById('total-alloc-count');
        const jpEl = document.getElementById('total-alloc-jp');
        if (countEl) countEl.textContent = rows.length;
        if (jpEl) jpEl.textContent = totalJp;
    }

    function loadDemoAllocations() {
        clearAllAllocationRows();

        const g1 = autogenGurus[0] ? autogenGurus[0].id : '';
        const g2 = autogenGurus[1] ? autogenGurus[1].id : g1;
        const g3 = autogenGurus[2] ? autogenGurus[2].id : g1;
        const g4 = autogenGurus[3] ? autogenGurus[3].id : g1;
        const g5 = autogenGurus[4] ? autogenGurus[4].id : g1;

        const c1 = autogenClasses[0] || 'X RPL 1';
        const c2 = autogenClasses[1] || 'XI RPL 1';
        const c3 = autogenClasses[2] || 'XII RPL 2';

        const r1 = autogenRooms[0] ? autogenRooms[0].id_ruangan : '';
        const r2 = autogenRooms[1] ? autogenRooms[1].id_ruangan : '';

        const sampleData = [
            { teacher_id: g1, subject: 'Pemrograman Web & Mobile', class_name: c1, room_id: r1, jp: 4, cycle_type: 'Jurusan' },
            { teacher_id: g2, subject: 'Matematika', class_name: c1, room_id: '', jp: 3, cycle_type: 'Normada' },
            { teacher_id: g3, subject: 'Bahasa Indonesia', class_name: c2, room_id: '', jp: 2, cycle_type: 'Normada' },
            { teacher_id: g4, subject: 'Bahasa Inggris', class_name: c2, room_id: '', jp: 2, cycle_type: 'Normada' },
            { teacher_id: g5, subject: 'Basis Data', class_name: c3, room_id: r2, jp: 4, cycle_type: 'Jurusan' }
        ];

        sampleData.forEach(d => addAllocationRow(d));
    }

    function confirmAutoGen(form) {
        const mode = document.getElementById('autogen-mode-select').value;
        const cycle = form.querySelector('[name="cycle_number"]').value;
        const rows = form.querySelectorAll('#allocation-table-body tr.alloc-row');

        if (rows.length === 0) {
            alert('Harap tambahkan minimal 1 baris alokasi terlebih dahulu.');
            return false;
        }

        if (mode === 'fresh') {
            return confirm(`PERINGATAN: Mode "Bersihkan & Buat Ulang" akan mereset jadwal lama pada Putaran ${cycle}.\n\nLanjutkan penyusunan jadwal otomatis?`);
        }
        return confirm(`Mulai proses penyusunan jadwal otomatis pada Putaran ${cycle}?`);
    }

    function confirmAutoGenExcel(form) {
        const mode = form.querySelector('[name="mode"]').value;
        const cycle = form.querySelector('[name="cycle_number"]').value;
        if (mode === 'fresh') {
            return confirm(`PERINGATAN: Mode "Bersihkan & Buat Ulang" akan mereset jadwal lama pada Putaran ${cycle}.\n\nLanjutkan penyusunan jadwal otomatis dari file Excel?`);
        }
        return confirm(`Mulai proses penyusunan jadwal otomatis dari file Excel pada Putaran ${cycle}?`);
    }

    function openBulkDeleteModal() {
        const modal = document.getElementById('bulkDeleteModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeBulkDeleteModal() {
        const modal = document.getElementById('bulkDeleteModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeBulkDeleteModal();
    });

    // Muat 3 baris kosong secara default saat halaman dibuka
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('allocation-table-body');
        if (tbody && tbody.children.length === 0) {
            addAllocationRow();
            addAllocationRow();
            addAllocationRow();
        }
    });
</script>
@endpush
