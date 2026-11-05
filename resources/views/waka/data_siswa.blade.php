@extends('layouts.waka')

@section('title', 'Data Siswa - Waka Kurikulum')

@section('content')
    <header class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Waka Kurikulum</p>
        <h2 class="mt-2 text-3xl font-bold text-slate-800">Data Siswa</h2>
        <p class="mt-1 text-slate-500 text-sm">Tambah, cari, ubah, dan hapus data siswa.</p>
    </header>

    @if(session('success'))
        <div class="mb-5 rounded-xl bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-5 rounded-xl bg-rose-100 px-4 py-3 text-sm text-rose-800">
            <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <!-- Impor Siswa dari Excel -->
    <section class="mb-6 rounded-2xl border border-sky-200 bg-white p-6 shadow-sm overflow-hidden">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex-1 min-w-0 pr-0 xl:pr-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 text-sky-700 shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </span>
                    <h3 class="text-xl font-bold text-slate-800">Impor Siswa dari Excel</h3>
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    Unggah file .xlsx, .xls, atau .csv. Mendukung template standar (Nama, Kelas, Jurusan, Alamat) maupun format buku <strong>Daftar Hadir Siswa</strong>.
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm font-semibold">
                    <a href="{{ route('waka.siswa.template') }}" class="text-sky-700 hover:underline">Unduh template kosong</a>
                    <span class="text-slate-300">•</span>
                    <a href="{{ route('waka.siswa.example') }}" class="text-sky-700 hover:underline">Unduh contoh berisi 5 siswa</a>
                </div>
            </div>
            <form method="POST" action="{{ route('waka.siswa.import') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-2.5 shrink-0 max-w-full">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="w-full sm:w-auto max-w-full sm:max-w-xs rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-slate-200 file:text-slate-700">
                <button type="submit" class="shrink-0 whitespace-nowrap rounded-lg bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 transition shadow-sm">Impor Data</button>
            </form>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Daftar Siswa</h3>
                <p class="mt-1 text-sm text-slate-500">Menampilkan <span class="font-bold text-sky-700">{{ $siswas->count() }}</span> siswa{{ $search !== '' ? ' untuk pencarian "' . $search . '"' : '' }}</p>
            </div>
            <form method="GET" action="{{ route('waka.siswa') }}" class="flex flex-col sm:flex-row flex-wrap gap-2 w-full lg:w-auto">
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, NIS, nomor HP..." class="w-full sm:w-auto min-w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="jurusan" class="w-full sm:w-auto rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua jurusan</option>
                    @foreach(['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BR', 'BD', 'LPS'] as $jurusan)
                        <option value="{{ $jurusan }}" @selected($selectedJurusan === $jurusan)>{{ $jurusan }}</option>
                    @endforeach
                </select>
                <select name="tingkat" class="w-full sm:w-auto rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua tingkat</option>
                    @foreach(['X', 'XI', 'XII'] as $tingkat)
                        <option value="{{ $tingkat }}" @selected($selectedTingkat === $tingkat)>{{ $tingkat }}</option>
                    @endforeach
                </select>
                <select name="rombel" class="w-full sm:w-auto rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua rombel</option>
                    @foreach(['1', '2', '3', '4'] as $rombel)
                        <option value="{{ $rombel }}" @selected(($selectedRombel ?? '') === $rombel)>Rombel {{ $rombel }}</option>
                    @endforeach
                </select>
                <select name="kelas" class="w-full sm:w-auto rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua kelas</option>
                    @foreach($availableClasses ?? [] as $c)
                        <option value="{{ $c->kelas }}" @selected(($selectedKelas ?? '') === $c->kelas)>{{ $c->kelas }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-none rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Tampilkan</button>
                    @if($search !== '' || $selectedJurusan !== '' || $selectedTingkat !== '' || ($selectedRombel ?? '') !== '' || ($selectedKelas ?? '') !== '')
                        <a href="{{ route('waka.siswa') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 text-center">Reset</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="overflow-x-auto table-scroll-container">
            <datalist id="school-class-list">
                @foreach($schoolClasses ?? [] as $className)
                    <option value="{{ $className }}"></option>
                @endforeach
            </datalist>
            <form id="create-student" method="POST" action="{{ route('waka.siswa.store') }}">@csrf</form>
            @foreach($siswas as $s)
                <form id="update-student-{{ $s->id }}" method="POST" action="{{ route('waka.siswa.update', $s->id) }}">@csrf @method('PATCH')</form>
            @endforeach
            @php($jurusanOptions = ['RPL', 'TKJ', 'DKV', 'AKL', 'MP', 'BR', 'BD', 'LPS'])
            <table class="w-full min-w-[1250px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">#</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">NIS</th>
                        <th class="px-4 py-3">Nomor HP</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-4 py-3">Jurusan</th>
                        <th class="px-4 py-3">Alamat</th>
                        <th class="px-4 py-3 rounded-r-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="bg-sky-50/70">
                        <td class="px-3 py-3 text-slate-400">+</td>
                        <td class="px-3 py-3"><input form="create-student" name="name" required maxlength="255" placeholder="Nama siswa" value="{{ old('name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="create-student" name="nis" maxlength="30" placeholder="NIS" value="{{ old('nis') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="create-student" type="tel" name="phone" maxlength="20" placeholder="Nomor HP" value="{{ old('phone') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="create-student" name="kelas" list="school-class-list" required maxlength="255" placeholder="Kelas (misal: X RPL 1)" value="{{ old('kelas') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3">
                            <select form="create-student" name="jurusan" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                                <option value="">Pilih jurusan</option>
                                @foreach($jurusanOptions as $jurusan)
                                    <option value="{{ $jurusan }}" @selected(old('jurusan') === $jurusan)>{{ $jurusan }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-3"><input form="create-student" name="alamat" required maxlength="2000" placeholder="Alamat" value="{{ old('alamat') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><button form="create-student" type="submit" class="whitespace-nowrap rounded-lg bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700">Tambah Siswa</button></td>
                    </tr>
                    @forelse($siswas as $i => $s)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3.5 text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                        <td class="px-3 py-3"><input form="update-student-{{ $s->id }}" name="name" required maxlength="255" value="{{ $s->name }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="update-student-{{ $s->id }}" name="nis" maxlength="30" value="{{ $s->nis }}" placeholder="NIS" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="update-student-{{ $s->id }}" type="tel" name="phone" maxlength="20" value="{{ $s->phone }}" placeholder="Nomor HP" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3"><input form="update-student-{{ $s->id }}" name="kelas" list="school-class-list" required maxlength="255" value="{{ $s->kelas }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3">
                            <select form="update-student-{{ $s->id }}" name="jurusan" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                                @if(!in_array($s->jurusan, $jurusanOptions, true))
                                    <option value="{{ $s->jurusan }}" selected>{{ $s->jurusan }}</option>
                                @endif
                                @foreach($jurusanOptions as $jurusan)
                                    <option value="{{ $jurusan }}" @selected($s->jurusan === $jurusan)>{{ $jurusan }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-3"><input form="update-student-{{ $s->id }}" name="alamat" required maxlength="2000" value="{{ $s->alamat }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></td>
                        <td class="px-3 py-3">
                            <div class="flex gap-2">
                                <button form="update-student-{{ $s->id }}" type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Simpan</button>
                                <form method="POST" action="{{ route('waka.siswa.destroy', $s->id) }}" onsubmit="return confirm('Hapus data siswa {{ $s->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-200">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400 italic">Belum ada data siswa yang cocok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Hapus Siswa per Kelas (Diletakkan di Bawah Daftar Siswa) -->
    <section class="mt-6 rounded-2xl border border-rose-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </span>
            <div>
                <h3 class="text-xl font-bold text-slate-800">Hapus Siswa per Kelas</h3>
                <p class="text-xs text-rose-600 font-semibold">Bisa mencentang siswa yang dipertahankan (tinggal kelas / tidak lulus)</p>
            </div>
        </div>
        <p class="mt-2 text-sm text-slate-500">
            Pilih kelas untuk menghapus seluruh data siswa. Anda dapat mencentang siswa yang <strong>TIDAK INGIN DIHAPUS</strong> agar tetap tersimpan (misal untuk siswa tinggal kelas / tidak lulus).
        </p>

        <form method="POST" action="{{ route('waka.siswa.destroy_class') }}" id="delete-class-form" class="mt-4 space-y-3" onsubmit="return confirmDeleteClass(this)">
            @csrf
            @method('DELETE')
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <select name="kelas" id="select-delete-class" required onchange="renderStudentExclusionList(this.value)" class="w-full sm:w-auto min-w-72 rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:border-rose-500 focus:ring-rose-500">
                    <option value="">-- Pilih Kelas untuk Dihapus --</option>
                    @forelse($availableClasses ?? [] as $c)
                        <option value="{{ $c->kelas }}" @selected(old('kelas') === $c->kelas)>{{ $c->kelas }} ({{ $c->total }} siswa)</option>
                    @empty
                        <option value="" disabled>Belum ada data kelas</option>
                    @endforelse
                </select>
            </div>

            <!-- Container daftar siswa untuk pengecualian (tidak dihapus) -->
            <div id="student-exclusion-container" class="hidden rounded-xl border border-slate-200 bg-slate-50/70 p-4 transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Centang Siswa yang TIDAK Ingin Dihapus:</span>
                        <p class="text-[11px] text-slate-500">Centang nama siswa yang tinggal kelas / tidak lulus agar tetap tersimpan di kelas ini.</p>
                    </div>
                    <div class="flex items-center gap-1.5 self-start sm:self-auto">
                        <button type="button" onclick="setAllExclusions(false)" class="text-[11px] font-semibold text-rose-600 hover:underline px-2.5 py-1 rounded bg-rose-50 border border-rose-200">
                            Hapus Semua
                        </button>
                        <button type="button" onclick="setAllExclusions(true)" class="text-[11px] font-semibold text-emerald-700 hover:underline px-2.5 py-1 rounded bg-emerald-50 border border-emerald-200">
                            Simpan Semua
                        </button>
                    </div>
                </div>

                <!-- Filter pencarian nama siswa di dalam kelas -->
                <div class="mb-2.5">
                    <input type="text" id="exclusion-search" placeholder="Cari nama siswa di kelas ini..." oninput="filterExclusionList(this.value)" class="w-full sm:max-w-md rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs">
                </div>

                <!-- Daftar siswa scrollable -->
                <div id="student-checklist" class="max-h-60 overflow-y-auto space-y-1.5 pr-1 text-xs">
                    <!-- Dimuat otomatis via JS berdasarkan kelas yang dipilih -->
                </div>

                <!-- Live Summary Footer -->
                <div class="mt-3 pt-2.5 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 font-semibold text-rose-700">
                            <span id="count-to-delete">0</span> Akan Dihapus
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 font-semibold text-emerald-800">
                            <span id="count-to-keep">0</span> Dipertahankan
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="force" value="1" id="force_delete_class" class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500" @checked(old('force'))>
                <label for="force_delete_class" class="text-xs text-slate-600 select-none cursor-pointer">
                    Hapus juga data terkait (akun pengguna siswa, presensi, izin) untuk siswa yang dihapus
                </label>
            </div>

            <div>
                <button type="submit" id="btn-submit-delete-class" class="rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-rose-700 transition flex items-center justify-center gap-2" @disabled(empty($availableClasses) || $availableClasses->isEmpty())>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span id="btn-delete-label">Hapus Semua Siswa di Kelas Ini</span>
                </button>
            </div>
        </form>
    </section>

    <script>
        const oldExcludeIds = @json(array_map('intval', old('exclude_ids', [])));
        let currentFetchController = null;

        function renderStudentExclusionList(selectedClass) {
            const container = document.getElementById('student-exclusion-container');
            const list = document.getElementById('student-checklist');
            const searchInput = document.getElementById('exclusion-search');
            if (searchInput) searchInput.value = '';

            if (!selectedClass) {
                container.classList.add('hidden');
                list.innerHTML = '';
                updateExclusionSummary();
                return;
            }

            container.classList.remove('hidden');
            list.innerHTML = '<div class="p-3 text-center text-slate-400 italic">Memuat daftar siswa...</div>';

            if (currentFetchController) {
                currentFetchController.abort();
            }
            currentFetchController = new AbortController();

            fetch('{{ route('waka.siswa.class_students') }}?kelas=' + encodeURIComponent(selectedClass), {
                signal: currentFetchController.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(students => {
                if (!students || students.length === 0) {
                    list.innerHTML = '<div class="p-3 text-center text-slate-400 italic">Tidak ada data siswa pada kelas ini.</div>';
                    updateExclusionSummary();
                    return;
                }

                let html = '';
                students.forEach((s) => {
                    const sId = parseInt(s.id, 10);
                    const isChecked = oldExcludeIds.includes(sId);
                    html += `
                        <label class="student-exclusion-item flex items-center justify-between p-2 rounded-lg border transition cursor-pointer select-none ${isChecked ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-200 bg-white hover:bg-slate-50'}" data-name="${(s.name || '').toLowerCase()}" data-nis="${(s.nis || '').toLowerCase()}">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" name="exclude_ids[]" value="${s.id}" ${isChecked ? 'checked' : ''} onchange="onExclusionItemChange(this)" class="student-exclude-cb h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="font-medium text-slate-800 text-xs">${s.name}</span>
                                    <span class="text-[11px] text-slate-400 block font-mono">NIS: ${s.nis || '-'}</span>
                                </div>
                            </div>
                            <span class="exclusion-badge text-[10px] font-semibold px-2 py-0.5 rounded-full ${isChecked ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-50 text-rose-600 border border-rose-200'}">
                                ${isChecked ? 'Dipertahankan (Tinggal Kelas)' : 'Akan Dihapus'}
                            </span>
                        </label>
                    `;
                });

                list.innerHTML = html;
                updateExclusionSummary();
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    list.innerHTML = '<div class="p-3 text-center text-rose-500 text-xs">Gagal memuat daftar siswa.</div>';
                }
            });
        }

        function onExclusionItemChange(checkbox) {
            const parentLabel = checkbox.closest('.student-exclusion-item');
            const badge = parentLabel.querySelector('.exclusion-badge');
            if (checkbox.checked) {
                parentLabel.classList.remove('border-slate-200', 'bg-white', 'hover:bg-slate-50');
                parentLabel.classList.add('border-emerald-300', 'bg-emerald-50/50');
                badge.className = 'exclusion-badge text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300';
                badge.innerText = 'Dipertahankan (Tinggal Kelas)';
            } else {
                parentLabel.classList.remove('border-emerald-300', 'bg-emerald-50/50');
                parentLabel.classList.add('border-slate-200', 'bg-white', 'hover:bg-slate-50');
                badge.className = 'exclusion-badge text-[10px] font-semibold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200';
                badge.innerText = 'Akan Dihapus';
            }
            updateExclusionSummary();
        }

        function updateExclusionSummary() {
            const checkboxes = document.querySelectorAll('.student-exclude-cb');
            const total = checkboxes.length;
            let toKeep = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) toKeep++;
            });
            const toDelete = total - toKeep;

            const countDeleteEl = document.getElementById('count-to-delete');
            const countKeepEl = document.getElementById('count-to-keep');
            const btnSubmit = document.getElementById('btn-submit-delete-class');
            const btnLabel = document.getElementById('btn-delete-label');

            if (countDeleteEl) countDeleteEl.innerText = toDelete;
            if (countKeepEl) countKeepEl.innerText = toKeep;

            if (total === 0) {
                if (btnSubmit) btnSubmit.disabled = true;
                if (btnLabel) btnLabel.innerText = 'Hapus Siswa per Kelas';
                return;
            }

            if (toDelete === 0) {
                if (btnSubmit) btnSubmit.disabled = true;
                if (btnLabel) btnLabel.innerText = 'Semua Siswa Dipertahankan (Tidak Ada Dihapus)';
            } else if (toKeep > 0) {
                if (btnSubmit) btnSubmit.disabled = false;
                if (btnLabel) btnLabel.innerText = `Hapus ${toDelete} Siswa (${toKeep} Siswa Dipertahankan)`;
            } else {
                if (btnSubmit) btnSubmit.disabled = false;
                if (btnLabel) btnLabel.innerText = `Hapus Semua (${toDelete}) Siswa di Kelas Ini`;
            }
        }

        function setAllExclusions(shouldExclude) {
            const checkboxes = document.querySelectorAll('.student-exclude-cb');
            checkboxes.forEach(cb => {
                cb.checked = shouldExclude;
                onExclusionItemChange(cb);
            });
        }

        function filterExclusionList(keyword) {
            keyword = (keyword || '').toLowerCase().trim();
            const items = document.querySelectorAll('.student-exclusion-item');
            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                const nis = item.getAttribute('data-nis') || '';
                if (name.includes(keyword) || nis.includes(keyword)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        function confirmDeleteClass(form) {
            const select = form.querySelector('select[name="kelas"]');
            const selectedClass = select.value;
            if (!selectedClass) {
                alert('Silakan pilih kelas yang ingin dihapus terlebih dahulu.');
                return false;
            }

            const checkboxes = form.querySelectorAll('.student-exclude-cb');
            const total = checkboxes.length;
            let toKeepNames = [];
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    const label = cb.closest('.student-exclusion-item');
                    const nameEl = label.querySelector('.font-medium');
                    if (nameEl) toKeepNames.push(nameEl.innerText.trim());
                }
            });

            const toDeleteCount = total - toKeepNames.length;
            if (toDeleteCount <= 0) {
                alert('Semua siswa di kelas ini dicentang untuk dipertahankan. Tidak ada siswa yang akan dihapus.');
                return false;
            }

            const forceCheckbox = form.querySelector('input[name="force"]');
            let message = 'PERINGATAN: Tindakan ini TIDAK DAPAT DIBATALKAN!\n\n';
            message += `Kelas: ${selectedClass}\n`;
            message += `• Siswa yang AKAN DIHAPUS: ${toDeleteCount} siswa\n`;

            if (toKeepNames.length > 0) {
                const previewKept = toKeepNames.slice(0, 5).join(', ') + (toKeepNames.length > 5 ? ` (+${toKeepNames.length - 5} lainnya)` : '');
                message += `• Siswa yang DIPERTAHANKAN (tinggal kelas/tidak lulus): ${toKeepNames.length} siswa [${previewKept}]\n`;
            }

            if (forceCheckbox && forceCheckbox.checked) {
                message += '\nAkun pengguna, presensi, dan izin untuk siswa yang dihapus juga akan dibersihkan.';
            }

            message += '\n\nApakah Anda yakin ingin memproses penghapusan ini?';
            return confirm(message);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('select-delete-class');
            if (select && select.value) {
                renderStudentExclusionList(select.value);
            }
        });
    </script>
@endsection
