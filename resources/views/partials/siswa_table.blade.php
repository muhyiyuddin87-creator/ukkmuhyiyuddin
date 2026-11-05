@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $siswaData = collect();

    if (Schema::hasTable('siswas')) {
        $siswaData = DB::table('siswas')
            ->leftJoin('attendances', function ($join) {
                $join->on('siswas.id', '=', 'attendances.student_id')
                    ->where('attendances.date', now()->toDateString());
            })
            ->select('siswas.id', 'siswas.name', 'siswas.kelas', 'siswas.jurusan', DB::raw('COALESCE(attendances.status, "Belum ada") as status'))
            ->orderBy('siswas.name')
            ->get();
    }
@endphp

<section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between gap-3">
        <h3 class="text-xl font-bold text-slate-800">Data Siswa</h3>
        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">Rekap Hari Ini</span>
    </div>

    <div class="overflow-x-auto table-scroll-container">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-semibold text-slate-700">No</th>
                    <th class="px-4 py-3 font-semibold text-slate-700">Nama</th>
                    <th class="px-4 py-3 font-semibold text-slate-700">Kelas</th>
                    <th class="px-4 py-3 font-semibold text-slate-700">Jurusan</th>
                    <th class="px-4 py-3 font-semibold text-slate-700">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse ($siswaData as $siswa)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-600">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $siswa->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $siswa->kelas }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $siswa->jurusan }}</td>
                        <td class="px-4 py-3">
                            @php
                                $status = $siswa->status ?? 'Belum ada';
                            @endphp

                            @if ($status === 'Hadir')
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ $status }}</span>
                            @elseif ($status === 'Izin')
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">{{ $status }}</span>
                            @elseif ($status === 'Sakit')
                                <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-bold text-yellow-700">{{ $status }}</span>
                            @elseif ($status === 'Alpha')
                                <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700">{{ $status }}</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $status }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data siswa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
