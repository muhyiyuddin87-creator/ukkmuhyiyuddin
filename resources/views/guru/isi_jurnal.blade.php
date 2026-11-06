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
            <section class="rounded-2xl bg-white p-6 shadow-sm mb-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Jurnal Tersimpan</h3>
                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[700px] text-left text-sm">
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
                        <tbody class="divide-y divide-slate-100">
                            @forelse($journals as $j)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $j->date }}</td>
                                <td class="px-4 py-3 font-medium {{ $roleId === 4 ? 'text-amber-700' : 'text-indigo-700' }}">{{ $j->subject ?? 'Jadwal #' . $j->schedule_id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $j->topic_description }}</td>
                                <td class="px-4 py-3">
                                    @if($j->photo_path)
                                        <a href="{{ asset('storage/' . $j->photo_path) }}" target="_blank" rel="noopener" class="inline-flex flex-col items-start gap-1 text-xs font-semibold {{ $roleId === 4 ? 'text-amber-700' : 'text-indigo-700' }} hover:underline">
                                            <img src="{{ asset('storage/' . $j->photo_path) }}" alt="Bukti foto jurnal tanggal {{ $j->date }}" class="h-12 w-16 rounded object-cover">
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
                            <tr>
                                <td colspan="{{ (!empty($canTakeAttendance) || ($roleId === 2 && ($isWali ?? false))) ? 7 : 6 }}" class="px-4 py-6 text-center text-slate-400 italic">Belum ada jurnal tersimpan.</td>
                            </tr>
                            @endforelse
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
                            <input type="date" id="journal-date-input" name="date" value="{{ old('date', $editJournal->date ?? date('Y-m-d')) }}" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm {{ $roleId === 4 ? 'focus:border-amber-500 focus:ring-amber-100' : 'focus:border-indigo-500 focus:ring-indigo-100' }} focus:ring-2 outline-none transition" required>
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
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Bukti Foto (opsional, maks. 5 MB)</label>
                            <input type="file" name="photo" accept="image/*" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm file:mr-3 file:h-7 file:rounded-md file:border-0 {{ $roleId === 4 ? 'file:bg-amber-100 file:text-amber-700' : 'file:bg-indigo-100 file:text-indigo-700' }} file:font-semibold file:px-2.5 file:text-xs focus:outline-none">
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
                    badge.title = activeLeave.reason ? `Alasan: ${activeLeave.reason}` : 'Surat izin telah disetujui';
                    badge.textContent = `Izin Disetujui${activeLeave.reason ? ' (' + activeLeave.reason + ')' : ''}`;
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

                // Jika ada surat izin yang disetujui untuk tanggal ini, default ke 'Izin'
                const fallbackStatus = activeLeave ? 'Izin' : 'Hadir';

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

        // Initial execution
        filterSchedulesByDay();
        renderAttendance();
    </script>
@endpush
