@extends((int)($roleId ?? 0) === 4 ? 'layouts.kesiswaan' : 'layouts.guru')

@section('title', 'Isi Jurnal ' . ((int)($roleId ?? 0) === 4 ? 'Kesiswaan' : 'Guru'))

@section('content')
            <header class="mb-6">
                <p class="text-sm uppercase tracking-widest text-slate-700 font-semibold">{{ $roleId === 4 ? 'Ruang kerja kesiswaan' : 'Ruang kerja guru' }}</p>
                <h2 class="mt-2 text-3xl font-bold">Isi Jurnal Mengajar</h2>
                <p class="mt-2 text-slate-500">{{ $roleId === 4 ? 'Pilih jadwal dari seluruh sekolah, lalu catat kegiatan pembelajaran dan lampirkan bukti foto.' : 'Catat kegiatan pembelajaran dan lampirkan bukti foto. Hasil akan muncul di Dashboard.' }}</p>
            </header>

            @if(session('success'))
            <div class="mb-4 rounded-xl bg-emerald-100 border border-emerald-300 px-5 py-3 text-emerald-800 font-semibold flex items-center gap-2">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 rounded-xl bg-rose-100 border border-rose-300 px-5 py-3 text-rose-800 font-semibold">
                @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            @if(!empty($hasTeacherSchedules) && empty($isKesiswaan) && (int)($roleId ?? 0) === 2 && isset($unfilledSchedules) && $unfilledSchedules->isNotEmpty())
            <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-4 sm:p-5 shadow-sm text-sm text-amber-900">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-200/70 text-amber-800 shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                        <div>
                            <h4 class="font-bold text-amber-900">Pengingat: {{ $unfilledSchedules->count() }} Jadwal Belum Diisi Jurnal</h4>
                            <p class="text-xs text-amber-800 mt-0.5">Silakan pilih jadwal yang bersangkutan pada form di bawah untuk mencatat materi dan kegiatan pembelajaran.</p>
                        </div>
                    </div>
                    <a href="#journal-form" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700 transition shadow-xs">
                        Tulis Jurnal
                    </a>
                </div>
            </div>
            @endif

            <!-- Tabel Jurnal Tersimpan -->
            <section class="rounded-2xl bg-white p-6 shadow-sm mb-6 border border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-slate-800">Riwayat Jurnal Mengajar</h3>
                            <span id="journal-count-badge" class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $roleId === 4 ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-indigo-100 text-indigo-800 border-indigo-200' }} border">
                                <span id="journal-visible-count">{{ $journals->count() }}</span> Data
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Daftar rekaman materi dan kegiatan KBM yang telah tersimpan di sistem.</p>
                    </div>

                    @if(!empty($startDate) || !empty($endDate))
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Filter: {{ $startDate ?: 'Awal' }} s.d. {{ $endDate ?: 'Sekarang' }}
                        </span>
                        <a href="{{ route('guru.jurnal') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-800 underline">Hapus Filter</a>
                    </div>
                    @endif
                </div>

                <!-- Panel Filtering Tanggal & Pencarian -->
                <div class="mb-5 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <form method="GET" action="{{ route('guru.jurnal') }}" id="journal-filter-form" class="space-y-3">
                        @if(request()->filled('edit'))
                            <input type="hidden" name="edit" value="{{ request('edit') }}">
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                            <!-- Input Dari Tanggal -->
                            <div class="lg:col-span-3">
                                <label for="filter-start-date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Dari Tanggal
                                </label>
                                <input type="date" id="filter-start-date" name="start_date" value="{{ $startDate ?? '' }}"
                                    class="h-9 w-full rounded-xl border border-slate-300 bg-white px-3 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                            </div>

                            <!-- Input Sampai Tanggal -->
                            <div class="lg:col-span-3">
                                <label for="filter-end-date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Sampai Tanggal
                                </label>
                                <input type="date" id="filter-end-date" name="end_date" value="{{ $endDate ?? '' }}"
                                    class="h-9 w-full rounded-xl border border-slate-300 bg-white px-3 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                            </div>

                            <!-- Input Cari Mapel / Materi -->
                            <div class="lg:col-span-4">
                                <label for="filter-search-text" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Cari Mapel / Materi
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </span>
                                    <input type="text" id="filter-search-text" placeholder="Ketik kata kunci mapel atau materi..."
                                        class="h-9 w-full rounded-xl border border-slate-300 bg-white pl-8 pr-3 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                                </div>
                            </div>

                            <!-- Tombol Submit & Reset -->
                            <div class="lg:col-span-2 flex items-center gap-1.5">
                                <button type="submit" class="h-9 flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl {{ $roleId === 4 ? 'bg-amber-600 hover:bg-amber-700' : 'bg-slate-900 hover:bg-slate-800' }} px-3 text-xs font-bold text-white shadow-xs transition active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                    <span>Filter</span>
                                </button>
                                <button type="button" onclick="resetJournalDateFilter()" class="h-9 inline-flex items-center justify-center px-3 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-100 transition" title="Reset filter">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- Pintasan Cepat Tanggal (Presets) -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-slate-200/70 text-xs">
                            <span class="text-slate-500 font-semibold mr-1">Pintasan Cepat:</span>
                            <button type="button" onclick="setJournalPreset('all')" class="preset-btn px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-slate-300 font-medium transition shadow-2xs">
                                Semua Tanggal
                            </button>
                            <button type="button" onclick="setJournalPreset('today')" class="preset-btn px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-slate-300 font-medium transition shadow-2xs">
                                Hari Ini
                            </button>
                            <button type="button" onclick="setJournalPreset('yesterday')" class="preset-btn px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-slate-300 font-medium transition shadow-2xs">
                                Kemarin
                            </button>
                            <button type="button" onclick="setJournalPreset('7days')" class="preset-btn px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-slate-300 font-medium transition shadow-2xs">
                                7 Hari Terakhir
                            </button>
                            <button type="button" onclick="setJournalPreset('this_month')" class="preset-btn px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-slate-300 font-medium transition shadow-2xs">
                                Bulan Ini
                            </button>
                            <button type="button" onclick="setJournalPreset('last_month')" class="preset-btn px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-slate-300 font-medium transition shadow-2xs">
                                Bulan Lalu
                            </button>
                            <span id="active-filter-summary" class="ml-auto text-[11px] font-semibold text-slate-500 hidden sm:inline-block"></span>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[700px] text-left text-sm" id="journal-history-table">
                        <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Jadwal / Mata Pelajaran</th>
                                <th class="px-4 py-3">Materi / Kegiatan</th>
                                <th class="px-4 py-3">Bukti Foto</th>
                                @if(!empty($canTakeAttendance) || ($roleId === 2 && ($isWali ?? false)))<th class="px-4 py-3">Presensi Siswa</th>@endif
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="journal-table-body">
                            @forelse($journals as $j)
                            <tr class="journal-row hover:bg-slate-50 transition" data-date="{{ $j->date }}" data-search="{{ strtolower(($j->subject ?? '') . ' ' . ($j->topic_description ?? '') . ' ' . $j->date) }}">
                                <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $j->date }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium {{ $roleId === 4 ? 'text-amber-700' : 'text-indigo-700' }}">
                                    {{ $j->subject ?? 'Jadwal #' . $j->schedule_id }}
                                    @if(!empty($j->class_name))
                                        <span class="block text-xs font-semibold text-slate-500">{{ $j->class_name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-700 max-w-xs break-words">{{ $j->topic_description }}</td>
                                <td class="px-4 py-3">
                                    @if($j->photo_path)
                                        <a href="{{ asset('storage/' . $j->photo_path) }}" target="_blank" rel="noopener" class="inline-flex flex-col items-start gap-1 text-xs font-semibold {{ $roleId === 4 ? 'text-amber-700' : 'text-indigo-700' }} hover:underline">
                                            <img src="{{ asset('storage/' . $j->photo_path) }}" alt="Bukti foto jurnal tanggal {{ $j->date }}" class="h-12 w-16 rounded object-cover shadow-2xs">
                                            Lihat foto
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs">Tanpa Foto</span>
                                    @endif
                                </td>
                                @if(!empty($canTakeAttendance) || ($roleId === 2 && ($isWali ?? false)))
                                <td class="px-4 py-3">
                                    @forelse($attendanceByJournal->get($j->id, collect()) as $attendance)
                                        <div class="whitespace-nowrap text-xs text-slate-600">{{ $attendance->student_name ?? 'Siswa' }}: <span class="font-semibold">{{ $attendance->status }}</span></div>
                                    @empty
                                        <span class="text-xs text-slate-400">Belum ada presensi</span>
                                    @endforelse
                                </td>
                                @endif
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Tersimpan</span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('guru.jurnal', ['edit' => $j->id]) }}#journal-form" class="inline-flex rounded-lg {{ $roleId === 4 ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200' }} px-3 py-1.5 text-xs font-semibold">Edit</a>
                                </td>
                            </tr>
                            @empty
                            <tr id="server-empty-row">
                                <td colspan="{{ (!empty($canTakeAttendance) || ($roleId === 2 && ($isWali ?? false))) ? 7 : 6 }}" class="px-4 py-8 text-center text-slate-400 italic">
                                    Belum ada jurnal yang tersimpan{{ (!empty($startDate) || !empty($endDate)) ? ' pada rentang tanggal ini.' : '.' }}
                                </td>
                            </tr>
                            @endforelse
                            <tr id="no-matching-journals" class="hidden">
                                <td colspan="{{ (!empty($canTakeAttendance) || ($roleId === 2 && ($isWali ?? false))) ? 7 : 6 }}" class="px-4 py-8 text-center text-slate-400 italic">
                                    Tidak ada data jurnal yang sesuai dengan filter tanggal atau pencarian yang dipilih.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="journal-form" class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 mb-4">{{ $editJournal ? 'Edit Jurnal' : 'Tambah Jurnal Baru' }}</h3>
                <form method="POST" action="{{ $editJournal ? route('guru.jurnal.update', $editJournal->id) : route('guru.jurnal.store') }}" enctype="multipart/form-data" onsubmit="return confirm('{{ $editJournal ? 'Apakah Anda yakin ingin menyimpan perubahan jurnal ini?' : 'Apakah Anda yakin ingin menyimpan jurnal ini?' }}')">
                    @csrf
                    @if($editJournal)
                        @method('PATCH')
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal</label>
                                <span id="journal-day-indicator" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700 border border-slate-200">
                                    Hari: <span id="journal-selected-day" class="font-bold {{ $roleId === 4 ? 'text-amber-700' : 'text-indigo-700' }}">{{ $todayDayName ?? 'Hari Ini' }}</span>
                                </span>
                            </div>
                            <input type="date" id="journal-date-input" name="date" value="{{ old('date', $editJournal->date ?? date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm {{ $roleId === 4 ? 'focus:border-amber-500 focus:ring-amber-100' : 'focus:border-indigo-500 focus:ring-indigo-100' }} focus:ring-2 outline-none transition" required>
                            <p class="mt-1 text-[11px] text-slate-500 font-medium">💡 <strong>Bebas memilih tanggal:</strong> Anda dapat memilih tanggal hari ini maupun tanggal-tanggal sebelumnya untuk mencatat jurnal susulan.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Pilih Jadwal / Mata Pelajaran</label>
                            <select id="journal-schedule-select" name="schedule_id" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm {{ $roleId === 4 ? 'focus:border-amber-500 focus:ring-amber-100' : 'focus:border-indigo-500 focus:ring-indigo-100' }} focus:ring-2 outline-none transition" required>
                                <option value="">-- Pilih Jadwal Sesuai Hari --</option>
                                @foreach($jadwals as $j)
                                <option value="{{ $j->id }}" data-day="{{ $j->day_of_week }}" @selected(old('schedule_id', $editJournal->schedule_id ?? '') == $j->id)>{{ $j->day_of_week }} &bull; {{ $j->subject }} ({{ $j->class_name ?? $j->class_id ?? '-' }}){{ $j->start_time ? ' [' . substr($j->start_time, 0, 5) . ' - ' . substr($j->end_time, 0, 5) . ']' : '' }}</option>
                                @endforeach
                            </select>
                            <p id="journal-no-schedules-hint" class="mt-1 text-xs text-amber-600 font-medium hidden">⚠️ Anda tidak memiliki jadwal mengajar pada hari <span id="no-schedules-day-name"></span>.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Materi / Kegiatan Pembelajaran</label>
                            <textarea name="topic_description" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm {{ $roleId === 4 ? 'focus:border-amber-500 focus:ring-amber-100' : 'focus:border-indigo-500 focus:ring-indigo-100' }} focus:ring-2 outline-none transition" placeholder="Tuliskan materi dan kegiatan pembelajaran..." required>{{ old('topic_description', $editJournal->topic_description ?? '') }}</textarea>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Bukti Foto Dokumentasi KBM (Maks. 5 MB)</label>
                                <button type="button" id="btn-open-classroom-webcam" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700 active:scale-95 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Ambil Foto via Webcam</span>
                                </button>
                            </div>
                            <input type="file" id="journal-photo-input" name="photo" accept="image/*" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm file:mr-3 file:h-7 file:rounded-md file:border-0 {{ $roleId === 4 ? 'file:bg-amber-100 file:text-amber-700' : 'file:bg-indigo-100 file:text-indigo-700' }} file:font-semibold file:px-2.5 file:text-xs focus:outline-none">

                            <!-- Preview foto yang dijepret via webcam realtime -->
                            <div id="webcam-captured-preview" class="mt-3 hidden rounded-xl border border-emerald-300 bg-emerald-50/70 p-3">
                                <div class="flex items-center justify-between gap-3 flex-wrap">
                                    <div class="flex items-center gap-3">
                                        <img id="webcam-preview-img" class="h-14 w-20 rounded-lg object-cover border border-emerald-300 shadow-xs" alt="Foto Suasana Kelas">
                                        <div>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                                ✓ Foto Realtime Siap Diunggah
                                            </span>
                                            <p class="text-xs text-slate-500 mt-0.5" id="webcam-file-info">Terlampir dari tangkapan kamera/webcam.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" id="btn-retake-webcam" class="rounded-lg border border-emerald-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition">Ambil Ulang</button>
                                        <button type="button" id="btn-remove-webcam" class="rounded-lg border border-rose-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">Hapus</button>
                                    </div>
                                </div>
                            </div>

                            @if($editJournal?->photo_path)
                                <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                                    <a href="{{ asset('storage/' . $editJournal->photo_path) }}" target="_blank" rel="noopener" class="font-semibold {{ $roleId === 4 ? 'text-amber-700' : 'text-indigo-700' }} underline">
                                        <img src="{{ asset('storage/' . $editJournal->photo_path) }}" alt="Foto jurnal saat ini" class="h-12 w-16 rounded object-cover">
                                    </a>
                                    <span>Foto saat ini. Biarkan kosong agar tetap digunakan.</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if(!empty($canTakeAttendance) || ($roleId === 2 && ($isWali ?? false)))
                    <section id="journal-attendance" class="mt-6 rounded-xl border {{ $roleId === 4 ? 'border-amber-200 bg-amber-50/50' : 'border-indigo-100 bg-indigo-50/50' }} p-4" hidden>
                        <h4 class="font-bold text-slate-800">Presensi Siswa</h4>
                        <p class="mt-1 text-sm text-slate-500">Pilih status kehadiran setiap siswa. Jurnal dan presensi akan disimpan bersamaan.</p>
                        <div id="journal-attendance-leave-alert" class="mt-3 hidden rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800"></div>
                        <div class="mt-4 overflow-x-auto table-scroll-container">
                            <table class="w-full min-w-[420px] text-left text-sm">
                                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2">Nama Siswa</th><th class="px-3 py-2">Status Kehadiran</th></tr></thead>
                                <tbody id="journal-attendance-rows" class="divide-y divide-slate-200"></tbody>
                            </table>
                        </div>
                        <p id="journal-attendance-empty" class="mt-3 text-sm text-amber-700" hidden>Tidak ada data siswa untuk kelas jadwal ini.</p>
                    </section>
                    @endif
                    <div class="mt-5 flex justify-end gap-3">
                        @if($editJournal)
                            <a href="{{ route('guru.jurnal') }}#journal-form" class="h-10 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition active:scale-95">Batal</a>
                        @endif
                        <button type="submit" class="h-10 inline-flex items-center justify-center rounded-lg {{ $roleId === 4 ? 'bg-amber-600 hover:bg-amber-700' : 'bg-slate-800 hover:bg-slate-700' }} px-6 text-sm font-semibold text-white transition shadow-xs active:scale-95">
                            {{ $editJournal ? 'Simpan Perubahan' : 'Simpan Jurnal ke Database' }}
                        </button>
                    </div>
                </form>
            </section>

    <!-- Modal Webcam Real-Time untuk Dokumentasi Kelas (TC-JRN-02) -->
    <div id="classroom-webcam-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/80 backdrop-blur-xs p-4 overflow-y-auto">
        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl overflow-hidden border border-slate-200 flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3.5 bg-slate-50">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Ambil Foto Suasana Kelas via Webcam</h3>
                        <p class="text-xs text-slate-500">Realtime Camera / Webcam Documentation</p>
                    </div>
                </div>
                <button type="button" id="btn-close-webcam-modal" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Video Viewfinder -->
            <div class="relative bg-slate-950 flex items-center justify-center aspect-4/3 max-h-[400px] overflow-hidden">
                <video id="classroom-video" class="w-full h-full object-cover" autoplay playsinline muted></video>
                <canvas id="classroom-canvas" class="hidden"></canvas>
                <div id="classroom-flash" class="absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-150"></div>

                <!-- Overlay Info -->
                <div class="absolute top-3 left-3 right-3 flex items-center justify-between pointer-events-none">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-900/80 backdrop-blur-xs px-3 py-1 text-xs font-semibold text-white">
                        <span class="h-2 w-2 rounded-full bg-rose-500 animate-ping"></span>
                        LIVE WEBCAM KBM
                    </span>
                    <button type="button" id="btn-switch-camera" class="pointer-events-auto rounded-full bg-slate-900/80 backdrop-blur-xs p-2 text-white hover:bg-slate-800 transition" title="Ganti Kamera">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>

                <div class="absolute bottom-3 inset-x-3 pointer-events-none text-center">
                    <span class="inline-block rounded-full bg-black/60 px-3 py-1 text-[11px] text-slate-200">
                        Pastikan suasana kelas terlihat jelas
                    </span>
                </div>

                <!-- Error message box -->
                <div id="classroom-webcam-error" class="hidden absolute inset-4 rounded-xl bg-rose-900/90 p-4 text-xs text-white flex flex-col items-center justify-center text-center">
                    <p class="font-bold text-sm mb-1">Gagal Mengakses Kamera</p>
                    <p id="classroom-webcam-error-text">Pastikan Anda telah memberikan izin akses kamera atau gawai Anda memiliki webcam aktif.</p>
                </div>
            </div>

            <!-- Modal Footer Controls -->
            <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-5 py-3">
                <button type="button" id="btn-cancel-webcam" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="button" id="btn-capture-classroom" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-emerald-700 active:scale-95 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Jepret Foto Sekarang</span>
                </button>
                <div class="w-12"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const scheduleSelect = document.getElementById('journal-schedule-select');
        const dateInput = document.getElementById('journal-date-input');
        const currentDayBadge = document.getElementById('journal-selected-day');
        const noSchedulesHint = document.getElementById('journal-no-schedules-hint');
        const noSchedulesDayName = document.getElementById('no-schedules-day-name');
        const editScheduleId = @json($editJournal->schedule_id ?? null);

        const indonesianDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        function getIndonesianDayName(dateString) {
            if (!dateString) return '';
            const parts = dateString.split('-');
            if (parts.length === 3) {
                const year = parseInt(parts[0], 10);
                const month = parseInt(parts[1], 10) - 1;
                const day = parseInt(parts[2], 10);
                const d = new Date(year, month, day);
                return indonesianDays[d.getDay()] || '';
            }
            const d = new Date(dateString);
            return indonesianDays[d.getDay()] || '';
        }

        function filterSchedulesByDay() {
            if (!scheduleSelect || !dateInput) return;
            const dayName = getIndonesianDayName(dateInput.value);
            if (currentDayBadge) {
                currentDayBadge.textContent = dayName || '-';
            }

            let matchingCount = 0;
            const currentSelectedVal = scheduleSelect.value;
            let currentSelectedStillValid = false;

            Array.from(scheduleSelect.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const optionDay = (option.getAttribute('data-day') || '').trim().toLowerCase();
                const targetDay = dayName.trim().toLowerCase();
                const isEditOriginal = editScheduleId && String(option.value) === String(editScheduleId);
                const isMatch = (optionDay === targetDay) || isEditOriginal;

                if (isMatch) {
                    option.hidden = false;
                    option.disabled = false;
                    matchingCount++;
                    if (option.value === currentSelectedVal) {
                        currentSelectedStillValid = true;
                    }
                } else {
                    option.hidden = true;
                    option.disabled = true;
                }
            });

            if (currentSelectedVal && !currentSelectedStillValid && (!editScheduleId || String(currentSelectedVal) !== String(editScheduleId))) {
                scheduleSelect.value = '';
                if (typeof renderAttendance === 'function') {
                    renderAttendance();
                }
            }

            if (noSchedulesHint && noSchedulesDayName) {
                if (matchingCount === 0 && dayName) {
                    noSchedulesDayName.textContent = dayName;
                    noSchedulesHint.classList.remove('hidden');
                } else {
                    noSchedulesHint.classList.add('hidden');
                }
            }
        }

        if (dateInput) {
            dateInput.addEventListener('change', () => {
                filterSchedulesByDay();
                if (typeof renderAttendance === 'function') {
                    renderAttendance();
                }
            });
        }

        // Attendance handling (for users with attendance permission)
        const studentsBySchedule = @json($studentsBySchedule ?? []);
        const savedAttendance = @json($existingAttendance ?? []);
        const oldStudentIds = @json(old('student_ids', []));
        const oldStatuses = @json(old('statuses', []));
        const approvedLeaves = @json($approvedLeaves ?? []);
        const attendancePanel = document.getElementById('journal-attendance');
        const attendanceRows = document.getElementById('journal-attendance-rows');
        const attendanceEmpty = document.getElementById('journal-attendance-empty');
        const leaveAlert = document.getElementById('journal-attendance-leave-alert');

        const getActiveLeave = (studentId, dateVal) => {
            if (!dateVal || !approvedLeaves) return null;
            return approvedLeaves.find(leave => {
                if (String(leave.student_id) !== String(studentId)) return false;
                const start = leave.start_date || '';
                const end = leave.end_date || start;
                if (!start && !end) return false;
                if (start && end) {
                    return dateVal >= start && dateVal <= end;
                }
                return dateVal === start || dateVal === end;
            });
        };

        const renderAttendance = () => {
            if (!attendancePanel || !attendanceRows) return;
            attendanceRows.replaceChildren();
            const scheduleId = scheduleSelect.value;
            if (!scheduleId) {
                attendancePanel.hidden = true;
                if (leaveAlert) leaveAlert.classList.add('hidden');
                return;
            }
            attendancePanel.hidden = false;
            const students = (studentsBySchedule && studentsBySchedule[scheduleId]) ? studentsBySchedule[scheduleId] : [];
            if (attendanceEmpty) attendanceEmpty.hidden = students.length > 0;

            const selectedDate = (dateInput && dateInput.value) ? dateInput.value : '';
            let leavesFoundCount = 0;

            students.forEach((student, index) => {
                const activeLeave = getActiveLeave(student.id, selectedDate);
                if (activeLeave) {
                    leavesFoundCount++;
                }

                const row = document.createElement('tr');
                if (activeLeave) {
                    row.className = 'bg-amber-50/40 hover:bg-amber-50/70 transition';
                }

                const nameCell = document.createElement('td');
                nameCell.className = 'px-3 py-3 font-medium text-slate-700';

                const nameWrap = document.createElement('div');
                nameWrap.className = 'flex flex-wrap items-center gap-2';

                const nameText = document.createElement('span');
                nameText.textContent = student.name;
                nameWrap.append(nameText);

                if (activeLeave) {
                    const badge = document.createElement('span');
                    badge.className = 'inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 border border-amber-300 shadow-2xs';
                    const leaveTypeLabel = activeLeave.leave_type || 'Izin';
                    badge.title = activeLeave.reason ? `Alasan: ${activeLeave.reason}` : `${leaveTypeLabel} telah disetujui`;
                    badge.textContent = `${leaveTypeLabel} Disetujui${activeLeave.reason ? ' (' + activeLeave.reason + ')' : ''}`;
                    nameWrap.append(badge);

                    if (activeLeave.document_path) {
                        const link = document.createElement('a');
                        link.href = `${@json(asset('storage'))}/${activeLeave.document_path.split('/').map(encodeURIComponent).join('/')}`;
                        link.target = '_blank';
                        link.rel = 'noopener';
                        link.className = 'text-[11px] font-semibold text-emerald-700 underline hover:text-emerald-800';
                        link.textContent = 'Lihat Bukti Foto';
                        nameWrap.append(link);
                    }
                }
                nameCell.append(nameWrap);

                const statusCell = document.createElement('td');
                statusCell.className = 'px-3 py-3';
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'student_ids[]';
                idInput.value = student.id;

                const statusSelect = document.createElement('select');
                statusSelect.name = 'statuses[]';
                statusSelect.className = activeLeave
                    ? 'rounded-lg border border-amber-400 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-200'
                    : 'rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm';

                const oldStudentIndex = oldStudentIds.map(String).indexOf(String(student.id));

                // Jika ada surat izin yang disetujui untuk tanggal ini, sesuaikan default ke 'Sakit' atau 'Izin'
                const fallbackStatus = activeLeave ? (activeLeave.leave_type === 'Sakit' ? 'Sakit' : 'Izin') : 'Hadir';

                const preferredStatus = oldStudentIds.length
                    ? oldStatuses[oldStudentIndex]
                    : (savedAttendance[student.id] || fallbackStatus);

                ['Hadir', 'Sakit', 'Izin', 'Alpha'].forEach((status) => {
                    const option = new Option(status, status);
                    option.selected = status === preferredStatus;
                    statusSelect.add(option);
                });

                statusCell.append(idInput, statusSelect);

                if (activeLeave) {
                    const hint = document.createElement('p');
                    hint.className = 'mt-1 text-[11px] text-amber-700 font-medium';
                    hint.textContent = 'Otomatis diatur Izin karena pengajuan izin telah disetujui.';
                    statusCell.append(hint);
                }

                row.append(nameCell, statusCell);
                attendanceRows.append(row);
            });

            if (leaveAlert) {
                if (leavesFoundCount > 0) {
                    leaveAlert.innerHTML = `<strong>ℹ️ Pemberitahuan Izin Siswa:</strong> Terdapat <strong>${leavesFoundCount} siswa</strong> di kelas ini yang memiliki pengajuan izin disetujui untuk tanggal <strong>${selectedDate}</strong>. Status kehadiran siswa tersebut otomatis diatur ke <strong>Izin</strong>.`;
                    leaveAlert.classList.remove('hidden');
                } else {
                    leaveAlert.classList.add('hidden');
                }
            }
        };

        if (scheduleSelect) {
            scheduleSelect.addEventListener('change', renderAttendance);
        }

        // Filtering Riwayat Jurnal
        const filterStartDate = document.getElementById('filter-start-date');
        const filterEndDate = document.getElementById('filter-end-date');
        const filterSearchText = document.getElementById('filter-search-text');
        const journalRows = document.querySelectorAll('.journal-row');
        const noMatchingJournals = document.getElementById('no-matching-journals');
        const journalVisibleCount = document.getElementById('journal-visible-count');
        const activeFilterSummary = document.getElementById('active-filter-summary');

        function applyJournalClientFilter() {
            if (!journalRows.length) return;
            const start = filterStartDate ? filterStartDate.value : '';
            const end = filterEndDate ? filterEndDate.value : '';
            const query = filterSearchText ? filterSearchText.value.toLowerCase().trim() : '';

            let visibleCount = 0;
            journalRows.forEach(row => {
                const date = row.getAttribute('data-date') || '';
                const searchContent = row.getAttribute('data-search') || '';

                let matchDate = true;
                if (start && date < start) matchDate = false;
                if (end && date > end) matchDate = false;

                let matchText = true;
                if (query && !searchContent.includes(query)) matchText = false;

                if (matchDate && matchText) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            if (noMatchingJournals) {
                if (visibleCount === 0) {
                    noMatchingJournals.classList.remove('hidden');
                } else {
                    noMatchingJournals.classList.add('hidden');
                }
            }

            if (journalVisibleCount) {
                journalVisibleCount.textContent = visibleCount;
            }

            if (activeFilterSummary) {
                if (start || end || query) {
                    let desc = [];
                    if (start && end) desc.push(`${start} s/d ${end}`);
                    else if (start) desc.push(`≥ ${start}`);
                    else if (end) desc.push(`≤ ${end}`);
                    if (query) desc.push(`"${query}"`);
                    activeFilterSummary.textContent = `Filter: ${desc.join(' • ')}`;
                } else {
                    activeFilterSummary.textContent = '';
                }
            }
        }

        function setJournalPreset(type) {
            const today = new Date();
            const formatIso = d => {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            if (type === 'today') {
                const todayStr = formatIso(today);
                if (filterStartDate) filterStartDate.value = todayStr;
                if (filterEndDate) filterEndDate.value = todayStr;
            } else if (type === 'yesterday') {
                const yesterday = new Date();
                yesterday.setDate(today.getDate() - 1);
                const yestStr = formatIso(yesterday);
                if (filterStartDate) filterStartDate.value = yestStr;
                if (filterEndDate) filterEndDate.value = yestStr;
            } else if (type === '7days') {
                const prev = new Date();
                prev.setDate(today.getDate() - 6);
                if (filterStartDate) filterStartDate.value = formatIso(prev);
                if (filterEndDate) filterEndDate.value = formatIso(today);
            } else if (type === 'this_month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                if (filterStartDate) filterStartDate.value = formatIso(firstDay);
                if (filterEndDate) filterEndDate.value = formatIso(lastDay);
            } else if (type === 'last_month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
                if (filterStartDate) filterStartDate.value = formatIso(firstDay);
                if (filterEndDate) filterEndDate.value = formatIso(lastDay);
            } else if (type === 'all') {
                if (filterStartDate) filterStartDate.value = '';
                if (filterEndDate) filterEndDate.value = '';
                if (filterSearchText) filterSearchText.value = '';
            }

            applyJournalClientFilter();
        }

        function resetJournalDateFilter() {
            if (window.location.search.includes('start_date') || window.location.search.includes('end_date')) {
                window.location.href = "{{ route('guru.jurnal') }}";
            } else {
                setJournalPreset('all');
            }
        }

        if (filterStartDate) filterStartDate.addEventListener('change', applyJournalClientFilter);
        if (filterEndDate) filterEndDate.addEventListener('change', applyJournalClientFilter);
        if (filterSearchText) filterSearchText.addEventListener('input', applyJournalClientFilter);

        // ================= WEBCAM CONTROLS (TC-JRN-02) =================
        const webcamModal = document.getElementById('classroom-webcam-modal');
        const btnOpenWebcam = document.getElementById('btn-open-classroom-webcam');
        const btnCloseWebcam = document.getElementById('btn-close-webcam-modal');
        const btnCancelWebcam = document.getElementById('btn-cancel-webcam');
        const btnCaptureWebcam = document.getElementById('btn-capture-classroom');
        const btnSwitchCamera = document.getElementById('btn-switch-camera');
        const btnRetakeWebcam = document.getElementById('btn-retake-webcam');
        const btnRemoveWebcam = document.getElementById('btn-remove-webcam');
        const classroomVideo = document.getElementById('classroom-video');
        const classroomCanvas = document.getElementById('classroom-canvas');
        const classroomFlash = document.getElementById('classroom-flash');
        const webcamErrorBox = document.getElementById('classroom-webcam-error');
        const webcamErrorText = document.getElementById('classroom-webcam-error-text');
        const webcamCapturedPreview = document.getElementById('webcam-captured-preview');
        const webcamPreviewImg = document.getElementById('webcam-preview-img');
        const webcamFileInput = document.getElementById('journal-photo-input');

        let webcamStream = null;
        let currentFacingMode = 'environment';

        async function startWebcam() {
            if (webcamErrorBox) webcamErrorBox.classList.add('hidden');
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (webcamErrorBox) {
                    webcamErrorBox.classList.remove('hidden');
                    webcamErrorText.textContent = 'Peramban Anda tidak mendukung akses webcam/kamera secara langsung.';
                }
                return;
            }

            try {
                if (webcamStream) {
                    webcamStream.getTracks().forEach(t => t.stop());
                }
                const constraints = {
                    video: {
                        facingMode: currentFacingMode,
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };
                try {
                    webcamStream = await navigator.mediaDevices.getUserMedia(constraints);
                } catch (fallbackErr) {
                    webcamStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                }
                classroomVideo.srcObject = webcamStream;
                await classroomVideo.play();
            } catch (err) {
                console.error('Webcam error:', err);
                if (webcamErrorBox) {
                    webcamErrorBox.classList.remove('hidden');
                    webcamErrorText.textContent = 'Tidak dapat mengakses kamera: ' + (err.message || 'Izin kamera ditolak atau kamera sedang digunakan aplikasi lain.');
                }
            }
        }

        function stopWebcam() {
            if (webcamStream) {
                webcamStream.getTracks().forEach(t => t.stop());
                webcamStream = null;
            }
            if (classroomVideo) {
                classroomVideo.srcObject = null;
            }
        }

        function openWebcamModal() {
            if (webcamModal) {
                webcamModal.classList.remove('hidden');
                startWebcam();
            }
        }

        function closeWebcamModal() {
            stopWebcam();
            if (webcamModal) {
                webcamModal.classList.add('hidden');
            }
        }

        if (btnOpenWebcam) btnOpenWebcam.addEventListener('click', openWebcamModal);
        if (btnCloseWebcam) btnCloseWebcam.addEventListener('click', closeWebcamModal);
        if (btnCancelWebcam) btnCancelWebcam.addEventListener('click', closeWebcamModal);
        if (btnRetakeWebcam) btnRetakeWebcam.addEventListener('click', openWebcamModal);

        if (btnSwitchCamera) {
            btnSwitchCamera.addEventListener('click', () => {
                currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
                startWebcam();
            });
        }

        if (btnRemoveWebcam) {
            btnRemoveWebcam.addEventListener('click', () => {
                if (webcamCapturedPreview) webcamCapturedPreview.classList.add('hidden');
                if (webcamPreviewImg) webcamPreviewImg.src = '';
                if (webcamFileInput) webcamFileInput.value = '';
            });
        }

        if (btnCaptureWebcam) {
            btnCaptureWebcam.addEventListener('click', () => {
                if (!classroomVideo || !classroomVideo.videoWidth) {
                    alert('Kamera belum siap mengambil gambar. Silakan tunggu sebentar.');
                    return;
                }

                if (classroomFlash) {
                    classroomFlash.classList.remove('opacity-0');
                    setTimeout(() => classroomFlash.classList.add('opacity-0'), 150);
                }

                const width = classroomVideo.videoWidth;
                const height = classroomVideo.videoHeight;
                classroomCanvas.width = width;
                classroomCanvas.height = height;

                const ctx = classroomCanvas.getContext('2d');
                ctx.drawImage(classroomVideo, 0, 0, width, height);

                const now = new Date();
                const timeString = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' +
                                   now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
                ctx.fillStyle = 'rgba(0, 0, 0, 0.55)';
                ctx.fillRect(16, height - 48, 360, 36);
                ctx.font = 'bold 15px sans-serif';
                ctx.fillStyle = '#ffffff';
                ctx.fillText('📷 Dokumentasi KBM • ' + timeString, 26, height - 25);

                classroomCanvas.toBlob((blob) => {
                    if (!blob) return;
                    const fileName = 'webcam_kbm_' + Date.now() + '.jpg';
                    const file = new File([blob], fileName, { type: 'image/jpeg' });

                    if (window.DataTransfer && webcamFileInput) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        webcamFileInput.files = dataTransfer.files;
                    }

                    if (webcamPreviewImg) {
                        webcamPreviewImg.src = URL.createObjectURL(blob);
                    }
                    const infoEl = document.getElementById('webcam-file-info');
                    if (infoEl) {
                        infoEl.textContent = fileName + ' (' + (blob.size / 1024).toFixed(1) + ' KB)';
                    }
                    if (webcamCapturedPreview) {
                        webcamCapturedPreview.classList.remove('hidden');
                    }

                    closeWebcamModal();
                }, 'image/jpeg', 0.88);
            });
        }

        // Initial execution
        filterSchedulesByDay();
        renderAttendance();
        applyJournalClientFilter();
    </script>
@endpush
