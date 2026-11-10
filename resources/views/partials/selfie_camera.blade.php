@php
    $cid = $componentId ?? 'selfie-camera-' . uniqid();
    $inputId = $inputId ?? 'leave-photo';
    $inputName = $inputName ?? 'photo';
    $label = $label ?? 'Foto Bukti Selfie / Surat Keterangan';
    $isRequired = !empty($required);
    $studentDisplayName = $studentName ?? (Auth::user()->fullname ?? Auth::user()->username ?? 'Siswa');
    $studentClassDisplay = $studentClass ?? '';
@endphp

<div class="selfie-camera-component space-y-3" id="{{ $cid }}"
     data-student-name="{{ $studentDisplayName }}"
     data-student-class="{{ $studentClassDisplay }}">
    
    <div class="flex items-center justify-between flex-wrap gap-2">
        <label class="block text-sm font-semibold text-slate-700">
            {{ $label }}
            @if($isRequired)
                <span class="text-rose-500 font-bold">* (Wajib Lampirkan Foto)</span>
            @else
                <span class="font-normal text-slate-500">(opsional, kamera live / galeri)</span>
            @endif
        </label>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200">
            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Kamera &amp; Galeri
        </span>
    </div>

    <!-- Hidden input file utama yang dikirim ke backend -->
    <input type="file" id="{{ $inputId }}" name="{{ $inputName }}" accept="image/jpeg,image/png,image/webp" class="hidden">
    <!-- Hidden input khusus untuk memilih file dari galeri / dokumen perangkat -->
    <input type="file" id="{{ $cid }}-input-gallery" accept="image/jpeg,image/png,image/webp" class="hidden">

    <!-- Box 1: Tampilan Awal (Idle State) -->
    <div id="{{ $cid }}-box-idle" class="rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-6 text-center transition hover:bg-emerald-50">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 mb-3 shadow-xs">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <h4 class="text-base font-bold text-slate-800">Lampirkan Foto Bukti (Kamera atau Galeri)</h4>
        <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">
            Gunakan kamera selfie langsung ATAU unggah foto surat dokter / keterangan dari galeri perangkat Anda (maks. 5 MB).
        </p>
        <div class="mt-4 flex flex-wrap justify-center items-center gap-3">
            <button type="button" id="{{ $cid }}-btn-open" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Buka Kamera Selfie Real-Time
            </button>
            <button type="button" id="{{ $cid }}-btn-gallery" class="inline-flex items-center gap-2 rounded-xl border border-emerald-400 bg-white px-5 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50 active:scale-95 transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Pilih dari Galeri / Berkas
            </button>
        </div>
        <!-- Pesan error kamera jika ditolak / tidak didukung -->
        <div id="{{ $cid }}-error-msg" class="mt-4 hidden rounded-xl bg-rose-100 p-3 text-xs text-rose-700 text-left"></div>
    </div>

    <!-- Box 2: Tampilan Live Viewfinder Kamera -->
    <div id="{{ $cid }}-box-viewfinder" class="hidden relative overflow-hidden rounded-2xl bg-slate-950 border-2 border-emerald-500 shadow-xl">
        <div class="relative w-full aspect-4/3 max-h-[400px] flex items-center justify-center bg-black overflow-hidden">
            <video id="{{ $cid }}-video" class="w-full h-full object-cover" autoplay playsinline muted style="transform: scaleX(-1);"></video>
            
            <!-- Shutter flash animation -->
            <div id="{{ $cid }}-shutter" class="absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-150"></div>

            <!-- Overlay Panduan Wajah Selfie -->
            <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-4">
                <div class="inline-flex items-center gap-2 rounded-full bg-slate-900/80 backdrop-blur-xs px-3 py-1 text-xs text-white">
                    <span class="h-2 w-2 rounded-full bg-rose-500 animate-ping"></span>
                    <span>Live Selfie Real-Time</span>
                </div>
                <!-- Panduan Oval Wajah -->
                <div class="w-44 h-56 sm:w-52 sm:h-64 border-2 border-dashed border-emerald-400/70 rounded-[50%] flex items-center justify-center">
                    <span class="text-[10px] text-emerald-200 tracking-wide font-medium bg-black/50 px-2.5 py-1 rounded-full">Arahkan wajah ke lingkaran</span>
                </div>
                <div class="text-[11px] text-slate-200 bg-slate-900/80 backdrop-blur-xs px-3 py-1 rounded-full">
                    {{ $studentDisplayName }} {{ $studentClassDisplay ? '• ' . $studentClassDisplay : '' }}
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Kamera -->
        <div class="bg-slate-900 px-4 py-3 flex items-center justify-between gap-3 border-t border-slate-800">
            <button type="button" id="{{ $cid }}-btn-cancel" class="rounded-lg px-3 py-2 text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition">
                Tutup Kamera
            </button>
            <button type="button" id="{{ $cid }}-btn-capture" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-emerald-600 active:scale-95 transition">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="10" r="7" />
                </svg>
                Ambil Foto Sekarang
            </button>
            <div class="w-16"></div>
        </div>
    </div>

    <!-- Box 3: Tampilan Pratinjau Foto Hasil Jepretan / Unggahan Galeri -->
    <div id="{{ $cid }}-box-preview" class="hidden rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
        <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-100 flex items-center justify-center">
            <img id="{{ $cid }}-img-preview" class="max-h-80 w-full object-contain" alt="Pratinjau Foto Bukti">
            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-600/95 backdrop-blur-xs px-3 py-1 text-xs font-semibold text-white shadow">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span id="{{ $cid }}-badge-text">Foto Siap Dikirim</span>
            </span>
        </div>
        <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <p id="{{ $cid }}-txt-timestamp" class="text-xs text-slate-500 truncate">Foto tercatat dengan stempel keterangan.</p>
            <div class="flex items-center flex-wrap gap-2">
                <button type="button" id="{{ $cid }}-btn-retake" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Selfie Ulang
                </button>
                <button type="button" id="{{ $cid }}-btn-retake-gallery" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Ganti dari Galeri
                </button>
                <button type="button" id="{{ $cid }}-btn-remove" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const cid = @json($cid);
    const root = document.getElementById(cid);
    if (!root) return;

    const inputId = @json($inputId);
    const fileInput = document.getElementById(inputId);
    const inputGallery = document.getElementById(`${cid}-input-gallery`);
    const boxIdle = document.getElementById(`${cid}-box-idle`);
    const boxViewfinder = document.getElementById(`${cid}-box-viewfinder`);
    const boxPreview = document.getElementById(`${cid}-box-preview`);
    const video = document.getElementById(`${cid}-video`);
    const shutter = document.getElementById(`${cid}-shutter`);
    const imgPreview = document.getElementById(`${cid}-img-preview`);
    const badgeText = document.getElementById(`${cid}-badge-text`);
    const txtTimestamp = document.getElementById(`${cid}-txt-timestamp`);
    const errorMsg = document.getElementById(`${cid}-error-msg`);

    const btnOpen = document.getElementById(`${cid}-btn-open`);
    const btnGallery = document.getElementById(`${cid}-btn-gallery`);
    const btnCancel = document.getElementById(`${cid}-btn-cancel`);
    const btnCapture = document.getElementById(`${cid}-btn-capture`);
    const btnRetake = document.getElementById(`${cid}-btn-retake`);
    const btnRetakeGallery = document.getElementById(`${cid}-btn-retake-gallery`);
    const btnRemove = document.getElementById(`${cid}-btn-remove`);

    const studentName = root.getAttribute('data-student-name') || 'Siswa';
    const studentClass = root.getAttribute('data-student-class') || '';

    let stream = null;
    let previewUrl = null;

    function stopStream() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        if (video) {
            video.srcObject = null;
        }
    }

    function clearPhoto() {
        if (fileInput) {
            fileInput.value = '';
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (inputGallery) inputGallery.value = '';
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
        if (imgPreview) imgPreview.removeAttribute('src');
    }

    function showIdle() {
        stopStream();
        boxIdle.classList.remove('hidden');
        boxViewfinder.classList.add('hidden');
        boxPreview.classList.add('hidden');
        errorMsg.classList.add('hidden');
    }

    async function openCamera() {
        errorMsg.classList.add('hidden');
        errorMsg.textContent = '';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            errorMsg.textContent = 'Browser Anda tidak mendukung akses kamera langsung atau Anda mengakses tanpa koneksi aman (HTTPS). Pastikan menggunakan browser modern atau gunakan tombol "Pilih dari Galeri / Berkas".';
            errorMsg.classList.remove('hidden');
            return;
        }

        try {
            // Gunakan facingMode: 'user' khusus kamera depan (selfie)
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });

            video.srcObject = stream;
            await video.play();

            boxIdle.classList.add('hidden');
            boxPreview.classList.add('hidden');
            boxViewfinder.classList.remove('hidden');
        } catch (err) {
            console.error('Camera error:', err);
            let msg = 'Gagal mengakses kamera. ';
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                msg += 'Izin akses kamera ditolak. Silakan izinkan akses kamera di pengaturan browser atau pilih foto dari galeri.';
            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                msg += 'Perangkat kamera tidak ditemukan. Anda dapat menggunakan opsi "Pilih dari Galeri / Berkas".';
            } else {
                msg += (err.message || 'Pastikan izin kamera diizinkan.') + ' Anda juga dapat memilih foto dari galeri.';
            }
            errorMsg.textContent = msg;
            errorMsg.classList.remove('hidden');
        }
    }

    function handleGallerySelect(e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        // Validasi tipe file gambar
        const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!validTypes.includes(file.type.toLowerCase())) {
            alert('Format berkas tidak didukung. Harap pilih gambar dengan format JPG, JPEG, PNG, atau WEBP.');
            if (inputGallery) inputGallery.value = '';
            return;
        }

        // Validasi ukuran berkas (maksimal 5 MB)
        const maxBytes = 5 * 1024 * 1024;
        if (file.size > maxBytes) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            alert(`Ukuran berkas (${sizeMB} MB) melebihi batas maksimal 5 MB. Silakan pilih foto dengan ukuran lebih kecil.`);
            if (inputGallery) inputGallery.value = '';
            return;
        }

        // Sinkronkan berkas ke input utama (name="photo") menggunakan DataTransfer
        if (fileInput) {
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            } catch (err) {
                console.warn('DataTransfer not fully supported:', err);
            }
        }

        // Tampilkan pratinjau foto galeri
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
        previewUrl = URL.createObjectURL(file);
        if (imgPreview) imgPreview.src = previewUrl;

        if (badgeText) {
            badgeText.textContent = 'Foto dari Galeri / Dokumen Terlampir';
        }
        if (txtTimestamp) {
            const sizeFormatted = file.size > 1024 * 1024 
                ? (file.size / (1024 * 1024)).toFixed(2) + ' MB' 
                : (file.size / 1024).toFixed(1) + ' KB';
            txtTimestamp.textContent = `File Berkas: ${file.name} (${sizeFormatted})`;
        }

        stopStream();
        boxIdle.classList.add('hidden');
        boxViewfinder.classList.add('hidden');
        boxPreview.classList.remove('hidden');
    }

    function capturePhoto() {
        if (!video || !video.videoWidth) {
            alert('Kamera belum siap, silakan tunggu sebentar.');
            return;
        }

        // Shutter flash effect
        if (shutter) {
            shutter.classList.remove('opacity-0');
            shutter.classList.add('opacity-90');
            setTimeout(() => {
                shutter.classList.remove('opacity-90');
                shutter.classList.add('opacity-0');
            }, 120);
        }

        const width = video.videoWidth || 640;
        const height = video.videoHeight || 480;

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');

        // Cerminkan secara horizontal agar foto selfie sesuai dengan apa yang dilihat siswa di layar
        ctx.translate(width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, width, height);
        ctx.setTransform(1, 0, 0, 1, 0, 0); // reset transform

        // Buat Banner Watermark semi-transparan di bagian bawah
        const bannerHeight = Math.max(56, Math.floor(height * 0.14));
        ctx.fillStyle = 'rgba(15, 23, 42, 0.78)'; // Slate-900 dengan transparansi
        ctx.fillRect(0, height - bannerHeight, width, bannerHeight);

        // Aksen garis hijau emerald di atas banner watermark
        ctx.fillStyle = '#10b981'; // emerald-500
        ctx.fillRect(0, height - bannerHeight, width, Math.max(3, Math.floor(height * 0.008)));

        // Teks Nama & Kelas Siswa
        const nameFontSize = Math.max(14, Math.floor(height * 0.038));
        ctx.fillStyle = '#ffffff';
        ctx.font = `bold ${nameFontSize}px "Segoe UI", sans-serif`;
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        const studentInfoText = `${studentName}${studentClass ? ' • ' + studentClass : ''}`;
        ctx.fillText(studentInfoText, 20, height - (bannerHeight * 0.65));

        // Teks Waktu Real-Time Saat Ini
        const now = new Date();
        const dateStr = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
        const timeStr = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const fullTimeStr = `${dateStr} - ${timeStr} WIB (Real-Time Camera)`;
        const timeFontSize = Math.max(12, Math.floor(height * 0.030));
        ctx.fillStyle = '#34d399'; // emerald-400
        ctx.font = `600 ${timeFontSize}px "Segoe UI", sans-serif`;
        ctx.fillText(fullTimeStr, 20, height - (bannerHeight * 0.28));

        // Convert canvas ke JPEG Blob
        canvas.toBlob((blob) => {
            if (!blob) {
                alert('Gagal memproses foto.');
                return;
            }

            const fileName = `selfie_${Date.now()}.jpg`;
            const file = new File([blob], fileName, { type: 'image/jpeg' });

            // Masukkan ke input file utama melalui DataTransfer
            if (fileInput) {
                try {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    // Trigger change event agar listener form mendeteksi file baru
                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                } catch (e) {
                    console.warn('DataTransfer not supported:', e);
                }
            }

            // Tampilkan Pratinjau
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
            previewUrl = URL.createObjectURL(blob);
            if (imgPreview) imgPreview.src = previewUrl;
            if (badgeText) badgeText.textContent = 'Foto Real-Time Siap Dikirim';
            if (txtTimestamp) txtTimestamp.textContent = `Tercatat: ${studentInfoText} (${dateStr} ${timeStr} WIB)`;

            stopStream();
            boxViewfinder.classList.add('hidden');
            boxPreview.classList.remove('hidden');
        }, 'image/jpeg', 0.90);
    }

    if (btnOpen) btnOpen.addEventListener('click', openCamera);
    if (btnGallery && inputGallery) {
        btnGallery.addEventListener('click', () => inputGallery.click());
    }
    if (inputGallery) {
        inputGallery.addEventListener('change', handleGallerySelect);
    }
    if (btnCancel) btnCancel.addEventListener('click', showIdle);
    if (btnCapture) btnCapture.addEventListener('click', capturePhoto);

    if (btnRetake) {
        btnRetake.addEventListener('click', () => {
            clearPhoto();
            openCamera();
        });
    }

    if (btnRetakeGallery && inputGallery) {
        btnRetakeGallery.addEventListener('click', () => {
            inputGallery.click();
        });
    }

    if (btnRemove) {
        btnRemove.addEventListener('click', () => {
            clearPhoto();
            showIdle();
        });
    }

    // Bersihkan stream jika halaman di-unload / navigasi pergi
    window.addEventListener('beforeunload', stopStream);
})();
</script>
