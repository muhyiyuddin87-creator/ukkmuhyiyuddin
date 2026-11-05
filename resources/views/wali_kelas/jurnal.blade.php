@extends('wali_kelas.layout')

@section('title', 'Jadwal dan Jurnal Kelas')

@section('content')
    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Wali Kelas</p>
        <h2 class="mt-2 text-3xl font-bold text-slate-800">Jadwal & Jurnal</h2>
        <p class="mt-2 text-sm text-slate-500">Tahun ajaran {{ $academicYear }} · Jadwal pelajaran dan jurnal mengajar yang terhubung ke kelas Anda.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($classNames as $className)<span class="rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-800">{{ $className }}</span>@endforeach
        </div>
    </header>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-xl font-bold text-slate-800">Jadwal Kelas</h3>
        <div class="overflow-x-auto table-scroll-container">
        <table class="w-full min-w-[700px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Putaran</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3">Hari / Waktu</th><th class="px-4 py-3">Mata Pelajaran</th><th class="px-4 py-3">Guru</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($schedules as $schedule)
                    <tr><td class="px-4 py-3">{{ $schedule->cycle_number ?? '-' }}</td><td class="px-4 py-3">{{ $schedule->class_name ?? '-' }}</td><td class="px-4 py-3">{{ $schedule->day_of_week }} · {{ \App\Support\SchoolTimetable::labelFor($schedule->day_of_week, $schedule->start_time, $schedule->end_time) ?? 'Waktu tersimpan' }} · {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}</td><td class="px-4 py-3 font-semibold text-indigo-700">{{ $schedule->subject }}</td><td class="px-4 py-3">{{ $schedule->teacher_name ?? '-' }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center italic text-slate-400">Belum ada jadwal untuk kelas yang ditugaskan.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </section>
    <section id="journal-form" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-xl font-bold text-slate-800">{{ $editJournal ? 'Edit Jurnal Mengajar' : 'Input Jurnal Mengajar' }}</h3>
        @if($schedules->isEmpty())
            <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">Belum ada jadwal untuk kelas Anda. Hubungi Waka agar jadwal kelas ditambahkan terlebih dahulu.</p>
        @else
            <form method="POST" action="{{ $editJournal ? route('wali-kelas.jurnal.update', $editJournal->id) : route('wali-kelas.jurnal.store') }}" enctype="multipart/form-data" onsubmit="return confirm('{{ $editJournal ? 'Apakah Anda yakin ingin menyimpan perubahan jurnal ini?' : 'Apakah Anda yakin ingin menyimpan jurnal ini?' }}')">
                @csrf
                @if($editJournal) @method('PATCH') @endif
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Tanggal
                        <input type="date" name="date" value="{{ old('date', $editJournal->date ?? date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Jadwal / Mata Pelajaran
                        <select name="schedule_id" required class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">
                            <option value="">Pilih jadwal</option>
                            @foreach($schedules as $schedule)
                                <option value="{{ $schedule->id }}" @selected(old('schedule_id', $editJournal->schedule_id ?? '') == $schedule->id)>{{ $schedule->class_name }} — {{ $schedule->day_of_week }} {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }} — {{ $schedule->subject }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 md:col-span-2">
                        Materi / Kegiatan Pembelajaran
                        <textarea name="topic_description" rows="4" required placeholder="Tuliskan materi dan kegiatan pembelajaran..." class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">{{ old('topic_description', $editJournal->topic_description ?? '') }}</textarea>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Bukti Foto (opsional, JPG/PNG/WEBP, maks. 5 MB)
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                        @if($editJournal?->photo_path)
                            <span class="mt-2 block normal-case tracking-normal">Foto saat ini: <a href="{{ asset('storage/' . $editJournal->photo_path) }}" target="_blank" rel="noopener" class="text-sky-700 underline">Lihat foto</a>. Biarkan kosong agar tetap digunakan.</span>
                        @endif
                    </label>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    @if($editJournal)
                        <a href="{{ route('wali-kelas.jurnal') }}#journal-form" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600">Batal</a>
                    @endif
                    <button type="submit" class="rounded-xl bg-sky-700 px-6 py-2.5 text-sm font-semibold text-white hover:bg-sky-800">{{ $editJournal ? 'Simpan Perubahan' : 'Simpan Jurnal' }}</button>
                </div>
            </form>
        @endif
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-xl font-bold text-slate-800">Jurnal Mengajar</h3>
        <div class="overflow-x-auto table-scroll-container">
        <table class="w-full min-w-[800px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Kelas / Pelajaran</th><th class="px-4 py-3">Pengajar / Pembuat</th><th class="px-4 py-3">Materi / Kegiatan</th><th class="px-4 py-3">Foto</th><th class="px-4 py-3">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($journals as $journal)
                    <tr>
                        <td class="px-4 py-3">{{ $journal->date }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $journal->class_name }}<span class="block text-xs text-indigo-700">{{ $journal->subject }}</span></td>
                        <td class="px-4 py-3">{{ $journal->journal_author_name ?? $journal->teacher_name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-pre-line">{{ $journal->topic_description }}</td>
                        <td class="px-4 py-3">@if($journal->photo_path)<a href="{{ asset('storage/' . $journal->photo_path) }}" target="_blank" rel="noopener" class="font-semibold text-sky-700 hover:underline">Lihat foto</a>@else<span class="text-slate-400">-</span>@endif</td>
                        <td class="px-4 py-3">@if((int) $journal->created_by === (int) $user->getKey())<a href="{{ route('wali-kelas.jurnal', ['edit' => $journal->id]) }}#journal-form" class="rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-200">Edit</a>@else<span class="text-xs text-slate-400">-</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center italic text-slate-400">Belum ada jurnal untuk kelas yang ditugaskan.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </section>
@endsection
