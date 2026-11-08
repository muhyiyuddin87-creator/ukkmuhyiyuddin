<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Class Progress UKK</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-badge.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50/80 via-slate-50 to-teal-50/80 flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200/80">
        <div class="grid md:grid-cols-2">
            <!-- Left Banner: Harmonis dengan Brand SCP -->
            <div class="hidden md:flex flex-col justify-between bg-gradient-to-br from-emerald-950 via-teal-950 to-slate-900 p-10 text-white relative overflow-hidden">
                <!-- Aksen Dekoratif Halus di Background -->
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ asset('images/logo-badge.png') }}" alt="Logo SCP" class="h-12 w-12 shrink-0 rounded-full object-cover">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-emerald-300 font-bold">School Class Progress</p>
                            <span class="text-[11px] text-emerald-100/80 font-semibold">SCP UKK System</span>
                        </div>
                    </div>
                    <h1 class="mt-8 text-3xl sm:text-4xl font-extrabold leading-tight tracking-tight text-white">Sistem Informasi Sekolah</h1>
                    <p class="mt-3 text-sm text-emerald-100/90 leading-relaxed">Platform manajemen pembelajaran cerdas terpadu untuk monitoring jadwal, presensi selfie real-time, jurnal mengajar, dan progres kelas siswa.</p>

                    <!-- Fitur Utama Pill Cards -->
                    <div class="mt-8 space-y-3">
                        <div class="flex items-center gap-3 rounded-xl bg-white/5 border border-white/10 px-4 py-2.5 backdrop-blur-xs">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <span class="text-xs font-medium text-emerald-100">Presensi Real-Time & Bukti Selfie</span>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl bg-white/5 border border-white/10 px-4 py-2.5 backdrop-blur-xs">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-500/20 text-teal-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="text-xs font-medium text-emerald-100">Jadwal Pembelajaran Putaran Siklus</span>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl bg-white/5 border border-white/10 px-4 py-2.5 backdrop-blur-xs">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/20 text-sky-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-medium text-emerald-100">Jurnal Mengajar & Rekap Presensi Guru</span>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-6 border-t border-white/10 text-xs text-emerald-300/80 flex items-center justify-between">
                    <span>© {{ date('Y') }} School Class Progress</span>
                    <span class="font-semibold text-emerald-400">v2.0 UKK</span>
                </div>
            </div>

            <!-- Right: Form Login -->
            <div class="p-8 sm:p-10 flex flex-col justify-center">
                <div class="text-center mb-8">
                    <img src="{{ asset('images/logo-badge.png') }}" alt="Logo SCP" class="mx-auto h-20 w-20 rounded-full object-cover hover:scale-105 transition-transform duration-200">
                    <h2 class="mt-4 text-3xl font-extrabold text-slate-800 tracking-tight">Masuk ke akun</h2>
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
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-xs outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-200" placeholder="Masukkan username">
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                        <input id="password" name="password" type="password" required
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-3 text-slate-800 shadow-xs outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-200" placeholder="Masukkan password">
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:from-emerald-700 hover:to-teal-800 active:scale-98">
                        Login
                    </button>
                </form>

                <div class="mt-4 text-right">
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                        Lupa sandi?
                    </a>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
