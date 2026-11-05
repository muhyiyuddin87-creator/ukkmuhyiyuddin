@extends('layouts.waka')

@section('title', 'Data Orang Tua - Waka Kurikulum')

@section('content')
    <header class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Waka Kurikulum</p>
        <h2 class="mt-2 text-3xl font-bold text-slate-800">Data Orang Tua</h2>
        <p class="mt-1 text-slate-500 text-sm">Kelola akun orang tua / wali murid siswa. Akun ini dapat masuk ke portal untuk memantau jadwal dan mengajukan izin siswa.</p>
    </header>

    @if(session('success'))
        <div class="mb-5 rounded-xl bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('reset_info'))
        <div class="mb-5 rounded-2xl border-2 border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
            <p class="font-bold text-base">{{ session('reset_info')['message'] }}</p>
            <div class="mt-2 inline-flex items-center gap-2 rounded-xl bg-white border border-amber-300 px-3 py-1.5 font-mono text-sm text-slate-800 font-semibold shadow-sm">
                <span>Sandi Baru:</span>
                <span class="text-amber-800 select-all">{{ session('reset_info')['temporary_password'] }}</span>
            </div>
            <p class="mt-2 text-xs text-amber-700">Berikan sandi ini kepada orang tua siswa. Pengguna dapat mengubahnya kapan saja melalui profil.</p>
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
                <h3 class="text-xl font-bold text-slate-800">Daftar Akun Orang Tua</h3>
                <p class="mt-1 text-sm text-slate-500">Menampilkan <span class="font-bold text-sky-700">{{ $ortus->count() }}</span> akun orang tua{{ $search !== '' ? ' untuk pencarian "' . $search . '"' : '' }}</p>
            </div>
            <form method="GET" action="{{ route('waka.ortu') }}" class="flex gap-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, username, siswa, HP..." class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Cari</button>
                @if($search !== '')
                    <a href="{{ route('waka.ortu') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto table-scroll-container">
            <form id="create-ortu" method="POST" action="{{ route('waka.ortu.store') }}" autocomplete="off">@csrf</form>
            @foreach($ortus as $o)
                <form id="update-ortu-{{ $o->id }}" method="POST" action="{{ route('waka.ortu.update', $o->id) }}" autocomplete="off">@csrf @method('PATCH')</form>
            @endforeach
            <table class="w-full min-w-[1550px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">#</th>
                        <th class="px-4 py-3">Nama Orang Tua</th>
                        <th class="px-4 py-3">Siswa (Putra / Putri)</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Nomor HP / WA</th>
                        <th class="px-4 py-3">Email Pemulihan</th>
                        <th class="px-4 py-3">Password</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 rounded-r-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Baris Tambah Baru -->
                    <tr class="bg-sky-50/70">
                        <td class="px-3 py-3 text-slate-400 font-bold text-center">+</td>
                        <td class="px-3 py-3">
                            <input form="create-ortu" name="fullname" required maxlength="100" placeholder="Nama orang tua/wali" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3">
                            <div class="space-y-1">
                                <input type="text" data-student-filter placeholder="🔍 Cari siswa..." class="w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none">
                                <select form="create-ortu" name="student_id" data-student-link required class="w-full min-w-56 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach($siswas as $siswa)
                                        <option value="{{ $siswa->id }}">{{ $siswa->name }} ({{ $siswa->kelas }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <input form="create-ortu" name="username" required maxlength="50" autocomplete="off" placeholder="Username login" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3">
                            <input form="create-ortu" type="tel" name="phone" maxlength="20" placeholder="08xxxxxxxx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3">
                            <input form="create-ortu" type="email" name="email" maxlength="255" placeholder="ortu@email.com" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3">
                            <input form="create-ortu" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Min. 8 karakter" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3 text-slate-500 text-xs">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                        </td>
                        <td class="px-3 py-3">
                            <button form="create-ortu" type="submit" class="whitespace-nowrap rounded-lg bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700 transition shadow-sm">Tambah Orang Tua</button>
                        </td>
                    </tr>

                    <!-- Baris Data Akun Orang Tua yang Ada -->
                    @forelse($ortus as $i => $o)
                    <tr class="hover:bg-slate-50 transition align-middle">
                        <td class="px-4 py-3.5 text-slate-400 font-mono text-xs text-center">{{ $i + 1 }}</td>
                        <td class="px-3 py-3">
                            <input form="update-ortu-{{ $o->id }}" name="fullname" required maxlength="100" value="{{ $o->fullname }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3">
                            <div class="space-y-1">
                                <input type="text" data-student-filter placeholder="🔍 Cari siswa..." class="w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none">
                                <select form="update-ortu-{{ $o->id }}" name="student_id" data-student-link required class="w-full min-w-56 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach($siswas as $siswa)
                                        <option value="{{ $siswa->id }}" @selected((int) $o->student_id === (int) $siswa->id)>{{ $siswa->name }} ({{ $siswa->kelas }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <input form="update-ortu-{{ $o->id }}" name="username" required maxlength="50" autocomplete="off" value="{{ $o->username }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3">
                            <input form="update-ortu-{{ $o->id }}" type="tel" name="phone" maxlength="20" value="{{ $o->phone }}" placeholder="Nomor HP" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3">
                            <input form="update-ortu-{{ $o->id }}" type="email" name="email" maxlength="255" value="{{ $o->email }}" placeholder="nama@email.com" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3">
                            <input form="update-ortu-{{ $o->id }}" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Tidak diubah" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </td>
                        <td class="px-3 py-3">
                            <select form="update-ortu-{{ $o->id }}" name="is_active" class="w-28 rounded-lg border border-slate-300 px-2 py-2 text-xs">
                                <option value="1" @selected((bool) $o->is_active)>Aktif</option>
                                <option value="0" @selected(! (bool) $o->is_active)>Nonaktif</option>
                            </select>
                            @if(!empty($o->must_change_password))
                                <span class="mt-1 block rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800 border border-amber-200">Wajib Ganti Sandi</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                <button form="update-ortu-{{ $o->id }}" type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition shadow-sm">Simpan</button>
                                <form method="POST" action="{{ route('waka.ortu.reset_password', $o->id) }}" onsubmit="return confirm('Reset sandi akun orang tua {{ $o->username }} ke sandi bawaan?')">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600 transition shadow-sm" title="Reset Sandi ke Bawaan">Reset</button>
                                </form>
                                <form method="POST" action="{{ route('waka.ortu.destroy', $o->id) }}" onsubmit="return confirm('Hapus akun orang tua {{ $o->fullname }} ({{ $o->username }})?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-200 transition">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center italic text-slate-400">
                            Belum ada akun orang tua yang terdaftar. Silakan tambahkan pada baris di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-student-filter]').forEach((filterInput) => {
            const container = filterInput.closest('td');
            const select = container?.querySelector('[data-student-link]');
            if (!select) return;

            const allOptions = Array.from(select.options);

            filterInput.addEventListener('input', () => {
                const query = filterInput.value.toLowerCase().trim();
                const selectedVal = select.value;

                select.innerHTML = '';
                allOptions.forEach((opt) => {
                    if (!opt.value || opt.value === selectedVal || opt.text.toLowerCase().includes(query)) {
                        select.appendChild(opt.cloneNode(true));
                    }
                });
                select.value = selectedVal;
            });
        });
    </script>
@endpush

