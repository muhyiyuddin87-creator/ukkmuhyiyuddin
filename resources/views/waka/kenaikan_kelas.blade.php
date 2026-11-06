@extends('layouts.waka')

@section('title', 'Kenaikan Kelas & Kelulusan Serentak - Waka Kurikulum')

@section('content')
    <header class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Waka Kurikulum</p>
                <h2 class="mt-1 text-3xl font-bold text-slate-800">Kenaikan Kelas & Kelulusan Serentak</h2>
                <p class="mt-1 text-slate-500 text-sm">Proses promosi tingkat kelas otomatis dan kelulusan siswa tingkat akhir secara massal.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('waka.alumni') }}" class="btn-standard border border-sky-300 bg-sky-50 text-sky-700 hover:bg-sky-100 transition shadow-xs">
                    🎓 Buka Data Alumni
                </a>
                <a href="{{ route('waka.siswa') }}" class="btn-standard border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition shadow-xs">
                    ← Kembali ke Data Siswa
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

    @if($errors->any())
        <div class="mb-5 rounded-xl bg-rose-100 border border-rose-300 px-5 py-4 text-sm text-rose-900 shadow-sm">
            <ul class="list-inside list-disc font-medium">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <!-- Kartu Alur & Statistik -->
    <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-3">
        <!-- Kelas X -->
        <div class="rounded-2xl border border-sky-200 bg-linear-to-br from-sky-50 to-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="rounded-lg bg-sky-100 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-sky-800">Tingkat X (Kelas 10)</span>
                <span class="text-2xl">⬆️</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-sky-950">{{ $countX }} <span class="text-sm font-medium text-slate-500">siswa aktif</span></div>
                <p class="mt-2 text-xs font-semibold text-sky-700 flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    Akan naik serentak menjadi <strong>Tingkat XI</strong>
                </p>
                <p class="mt-1 text-[11px] text-slate-500">Contoh: X RPL 1 → XI RPL 1</p>
            </div>
        </div>

        <!-- Kelas XI -->
        <div class="rounded-2xl border border-indigo-200 bg-linear-to-br from-indigo-50 to-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="rounded-lg bg-indigo-100 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-indigo-800">Tingkat XI (Kelas 11)</span>
                <span class="text-2xl">🚀</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-indigo-950">{{ $countXI }} <span class="text-sm font-medium text-slate-500">siswa aktif</span></div>
                <p class="mt-2 text-xs font-semibold text-indigo-700 flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    Akan naik serentak menjadi <strong>Tingkat XII</strong>
                </p>
                <p class="mt-1 text-[11px] text-slate-500">Contoh: XI RPL 1 → XII RPL 1</p>
            </div>
        </div>

        <!-- Kelas XII -->
        <div class="rounded-2xl border border-emerald-200 bg-linear-to-br from-emerald-50 to-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-800">Tingkat XII (Kelas 12)</span>
                <span class="text-2xl">🎓</span>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-emerald-950">{{ $countXII }} <span class="text-sm font-medium text-slate-500">siswa aktif</span></div>
                <p class="mt-2 text-xs font-semibold text-emerald-700 flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Akan <strong>Lulus & Dinonaktifkan Akunnya</strong>
                </p>
                <p class="mt-1 text-[11px] text-slate-500">Data otomatis disimpan ke Arsip Alumni & akun terkunci</p>
            </div>
        </div>
    </div>

    <!-- Form Pemrosesan Kenaikan & Kelulusan -->
    <form id="form-promosi" method="POST" action="{{ route('waka.kenaikan_kelas.proses') }}" onsubmit="return confirmProses(event)">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
            <h3 class="text-lg font-bold text-slate-800 mb-1 flex items-center gap-2">
                <span>⚙️</span> Parameter Kelulusan & Tahun Ajaran
            </h3>
            <p class="text-sm text-slate-500 mb-5">Masukkan informasi tahun kelulusan untuk siswa kelas XII yang akan diluluskan.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Tahun Kelulusan / Tahun Ajaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="tahun_lulus" value="{{ old('tahun_lulus', '2026/2027') }}" required placeholder="Contoh: 2026/2027" class="form-control-standard w-full">
                    <span class="text-[11px] text-slate-400 mt-1 block">Tahun ajaran saat siswa dinyatakan lulus</span>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Tanggal Kelulusan Resmi <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_lulus" value="{{ old('tanggal_lulus', date('Y-m-d')) }}" required class="form-control-standard w-full">
                    <span class="text-[11px] text-slate-400 mt-1 block">Tanggal terbit SK atau ijazah kelulusan</span>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nomor SK / Keterangan (Opsional)
                    </label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="Contoh: SK Kelulusan No. 421/2026" class="form-control-standard w-full">
                    <span class="text-[11px] text-slate-400 mt-1 block">Catatan yang tersimpan pada riwayat alumni</span>
                </div>
            </div>
        </div>

        <!-- Pengecualian Siswa (Tinggal Kelas / Tidak Lulus) -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>⚠️</span> Pengecualian: Siswa Tinggal Kelas / Tidak Lulus
                    </h3>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Secara default, <strong>seluruh siswa aktif akan dipromosikan/diluluskan</strong>. Centang siswa di bawah jika ada yang <strong>tidak naik kelas</strong> atau <strong>tidak lulus</strong> agar dipertahankan di kelasnya saat ini.
                    </p>
                </div>
                <div class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 shrink-0">
                    Siswa terpilih tidak akan naik/lulus
                </div>
            </div>

            <!-- Accordion List Per Kelas -->
            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                @foreach($classesWithStudents as $className => $students)
                    @php
                        $tingkat = explode(' ', $className)[0] ?? '';
                        $badgeColor = match($tingkat) {
                            'XII' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'XI' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            default => 'bg-sky-100 text-sky-800 border-sky-200'
                        };
                    @endphp
                    <details class="group rounded-xl border border-slate-200 bg-slate-50 overflow-hidden transition">
                        <summary class="flex items-center justify-between px-4 py-3 cursor-pointer bg-slate-100 hover:bg-slate-200/80 transition select-none">
                            <div class="flex items-center gap-3">
                                <span class="rounded-md border px-2 py-0.5 text-xs font-bold {{ $badgeColor }}">{{ $className }}</span>
                                <span class="text-sm font-semibold text-slate-700">{{ count($students) }} Siswa</span>
                            </div>
                            <span class="text-xs text-slate-500 group-open:rotate-180 transition-transform">▼</span>
                        </summary>
                        <div class="p-4 bg-white grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                            @foreach($students as $st)
                                <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-100 hover:bg-rose-50/50 hover:border-rose-200 transition cursor-pointer">
                                    <input type="checkbox" name="excluded_students[]" value="{{ $st->id }}" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                    <div class="truncate">
                                        <p class="font-semibold text-slate-800 truncate">{{ $st->name }}</p>
                                        <p class="text-[10px] text-slate-400">NIS: {{ $st->nis }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </div>

        <!-- Tombol Eksekusi -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl border border-rose-200 bg-rose-50/50 p-6 shadow-sm">
            <div>
                <h4 class="font-bold text-rose-950 text-base flex items-center gap-2">
                    <span>⚡</span> Siap Menjalankan Kenaikan Kelas & Kelulusan?
                </h4>
                <p class="text-xs text-rose-800 mt-1">
                    Tindakan ini akan memperbarui kelas seluruh siswa aktif dan memindahkan siswa kelas XII ke arsip alumni serta menonaktifkan akun login mereka.
                </p>
            </div>
            <button type="submit" class="shrink-0 rounded-xl bg-rose-600 px-6 py-3.5 text-sm font-bold text-white hover:bg-rose-700 transition shadow-md flex items-center gap-2">
                <span>🚀</span> Proses Kenaikan & Kelulusan Serentak
            </button>
        </div>
    </form>

    <script>
        function confirmProses(e) {
            const confirmed = confirm(
                "PERINGATAN KONFIRMASI:\n\n" +
                "Apakah Anda yakin ingin memproses kenaikan kelas & kelulusan serentak sekarang?\n\n" +
                "1. Seluruh siswa Kelas XII akan diluluskan, akunnya dinonaktifkan, dan diarsipkan ke Data Alumni.\n" +
                "2. Seluruh siswa Kelas XI akan naik menjadi Kelas XII.\n" +
                "3. Seluruh siswa Kelas X akan naik menjadi Kelas XI.\n" +
                "4. Siswa yang Anda centang tinggal kelas akan dipertahankan.\n\n" +
                "Klik OK untuk melanjutkan proses."
            );
            return confirmed;
        }
    </script>
@endsection

