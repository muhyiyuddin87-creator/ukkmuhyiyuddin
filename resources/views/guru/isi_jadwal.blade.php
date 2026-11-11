@extends((int)(Auth::user()->role_id ?? 0) === 4 ? 'layouts.kesiswaan' : 'layouts.guru')

@section('title', 'Jadwal Mengajar - School System')

@section('content')
    @php
        $dayThemes = [
            'Senin' => [
                'border' => 'border-t-4 border-t-emerald-500',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'time_pill' => 'bg-emerald-600 text-white',
                'time_icon' => 'text-emerald-200',
                'accent' => 'text-emerald-600',
                'avatar' => 'bg-emerald-600 text-white',
            ],
            'Selasa' => [
                'border' => 'border-t-4 border-t-indigo-500',
                'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'time_pill' => 'bg-indigo-600 text-white',
                'time_icon' => 'text-indigo-200',
                'accent' => 'text-indigo-600',
                'avatar' => 'bg-indigo-600 text-white',
            ],
            'Rabu' => [
                'border' => 'border-t-4 border-t-purple-500',
                'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                'time_pill' => 'bg-purple-600 text-white',
                'time_icon' => 'text-purple-200',
                'accent' => 'text-purple-600',
                'avatar' => 'bg-purple-600 text-white',
            ],
            'Kamis' => [
                'border' => 'border-t-4 border-t-amber-500',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                'time_pill' => 'bg-amber-600 text-white',
                'time_icon' => 'text-amber-200',
                'accent' => 'text-amber-600',
                'avatar' => 'bg-amber-600 text-white',
            ],
            'Jumat' => [
                'border' => 'border-t-4 border-t-sky-500',
                'badge' => 'bg-sky-50 text-sky-700 border-sky-200',
                'time_pill' => 'bg-sky-600 text-white',
                'time_icon' => 'text-sky-200',
                'accent' => 'text-sky-600',
                'avatar' => 'bg-sky-600 text-white',
            ],
        ];

        $daysList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $isKesiswaan = (int)(Auth::user()->role_id ?? 0) === 4;
        $primaryColor = $isKesiswaan ? 'amber' : 'indigo';
    @endphp

    <!-- Header Agenda Guru Modern (Senada dengan Siswa) -->
    <header class="mb-6 rounded-2xl border {{ $isKesiswaan ? 'border-amber-200/80' : 'border-indigo-200/80' }} bg-white p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2 w-2 rounded-full {{ $isKesiswaan ? 'bg-amber-500' : 'bg-indigo-500' }} animate-pulse"></span>
                    <p class="text-xs font-bold uppercase tracking-widest {{ $isKesiswaan ? 'text-amber-700' : 'text-indigo-700' }}">{{ $isKesiswaan ? 'Ruang Kerja Kesiswaan' : 'Agenda Akademik Guru' }}</p>
                </div>
                <h2 class="mt-1.5 text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Jadwal Mengajar Guru</h2>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">Daftar agenda mengajar aktif tahun ajaran <span class="font-semibold text-slate-700">{{ $academicYear }}</span> (Putaran ke-{{ $activeCycle }})</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-xl bg-slate-100 border border-slate-200/80 px-3.5 py-2 text-xs font-semibold text-slate-700">
                    TA {{ $academicYear }}
                </div>
                <a href="{{ route('guru.jadwal.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-slate-800 active:scale-95 transition">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Ubah ke PDF / Cetak Jadwal</span>
                </a>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-5 rounded-xl bg-emerald-100 border border-emerald-300 px-5 py-3 text-emerald-800 font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl bg-rose-100 border border-rose-300 px-5 py-3 text-rose-800 font-semibold space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if($scheduleLocked)
        <div class="mb-6 rounded-xl border border-rose-300 bg-rose-50 px-5 py-4 font-semibold text-rose-800 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Putaran {{ $activeCycle }} sedang dikunci oleh Waka Kurikulum. Jadwal resmi tersimpan aktif untuk KBM.</span>
        </div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm mb-8">
        <div class="mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Daftar Jadwal Mengajar</h3>
                <p class="text-xs text-slate-500 mt-0.5">Jadwal pembelajaran aktif untuk {{ Auth::user()->fullname ?? Auth::user()->username }} pada Putaran {{ $activeCycle }}.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full {{ $isKesiswaan ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }} px-3 py-1 text-xs font-bold uppercase tracking-wide">
                    {{ Auth::user()->fullname ?? Auth::user()->username }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">
                    Putaran {{ $activeCycle }}
                </span>
            </div>
        </div>

        <!-- Filter Hari Interaktif Sederhana (Pill Style) -->
        <div class="mb-5 flex flex-wrap items-center gap-1.5" id="day-filter-container">
            <button type="button" onclick="filterScheduleDay('all')" class="day-filter-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold {{ $isKesiswaan ? 'bg-amber-600' : 'bg-indigo-600' }} text-white shadow-xs transition" data-day="all">
                <span>Semua Hari</span>
                <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px]">{{ $jadwals->count() }}</span>
            </button>
            @foreach($daysList as $d)
                @php
                    $countDay = $jadwals->where('day_of_week', $d)->count();
                @endphp
                <button type="button" onclick="filterScheduleDay('{{ $d }}')" class="day-filter-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition" data-day="{{ $d }}">
                    <span>{{ $d }}</span>
                    <span class="rounded-full bg-slate-200 text-slate-700 px-1.5 py-0.2 text-[10px] font-bold">{{ $countDay }}</span>
                </button>
            @endforeach
        </div>

        <!-- Tabel Bersih Sesuai Konsep Gambar 2 -->
        <div class="overflow-x-auto table-scroll-container">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50/70">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3">Hari</th>
                        <th class="whitespace-nowrap px-4 py-3">Mata Pelajaran</th>
                        <th class="whitespace-nowrap px-4 py-3">Waktu Mengajar</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Kelas</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Ruangan</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Jenis</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="schedule-table-body">
                    @forelse($jadwals as $j)
                        @php
                            $periodLabel = \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time);
                            $startTime = substr($j->start_time, 0, 5);
                            $endTime = substr($j->end_time, 0, 5);
                            $className = $j->class_name ?? ($j->class_id ? 'Kelas ' . $j->class_id : '-');
                            $roomName = $j->nama_ruangan ?? ($j->room_id ? 'Ruang ' . $j->room_id : '-');
                            $jenis = in_array($j->cycle_type, ['Regular', 'Normada', null], true) ? 'Regular' : $j->cycle_type;
                        @endphp
                        <tr class="schedule-table-row hover:bg-slate-50/80 transition" data-day="{{ $j->day_of_week }}">
                            <td class="whitespace-nowrap px-4 py-3.5 font-bold text-slate-800">{{ $j->day_of_week }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-800">{{ $j->subject }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-600">
                                {{ $startTime }} - {{ $endTime }}
                                @if($periodLabel)
                                    <span class="block text-[11px] text-slate-400 font-normal">{{ $periodLabel }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center text-slate-700 font-medium">{{ $className }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center text-slate-600">{{ $roomName }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold {{ $jenis === 'Jurusan' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $jenis }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                <a href="{{ route('guru.jurnal') }}" class="inline-flex items-center gap-1 rounded-full bg-indigo-50 border border-indigo-200 px-3 py-0.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition" title="Buka form jurnal">
                                    <span>Jurnal</span>
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">
                                Belum ada jadwal tersimpan untuk putaran ini.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="empty-schedule-filter-row" class="hidden">
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">
                            Tidak ada jadwal mengajar pada hari yang dipilih.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

        <!-- Form Tambah Jadwal Baru (Jika tidak dikunci) -->
    @if(!$scheduleLocked)
        <section class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-800">Tambah Jadwal Baru</h3>
                <p class="text-xs text-slate-500">Input rencana mengajar secara mandiri untuk Putaran {{ $activeCycle }}.</p>
            </div>
            <form method="POST" action="{{ route('guru.jadwal.store') }}">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="border-b text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="whitespace-nowrap px-3 py-3">Hari</th>
                                <th class="whitespace-nowrap px-3 py-3">Mata Pelajaran</th>
                                <th class="whitespace-nowrap px-3 py-3">Mulai</th>
                                <th class="whitespace-nowrap px-3 py-3">Selesai</th>
                                <th class="whitespace-nowrap px-3 py-3">Kelas</th>
                                <th class="whitespace-nowrap px-3 py-3">Jenis</th>
                            </tr>
                        </thead>
                        <tbody id="jadwal-tbody" class="divide-y divide-slate-100">
                            <tr class="jadwal-row">
                                <td class="px-3 py-4">
                                    <select name="day_of_week[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                                        <option>Senin</option>
                                        <option>Selasa</option>
                                        <option>Rabu</option>
                                        <option>Kamis</option>
                                        <option>Jumat</option>
                                    </select>
                                </td>
                                <td class="px-3 py-4">
                                    <input type="text" name="subject[]" placeholder="Mata pelajaran" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                                </td>
                                <td class="px-3 py-4">
                                    <input type="time" name="start_time[]" value="07:30" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                                </td>
                                <td class="px-3 py-4">
                                    <input type="time" name="end_time[]" value="09:00" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                                </td>
                                <td class="px-3 py-4">
                                    <input type="number" name="class_id[]" min="1" placeholder="ID kelas" class="h-9 w-28 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                                </td>
                                <td class="px-3 py-4">
                                    <select name="cycle_type[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                                        <option value="Normada">Normada</option>
                                        <option value="Jurusan">Jurusan</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" id="btn-tambah" onclick="addRow()" class="h-10 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition active:scale-95 shadow-xs">
                        + Tambah baris
                    </button>
                    <button type="submit" class="h-10 inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 text-sm font-semibold text-white hover:bg-indigo-500 transition shadow-xs hover:shadow active:scale-95">
                        Simpan Jadwal ke Database
                    </button>
                </div>
            </form>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        function filterScheduleDay(day) {
            const rows = document.querySelectorAll('.schedule-table-row');
            const buttons = document.querySelectorAll('.day-filter-btn');
            const emptyRow = document.getElementById('empty-schedule-filter-row');

            buttons.forEach(btn => {
                if (btn.getAttribute('data-day') === day) {
                    btn.className = 'day-filter-btn active inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold {{ $isKesiswaan ? 'bg-amber-600' : 'bg-indigo-600' }} text-white shadow-xs transition';
                } else {
                    btn.className = 'day-filter-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
                }
            });

            let visibleCount = 0;
            rows.forEach(row => {
                if (day === 'all' || row.getAttribute('data-day') === day) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (emptyRow) {
                if (visibleCount === 0 && rows.length > 0) {
                    emptyRow.classList.remove('hidden');
                } else {
                    emptyRow.classList.add('hidden');
                }
            }
        }

        function addRow() {
            const tbody = document.getElementById('jadwal-tbody');
            const tr = document.createElement('tr');
            tr.className = 'jadwal-row';
            tr.innerHTML = `
                <td class="px-3 py-4">
                    <select name="day_of_week[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                        <option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option>
                    </select>
                </td>
                <td class="px-3 py-4">
                    <input type="text" name="subject[]" placeholder="Mata pelajaran" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                </td>
                <td class="px-3 py-4">
                    <input type="time" name="start_time[]" value="07:30" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                </td>
                <td class="px-3 py-4">
                    <input type="time" name="end_time[]" value="09:00" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none" required>
                </td>
                <td class="px-3 py-4">
                    <input type="number" name="class_id[]" min="1" placeholder="ID kelas" class="h-9 w-28 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                </td>
                <td class="px-3 py-4">
                    <select name="cycle_type[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-indigo-500 outline-none">
                        <option value="Normada">Normada</option>
                        <option value="Jurusan">Jurusan</option>
                    </select>
                </td>
            `;
            tbody.appendChild(tr);
        }
    </script>
@endpush
