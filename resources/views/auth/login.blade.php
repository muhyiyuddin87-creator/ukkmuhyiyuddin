<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Class Progress UKK</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-100 via-white to-indigo-100 flex items-center justify-center p-6">
    <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200">
        <div class="grid md:grid-cols-2">
            <div class="hidden md:flex flex-col justify-start bg-gradient-to-br from-sky-700 via-blue-700 to-indigo-800 p-10 text-white">
                <div>
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo SCP" class="h-12 w-12 rounded-full border-2 border-white/40 object-cover shadow-lg">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-sky-200 font-bold">School Class Progress</p>
                            <span class="text-[11px] text-sky-100/90 font-semibold">SCP UKK System</span>
                        </div>
                    </div>
                    <h1 class="mt-6 text-4xl font-bold leading-tight">Sistem Informasi Sekolah</h1>
                    <p class="mt-3 text-sm text-sky-100/90 leading-relaxed">Platform manajemen pembelajaran cerdas terpadu untuk monitoring jadwal, presensi selfie real-time, jurnal mengajar, dan progres kelas siswa.</p>
                </div>
            </div>

            <div class="p-8 md:p-10">
                <div class="text-center mb-8">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SCP" class="mx-auto h-20 w-20 rounded-full border-2 border-slate-200/90 object-cover shadow-md hover:scale-105 transition-transform">
                    <h2 class="mt-4 text-3xl font-bold text-slate-800">Masuk ke akun</h2>
                    <p class="mt-1.5 text-sm text-slate-500">School Class Progress (SCP) System</p>
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
