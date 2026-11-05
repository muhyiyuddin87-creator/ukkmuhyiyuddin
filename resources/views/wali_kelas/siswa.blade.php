@extends('wali_kelas.layout')

@section('title', 'Kelola Akun Siswa Kelas - Wali Kelas')

@section('content')
<div class="space-y-6">
    <header class="rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-indigo-600 font-semibold">Wali Kelas</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-800">Akun Siswa & Reset Sandi Kelas</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Daftar siswa di kelas yang Anda ampu: 
                    <span class="font-semibold text-indigo-700">{{ $classNames->join(', ') }}</span>. 
                    Jika siswa lupa kata sandi, Anda dapat meresetnya langsung di sini.
                </p>
            </div>
            <div class="rounded-xl bg-indigo-50 border border-indigo-200 px-4 py-3 text-xs text-indigo-800 max-w-sm">
                <strong>Catatan Keamanan:</strong> Setelah Anda mereset sandi siswa, sistem akan <u>mewajibkan</u> siswa membuat sandi pribadi baru saat pertama kali login.
            </div>
        </div>
    </header>

    @if(session('reset_info'))
        <div class="rounded-2xl border-2 border-emerald-400 bg-emerald-50 p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
                <div>
                    <h3 class="font-bold text-emerald-900 text-base">Sandi Berhasil Diatur Ulang!</h3>
                    <p class="mt-1 text-sm text-emerald-800">
                        {{ session('reset_info')['message'] }}
                    </p>
                    <div class="mt-3 inline-flex items-center gap-2 rounded-xl bg-white border border-emerald-300 px-4 py-2 font-mono text-sm text-slate-800 font-semibold shadow-sm">
                        <span>Sandi Sementara:</span>
                        <span class="text-emerald-700 select-all">{{ session('reset_info')['temporary_password'] }}</span>
                    </div>
                    <p class="mt-2 text-xs text-emerald-700">
                        Berikan sandi sementara ini kepada siswa. Siswa wajib menggantinya begitu berhasil masuk ke akunnya.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-bold text-slate-800">Daftar Siswa ({{ $students->count() }})</h2>
            
            <form method="GET" action="{{ route('wali-kelas.siswa') }}" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NIS..."
                    class="rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800 outline-none focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition">
                    Cari
                </button>
                @if(request('q'))
                    <a href="{{ route('wali-kelas.siswa') }}" class="rounded-xl bg-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-300 transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto table-scroll-container">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">No</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">NIS</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-4 py-3">Akun Pengguna</th>
                        <th class="px-4 py-3">Email Pemulihan</th>
                        <th class="px-4 py-3">Status Sandi</th>
                        <th class="px-4 py-3 text-right rounded-r-lg">Aksi Lupa Sandi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $index => $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ $item->name }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 font-mono text-xs">
                                {{ $item->nis ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <span class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 border border-indigo-100">
                                    {{ $item->kelas }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($item->account_id)
                                    <div>
                                        <span class="font-mono text-xs font-semibold text-slate-800">{{ $item->account_username }}</span>
                                        @if(!$item->account_active)
                                            <span class="ml-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-semibold text-rose-700">Nonaktif</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs italic text-slate-400">Belum ada akun</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($item->account_email)
                                    <span class="text-emerald-700 font-medium">{{ $item->account_email }}</span>
                                @else
                                    <span class="text-slate-400 italic">Belum disetel</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($item->must_change_password)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-medium text-amber-800">
                                        <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Menunggu Ganti Sandi
                                    </span>
                                @elseif($item->account_id)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-medium text-emerald-800">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Normal
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($item->account_id)
                                    <form method="POST" action="{{ route('wali-kelas.siswa.reset_password', $item->account_id) }}" 
                                        onsubmit="return confirm('Apakah Anda yakin ingin mereset kata sandi akun {{ $item->name }} ({{ $item->account_username }})? Sandi akan disetel ke default dan siswa wajib menggantinya saat login.')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-600 transition">
                                            Reset Sandi
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400 italic">Minta Waka buat akun</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400 italic">
                                Tidak ada data siswa di kelas yang Anda ampu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
