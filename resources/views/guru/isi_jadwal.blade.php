@extends((int)(Auth::user()->role_id ?? 0) === 4 ? 'layouts.kesiswaan' : 'layouts.guru')

@section('title', 'Jadwal Mengajar - School System')

@section('content')
            <header class="mb-6">
                <p class="text-sm uppercase tracking-widest {{ (int)(Auth::user()->role_id ?? 0) === 4 ? 'text-amber-600' : 'text-indigo-600' }} font-semibold">{{ (int)(Auth::user()->role_id ?? 0) === 4 ? 'Ruang kerja kesiswaan' : 'Ruang kerja guru' }}</p>
                <h2 class="mt-2 text-3xl font-bold">Jadwal Mengajar Tahun Ajaran {{ $academicYear }}</h2>
                <p class="mt-2 text-slate-500">Input rencana mengajar secara mandiri untuk Putaran {{ $activeCycle }}. Pengisian mengikuti status kunci dari Waka.</p>
            </header>

            @if(session('success'))
            <div class="mb-4 rounded-xl bg-emerald-100 border border-emerald-300 px-5 py-3 text-emerald-800 font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 rounded-xl bg-rose-100 border border-rose-300 px-5 py-3 text-rose-800 font-semibold space-y-1">
                @foreach($errors->all() as $error)
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $error }}</span>
                </div>
                @endforeach
            </div>
            @endif

            @if($scheduleLocked)
            <div class="mb-6 rounded-xl border border-rose-300 bg-rose-50 px-5 py-4 font-semibold text-rose-800">
                Putaran {{ $activeCycle }} sedang dikunci oleh Waka. Pengisian jadwal sementara tidak tersedia.
            </div>
            @else
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-800">
                Putaran {{ $activeCycle }} terbuka untuk pengisian rencana jadwal.
            </div>
            @endif

            <!-- Tabel Data Jadwal dari DB -->
            <section class="rounded-2xl bg-white p-6 shadow-sm mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Jadwal Mengajar Tersimpan</h3>
                    <a href="{{ route('guru.jadwal.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-rose-700 active:scale-95 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ubah ke PDF / Cetak Jadwal
                    </a>
                </div>
                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                            <tr>
                                <th class="px-4 py-3">Hari</th>
                                <th class="px-4 py-3">Mata Pelajaran</th>
                                <th class="px-4 py-3">Waktu Mengajar</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Putaran</th>
                                <th class="px-4 py-3">Jenis</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($jadwals as $j)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-bold text-slate-800">{{ $j->day_of_week }}</td>
                                <td class="px-4 py-3 font-medium text-slate-700">{{ $j->subject }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ \App\Support\SchoolTimetable::labelFor($j->day_of_week, $j->start_time, $j->end_time) ?? 'Waktu tersimpan' }} · {{ substr($j->start_time,0,5) }} - {{ substr($j->end_time,0,5) }}</td>
                                <td class="px-4 py-3 font-semibold text-indigo-700">{{ $j->class_name ?? $j->class_id ?? '-' }}</td>
                                <td class="px-4 py-3">Putaran {{ $j->cycle_number }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1 text-xs font-bold">{{ $j->cycle_type }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-400 italic">Belum ada jadwal tersimpan untuk putaran ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Form Tambah Jadwal Baru -->
            @if(!$scheduleLocked)
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Tambah Jadwal Baru</h3>
                <form method="POST" action="{{ route('guru.jadwal.store') }}">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="border-b text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-3">Hari</th>
                                    <th class="px-3 py-3">Mata Pelajaran</th>
                                    <th class="px-3 py-3">Mulai</th>
                                    <th class="px-3 py-3">Selesai</th>
                                    <th class="px-3 py-3">Kelas</th>
                                    <th class="px-3 py-3">Jenis</th>
                                </tr>
                            </thead>
                            <tbody id="jadwal-tbody" class="divide-y divide-slate-100">
                                <tr class="jadwal-row">
                                    <td class="px-3 py-4">
                                        <select name="day_of_week[]" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white outline-none">
                                            <option>Senin</option>
                                            <option>Selasa</option>
                                            <option>Rabu</option>
                                            <option>Kamis</option>
                                            <option>Jumat</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-4">
                                        <input type="text" name="subject[]" placeholder="Mata pelajaran" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white outline-none" required>
                                    </td>
                                    <td class="px-3 py-4">
                                        <input type="time" name="start_time[]" value="07:30" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white outline-none" required>
                                    </td>
                                    <td class="px-3 py-4">
                                        <input type="time" name="end_time[]" value="09:00" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white outline-none" required>
                                    </td>
                                    <td class="px-3 py-4">
                                        <input type="number" name="class_id[]" min="1" placeholder="ID kelas" class="w-28 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white outline-none">
                                    </td>
                                    <td class="px-3 py-4">
                                        <select name="cycle_type[]" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white outline-none">
                                            <option value="Normada">Normada</option>
                                            <option value="Jurusan">Jurusan</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-5 flex justify-end gap-3">
                        <button type="button" id="btn-tambah" onclick="addRow()" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition active:scale-95">
                            + Tambah baris
                        </button>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 transition shadow-md hover:shadow-lg active:scale-95">
                            Simpan Jadwal ke Database
                        </button>
                    </div>
                </form>
            </section>
            @endif
@endsection

@push('scripts')
    <script>
        function addRow() {
            const tbody = document.getElementById('jadwal-tbody');
            const tr = document.createElement('tr');
            tr.className = 'jadwal-row';
            tr.innerHTML = `
                <td class="px-3 py-4">
                    <select name="day_of_week[]" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm outline-none">
                        <option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option>
                    </select>
                </td>
                <td class="px-3 py-4">
                    <input type="text" name="subject[]" placeholder="Mata pelajaran" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm outline-none" required>
                </td>
                <td class="px-3 py-4">
                    <input type="time" name="start_time[]" value="07:30" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm outline-none" required>
                </td>
                <td class="px-3 py-4">
                    <input type="time" name="end_time[]" value="09:00" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm outline-none" required>
                </td>
                <td class="px-3 py-4">
                    <input type="number" name="class_id[]" min="1" placeholder="ID kelas" class="w-28 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm outline-none">
                </td>
                <td class="px-3 py-4">
                    <select name="cycle_type[]" class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm outline-none">
                        <option value="Normada">Normada</option>
                        <option value="Jurusan">Jurusan</option>
                    </select>
                </td>
            `;
            tbody.appendChild(tr);
        }
    </script>
@endpush
