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
                        <!-- Pilihan Jenis Pengajuan: Sakit atau Izin -->
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Jenis Pengajuan <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3 sm:max-w-md">
                                <label class="relative flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs transition hover:border-emerald-300 hover:bg-emerald-50/30 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50/60 has-[:checked]:ring-2 has-[:checked]:ring-yellow-500/20">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" name="leave_type" value="Sakit" {{ old('leave_type') === 'Sakit' ? 'checked' : '' }} class="h-4 w-4 border-slate-300 text-yellow-600 focus:ring-yellow-500">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-yellow-100 text-yellow-800">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-bold text-slate-800">Sakit</p>
                                                <p class="text-[11px] text-slate-500">Kondisi kesehatan kurang fit</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white p-3.5 shadow-xs transition hover:border-emerald-300 hover:bg-emerald-50/30 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/60 has-[:checked]:ring-2 has-[:checked]:ring-sky-500/20">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" name="leave_type" value="Izin" {{ old('leave_type', 'Izin') === 'Izin' ? 'checked' : '' }} class="h-4 w-4 border-slate-300 text-sky-600 focus:ring-sky-500">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-800">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-bold text-slate-800">Izin</p>
                                                <p class="text-[11px] text-slate-500">Keperluan penting / keluarga</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <label class="block md:col-span-2">
                            <span class="mb-1 block text-sm font-semibold text-slate-700">Alasan Izin / Sakit <span class="text-rose-500">*</span></span>
                            <textarea name="reason" rows="3" required maxlength="2000" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="Jelaskan keterangan atau alasan mengapa izin / sakit...">{{ old('reason') }}</textarea>
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
                            <button id="submit-leave-request" type="submit" class="h-10 inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700 shadow-xs active:scale-95 disabled:cursor-wait disabled:opacity-60">
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
                                <th class="px-4 py-3">Jenis Izin</th>
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
                                    <td class="px-4 py-4 text-indigo-700 font-medium">{{ $leave->kelas ?? '-' }}</td>
                                    <td class="px-4 py-4">
                                        @php
                                            $leaveType = $leave->leave_type ?? (stripos($leave->reason, 'sakit') !== false ? 'Sakit' : 'Izin');
                                        @endphp
                                        @if($leaveType === 'Sakit')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-bold text-yellow-800 border border-yellow-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
                                                Sakit
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-bold text-sky-800 border border-sky-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                                Izin
                                            </span>
                                        @endif
                                    </td>
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
                                    <td colspan="8" class="px-4 py-6 text-center text-slate-500">Belum ada pengajuan izin.</td>
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
                cell.colSpan = 8;
                cell.className = 'px-4 py-6 text-center text-slate-500';
                cell.textContent = 'Belum ada pengajuan izin.';
                row.append(cell);
                rows.append(row);
                return;
            }

            requests.forEach((request) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50';

                // 1. Nama Siswa
                const nameCell = document.createElement('td');
                nameCell.className = 'px-4 py-4 font-semibold text-slate-800';
                nameCell.textContent = request.student_name || `Siswa #${request.student_id}`;
                row.append(nameCell);

                // 2. Kelas
                const classCell = document.createElement('td');
                classCell.className = 'px-4 py-4 text-indigo-700 font-medium';
                classCell.textContent = request.kelas || '-';
                row.append(classCell);

                // 3. Jenis Izin
                const typeCell = document.createElement('td');
                typeCell.className = 'px-4 py-4';
                const isSakit = (request.leave_type === 'Sakit') || (!request.leave_type && (request.reason || '').toLowerCase().includes('sakit'));
                const typeBadge = document.createElement('span');
                if (isSakit) {
                    typeBadge.className = 'inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-bold text-yellow-800 border border-yellow-200';
                    typeBadge.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span> Sakit';
                } else {
                    typeBadge.className = 'inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-bold text-sky-800 border border-sky-200';
                    typeBadge.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span> Izin';
                }
                typeCell.append(typeBadge);
                row.append(typeCell);

                // 4. Tanggal Pengajuan
                const dateCell = document.createElement('td');
                dateCell.className = 'whitespace-nowrap px-4 py-4 text-slate-600';
                dateCell.textContent = request.start_date || '-';
                row.append(dateCell);

                // 5. Alasan
                const reasonCell = document.createElement('td');
                reasonCell.className = 'px-4 py-4 text-slate-700';
                reasonCell.textContent = request.reason || '-';
                row.append(reasonCell);

                // 6. Foto Bukti
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

                // 7. Status
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

                // 8. Verifier
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
