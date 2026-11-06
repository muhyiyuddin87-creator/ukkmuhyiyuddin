<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Waka Kurikulum - School System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #eef6ff 0%, #f8fafc 100%);
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

        .overflow-x-auto:has(table) table,
        .table-scroll-container table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .overflow-x-auto:has(table) thead,
        .table-scroll-container thead {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #f8fafc;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .overflow-x-auto:has(table) thead th,
        .table-scroll-container thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: inherit;
            border-bottom: 1px solid #e2e8f0;
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
            border-color: #0284c7;
            outline: none;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
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
            border-color: #0284c7;
            outline: none;
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.15);
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
    <header class="lg:hidden sticky top-0 z-40 flex items-center justify-between bg-slate-900 px-4 py-3 text-white shadow-md">
        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleSidebar()" aria-label="Buka Menu" class="rounded-xl bg-slate-800 p-2 text-slate-300 hover:bg-slate-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-sky-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-sky-400">School System</span>
                <h1 class="text-sm font-bold leading-tight text-white">Waka Kurikulum</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('profile') }}" class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-600 text-xs font-bold text-white shadow" title="Profil">
                {{ strtoupper(substr($user->username ?? Auth::user()->username ?? 'W', 0, 1)) }}
            </a>
        </div>
    </header>

    <div class="flex min-h-screen">
        <!-- Backdrop for mobile drawer -->
        <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-xs transition-opacity lg:hidden"></div>

        <!-- Sidebar (Drawer on mobile, static on lg) -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 shrink-0 -translate-x-full flex-col justify-between overflow-y-auto bg-slate-900 p-6 text-white transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:min-h-screen">
            <div>
                <!-- Brand & Close button on Mobile -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-[0.3em] text-sky-300">School System</div>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-white">Waka</h2>
                    </div>
                    <button type="button" onclick="toggleSidebar()" aria-label="Tutup Menu" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-1.5">
                    <a href="{{ route('dashboard.role', ['role' => 'waka']) }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('dashboard*') && !request()->routeIs('waka.*') ? 'bg-sky-600 font-semibold text-white shadow' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'dashboard'])
                        Dashboard
                    </a>
                    <a href="{{ route('waka.siswa') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('waka.siswa*') ? 'bg-sky-600 font-semibold text-white shadow' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'students'])
                        Data Siswa
                    </a>
                    <a href="{{ route('waka.guru') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('waka.guru*') ? 'bg-sky-600 font-semibold text-white shadow' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'teachers'])
                        Data Guru
                    </a>
                    <a href="{{ route('waka.ortu') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('waka.ortu*') ? 'bg-sky-600 font-semibold text-white shadow' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'parents'])
                        Data Orang Tua
                    </a>
                    <a href="{{ route('waka.accounts') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('waka.accounts*') ? 'bg-sky-600 font-semibold text-white shadow' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'accounts'])
                        Kelola Akun & Role
                    </a>
                    <a href="{{ route('waka.jadwal') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('waka.jadwal*') ? 'bg-sky-600 font-semibold text-white shadow' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'schedule'])
                        Jadwal
                    </a>
                    <a href="{{ route('waka.laporan') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('waka.laporan*') ? 'bg-sky-600 font-semibold text-white shadow' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'reports'])
                        Laporan
                    </a>
                </nav>
            </div>

            <!-- Profile & Logout Box in Sidebar -->
            <div class="mt-8 rounded-2xl bg-slate-800/80 p-4 border border-slate-700/50">
                <p class="text-[10px] uppercase tracking-[0.25em] text-slate-400">Akun Masuk</p>
                <div class="mt-2 text-base font-semibold truncate">{{ $user->fullname ?? (Auth::user()->fullname ?? Auth::user()->username ?? 'Waka') }}</div>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('profile') }}" class="w-1/2 text-center rounded-lg bg-sky-600 px-2 py-2 text-xs font-semibold text-white hover:bg-sky-500 transition">
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
