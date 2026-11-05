<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wajib Ganti Sandi - Sistem Informasi Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-amber-50 via-white to-sky-100 p-6">
    <main class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-200 md:p-10">
        <div class="text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 shadow-sm">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h1 class="mt-5 text-2xl font-bold text-slate-800">Ganti Sandi Akun Anda</h1>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                Kata sandi akun Anda baru saja diatur ulang oleh <span class="font-semibold text-slate-800">Administrator / Waka Kurikulum</span>.
                Demi menjaga keamanan dan kerahasiaan akun pribadi, Anda <strong class="text-amber-700">wajib</strong> membuat sandi baru sebelum dapat menggunakan sistem.
            </p>
        </div>

        <div class="mt-6 rounded-2xl bg-slate-50 p-4 border border-slate-200 text-xs text-slate-600 space-y-1">
            <p><strong>Nama:</strong> {{ Auth::user()->fullname ?? Auth::user()->username }}</p>
            <p><strong>Username:</strong> {{ Auth::user()->username }}</p>
        </div>

        @if (session('status'))
            <div role="status" class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.force_change.update') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">Sandi Baru</label>
                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-sm outline-none transition focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-200"
                    placeholder="Minimal 8 karakter">
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-slate-700">Konfirmasi Sandi Baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-sm outline-none transition focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-200"
                    placeholder="Ulangi sandi baru">
            </div>

            @if(!Auth::user()->email)
                <div class="pt-2">
                    <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">
                        Email Pemulihan Pribadi <span class="text-xs font-normal text-slate-500">(Opsional tapi sangat disarankan)</span>
                    </label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-sm outline-none transition focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-200"
                        placeholder="nama@email.com (untuk lupa sandi mandiri nanti)">
                    <p class="mt-1 text-xs text-slate-400">Jika diisi, Anda bisa mereset sandi sendiri via OTP email di kemudian hari.</p>
                </div>
            @endif

            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 px-4 py-3 font-semibold text-white shadow-lg shadow-amber-200 transition hover:from-amber-500 hover:to-amber-600">
                Simpan Sandi Baru & Masuk
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-5 text-center">
            @csrf
            <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-rose-600">
                Batal & Logout
            </button>
        </form>
    </main>
</body>
</html>
