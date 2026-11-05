<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Sandi - Sistem Informasi Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-sky-100 via-white to-indigo-100 p-6">
    <main class="w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-200 md:p-10">
        <div class="text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 shadow-sm">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="mt-4 text-3xl font-bold text-slate-800">Lupa Sandi?</h1>
            <p class="mt-2 text-sm text-slate-500">Pilih metode pemulihan sesuai dengan jenis akun Anda.</p>
        </div>

        <!-- Panduan Hybrid -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="rounded-2xl border border-sky-100 bg-sky-50/70 p-4">
                <div class="flex items-center gap-2 text-sky-800 font-semibold text-xs uppercase tracking-wide">
                    Guru & Staf
                </div>
                <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                    Kirim kode OTP 6-digit ke <strong>email pemulihan</strong> terdaftar (Gratis tanpa biaya SMS).
                </p>
            </div>
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4">
                <div class="flex items-center gap-2 text-indigo-800 font-semibold text-xs uppercase tracking-wide">
                    Siswa
                </div>
                <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                    Tidak punya email? Cukup hubungi <strong>Waka Kurikulum / Admin Sekolah</strong> untuk bantuan reset sandi.
                </p>
            </div>
        </div>

        @if (session('status'))
            <div role="status" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if (session('info_siswa'))
            <div role="status" class="mt-6 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                <p class="font-bold">Informasi Akun Siswa</p>
                <p class="mt-1 text-xs leading-relaxed">{{ session('info_siswa') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Username atau Email</label>
                <input id="username" name="username" type="text" value="{{ old('username', old('identifier')) }}" required autofocus autocomplete="username" maxlength="100"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-sm outline-none transition focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-200"
                    placeholder="Masukkan username atau email pemulihan">
            </div>
            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 px-4 py-3 font-semibold text-white shadow-lg shadow-sky-200 transition hover:from-sky-500 hover:to-indigo-500">
                Kirim Kode OTP Pemulihan
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            <a href="{{ route('login') }}" class="font-semibold text-sky-700 hover:underline">← Kembali ke halaman login</a>
        </p>
    </main>
</body>
</html>
