@extends('layouts.waka')

@section('title', 'Data Guru - Waka Kurikulum')

@section('content')
    <header class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Waka Kurikulum</p>
        <h2 class="mt-2 text-3xl font-bold text-slate-800">Data Guru</h2>
        <p class="mt-1 text-slate-500 text-sm">Tambah, cari, ubah, dan hapus data guru.</p>
    </header>

    @if(session('success'))
        <div class="mb-5 rounded-xl bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-5 rounded-xl bg-rose-100 px-4 py-3 text-sm text-rose-800">
            <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <!-- Impor Guru dari Excel & PDF -->
    <section class="mb-6 rounded-2xl border border-sky-200 bg-white p-6 shadow-sm overflow-hidden">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex-1 min-w-0 pr-0 xl:pr-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 text-sky-700 shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </span>
                    <h3 class="text-xl font-bold text-slate-800">Impor Guru dari Excel & PDF</h3>
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    Unggah file .xlsx, .xls, atau .pdf (mendukung format tabel standar maupun dokumen PDF Jadwal Guru FET). Kolom tabel: Nama Lengkap (wajib), Username, NIP, Nomor HP, Email Pemulihan, Password, Wali Kelas, dan Guru BK (opsional).
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm font-semibold">
                    <a href="{{ route('waka.guru.template') }}" class="text-sky-700 hover:underline">Unduh template kosong (.xlsx)</a>
                    <span class="text-slate-300">•</span>
                    <a href="{{ route('waka.guru.example') }}" class="text-sky-700 hover:underline">Unduh contoh berisi 5 guru (.xlsx)</a>
                </div>
            </div>
            <form method="POST" action="{{ route('waka.guru.import') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-2.5 shrink-0 max-w-full">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.pdf" required class="w-full sm:w-auto max-w-full sm:max-w-xs rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-slate-200 file:text-slate-700">
                <button type="submit" class="shrink-0 whitespace-nowrap rounded-lg bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 transition shadow-sm">Impor Data</button>
            </form>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Daftar Guru</h3>
                <p class="mt-1 text-sm text-slate-500">Menampilkan <span class="font-bold text-sky-700">{{ $gurus->count() }}</span> guru{{ $search !== '' ? ' untuk pencarian "' . $search . '"' : '' }}</p>
            </div>
            <form method="GET" action="{{ route('waka.guru') }}" class="flex gap-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, username, NIP, HP, mapel..." class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Cari</button>
                @if($search !== '')
                    <a href="{{ route('waka.guru') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Reset</a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto table-scroll-container">
            <form id="create-teacher" method="POST" action="{{ route('waka.guru.store') }}">@csrf</form>
            @foreach($gurus as $g)
                <form id="update-teacher-{{ $g->id }}" method="POST" action="{{ route('waka.guru.update', $g->id) }}">@csrf @method('PATCH')</form>
            @endforeach
            <table class="w-full min-w-[1950px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">#</th>
                        <th class="px-4 py-3">Nama Lengkap</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Mapel</th>
                        <th class="px-4 py-3">NIP</th>
                        <th class="px-4 py-3">Nomor HP</th>
                        <th class="px-4 py-3">Email Pemulihan</th>
                        <th class="px-4 py-3">Password</th>
                        <th class="px-4 py-3">Wali Kelas</th>
                        <th class="px-4 py-3">Guru BK (Kelas)</th>
                        <th class="px-4 py-3">Kelas (ID)</th>
                        <th class="px-4 py-3 rounded-r-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="bg-sky-50/70">
                        <td class="px-3 py-3 text-slate-400">+</td>
                        <td class="px-3 py-3"><input form="create-teacher" name="fullname" required maxlength="100" placeholder="Nama lengkap" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="create-teacher" name="username" required maxlength="50" placeholder="Username" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3 text-slate-400">Diatur melalui jadwal</td>
                        <td class="px-3 py-3"><input form="create-teacher" name="nip" maxlength="30" placeholder="NIP" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="create-teacher" type="tel" name="phone" maxlength="20" placeholder="Nomor HP" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="create-teacher" type="email" name="email" maxlength="255" placeholder="nama@email.com" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="create-teacher" type="password" name="password" required minlength="8" placeholder="Min. 8 karakter" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3">
                            <select form="create-teacher" name="class_ids[]" multiple class="h-24 w-full min-w-40 rounded-lg border border-slate-300 px-3 py-2 text-xs" aria-label="Penugasan wali kelas">
                                @foreach($schoolClasses as $schoolClass)
                                    <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-xs text-slate-500">Opsional, pilih kelas.</span>
                        </td>
                        <td class="px-3 py-3">
                            <select form="create-teacher" name="bk_class_ids[]" multiple class="h-24 w-full min-w-40 rounded-lg border border-slate-300 px-3 py-2 text-xs" aria-label="Penugasan guru BK">
                                @foreach($schoolClasses as $schoolClass)
                                    <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-xs text-slate-500">Opsional, kelas BK.</span>
                        </td>
                        <td class="px-3 py-3"><input form="create-teacher" type="number" name="class_id" min="1" placeholder="Opsional" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><button form="create-teacher" type="submit" class="whitespace-nowrap rounded-lg bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700">Tambah Guru</button></td>
                    </tr>
                    @forelse($gurus as $i => $g)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3.5 text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                        <td class="px-3 py-3"><input form="update-teacher-{{ $g->id }}" name="fullname" required maxlength="100" value="{{ $g->fullname }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3">
                            <input form="update-teacher-{{ $g->id }}" name="username" required maxlength="50" value="{{ $g->username }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                            @if((int)($g->role_id ?? 0) === 4)
                                <div class="mt-1">
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200">Kesiswaan</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-3 py-3">{{ $g->subjects !== '' ? $g->subjects : '-' }}</td>
                        <td class="px-3 py-3"><input form="update-teacher-{{ $g->id }}" name="nip" maxlength="30" value="{{ $g->nip }}" placeholder="NIP" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="update-teacher-{{ $g->id }}" type="tel" name="phone" maxlength="20" value="{{ $g->phone }}" placeholder="Nomor HP" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="update-teacher-{{ $g->id }}" type="email" name="email" maxlength="255" value="{{ $g->email }}" placeholder="nama@email.com" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="update-teacher-{{ $g->id }}" type="password" name="password" minlength="8" placeholder="Tidak diubah" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3">
                            @php
                                $assignedWaliIds = $teacherClassIds->get($g->id, []);
                                $assignedWaliNames = $schoolClasses->whereIn('id', $assignedWaliIds)->pluck('name');
                            @endphp
                            @if($assignedWaliNames->isNotEmpty())
                                <div class="mb-1.5 flex flex-wrap gap-1 max-w-xs">
                                    @foreach($assignedWaliNames as $cName)
                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 border border-indigo-200">
                                            {{ $cName }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <select form="update-teacher-{{ $g->id }}" name="class_ids[]" multiple class="h-20 w-full min-w-40 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs" aria-label="Penugasan wali kelas {{ $g->fullname }}">
                                @foreach($schoolClasses as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected(in_array((int) $schoolClass->id, $assignedWaliIds, true))>{{ $schoolClass->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-3">
                            @php
                                $assignedBkIds = $teacherBkClassIds->get($g->id, []);
                                $assignedBkNames = $schoolClasses->whereIn('id', $assignedBkIds)->pluck('name');
                            @endphp
                            @if($assignedBkNames->isNotEmpty())
                                <div class="mb-1.5 flex flex-wrap gap-1 max-w-xs">
                                    @foreach($assignedBkNames as $cName)
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-200">
                                            {{ $cName }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <select form="update-teacher-{{ $g->id }}" name="bk_class_ids[]" multiple class="h-20 w-full min-w-40 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs" aria-label="Penugasan guru BK {{ $g->fullname }}">
                                @foreach($schoolClasses as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected(in_array((int) $schoolClass->id, $assignedBkIds, true))>{{ $schoolClass->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-3"><input form="update-teacher-{{ $g->id }}" type="number" name="class_id" min="1" value="{{ $g->class_id }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3">
                            <div class="flex gap-2">
                                <button form="update-teacher-{{ $g->id }}" type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Simpan</button>
                                <form method="POST" action="{{ route('waka.guru.destroy', $g->id) }}" onsubmit="return confirm('Hapus akun guru {{ $g->fullname }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-200">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-4 py-8 text-center text-slate-400 italic">Belum ada data guru yang cocok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
