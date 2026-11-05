@extends('layouts.siswa')

@section('title', 'Izin Siswa - School System')

@section('content')
            <header class="mb-8 rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Selamat datang</p>
                        <h2 class="mt-2 text-3xl font-bold text-slate-800">Pengajuan Izin</h2>
                    </div>
                    <div class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                        Status diperbarui otomatis
                    </div>
                </div>
            </header>

            <section class="mb-8 rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <h3 class="text-xl font-bold text-slate-800">Buat Pengajuan Izin</h3>
                    <p class="mt-1 text-sm text-slate-500">Tanggal pengajuan otomatis menggunakan tanggal hari ini. Foto bukti tampil langsung setelah dipilih dan dikirim bersama pengajuan.</p>
                    @if($student)
                        <p class="mt-2 text-sm font-semibold text-emerald-700">Pengajuan untuk {{ $student->name }} — {{ $student->kelas }}</p>
                    @endif
                </div>

                @if($errors->any())
                    <div id="form-errors" role="alert" class="mb-5 rounded-xl bg-rose-100 px-4 py-3 text-sm text-rose-800">
                        <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @else
                    <div id="form-errors" role="alert" class="mb-5 hidden rounded-xl bg-rose-100 px-4 py-3 text-sm text-rose-800"></div>
                @endif
                @if(session('success'))
                    <div id="form-success" role="status" class="mb-5 rounded-xl bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
                @else
                    <div id="form-success" role="status" class="mb-5 hidden rounded-xl bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-800"></div>
                @endif

                @if(!$student)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Akun ini belum ditautkan ke data siswa. Minta Waka menetapkan data siswa pada menu Kelola Akun &amp; Role.
                    </div>
                @else
                    <form id="leave-request-form" method="POST" action="{{ route('siswa.izin.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @csrf
                        <label class="block md:col-span-2">
                            <span class="mb-1 block text-sm font-semibold text-slate-700">Alasan Izin</span>
                            <textarea name="reason" rows="3" required maxlength="2000" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="Jelaskan alasan izin">{{ old('reason') }}</textarea>
                        </label>
                        <div class="md:col-span-2">
                            @include('partials.selfie_camera', [
                                'componentId' => 'leave-selfie-camera',
                                'inputId' => 'leave-photo',
                                'inputName' => 'photo',
                                'label' => 'Foto Bukti Selfie (Real-Time)',
                                'studentName' => $student->name ?? (Auth::user()->fullname ?? Auth::user()->username),
                                'studentClass' => $student->kelas ?? '',
                                'required' => false,
                            ])
                        </div>
                        <div class="md:col-span-2">
                            <button id="submit-leave-request" type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
                                Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                @endif
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Riwayat Pengajuan</h3>
                        <p class="mt-1 text-sm text-slate-500">Data dan keputusan izin tersinkron otomatis dengan Kesiswaan.</p>
                    </div>
                    <span id="leave-request-count" class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">{{ count($leaveRequests) }} data</span>
                </div>

                <div class="overflow-x-auto table-scroll-container">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="rounded-l-lg px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Tanggal Pengajuan</th>
                                <th class="px-4 py-3">Alasan</th>
                                <th class="px-4 py-3">Foto Bukti</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="rounded-r-lg px-4 py-3">Diproses Oleh</th>
                            </tr>
                        </thead>
                        <tbody id="leave-request-rows" class="divide-y divide-slate-100">
                            @forelse ($leaveRequests as $leave)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-4 font-semibold text-slate-800">{{ $leave->student_name ?? ('Siswa #' . $leave->student_id) }}</td>
                                    <td class="px-4 py-4 text-indigo-700">{{ $leave->kelas ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-slate-600">{{ $leave->start_date ?? '-' }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $leave->reason ?? '-' }}</td>
                                    <td class="px-4 py-4">
                                        @if($leave->document_path)
                                            <a href="{{ asset('storage/' . $leave->document_path) }}" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:underline">Lihat foto</a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @php
                                            $status = $leave->status_approval ?? 'Pending';
                                        @endphp

                                        @if ($status === 'Approved')
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Disetujui</span>
                                        @elseif ($status === 'Rejected')
                                            <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700">Ditolak</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">{{ $leave->verifier_name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-slate-500">Belum ada pengajuan izin.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
@endsection

@push('scripts')
    <script>
        const form = document.getElementById('leave-request-form');
        const rows = document.getElementById('leave-request-rows');
        const count = document.getElementById('leave-request-count');
        const errors = document.getElementById('form-errors');
        const success = document.getElementById('form-success');

        function renderRequests(requests) {
            rows.replaceChildren();
            count.textContent = `${requests.length} data`;

            if (requests.length === 0) {
                const row = document.createElement('tr');
                const cell = document.createElement('td');
                cell.colSpan = 7;
                cell.className = 'px-4 py-6 text-center text-slate-500';
                cell.textContent = 'Belum ada pengajuan izin.';
                row.append(cell);
                rows.append(row);
                return;
            }

            requests.forEach((request) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50';
                [
                    request.student_name || `Siswa #${request.student_id}`,
                    request.kelas || '-',
                    request.start_date || '-',
                    request.reason || '-',
                ].forEach((value, index) => {
                    const cell = document.createElement('td');
                    cell.className = index === 0
                        ? 'px-4 py-4 font-semibold text-slate-800'
                        : index === 1
                            ? 'px-4 py-4 text-indigo-700'
                            : index === 2
                                ? 'whitespace-nowrap px-4 py-4 text-slate-600'
                                : 'px-4 py-4 text-slate-700';
                    cell.textContent = value;
                    row.append(cell);
                });

                const photoCell = document.createElement('td');
                photoCell.className = 'px-4 py-4';
                if (request.document_path) {
                    const link = document.createElement('a');
                    link.href = `${@json(asset('storage'))}/${request.document_path.split('/').map(encodeURIComponent).join('/')}`;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.className = 'font-semibold text-emerald-700 hover:underline';
                    link.textContent = 'Lihat foto';
                    photoCell.append(link);
                } else {
                    photoCell.textContent = '-';
                    photoCell.className += ' text-slate-400';
                }
                row.append(photoCell);

                const statusCell = document.createElement('td');
                statusCell.className = 'px-4 py-4';
                const badge = document.createElement('span');
                const statusStyles = {
                    Approved: ['bg-emerald-100', 'text-emerald-700'],
                    Rejected: ['bg-rose-100', 'text-rose-700'],
                    Pending: ['bg-amber-100', 'text-amber-700'],
                };
                const statusLabels = { Approved: 'Disetujui', Rejected: 'Ditolak', Pending: 'Menunggu' };
                const status = request.status_approval;
                badge.className = `rounded-full px-3 py-1 text-xs font-bold ${statusStyles[status] ? statusStyles[status].join(' ') : statusStyles.Pending.join(' ')}`;
                badge.textContent = statusLabels[status] || 'Menunggu';
                statusCell.append(badge);
                row.append(statusCell);

                const verifierCell = document.createElement('td');
                verifierCell.className = 'px-4 py-4 text-slate-600';
                verifierCell.textContent = request.verifier_name || '-';
                row.append(verifierCell);
                rows.append(row);
            });
        }

        if (form) {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                errors.classList.add('hidden');
                success.classList.add('hidden');
                const button = document.getElementById('submit-leave-request');
                button.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: new FormData(form),
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        const messages = Object.values(result.errors || {}).flat();
                        throw new Error(messages.join(' ') || result.message || 'Pengajuan izin gagal dikirim.');
                    }

                    form.reset();
                    // Reset kamera selfie ke state awal
                    const cameraCancelBtn = document.getElementById('leave-selfie-camera-btn-cancel');
                    if (cameraCancelBtn) cameraCancelBtn.click();
                    const cameraFileInput = document.getElementById('leave-photo');
                    if (cameraFileInput) cameraFileInput.value = '';
                    const cameraPreview = document.getElementById('leave-selfie-camera-box-preview');
                    if (cameraPreview) cameraPreview.classList.add('hidden');
                    const cameraIdle = document.getElementById('leave-selfie-camera-box-idle');
                    if (cameraIdle) cameraIdle.classList.remove('hidden');

                    success.textContent = result.message;
                    success.classList.remove('hidden');
                    refreshRequests().catch((refreshError) => {
                        console.error(refreshError);
                    });
                } catch (error) {
                    errors.textContent = error.message;
                    errors.classList.remove('hidden');
                } finally {
                    button.disabled = false;
                }
            });
        }

        async function refreshRequests() {
            const response = await fetch(@json(route('siswa.izin.updates')), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error('Riwayat pengajuan belum dapat diperbarui.');
            }

            const result = await response.json();
            renderRequests(result.data);
        }

        window.setInterval(() => {
            if (!document.hidden) {
                refreshRequests().catch((error) => {
                    console.error(error);
                });
            }
        }, 5000);
    </script>
@endpush
