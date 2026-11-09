<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Class Progress (SCP) - Sistem Informasi Sekolah Terpadu</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-badge.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .hero-gradient {
            background: radial-gradient(circle at 10% 20%, rgba(16, 185, 129, 0.12) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(13, 148, 136, 0.12) 0%, transparent 40%),
                        linear-gradient(to bottom, #f8fafc, #f1f5f9);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-brand-500 selection:text-white">

    <!-- 1. HEADER & NAVBAR -->
    <header class="fixed top-0 left-0 right-0 z-50 glass-nav border-b border-slate-200/70 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <a href="{{ url('/') }}" class="flex items-center gap-3.5 group">
                    <img src="{{ asset('images/logo-badge.png') }}" alt="Logo SCP" class="h-11 w-11 rounded-full object-cover shadow-sm group-hover:scale-105 transition-transform duration-200">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-lg font-extrabold text-slate-900 tracking-tight group-hover:text-brand-600 transition-colors">School Class Progress</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-brand-100 text-brand-800 rounded-full tracking-wide">SCP</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Sistem Informasi Sekolah & KBM Digital</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#beranda" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">Beranda</a>
                    <a href="#fitur" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">Fitur Unggulan</a>
                    <a href="#statistik" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">Statistik</a>
                    <a href="#alur" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">Alur Presensi</a>
                    <a href="#portal" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors">Portal Masuk</a>
                </nav>

                <!-- Action Button -->
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-teal-700 hover:from-brand-700 hover:to-teal-800 text-white text-sm font-bold shadow-md shadow-brand-600/20 hover:shadow-lg hover:shadow-brand-600/30 transition-all duration-200 active:scale-95">
                        <span>Masuk ke Akun</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden">
                    <button type="button" id="mobile-menu-btn" class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none" aria-label="Toggle Menu">
                        <svg id="menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200/80 bg-white/95 px-4 pt-3 pb-6 space-y-3 shadow-xl">
            <a href="#beranda" class="mobile-link block px-3 py-2 rounded-lg text-base font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600">Beranda</a>
            <a href="#fitur" class="mobile-link block px-3 py-2 rounded-lg text-base font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600">Fitur Unggulan</a>
            <a href="#statistik" class="mobile-link block px-3 py-2 rounded-lg text-base font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600">Statistik</a>
            <a href="#alur" class="mobile-link block px-3 py-2 rounded-lg text-base font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600">Alur Presensi</a>
            <a href="#portal" class="mobile-link block px-3 py-2 rounded-lg text-base font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600">Portal Masuk</a>
            <div class="pt-2">
                <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-teal-700 text-white font-bold shadow-md">
                    <span>Masuk ke Akun</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <!-- 2. HERO SECTION -->
    <section id="beranda" class="relative pt-32 pb-20 md:pt-40 md:pb-28 hero-gradient overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                <!-- Left Text Content -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <!-- Status Chip -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-brand-200/80 shadow-xs">
                        <span class="flex h-2.5 w-2.5 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-600"></span>
                        </span>
                        <span class="text-xs font-bold text-brand-900 tracking-wide">
                            Putaran {{ $activeCycle ?? 1 }} Aktif &bull; Semester Ganjil 2026/2027
                        </span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                        Transformasi Digital <span class="bg-gradient-to-r from-brand-600 via-teal-600 to-emerald-700 bg-clip-text text-transparent">Presensi, Jadwal & KBM</span> Sekolah
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Platform cerdas terpadu untuk monitoring presensi selfie real-time siswa, penjadwalan blok fleksibel (Putaran 1 & 2), pengisian jurnal mengajar guru, hingga persetujuan izin kesiswaan dalam satu sistem terintegrasi.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-gradient-to-r from-brand-600 to-teal-700 hover:from-brand-700 hover:to-teal-800 text-white font-bold text-base shadow-lg shadow-brand-600/25 hover:shadow-xl hover:shadow-brand-600/35 transition-all duration-200 active:scale-95">
                            <span>Buka Portal Login</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#fitur" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold text-base shadow-xs hover:border-slate-400 transition-colors">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                            <span>Jelajahi Fitur</span>
                        </a>
                    </div>

                    <!-- Trust Points / Highlights -->
                    <div class="pt-6 border-t border-slate-200/80 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-slate-500 font-medium">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Presensi Selfie Anti-Titip Absen</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Sistem Blok SMK Teruji</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Rekapitulasi Otomatis & Cepat</span>
                        </div>
                    </div>
                </div>

                <!-- Right Visual: Interactive System Showcase Card -->
                <div class="lg:col-span-5 relative">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <!-- Glow Backdrops -->
                        <div class="absolute -top-6 -left-6 w-72 h-72 bg-brand-400/20 rounded-full filter blur-3xl pointer-events-none"></div>
                        <div class="absolute -bottom-6 -right-6 w-72 h-72 bg-teal-400/20 rounded-full filter blur-3xl pointer-events-none"></div>

                        <!-- Main Glass Device Card -->
                        <div class="relative bg-white/95 rounded-3xl p-6 sm:p-7 shadow-2xl border border-slate-200/90 space-y-5">
                            <!-- Card Header -->
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-brand-100 flex items-center justify-center text-brand-700 font-bold">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-sm font-bold text-slate-800">Monitoring Kelas Real-Time</h2>
                                        <p class="text-[11px] text-slate-400">Status Pembelajaran Hari Ini</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                    Live
                                </span>
                            </div>

                            <!-- Mockup Widget 1: Presensi Selfie Siswa -->
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-600 flex items-center justify-center text-white">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">Presensi Mandiri Siswa</p>
                                        <p class="text-[11px] text-slate-500">Verifikasi Kamera & Waktu Real-Time</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-brand-100 text-brand-800 rounded-md">Hadir Tepat Waktu</span>
                            </div>

                            <!-- Mockup Widget 2: Jurnal KBM Guru -->
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-teal-100 flex items-center justify-center text-teal-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">Jurnal Mengajar Digital</p>
                                        <p class="text-[11px] text-slate-500">Materi & Rekap Kehadiran Kelas</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-teal-100 text-teal-800 rounded-md">Terisi Lengkap</span>
                            </div>

                            <!-- Mockup Widget 3: Status Izin Kesiswaan -->
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">Verifikasi Izin & Disposisi</p>
                                        <p class="text-[11px] text-slate-500">Persetujuan Kesiswaan Online</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 rounded-md">Approved</span>
                            </div>

                            <!-- Direct Login Quick Link -->
                            <div class="pt-2">
                                <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors">
                                    <span>Masuk ke Dashboard Sistem</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. STATISTIK SEKOLAH REAL-TIME (DARI DATABASE) -->
    <section id="statistik" class="py-16 bg-white border-y border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-extrabold uppercase tracking-widest text-brand-600">Transparansi & Skalabilitas</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Data Statistik Sistem Aktif</h2>
                <p class="text-sm text-slate-500 mt-2">Gambaran komprehensif ekosistem pembelajaran yang dikelola secara terpusat oleh School Class Progress.</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat 1: Siswa -->
                <div class="p-6 rounded-2xl bg-gradient-to-br from-emerald-50/50 to-slate-50 border border-emerald-100/80 hover:shadow-lg transition-all duration-200">
                    <div class="h-12 w-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($totalSiswa ?? 1711, 0, ',', '.') }}
                    </div>
                    <p class="text-sm font-bold text-slate-700 mt-1">Siswa Terdata</p>
                    <p class="text-xs text-slate-500 mt-0.5">Tingkat X, XI, & XII</p>
                </div>

                <!-- Stat 2: Guru -->
                <div class="p-6 rounded-2xl bg-gradient-to-br from-teal-50/50 to-slate-50 border border-teal-100/80 hover:shadow-lg transition-all duration-200">
                    <div class="h-12 w-12 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center mb-4 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($totalGuru ?? 93, 0, ',', '.') }}
                    </div>
                    <p class="text-sm font-bold text-slate-700 mt-1">Guru & Pendidik</p>
                    <p class="text-xs text-slate-500 mt-0.5">Pengampu & Wali Kelas</p>
                </div>

                <!-- Stat 3: Rombel / Kelas -->
                <div class="p-6 rounded-2xl bg-gradient-to-br from-cyan-50/50 to-slate-50 border border-cyan-100/80 hover:shadow-lg transition-all duration-200">
                    <div class="h-12 w-12 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center mb-4 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($totalKelas ?? 48, 0, ',', '.') }}
                    </div>
                    <p class="text-sm font-bold text-slate-700 mt-1">Rombongan Belajar</p>
                    <p class="text-xs text-slate-500 mt-0.5">Seluruh Jurusan Keahlian</p>
                </div>

                <!-- Stat 4: Jadwal KBM -->
                <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-100/50 to-slate-50 border border-slate-200 hover:shadow-lg transition-all duration-200">
                    <div class="h-12 w-12 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center mb-4 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($totalJadwal ?? 1363, 0, ',', '.') }}
                    </div>
                    <p class="text-sm font-bold text-slate-700 mt-1">Sesi Jadwal KBM</p>
                    <p class="text-xs text-slate-500 mt-0.5">Rotasi Putaran 1 & Putaran 2</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. FITUR UNGGULAN SISTEM -->
    <section id="fitur" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-brand-600">Kapabilitas Modern</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1">Fitur Cerdas untuk Efisiensi Sekolah</h2>
                <p class="text-base text-slate-600 mt-3">Dirancang khusus untuk memenuhi standar operasional SMK vokasi dengan alur kerja yang mudah, cepat, dan akuntabel.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="h-14 w-14 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-2">Presensi Selfie Real-Time</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Siswa melakukan verifikasi absensi mandiri menggunakan kamera perangkat saat jam pelajaran berlangsung, meminimalisir titip absen secara efektif.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="h-14 w-14 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-2">Jadwal Blok Fleksibel</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Mendukung penuh rotasi sistem blok SMK (Putaran 1 Teori Normada & Putaran 2 Praktik Lab Kejuruan) serta impor jadwal langsung dari FET Timetable & Excel.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="h-14 w-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-2">Jurnal Mengajar Guru Digital</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Bapak/Ibu guru dapat mencatat materi pelajaran, capaian kompetensi, jurnal harian, serta merekap kehadiran siswa per jam tatap muka secara paperless.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="h-14 w-14 rounded-2xl bg-cyan-100 text-cyan-700 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-2">Pengajuan Izin Online</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Pengajuan surat izin sakit atau keperluan siswa dapat diunggah dengan bukti foto/surat dan langsung diverifikasi satu klik oleh tim Kesiswaan.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="h-14 w-14 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-2">Rekapitulasi & Ekspor Laporan</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Data kehadiran harian, mingguan, hingga bulanan dapat diekspor ke format Excel dan PDF siap cetak untuk pelaporan evaluasi waka dan kepala sekolah.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="h-14 w-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900 mb-2">Keamanan & Peran Terisolasi</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Setiap akun memiliki hak akses ketat sesuai perannya (Siswa, Guru, Kesiswaan, dan Waka Kurikulum) dengan enkripsi data yang terlindungi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. ALUR PRESENSI & PENGGUNAAN SISTEM -->
    <section id="alur" class="py-20 bg-white border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-brand-600">Alur Kerja Praktis</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1">Bagaimana Sistem Bekerja Setiap Hari?</h2>
                <p class="text-base text-slate-600 mt-3">Empat langkah mudah dan terintegrasi dari awal jam masuk hingga laporan kesiswaan.</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8 relative">
                <!-- Step 1 -->
                <div class="text-center space-y-4">
                    <div class="relative mx-auto w-16 h-16 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-xl font-extrabold shadow-lg shadow-brand-600/30">
                        1
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Masuk ke Portal</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Siswa dan Guru membuka aplikasi via smartphone atau komputer, kemudian login menggunakan akun masing-masing.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center space-y-4">
                    <div class="relative mx-auto w-16 h-16 rounded-2xl bg-teal-600 text-white flex items-center justify-center text-xl font-extrabold shadow-lg shadow-teal-600/30">
                        2
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Cek Jadwal Aktif</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Sistem otomatis menampilkan jadwal sesi hari ini sesuai putaran rotasi blok yang sedang aktif (Putaran 1 / 2).
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center space-y-4">
                    <div class="relative mx-auto w-16 h-16 rounded-2xl bg-emerald-700 text-white flex items-center justify-center text-xl font-extrabold shadow-lg shadow-emerald-700/30">
                        3
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Absen Selfie & Isi Jurnal</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Siswa melakukan foto selfie kamera untuk presensi, sedangkan guru mengisi jurnal materi dan memvalidasi kehadiran kelas.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="text-center space-y-4">
                    <div class="relative mx-auto w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl font-extrabold shadow-lg shadow-slate-900/30">
                        4
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Monitoring Terpusat</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Tim Kesiswaan dan Waka Kurikulum memantau grafik kehadiran, memproses izin, dan mencetak laporan secara real-time.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. PORTAL PINTU MASUK MULTI-PERAN -->
    <section id="portal" class="py-20 bg-slate-50 border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-brand-600">Pintu Masuk Terpadu</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1">Pilih Portal Berdasarkan Peran Anda</h2>
                <p class="text-base text-slate-600 mt-3">Satu pintu masuk untuk seluruh civitas akademika sekolah dengan antarmuka yang disesuaikan.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Role 1: Siswa -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Portal Siswa</h3>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Akses jadwal harian, lakukan presensi selfie jam pelajaran, pantau histori kehadiran, dan ajukan surat izin sakit online.
                        </p>
                    </div>
                    <div class="pt-6">
                        <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition-colors">
                            <span>Masuk sebagai Siswa</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Role 2: Guru & Wali Kelas -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Guru & Wali Kelas</h3>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Isi jurnal mengajar kelas, pantau daftar absensi siswa bimbingan, catat topik KBM, dan verifikasi kehadiran tatap muka.
                        </p>
                    </div>
                    <div class="pt-6">
                        <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs transition-colors">
                            <span>Masuk sebagai Guru</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Role 3: Kesiswaan -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Portal Kesiswaan</h3>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Verifikasi dan disposisi izin ketidakhadiran siswa, pantau rekapitulasi kedisiplinan, dan ekspor arsip laporan presensi.
                        </p>
                    </div>
                    <div class="pt-6">
                        <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs transition-colors">
                            <span>Masuk Kesiswaan</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Role 4: Waka Kurikulum -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-slate-900 text-white flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Waka Kurikulum</h3>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Manajemen jadwal pelajaran (impor FET/Excel), konfigurasi putaran aktif, pemetaan ruangan, dan kontrol penuh data KBM.
                        </p>
                    </div>
                    <div class="pt-6">
                        <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-colors">
                            <span>Masuk Kurikulum</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. FOOTER -->
    <footer class="bg-slate-950 text-slate-400 py-12 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-slate-800/80">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-badge.png') }}" alt="Logo SCP" class="h-10 w-10 rounded-full object-cover">
                    <div>
                        <p class="text-base font-extrabold text-white">School Class Progress</p>
                        <p class="text-xs text-slate-500">Sistem Informasi Sekolah & KBM Digital Terpadu</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-semibold text-slate-400">
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">Login Portal</a>
                    <a href="{{ route('password.request') }}" class="hover:text-white transition-colors">Lupa Password</a>
                    <a href="#fitur" class="hover:text-white transition-colors">Fitur Unggulan</a>
                    <a href="#portal" class="hover:text-white transition-colors">Role Gateway</a>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} School Class Progress (SCP). Seluruh hak cipta dilindungi.</p>
                <p class="flex items-center gap-1.5">
                    <span>Sistem Informasi Sekolah</span>
                    <span>&bull;</span>
                    <span class="text-brand-400 font-medium">SMK Prestasi</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Mobile Drawer Javascript -->
    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('menu-icon-open');
        const iconClose = document.getElementById('menu-icon-close');
        const mobileLinks = document.querySelectorAll('.mobile-link');

        function toggleMenu() {
            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                iconOpen.classList.add('hidden');
                iconClose.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
                iconOpen.classList.remove('hidden');
                iconClose.classList.add('hidden');
            }
        }

        btn.addEventListener('click', toggleMenu);
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                menu.classList.add('hidden');
                iconOpen.classList.remove('hidden');
                iconClose.classList.add('hidden');
            });
        });
    </script>
</body>
</html>

