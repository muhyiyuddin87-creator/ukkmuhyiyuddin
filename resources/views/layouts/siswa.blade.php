<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', ((int) (Auth::user()->role_id ?? 0) === 6 || str_ends_with(strtolower(Auth::user()->username ?? ''), 'p') ? 'Orang Tua - School System' : 'Siswa - School System'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-badge.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #ecfdf5 0%, #f8fafc 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Batas tinggi tabel maks ~10 baris data dengan scroll internal & sticky header */
        .overflow-x-auto:has(table),
        .table-scroll-container {
            max-height: 520px;
            overflow-y: auto;
            overflow-x: auto;
            position: relative;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        .overflow-x-auto:has(table:not(.official-schedule-table)) table:not(.official-schedule-table),
        .table-scroll-container table:not(.official-schedule-table) {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-scroll-container thead:not(:has(.official-schedule-table th)) {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #f8fafc;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .table-scroll-container thead:not(:has(.official-schedule-table th)) th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: inherit;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Konsep Resmi Tabel Jadwal Bergaris Tegas (Sesuai PDF/Cetak) */
        .official-schedule-table {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        .official-schedule-table th,
        .official-schedule-table td {
            border: 1px solid #1e293b !important;
        }

        .official-schedule-table thead th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #0f172a !important;
        }

        /* Scrollbar kustom halus */
        .overflow-x-auto:has(table)::-webkit-scrollbar,
        .table-scroll-container::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .overflow-x-auto:has(table)::-webkit-scrollbar-track,
        .table-scroll-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .overflow-x-auto:has(table)::-webkit-scrollbar-thumb,
        .table-scroll-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .overflow-x-auto:has(table)::-webkit-scrollbar-thumb:hover,
        .table-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Unified Form Controls */
        .form-control-standard {
            height: 40px;
            font-size: 0.875rem;
            line-height: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            padding: 0.5rem 0.75rem;
            background-color: #ffffff;
            transition: all 0.15s ease-in-out;
            box-sizing: border-box;
        }
        .form-control-standard:focus {
            border-color: #059669;
            outline: none;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }

        .table-input {
            height: 36px;
            font-size: 0.8125rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            padding: 0.375rem 0.625rem;
            background-color: #ffffff;
            transition: all 0.15s ease-in-out;
            box-sizing: border-box;
        }
        .table-input:focus {
            border-color: #059669;
            outline: none;
            box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.15);
        }

        .btn-standard {
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.15s ease-in-out;
            box-sizing: border-box;
        }

        .btn-table {
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.15s ease-in-out;
            box-sizing: border-box;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <!-- Mobile Top Navigation Bar -->
    <header class="lg:hidden sticky top-0 z-40 flex items-center justify-between bg-emerald-950 px-4 py-3 text-white shadow-md">
        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleSidebar()" aria-label="Buka Menu" class="rounded-xl bg-emerald-900 p-2 text-emerald-200 hover:bg-emerald-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <img src="{{ asset('images/logo-badge.png') }}" alt="Logo SCP" class="h-9 w-9 rounded-full object-cover shadow-xs">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-300">SCP UKK</span>
                <h1 class="text-sm font-bold leading-tight text-white">{{ (int) (Auth::user()->role_id ?? 0) === 6 || str_ends_with(strtolower(Auth::user()->username ?? ''), 'p') ? 'Portal Orang Tua' : 'Portal Siswa' }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('profile') }}" class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-xs font-bold text-white shadow" title="Profil">
                {{ strtoupper(substr($user->username ?? Auth::user()->username ?? 'U', 0, 1)) }}
            </a>
        </div>
    </header>

    <div class="flex min-h-screen">
        <!-- Backdrop for mobile drawer -->
        <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-xs transition-opacity lg:hidden"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 shrink-0 -translate-x-full flex-col justify-between overflow-y-auto bg-emerald-950 p-6 text-white transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:min-h-screen">
            <div>
                <div class="mb-8 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-badge.png') }}" alt="Logo SCP" class="h-11 w-11 shrink-0 rounded-full object-cover shadow-md">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-[0.25em] text-emerald-300">SCP UKK</div>
                            <h2 class="mt-0.5 text-xl font-bold tracking-tight text-white">{{ (int) (Auth::user()->role_id ?? 0) === 6 || str_ends_with(strtolower(Auth::user()->username ?? ''), 'p') ? 'Orang Tua' : 'Siswa' }}</h2>
                        </div>
                    </div>
                    <button type="button" onclick="toggleSidebar()" aria-label="Tutup Menu" class="rounded-lg p-1.5 text-emerald-300 hover:bg-emerald-900 hover:text-white lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-1.5 text-sm font-medium">
                    <a href="{{ route('dashboard.role', ['role' => 'siswa']) }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('dashboard*') ? 'bg-emerald-600 font-semibold text-white shadow' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'dashboard'])
                        Dashboard
                    </a>
                    <a href="{{ route('siswa.jadwal') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('siswa.jadwal*') ? 'bg-emerald-600 font-semibold text-white shadow' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'schedule'])
                        Jadwal Belajar
                    </a>
                    <a href="{{ route('siswa.absensi') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('siswa.absensi*') ? 'bg-emerald-600 font-semibold text-white shadow' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'attendance'])
                        Riwayat Kehadiran
                    </a>
                    <a href="{{ route('siswa.izin') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('siswa.izin*') ? 'bg-emerald-600 font-semibold text-white shadow' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'leave'])
                        Pengajuan Izin
                    </a>
                </nav>
            </div>

            <div class="mt-8 rounded-2xl bg-emerald-900/80 p-4 border border-emerald-800/60">
                <p class="text-[10px] uppercase tracking-[0.25em] text-emerald-300">{{ (int) (Auth::user()->role_id ?? 0) === 6 ? 'Akun Orang Tua' : 'Akun Siswa' }}</p>
                <div class="mt-2 text-base font-semibold truncate">{{ $user->fullname ?? (Auth::user()->fullname ?? Auth::user()->username ?? 'Siswa') }}</div>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('profile') }}" class="w-1/2 text-center rounded-lg bg-emerald-600 px-2 py-2 text-xs font-semibold text-white hover:bg-emerald-500 transition">
                        Edit Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="w-1/2">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-rose-600 px-2 py-2 text-xs font-semibold text-white hover:bg-rose-500 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    @include('partials.confirm_crud')

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar || !backdrop) return;
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
