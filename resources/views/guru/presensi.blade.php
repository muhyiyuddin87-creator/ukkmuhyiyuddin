@extends('layouts.guru')

@section('title', 'Riwayat Absensi Siswa - School System')

@section('content')
            <header class="mb-6">
                <p class="text-sm uppercase tracking-widest text-emerald-600 font-semibold">Ruang kerja guru</p>
                <h2 class="mt-2 text-3xl font-bold">Riwayat Absensi Siswa</h2>
                <p class="mt-2 text-slate-500">Catat dan tinjau riwayat kehadiran siswa berdasarkan jurnal mengajar kelas Anda.</p>
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

            <!-- Presensi Tersimpan -->
            <section class="rounded-2xl bg-white p-6 shadow-sm mb-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Data Presensi Tersimpan</h3>
                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-emerald-50/50">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Siswa</th>
                                <th class="px-4 py-3">Mata Pelajaran / Jurnal</th>
                                <th class="px-4 py-3">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($presensiList as $p)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-slate-600">{{ $p->date }}</td>
                                <td class="px-4 py-3 font-bold text-slate-800">{{ $p->student_name ?? 'Siswa #' . $p->student_id }}</td>
                                <td class="px-4 py-3 font-medium text-indigo-700">{{ $p->subject ?? 'Jurnal' }}: {{ $p->topic_description ?? 'Jurnal #' . $p->journal_id }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $color = match($p->status) {
                                            'Hadir' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                            'Izin'  => 'bg-amber-100 text-amber-700 border-amber-200',
                                            'Sakit' => 'bg-sky-100 text-sky-700 border-sky-200',
                                            default => 'bg-rose-100 text-rose-700 border-rose-200',
                                        };
                                    @endphp
                                    <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $color }}">{{ $p->status }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-400 italic">Belum ada data presensi. Isi form di bawah untuk mulai mencatat kehadiran.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Form Input Presensi -->
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Input Presensi Siswa</h3>

                @if($journals->isEmpty())
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-amber-800 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Belum ada jurnal tersedia. Silakan isi jurnal terlebih dahulu melalui menu <a href="{{ route('guru.jurnal') }}" class="underline font-bold">Isi Jurnal</a>.</span>
                </div>
                @else
                <form method="POST" action="{{ route('guru.presensi.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Pilih Jurnal</label>
                            <select name="journal_id" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition" required>
                                <option value="">-- Pilih Jurnal --</option>
                                @foreach($journals as $j)
                                <option value="{{ $j->id }}" @selected(old('journal_id') == $j->id)>{{ $j->date }} - {{ $j->subject }} - {{ $j->topic_description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Tanggal</label>
                            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition" required>
                        </div>
                    </div>

                    <div class="overflow-x-auto table-scroll-container">
                        <table class="w-full min-w-[500px] text-left text-sm">
                            <thead class="border-b text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-3">Nama Siswa</th>
                                    <th class="px-3 py-3">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($siswas as $s)
                                <tr>
                                    <td class="px-3 py-3 font-medium">{{ $s->name }}</td>
                                    <td class="px-3 py-3">
                                        <input type="hidden" name="student_ids[]" value="{{ $s->id }}">
                                        <select name="statuses[]" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-emerald-500 outline-none">
                                            <option value="Hadir" @selected(old('statuses.' . $loop->index, 'Hadir') === 'Hadir')>Hadir</option>
                                            <option value="Sakit" @selected(old('statuses.' . $loop->index) === 'Sakit')>Sakit</option>
                                            <option value="Izin" @selected(old('statuses.' . $loop->index) === 'Izin')>Izin</option>
                                            <option value="Alpha" @selected(old('statuses.' . $loop->index) === 'Alpha')>Alpha</option>
                                        </select>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="px-3 py-4 text-center text-slate-400 italic">Belum ada data siswa.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(!$siswas->isEmpty())
                    <div class="mt-5 flex justify-end">
                        <button type="submit" class="h-10 inline-flex items-center justify-center rounded-lg bg-emerald-600 px-6 text-sm font-semibold text-white hover:bg-emerald-500 transition shadow-xs active:scale-95">
                            Simpan Presensi ke Database
                        </button>
                    </div>
                    @endif
                </form>
                @endif
            </section>
@endsection
