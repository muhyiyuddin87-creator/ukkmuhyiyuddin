<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Jadwal Pelajaran' }} - PDF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: #000 !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .page-break {
                page-break-after: always;
            }
            @page {
                size: A4 landscape;
                margin: 10mm 12mm;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #1e293b !important;
            }
        }

        body {
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            background-color: #f1f5f9;
        }

        .kop-border {
            border-bottom: 3px double #0f172a;
        }
    </style>
</head>
<body class="p-4 sm:p-6 md:p-8 text-slate-900">

    <!-- Top Action Toolbar (Hidden during print / in exported PDF) -->
    <div class="no-print max-w-6xl mx-auto mb-6 rounded-2xl bg-white p-4 shadow-md border border-slate-200">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">Pratinjau Dokumen Jadwal (Siap Cetak / PDF)</h2>
                    <p class="text-xs text-slate-500">Klik tombol merah di samping untuk langsung menyimpan sebagai file PDF.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(isset($availableClasses) && count($availableClasses) > 0)
                <form method="GET" class="flex items-center gap-2">
                    <select name="class" onchange="this.form.submit()" class="rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($availableClasses as $c)
                            <option value="{{ $c }}" @selected(($filterClass ?? '') === $c)>Kelas: {{ $c }}</option>
                        @endforeach
                    </select>
                    @if(isset($filterCycle))
                        <input type="hidden" name="cycle" value="{{ $filterCycle }}">
                    @endif
                    @if(!empty($filterJurusan))
                        <input type="hidden" name="jurusan" value="{{ $filterJurusan }}">
                    @endif
                    @if(!empty($filterTingkat))
                        <input type="hidden" name="tingkat" value="{{ $filterTingkat }}">
                    @endif
                    @if(!empty($search))
                        <input type="hidden" name="q" value="{{ $search }}">
                    @endif
                </form>
                @endif

                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-rose-700 transition active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Simpan / Cetak PDF
                </button>
                <button type="button" onclick="window.close()" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Tutup
                </button>
            </div>
        </div>

        <div class="mt-3 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-[11px] text-amber-800 flex items-center gap-2">
            <span><strong>Tips:</strong> Pada jendela cetak browser yang muncul, pilih tujuan (*Destination/Printer*): <strong>"Save as PDF" (Simpan sebagai PDF)</strong> lalu klik <strong>Save</strong>.</span>
        </div>
    </div>

    <!-- Official Printable Document Container (A4 Landscape Formatted) -->
    <div class="max-w-6xl mx-auto bg-white p-8 sm:p-10 shadow-lg rounded-2xl border border-slate-200 print:shadow-none print:border-none print:p-0">
        
        <!-- Kop Surat Resmi -->
        <header class="kop-border pb-4 mb-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-white font-black text-2xl shadow-sm">
                        SCH
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black uppercase tracking-wider text-slate-900">SCHOOL SYSTEM ACADEMY</h1>
                        <p class="text-xs text-slate-600 font-medium">Sistem Informasi Kurikulum, Pembelajaran, dan Presensi Siswa</p>
                        <p class="text-[11px] text-slate-500">Jl. Pendidikan No. 45 • Telp: (021) 7890-1234 • Email: info@school.system.sch.id</p>
                    </div>
                </div>
                <div class="text-right hidden sm:block">
                    <span class="inline-block rounded-lg bg-slate-100 border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-800">
                        DOKUMEN RESMI
                    </span>
                    <p class="text-[10px] text-slate-500 mt-1">Status: Terverifikasi Sistem</p>
                </div>
            </div>
        </header>

        <!-- Judul & Keterangan Dokumen -->
        <div class="text-center mb-6">
            <h2 class="text-lg sm:text-xl font-bold uppercase tracking-wide text-slate-900 underline decoration-2 underline-offset-4">
                {{ $documentTitle ?? 'JADWAL PELAJARAN SEKOLAH' }}
            </h2>
            <p class="text-xs font-semibold text-slate-700 mt-1.5">
                Tahun Ajaran: {{ $academicYear ?? '2026/2027' }} 
                @if(!empty($activeCycle)) • Putaran {{ $activeCycle }} @endif
                @if(!empty($filterJurusan)) • Jurusan: <span class="text-sky-700 font-bold">{{ $filterJurusan }}</span> @endif
                @if(!empty($filterTingkat)) • Tingkat: <span class="text-purple-700 font-bold">{{ $filterTingkat }}</span> @endif
                @if(!empty($filterClass)) • Kelas: <span class="text-emerald-700 font-bold">{{ $filterClass }}</span> @endif
                @if(!empty($filterTeacherName)) • Guru: <span class="text-indigo-700 font-bold">{{ $filterTeacherName }}</span> @endif
                @if(!empty($search)) • Pencarian: <span class="text-slate-800 font-bold">"{{ $search }}"</span> @endif
            </p>
            <p class="text-[10px] text-slate-500 mt-0.5">Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y • H:i') }} WIB</p>
        </div>

        <!-- Tabel Jadwal Resmi Bergaris -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-800">
                <thead>
                    <tr class="bg-slate-100 text-slate-900 font-bold uppercase tracking-wider border-b border-slate-800">
                        <th class="border border-slate-800 px-3 py-2.5 text-center w-10">No</th>
                        <th class="border border-slate-800 px-3 py-2.5 w-24">Hari</th>
                        <th class="border border-slate-800 px-3 py-2.5 w-32">Waktu / Jam</th>
                        <th class="border border-slate-800 px-3 py-2.5">Mata Pelajaran</th>
                        <th class="border border-slate-800 px-3 py-2.5">Guru Pengampu</th>
                        <th class="border border-slate-800 px-3 py-2.5 w-24 text-center">Kelas</th>
                        <th class="border border-slate-800 px-3 py-2.5 w-28 text-center">Ruangan</th>
                        <th class="border border-slate-800 px-3 py-2.5 w-20 text-center">Jenis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($jadwals as $index => $j)
                    <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                        <td class="border border-slate-800 px-3 py-2 text-center font-medium">{{ $index + 1 }}</td>
                        <td class="border border-slate-800 px-3 py-2 font-bold text-slate-900">{{ $j->day_of_week }}</td>
                        <td class="border border-slate-800 px-3 py-2 text-slate-800 whitespace-nowrap">
                            {{ substr($j->start_time, 0, 5) }} - {{ substr($j->end_time, 0, 5) }} WIB
                            <div class="text-[10px] text-slate-500">{{ \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time) ?? '' }}</div>
                        </td>
                        <td class="border border-slate-800 px-3 py-2 font-bold text-slate-900">{{ $j->subject }}</td>
                        <td class="border border-slate-800 px-3 py-2 font-medium text-slate-800">{{ $j->guru_name ?? 'Guru #' . $j->teacher_id }}</td>
                        <td class="border border-slate-800 px-3 py-2 text-center font-bold text-slate-900">{{ $j->class_name ?? ($j->class_id ?? '-') }}</td>
                        <td class="border border-slate-800 px-3 py-2 text-center text-slate-700">{{ $j->nama_ruangan ?? '-' }}</td>
                        <td class="border border-slate-800 px-3 py-2 text-center font-semibold text-slate-700">{{ $j->cycle_type === 'Regular' ? 'Normada' : ($j->cycle_type ?? 'Normada') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="border border-slate-800 px-4 py-8 text-center text-slate-500 italic">
                            Tidak ada jadwal pelajaran yang ditemukan untuk kriteria ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Total Jam & Catatan -->
        <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between text-xs text-slate-600">
            <div>
                Total Jadwal Terdata: <span class="font-bold text-slate-900">{{ count($jadwals) }} pertemuan</span>
            </div>
            <div>
                Sistem Jadwal Berdasarkan Kalender Kurikulum Semester Berjalan
            </div>
        </div>

        <!-- Kolom Tanda Tangan Resmi Pengesahan -->
        <div class="mt-12 pt-4 border-t border-slate-300 grid grid-cols-2 gap-8 text-xs text-slate-900">
            <div class="text-center">
                <p class="text-slate-500 mb-1">Mengetahui,</p>
                <p class="font-bold text-slate-800">Kepala Sekolah</p>
                <div class="h-20"></div>
                <p class="font-bold underline text-slate-900">Dr. H. Ahmad Fauzi, M.Pd.</p>
                <p class="text-[10px] text-slate-500">NIP. 19780512 200212 1 003</p>
            </div>
            <div class="text-center">
                <p class="text-slate-500 mb-1">Ditetapkan di Jakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-slate-800">Waka Bidang Kurikulum</p>
                <div class="h-20"></div>
                <p class="font-bold underline text-slate-900">{{ Auth::user()->fullname ?? (Auth::user()->username ?? 'Waka Kurikulum') }}</p>
                <p class="text-[10px] text-slate-500">NIP. {{ Auth::user()->nip ?? '19820315 200801 1 005' }}</p>
            </div>
        </div>

    </div>

</body>
</html>

