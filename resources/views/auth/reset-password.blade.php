<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Sandi - Sistem Informasi Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-sky-100 via-white to-indigo-100 p-6">
    <main class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-200 md:p-10">
        <div class="text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <h1 class="mt-5 text-3xl font-bold text-slate-800">Atur ulang sandi</h1>
            <p class="mt-2 text-sm text-slate-500">Masukkan kode OTP dari email pemulihan, lalu buat sandi baru.</p>
        </div>

        @if (session('status'))
            <div role="status" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="otp" class="mb-2 block text-sm font-semibold text-slate-700">Kode OTP</label>
                <input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" value="{{ old('otp') }}" required autocomplete="one-time-code"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-sm outline-none transition focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-200"
                    placeholder="Masukkan 6 digit kode">
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Sandi baru</label>
                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-sm outline-none transition focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-200"
                    placeholder="Minimal 8 karakter">
            </div>
            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi sandi baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-sm outline-none transition focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-200">
            </div>
            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 px-4 py-3 font-semibold text-white shadow-lg shadow-sky-200 transition hover:from-sky-500 hover:to-indigo-500">
                Simpan sandi baru
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            <a href="{{ route('password.request') }}" class="font-semibold text-sky-700 hover:underline">Minta kode OTP baru</a>
        </p>
    </main>
</body>
</html>
