<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-100 via-white to-indigo-100 flex items-center justify-center p-6">
    <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200">
        <div class="grid md:grid-cols-2">
            <div class="hidden md:flex flex-col justify-center bg-gradient-to-br from-sky-700 via-blue-700 to-indigo-800 p-10 text-white">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-sky-100">School System</p>
                    <h1 class="mt-4 text-4xl font-bold">Sistem Informasi Sekolah</h1>
                </div>
            </div>

            <div class="p-8 md:p-10">
                <div class="text-center mb-8">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 shadow-sm">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h2 class="mt-5 text-3xl font-bold text-slate-800">Masuk ke akun</h2>
                    <p class="mt-2 text-sm text-slate-500">Silakan login untuk mengakses dashboard</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif
                @if (session('status'))
                    <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Username</label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-sm outline-none transition focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-200" placeholder="Masukkan username">
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                        <input id="password" name="password" type="password" required
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-sm outline-none transition focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-200" placeholder="Masukkan password">
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-sky-200 transition hover:from-sky-500 hover:to-indigo-500">
                        Login
                    </button>
                </form>

                <div class="mt-4 text-right">
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-sky-700 hover:text-indigo-700 hover:underline">
                        Lupa sandi?
                    </a>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
