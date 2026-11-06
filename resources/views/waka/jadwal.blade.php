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
                    <div class="mt-2 flex flex-wrap gap-4 text-xs">
                        <a href="{{ route('waka.jadwal.template') }}" class="font-medium text-sky-600 hover:underline">Unduh template kosong (.xlsx)</a>
                        <span class="text-slate-300">·</span>
                        <a href="{{ route('waka.jadwal.example') }}" class="font-medium text-sky-600 hover:underline">Unduh contoh berisi 5 data (.xlsx)</a>
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

                    <a href="{{ route('waka.jadwal.pdf', ['cycle' => $activeCycle, 'jurusan' => $selectedJurusan ?? '', 'tingkat' => $selectedTingkat ?? '', 'rombel' => $selectedRombel ?? '', 'q' => $search ?? '']) }}" target="_blank" rel="noopener" class="h-10 inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 text-sm font-bold text-white shadow-sm hover:bg-rose-700 active:scale-95 transition whitespace-nowrap">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ubah ke PDF
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto table-scroll-container">
                @foreach($jadwals as $j)
                    <form id="update-schedule-{{ $j->id }}" method="POST" action="{{ route('waka.jadwal.update', $j->id) }}">@csrf @method('PATCH')</form>
                @endforeach
                <table class="w-full min-w-[1250px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Hari</th>
                            <th class="px-4 py-3">Mata Pelajaran</th>
                            <th class="px-4 py-3">Guru</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Ruangan</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3 rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jadwals as $j)
                        @php
                            $currentPeriodCode = \App\Support\SchoolTimetable::codeFor($j->day_of_week, $j->start_time, $j->end_time);
                        @endphp
                            <tr class="hover:bg-slate-50 transition" data-period-row data-original-day="{{ $j->day_of_week }}" data-legacy-start="{{ substr($j->start_time, 0, 5) }}" data-legacy-end="{{ substr($j->end_time, 0, 5) }}" data-legacy-code="{{ $currentPeriodCode ?? 'legacy' }}">
                            <td class="px-2.5 py-2.5"><select form="update-schedule-{{ $j->id }}" name="day_of_week" required data-day-select class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                            @foreach(\App\Support\SchoolTimetable::DAYS as $day)<option value="{{ $day }}" @selected($j->day_of_week === $day)>{{ $day }}</option>@endforeach
                            @if(!in_array($j->day_of_week, \App\Support\SchoolTimetable::DAYS, true))
                                <option value="{{ $j->day_of_week }}" selected>{{ $j->day_of_week }} (jadwal lama)</option>
                            @endif
                            </select></td>
                            <td class="px-2.5 py-2.5"><input form="update-schedule-{{ $j->id }}" name="subject" list="mapel-list" required maxlength="100" value="{{ $j->subject }}" class="h-9 w-full min-w-32 rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none"></td>
                            <td class="px-2.5 py-2.5"><select form="update-schedule-{{ $j->id }}" name="teacher_id" required class="h-9 w-full min-w-36 rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                @foreach($gurus as $guru)<option value="{{ $guru->id }}" @selected((int) $j->teacher_id === (int) $guru->id)>{{ $guru->fullname ?? $guru->username }}</option>@endforeach
                            </select></td>
                            <td class="px-2.5 py-2.5">
                                <div class="grid grid-cols-2 gap-1.5">
                                    <label class="text-[10px] font-medium text-slate-500">Sesi Mulai<select form="update-schedule-{{ $j->id }}" name="start_period_code" required data-start-period class="mt-0.5 h-8 w-full rounded border border-slate-300 bg-white px-1.5 text-xs focus:border-sky-500 focus:outline-none"></select></label>
                                    <label class="text-[10px] font-medium text-slate-500">Sesi Selesai<select form="update-schedule-{{ $j->id }}" name="end_period_code" required data-end-period class="mt-0.5 h-8 w-full rounded border border-slate-300 bg-white px-1.5 text-xs focus:border-sky-500 focus:outline-none"></select></label>
                                </div>
                                <input type="hidden" value="{{ substr($j->start_time, 0, 5) }}" data-start-time>
                                <input type="hidden" value="{{ substr($j->end_time, 0, 5) }}" data-end-time>
                                <span data-period-preview class="mt-0.5 block text-[11px] text-slate-500"></span>
                            </td>
                            <td class="px-2.5 py-2.5">
                                @php
                                    $scheduleClass = $j->class_name ?? ($j->class_id !== null ? (string) $j->class_id : '');
                                @endphp
                                <select form="update-schedule-{{ $j->id }}" name="class_name" class="h-9 w-full min-w-32 rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                    <option value="">Pilih kelas</option>
                                    @foreach($classOptions as $className)
                                        <option value="{{ $className }}" @selected($scheduleClass === $className)>{{ $className }}</option>
                                    @endforeach
                                    @if($scheduleClass !== '' && ! $classOptions->contains($scheduleClass))
                                        <option value="{{ $scheduleClass }}" selected>Kelas lama: {{ $scheduleClass }}</option>
                                    @endif
                                </select>
                            </td>
                            <td class="px-2.5 py-2.5"><select form="update-schedule-{{ $j->id }}" name="room_id" class="h-9 w-full min-w-32 rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                <option value="">Belum ditentukan</option>
                                @foreach($rooms as $room)<option value="{{ $room->id_ruangan }}" @selected((int) $j->room_id === (int) $room->id_ruangan)>{{ $room->nama_ruangan }}</option>@endforeach
                            </select></td>
                            <td class="px-2.5 py-2.5">
                                <div class="flex items-center gap-1.5 flex-nowrap">
                                    <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-bold text-sky-800 whitespace-nowrap">Putaran {{ $j->cycle_number }}</span>
                                    @if(!$scheduleLocked)
                                        <select form="update-schedule-{{ $j->id }}" name="cycle_type" class="h-9 rounded-lg border border-slate-300 bg-white px-2 text-xs font-bold focus:border-sky-500 focus:outline-none {{ $j->cycle_type === 'Jurusan' ? 'text-purple-700' : 'text-indigo-700' }}">
                                            <option value="Normada" @selected(in_array($j->cycle_type, ['Normada', 'Regular']))>Normada</option>
                                            <option value="Jurusan" @selected($j->cycle_type === 'Jurusan')>Jurusan</option>
                                        </select>
                                    @else
                                        <input form="update-schedule-{{ $j->id }}" type="hidden" name="cycle_type" value="{{ $j->cycle_type }}">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $j->cycle_type === 'Jurusan' ? 'bg-purple-100 text-purple-700' : 'bg-indigo-100 text-indigo-700' }}">{{ $j->cycle_type }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2.5 py-2.5">
                                @if(!$scheduleLocked)
                                    <div class="flex items-center gap-1.5">
                                        <button form="update-schedule-{{ $j->id }}" type="submit" class="h-9 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-700 transition inline-flex items-center justify-center">Simpan</button>
                                        <form method="POST" action="{{ route('waka.jadwal.destroy', $j->id) }}" onsubmit="return confirm('Hapus jadwal {{ $j->subject }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="h-9 rounded-lg bg-rose-100 px-3 text-xs font-semibold text-rose-700 hover:bg-rose-200 transition inline-flex items-center justify-center">Hapus</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Putaran terkunci</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500 italic">
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
</script>
@endpush
