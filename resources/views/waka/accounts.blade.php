@extends('layouts.waka')

@section('title', 'Kelola Akun & Role - Waka')

@section('content')
            <header class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Waka Kurikulum</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-800">Kelola Akun & Role</h2>
                <p class="mt-2 text-sm text-slate-500">Tambah akun, ubah data dan role, atau hapus akun yang tidak lagi digunakan.</p>
            </header>

            @if(session('success'))
                <div class="mb-5 rounded-xl bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('reset_info'))
                <div class="mb-5 rounded-2xl border-2 border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
                    <p class="font-bold text-base">{{ session('reset_info')['message'] }}</p>
                    <div class="mt-2 inline-flex items-center gap-2 rounded-xl bg-white border border-amber-300 px-3 py-1.5 font-mono text-sm text-slate-800 font-semibold shadow-sm">
                        <span>Sandi Sementara:</span>
                        <span class="text-amber-800 select-all">{{ session('reset_info')['temporary_password'] }}</span>
                    </div>
                    <p class="mt-2 text-xs text-amber-700">Pengguna wajib mengganti sandi sementara ini saat login pertama kali.</p>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-xl bg-rose-100 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Daftar Akun</h3>
                        <p class="mt-1 text-sm text-slate-500">Menampilkan <span class="font-bold text-sky-700">{{ method_exists($accounts, 'total') ? $accounts->total() : $accounts->count() }}</span> akun{{ ($search ?? '') !== '' ? ' untuk pencarian "' . $search . '"' : '' }}. Gunakan tombol Ubah untuk memperbarui data; sandi lama tidak ditampilkan.</p>
                    </div>
                    <form method="GET" action="{{ route('waka.accounts') }}" class="flex items-center gap-2">
                        <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama, username, siswa, kelas..." class="h-10 w-64 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none">
                        <button type="submit" class="h-10 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-700 transition inline-flex items-center justify-center shadow-sm">Cari</button>
                        @if(($search ?? '') !== '')
                            <a href="{{ route('waka.accounts') }}" class="h-10 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition inline-flex items-center justify-center">Reset</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <form id="create-account" method="POST" action="{{ route('waka.accounts.store') }}" autocomplete="off">@csrf</form>
                    @foreach($accounts as $account)
                        <form id="update-account-{{ $account->id }}" method="POST" action="{{ route('waka.accounts.update', $account->id) }}" autocomplete="off">@csrf @method('PATCH')</form>
                    @endforeach
                    @php
                        $classesById = $schoolClasses->keyBy('id');
                    @endphp
                    <table class="w-full min-w-[1780px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="rounded-l-lg px-4 py-3">Nama Lengkap</th>
                                <th class="px-4 py-3">Username</th>
                                <th class="px-4 py-3">Email Pemulihan</th>
                                <th class="px-4 py-3">Password</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Data Siswa</th>
                                <th class="px-4 py-3">Penugasan Wali Kelas</th>
                                <th class="px-4 py-3">Penugasan Guru BK</th>
                                <th class="px-4 py-3">Status Akun</th>
                                <th class="rounded-r-lg px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr class="bg-sky-50/70">
                                <td class="px-3 py-2.5"><input form="create-account" type="text" name="fullname" value="{{ old('fullname') }}" maxlength="100" required placeholder="Nama lengkap" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none"></td>
                                <td class="px-3 py-2.5"><input form="create-account" type="text" name="username" value="{{ old('username') }}" maxlength="50" required autocomplete="off" placeholder="Username" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none"></td>
                                <td class="px-3 py-2.5"><input form="create-account" type="email" name="email" value="{{ old('email') }}" maxlength="255" placeholder="nama@email.com" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none"></td>
                                <td class="px-3 py-2.5"><input form="create-account" type="password" name="password" minlength="8" required autocomplete="new-password" placeholder="Min. 8 karakter" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none"></td>
                                <td class="px-3 py-2.5">
                                    <select form="create-account" name="role_id" required data-account-role class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                        <option value="">Pilih role</option>
                                        @foreach([1 => 'Waka', 2 => 'Guru', 3 => 'Siswa', 4 => 'Kesiswaan', 6 => 'Orang Tua'] as $roleId => $roleName)
                                            <option value="{{ $roleId }}" @selected(old('role_id') == $roleId)>{{ $roleName }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="space-y-1">
                                        <input type="text" data-student-filter placeholder="🔍 Cari siswa..." disabled class="h-7 w-full rounded-md border border-slate-300 bg-white px-2 text-xs text-slate-700 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none disabled:bg-slate-100 disabled:text-slate-400">
                                        <select form="create-account" name="student_id" data-student-link required disabled class="h-9 w-full min-w-56 rounded-lg border border-slate-300 bg-white px-2.5 text-xs disabled:bg-slate-100 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                            <option value="">Pilih siswa</option>
                                            @foreach($siswas as $siswa)
                                                <option value="{{ $siswa->id }}" data-assigned-student="{{ $siswa->assigned_student_account_id ? '1' : '0' }}" @disabled($siswa->assigned_student_account_id && old('role_id') == 3)>{{ $siswa->name }} — {{ $siswa->kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <select form="create-account" name="class_ids[]" data-class-link multiple disabled class="h-20 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs disabled:bg-slate-100 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                        @foreach($schoolClasses as $schoolClass)
                                            <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-[11px] text-slate-500">Pilih kelas jika ditugaskan sebagai wali kelas.</p>
                                </td>
                                <td class="px-3 py-2.5">
                                    <select form="create-account" name="bk_class_ids[]" data-bk-class-link multiple disabled class="h-20 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs disabled:bg-slate-100 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                        @foreach($schoolClasses as $schoolClass)
                                            <option value="{{ $schoolClass->id }}">{{ $schoolClass->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-[11px] text-slate-500">Pilih kelas jika ditugaskan sebagai guru BK.</p>
                                </td>
                                <td class="px-3 py-2.5 text-slate-400 text-xs">Aktif</td>
                                <td class="px-3 py-2.5"><button form="create-account" type="submit" class="h-9 whitespace-nowrap rounded-lg bg-sky-600 px-4 text-xs font-semibold text-white transition hover:bg-sky-700 inline-flex items-center justify-center shadow-sm">Tambah Akun</button></td>
                            </tr>

                            @forelse($accounts as $account)
                                <tr class="align-middle hover:bg-slate-50 transition" data-account-row>
                                    <td class="px-3 py-2.5"><input form="update-account-{{ $account->id }}" data-editable readonly type="text" name="fullname" value="{{ $account->fullname }}" maxlength="100" required class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none read-only:bg-slate-100"></td>
                                    <td class="px-3 py-2.5"><input form="update-account-{{ $account->id }}" data-editable readonly type="text" name="username" value="{{ $account->username }}" maxlength="50" required autocomplete="off" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none read-only:bg-slate-100"></td>
                                    <td class="px-3 py-2.5"><input form="update-account-{{ $account->id }}" data-editable readonly type="email" name="email" value="{{ $account->email }}" maxlength="255" placeholder="nama@email.com" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none read-only:bg-slate-100"></td>
                                    <td class="px-3 py-2.5"><input form="update-account-{{ $account->id }}" data-editable disabled type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Tidak diubah" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none disabled:bg-slate-100"></td>
                                    <td class="px-3 py-2.5">
                                        <select form="update-account-{{ $account->id }}" name="role_id" required data-editable data-account-role disabled class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs disabled:bg-slate-100 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                            @foreach([1 => 'Waka', 2 => 'Guru', 3 => 'Siswa', 4 => 'Kesiswaan', 6 => 'Orang Tua'] as $roleId => $roleName)
                                                <option value="{{ $roleId }}" @selected((int) $account->role_id === $roleId || ((int) $account->role_id === 5 && $roleId === 2))>{{ $roleName }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <div class="space-y-1">
                                            <input type="text" data-student-filter placeholder="🔍 Cari siswa..." disabled class="h-7 w-full rounded-md border border-slate-300 bg-white px-2 text-xs text-slate-700 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none disabled:bg-slate-100 disabled:text-slate-400">
                                            <select form="update-account-{{ $account->id }}" name="student_id" data-editable data-student-link required disabled class="h-9 w-full min-w-56 rounded-lg border border-slate-300 bg-white px-2.5 text-xs disabled:bg-slate-100 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                                <option value="">Pilih siswa</option>
                                                @foreach($siswas as $siswa)
                                                    <option value="{{ $siswa->id }}" data-assigned-student="{{ $siswa->assigned_student_account_id && (int) $siswa->assigned_student_account_id !== (int) $account->id ? '1' : '0' }}" @selected((int) $account->student_id === (int) $siswa->id) @disabled($siswa->assigned_student_account_id && (int) $siswa->assigned_student_account_id !== (int) $account->id && (int) $account->role_id === 3)>{{ $siswa->name }} — {{ $siswa->kelas }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        @php
                                            $assignedWaliIds = $accountClassIds->get($account->id, []);
                                            $assignedWaliFlip = array_flip($assignedWaliIds);
                                            $assignedWaliNames = collect($assignedWaliIds)->map(fn ($id) => $classesById[$id]->name ?? null)->filter();
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
                                        <select form="update-account-{{ $account->id }}" name="class_ids[]" data-editable data-class-link multiple disabled class="h-20 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs disabled:bg-slate-100 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                            @foreach($schoolClasses as $schoolClass)
                                                <option value="{{ $schoolClass->id }}" @selected(isset($assignedWaliFlip[$schoolClass->id]))>{{ $schoolClass->name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-[11px] text-slate-500">Penugasan opsional untuk guru.</p>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        @php
                                            $assignedBkIds = $accountBkClassIds->get($account->id, []);
                                            $assignedBkFlip = array_flip($assignedBkIds);
                                            $assignedBkNames = collect($assignedBkIds)->map(fn ($id) => $classesById[$id]->name ?? null)->filter();
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
                                        <select form="update-account-{{ $account->id }}" name="bk_class_ids[]" data-editable data-bk-class-link multiple disabled class="h-20 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs disabled:bg-slate-100 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                            @foreach($schoolClasses as $schoolClass)
                                                <option value="{{ $schoolClass->id }}" @selected(isset($assignedBkFlip[$schoolClass->id]))>{{ $schoolClass->name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-[11px] text-slate-500">Penugasan BK opsional.</p>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <select form="update-account-{{ $account->id }}" name="is_active" data-editable disabled class="h-9 w-full rounded-lg border border-slate-300 bg-white px-2.5 text-xs disabled:bg-slate-100 focus:border-sky-500 focus:ring-1 focus:ring-sky-200 focus:outline-none">
                                            <option value="1" @selected((bool) $account->is_active)>Aktif</option>
                                            <option value="0" @selected(! (bool) $account->is_active)>Tidak Aktif</option>
                                        </select>
                                        @if(!empty($account->must_change_password))
                                            <span class="mt-1 inline-block rounded bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 border border-amber-200">
                                                Wajib Ganti Sandi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <div class="flex items-center gap-1.5">
                                            {{-- Tombol Awal: Hanya Ubah & Hapus --}}
                                            <button type="button" data-edit-button class="h-9 rounded-lg bg-sky-600 px-3 text-xs font-semibold text-white transition hover:bg-sky-700 inline-flex items-center justify-center">Ubah</button>
                                            @if((int) $account->id !== (int) $user->getKey())
                                                <form method="POST" action="{{ route('waka.accounts.destroy', $account->id) }}" onsubmit="return confirm('Yakin ingin menghapus akun {{ $account->username }}?')" class="inline" data-delete-form>
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="h-9 rounded-lg bg-rose-100 px-3 text-xs font-semibold text-rose-700 transition hover:bg-rose-200 inline-flex items-center justify-center">Hapus</button>
                                                </form>
                                            @else
                                                <span class="self-center text-xs text-slate-400" data-self-label>Akun Anda</span>
                                            @endif

                                            {{-- Tombol Saat Ubah: Hanya Simpan & Batal (Awalnya tersembunyi) --}}
                                            <button form="update-account-{{ $account->id }}" type="submit" data-save-button hidden style="display: none;" class="h-9 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white transition hover:bg-emerald-700 items-center justify-center">Simpan</button>
                                            <button type="button" data-cancel-button hidden style="display: none;" class="h-9 rounded-lg bg-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-300 items-center justify-center">Batal</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-4 py-8 text-center italic text-slate-400">Belum ada akun pengguna.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($accounts, 'hasPages') && $accounts->hasPages())
                    <div class="mt-4 px-2">
                        {{ $accounts->links() }}
                    </div>
                @endif
            </section>
@endsection

@push('scripts')
    <script>
        const updateStudentOptionsAvailability = (select, roleVal) => {
            if (!select) return;
            const isSiswa = roleVal === '3';
            Array.from(select.options).forEach((opt) => {
                if (!opt.value) return;
                if (isSiswa && opt.dataset.assignedStudent === '1') {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });
        };

        document.querySelectorAll('[data-account-row]').forEach((row) => {
            const editableFields = row.querySelectorAll('[data-editable]');
            const roleSelect = row.querySelector('[data-account-role]');
            const studentSelect = row.querySelector('[data-student-link]');
            const studentFilter = row.querySelector('[data-student-filter]');
            const classSelect = row.querySelector('[data-class-link]');
            const bkClassSelect = row.querySelector('[data-bk-class-link]');
            const editButton = row.querySelector('[data-edit-button]');
            const saveButton = row.querySelector('[data-save-button]');
            const cancelButton = row.querySelector('[data-cancel-button]');
            const deleteForm = row.querySelector('[data-delete-form]');
            const selfLabel = row.querySelector('[data-self-label]');

            // Snapshot nilai awal untuk reset saat tombol Batal diklik
            const snapshotInitialValues = () => {
                editableFields.forEach((field) => {
                    if (field.matches('select[multiple]')) {
                        field._initialSelected = Array.from(field.options).map(o => ({ value: o.value, selected: o.selected }));
                    } else {
                        field._initialValue = field.value;
                    }
                });
            };

            const restoreInitialValues = () => {
                editableFields.forEach((field) => {
                    if (field.matches('select[multiple]')) {
                        if (field._initialSelected) {
                            field._initialSelected.forEach(item => {
                                const opt = Array.from(field.options).find(o => o.value === item.value);
                                if (opt) opt.selected = item.selected;
                            });
                        }
                    } else if (field._initialValue !== undefined) {
                        field.value = field._initialValue;
                    }
                });
            };

            snapshotInitialValues();

            const setEditing = (isEditing) => {
                editableFields.forEach((field) => {
                    if (field.matches('select, input[type="password"]')) {
                        field.disabled = !isEditing;
                    } else {
                        field.readOnly = !isEditing;
                    }
                });

                roleSelect.disabled = !isEditing;
                const isStudentOrParent = roleSelect.value === '3' || roleSelect.value === '6';
                studentSelect.disabled = !isEditing || !isStudentOrParent;
                studentSelect.required = isStudentOrParent;
                if (studentFilter) {
                    studentFilter.disabled = !isEditing || !isStudentOrParent;
                }
                updateStudentOptionsAvailability(studentSelect, roleSelect.value);

                const isTeacherOrKesiswaan = roleSelect.value === '2' || roleSelect.value === '4';
                if (classSelect) {
                    classSelect.disabled = !isEditing || !isTeacherOrKesiswaan;
                    classSelect.required = false;
                }
                if (bkClassSelect) {
                    bkClassSelect.disabled = !isEditing || !isTeacherOrKesiswaan;
                    bkClassSelect.required = false;
                }

                if (isEditing) {
                    editButton.style.display = 'none';
                    if (deleteForm) deleteForm.style.display = 'none';
                    if (selfLabel) selfLabel.style.display = 'none';
                    saveButton.style.display = 'inline-flex';
                    cancelButton.style.display = 'inline-flex';
                } else {
                    editButton.style.display = 'inline-flex';
                    if (deleteForm) deleteForm.style.display = 'inline';
                    if (selfLabel) selfLabel.style.display = 'inline';
                    saveButton.style.display = 'none';
                    cancelButton.style.display = 'none';
                }
            };

            editButton.addEventListener('click', () => {
                snapshotInitialValues();
                setEditing(true);
            });
            cancelButton.addEventListener('click', () => {
                restoreInitialValues();
                setEditing(false);
            });
            roleSelect.addEventListener('change', () => {
                const isStudentOrParent = roleSelect.value === '3' || roleSelect.value === '6';
                studentSelect.disabled = roleSelect.disabled || !isStudentOrParent;
                studentSelect.required = isStudentOrParent;
                if (studentFilter) {
                    studentFilter.disabled = roleSelect.disabled || !isStudentOrParent;
                }
                updateStudentOptionsAvailability(studentSelect, roleSelect.value);

                const isTeacherOrKesiswaan = roleSelect.value === '2' || roleSelect.value === '4';
                if (classSelect) {
                    classSelect.disabled = roleSelect.disabled || !isTeacherOrKesiswaan;
                    classSelect.required = false;
                }
                if (bkClassSelect) {
                    bkClassSelect.disabled = roleSelect.disabled || !isTeacherOrKesiswaan;
                    bkClassSelect.required = false;
                }
            });
        });

        document.querySelectorAll('[data-account-role]').forEach((roleSelect) => {
            if (roleSelect.closest('[data-account-row]')) {
                return;
            }

            const formId = roleSelect.getAttribute('form');
            const studentSelect = formId
                ? document.querySelector(`[form="${formId}"][data-student-link]`)
                : roleSelect.closest('form')?.querySelector('[data-student-link]');
            const studentFilter = formId
                ? document.querySelector(`td:has([form="${formId}"][data-student-link]) [data-student-filter]`)
                : roleSelect.closest('tr')?.querySelector('[data-student-filter]');
            const classSelect = formId
                ? document.querySelector(`[form="${formId}"][data-class-link]`)
                : roleSelect.closest('form')?.querySelector('[data-class-link]');
            const bkClassSelect = formId
                ? document.querySelector(`[form="${formId}"][data-bk-class-link]`)
                : roleSelect.closest('form')?.querySelector('[data-bk-class-link]');

            if (!studentSelect || !classSelect) {
                return;
            }

            const syncRoleFields = () => {
                const isStudentOrParent = roleSelect.value === '3' || roleSelect.value === '6';
                studentSelect.disabled = !isStudentOrParent;
                studentSelect.required = isStudentOrParent;
                if (studentFilter) {
                    studentFilter.disabled = !isStudentOrParent;
                }
                updateStudentOptionsAvailability(studentSelect, roleSelect.value);

                const isTeacherOrKesiswaan = roleSelect.value === '2' || roleSelect.value === '4';
                if (classSelect) {
                    classSelect.disabled = !isTeacherOrKesiswaan;
                    classSelect.required = false;
                }
                if (bkClassSelect) {
                    bkClassSelect.disabled = !isTeacherOrKesiswaan;
                    bkClassSelect.required = false;
                }
            };

            roleSelect.addEventListener('change', syncRoleFields);
            syncRoleFields();
        });

        // Live search filter untuk opsi data siswa pada dropdown
        document.querySelectorAll('[data-student-filter]').forEach((filterInput) => {
            const container = filterInput.closest('td');
            const select = container?.querySelector('[data-student-link]');
            const row = filterInput.closest('tr');
            const roleSelect = row?.querySelector('[data-account-role]');
            if (!select) return;

            const allOptions = Array.from(select.options);

            const filterOptions = () => {
                const query = filterInput.value.toLowerCase().trim();
                const selectedVal = select.value;
                const roleVal = roleSelect ? roleSelect.value : '';

                select.innerHTML = '';
                allOptions.forEach((opt) => {
                    if (!opt.value || opt.value === selectedVal || opt.text.toLowerCase().includes(query)) {
                        const clone = opt.cloneNode(true);
                        if (roleVal === '3' && clone.dataset.assignedStudent === '1') {
                            clone.disabled = true;
                        } else if (roleVal === '6') {
                            clone.disabled = false;
                        }
                        select.appendChild(clone);
                    }
                });
                select.value = selectedVal;
            };

            filterInput.addEventListener('input', filterOptions);
            if (roleSelect) {
                roleSelect.addEventListener('change', filterOptions);
            }
        });
    </script>
@endpush
