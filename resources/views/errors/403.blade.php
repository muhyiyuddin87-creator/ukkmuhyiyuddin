<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | Sistem Akademik</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-badge.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 text-slate-100">
    <div class="w-full max-w-md text-center bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-md">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 mb-6 shadow-inner">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-3.5a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/10 px-3 py-1 text-xs font-bold text-rose-400 border border-rose-500/20">
            HTTP 403 • FORBIDDEN
        </span>

        <h1 class="mt-4 text-2xl font-black tracking-tight text-white sm:text-3xl">Akses Ditolak</h1>
        <p class="mt-3 text-sm text-slate-400 leading-relaxed">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses untuk membuka halaman ini. Proteksi backend khusus Waka Kurikulum aktif.' }}
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 active:scale-95 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Kembali ke Dashboard
            </a>
            <button onclick="window.history.back()" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800/80 px-5 py-3 text-sm font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition">
                Halaman Sebelumnya
            </button>
        </div>
    </div>
</body>
</html>
