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
        <div class="overflow-hidden rounded-2xl bg-white shadow-xs border border-slate-700">
            <div class="overflow-x-auto table-scroll-container">
                <table class="w-full min-w-[950px] text-left text-xs border-collapse border border-slate-700">
                    <thead>
                        <tr class="bg-slate-100 text-slate-900 font-bold uppercase tracking-wider border-b border-slate-700">
                            <th class="border border-slate-700 px-3 py-3 text-center w-12">NO</th>
                            <th class="border border-slate-700 px-4 py-3 w-28">HARI</th>
                            <th class="border border-slate-700 px-4 py-3 w-40">WAKTU / JAM</th>
                            <th class="border border-slate-700 px-4 py-3">MATA PELAJARAN</th>
                            <th class="border border-slate-700 px-4 py-3">GURU PENGAMPU</th>
                            <th class="border border-slate-700 px-4 py-3 w-28 text-center">KELAS</th>
                            <th class="border border-slate-700 px-4 py-3 w-28 text-center">RUANGAN</th>
                            <th class="border border-slate-700 px-4 py-3 w-24 text-center">JENIS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($schedules as $index => $schedule)
                            @php
                                $periodLabel = \App\Support\SchoolTimetable::labelFor($schedule->day_of_week, $schedule->start_time, $schedule->end_time);
                            @endphp
                            <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }} hover:bg-slate-100/70 transition">
                                <td class="border border-slate-700 px-3 py-2.5 text-center font-bold text-slate-800">{{ $index + 1 }}</td>
                                <td class="border border-slate-700 px-4 py-2.5 font-bold text-slate-900 whitespace-nowrap">{{ $schedule->day_of_week }}</td>
                                <td class="border border-slate-700 px-4 py-2.5 text-slate-800 whitespace-nowrap">
                                    <div class="font-bold">{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }} WIB</div>
                                    @if($periodLabel)
                                        <div class="text-[10px] text-slate-500 font-medium">{{ $periodLabel }}</div>
                                    @endif
                                </td>
                                <td class="border border-slate-700 px-4 py-2.5 font-bold text-slate-900">{{ $schedule->subject }}</td>
                                <td class="border border-slate-700 px-4 py-2.5 font-medium text-slate-800">{{ $schedule->teacher_name ?? '-' }}</td>
                                <td class="border border-slate-700 px-4 py-2.5 text-center font-bold text-slate-900 whitespace-nowrap">{{ $schedule->class_name ?? '-' }}</td>
                                <td class="border border-slate-700 px-4 py-2.5 text-center text-slate-700 whitespace-nowrap">{{ $schedule->nama_ruangan ?? '-' }}</td>
                                <td class="border border-slate-700 px-4 py-2.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ ($schedule->cycle_type ?? '') === 'Jurusan' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ ($schedule->cycle_type ?? '') === 'Regular' ? 'Normada' : ($schedule->cycle_type ?? 'Normada') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border border-slate-700 px-4 py-8 text-center italic text-slate-400">
                                    Belum ada jadwal untuk kelas yang ditugaskan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                        <span class="mb-1.5 block">Tanggal</span>
                        <input type="date" name="date" value="{{ old('date', $editJournal->date ?? date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition">
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <span class="mb-1.5 block">Jadwal / Mata Pelajaran</span>
                        <select name="schedule_id" required class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition">
                            <option value="">Pilih jadwal</option>
                            @foreach($schedules as $schedule)
                                <option value="{{ $schedule->id }}" @selected(old('schedule_id', $editJournal->schedule_id ?? '') == $schedule->id)>{{ $schedule->class_name }} — {{ $schedule->day_of_week }} {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }} — {{ $schedule->subject }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 md:col-span-2">
                        <span class="mb-1.5 block">Materi / Kegiatan Pembelajaran</span>
                        <textarea name="topic_description" rows="4" required placeholder="Tuliskan materi dan kegiatan pembelajaran..." class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition">{{ old('topic_description', $editJournal->topic_description ?? '') }}</textarea>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <span class="mb-1.5 block">Bukti Foto (opsional, JPG/PNG/WEBP, maks. 5 MB)</span>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 file:mr-3 file:h-7 file:rounded-md file:border-0 file:bg-indigo-100 file:text-indigo-700 file:font-semibold file:px-2.5 file:text-xs">
                        @if($editJournal?->photo_path)
                            <span class="mt-2 block normal-case tracking-normal">Foto saat ini: <a href="{{ asset('storage/' . $editJournal->photo_path) }}" target="_blank" rel="noopener" class="text-sky-700 underline">Lihat foto</a>. Biarkan kosong agar tetap digunakan.</span>
                        @endif
                    </label>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    @if($editJournal)
                        <a href="{{ route('wali-kelas.jurnal') }}#journal-form" class="h-10 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition active:scale-95">Batal</a>
                    @endif
                    <button type="submit" class="h-10 inline-flex items-center justify-center rounded-lg bg-sky-700 px-6 text-sm font-semibold text-white hover:bg-sky-800 transition shadow-xs active:scale-95">{{ $editJournal ? 'Simpan Perubahan' : 'Simpan Jurnal' }}</button>
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
