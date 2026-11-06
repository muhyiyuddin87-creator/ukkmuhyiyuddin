@extends('wali_kelas.layout')

@section('title', 'Laporan Presensi Kelas')

@section('content')
    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Wali Kelas</p>
        <h2 class="mt-2 text-3xl font-bold text-slate-800">Laporan Presensi</h2>
        <p class="mt-2 text-sm text-slate-500">Hanya data dari kelas yang ditugaskan kepada Anda.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($classNames as $className)<span class="rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-800">{{ $className }}</span>@endforeach
        </div>
    </header>
    <section id="input-presensi" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-2 text-xl font-bold text-slate-800">Input Presensi Siswa</h3>
        <p class="mb-5 text-sm text-slate-500">Pilih jurnal kelas yang ditugaskan kepada Anda, lalu tentukan status kehadiran setiap siswa.</p>
        @if($journals->isEmpty())
            <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">Belum ada jurnal untuk kelas Anda. Buat jurnal terlebih dahulu melalui menu Jadwal & Jurnal.</p>
        @elseif($siswas->isEmpty())
            <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">Belum ada siswa pada kelas yang ditugaskan kepada Anda.</p>
        @else
            <form method="POST" action="{{ route('wali-kelas.presensi.store') }}">
                @csrf
                <div class="mb-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <span class="mb-1.5 block">Pilih Jurnal</span>
                        <select id="attendance-journal" name="journal_id" required class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition">
                            <option value="">Pilih jurnal</option>
                            @foreach($journals as $journal)
                                <option value="{{ $journal->id }}" data-class="{{ $journal->class_name }}" @selected(old('journal_id', $journals->first()->id) == $journal->id)>{{ $journal->date }} · {{ $journal->class_name }} · {{ $journal->subject }} · {{ $journal->topic_description }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <span class="mb-1.5 block">Tanggal Presensi</span>
                        <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition">
                    </label>
                </div>
                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[500px] text-left text-sm">
                        <thead class="border-b text-xs uppercase tracking-wide text-slate-500">
                            <tr><th class="px-3 py-3">Nama Siswa</th><th class="px-3 py-3">Kelas</th><th class="px-3 py-3">Status Kehadiran</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($siswas as $student)
                                <tr data-student-class="{{ $student->kelas }}">
                                    <td class="px-3 py-3 font-medium">{{ $student->name }}</td>
                                    <td class="px-3 py-3">{{ $student->kelas }}</td>
                                    <td class="px-3 py-3">
                                        <input type="hidden" name="student_ids[]" value="{{ $student->id }}" disabled>
                                        <select name="statuses[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-indigo-500" disabled>
                                            <option value="Hadir">Hadir</option>
                                            <option value="Sakit">Sakit</option>
                                            <option value="Izin">Izin</option>
                                            <option value="Alpha">Alpha</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="h-10 inline-flex items-center justify-center rounded-lg bg-sky-700 px-6 text-sm font-semibold text-white hover:bg-sky-800 transition shadow-xs active:scale-95">Simpan Presensi</button>
                </div>
            </form>
            <script>
                const journalSelect = document.getElementById('attendance-journal');
                const updateStudentRows = () => {
                    const selectedClass = journalSelect.selectedOptions[0]?.dataset.class;
                    document.querySelectorAll('[data-student-class]').forEach((row) => {
                        const isSelectedClass = row.dataset.studentClass === selectedClass;
                        row.querySelectorAll('input, select').forEach((field) => {
                            field.disabled = !isSelectedClass;
                        });
                        row.hidden = !isSelectedClass;
                    });
                };
                journalSelect.addEventListener('change', updateStudentRows);
                updateStudentRows();
            </script>
        @endif
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="overflow-x-auto table-scroll-container">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Siswa</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3">Mata Pelajaran</th><th class="px-4 py-3">Jurnal</th><th class="px-4 py-3">Status</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($attendances as $attendance)
                    <tr>
                        <td class="px-4 py-3">{{ $attendance->date ?? '-' }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $attendance->student_name }}</td>
                        <td class="px-4 py-3">{{ $attendance->kelas }}</td>
                        <td class="px-4 py-3">{{ $attendance->subject ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $attendance->topic_description ?? '-' }}</td>
                        <td class="px-4 py-3 font-bold {{ $attendance->status === 'Hadir' ? 'text-emerald-700' : ($attendance->status === 'Alpha' ? 'text-rose-700' : 'text-amber-700') }}">{{ $attendance->status ?? 'Belum ada presensi' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center italic text-slate-400">Belum ada data presensi untuk kelas yang ditugaskan.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </section>
@endsection
