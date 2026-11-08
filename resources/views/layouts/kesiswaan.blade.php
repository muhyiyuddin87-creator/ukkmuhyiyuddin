@php
    $currentUserId = (int) (Auth::user()->id ?? 0);
    $bkClassesList = \Illuminate\Support\Facades\Schema::hasTable('user_bk_classes')
        ? \Illuminate\Support\Facades\DB::table('user_bk_classes')
            ->join('school_classes', 'user_bk_classes.school_class_id', '=', 'school_classes.id')
            ->where('user_bk_classes.user_id', $currentUserId)
            ->pluck('school_classes.name')
        : collect();
    $isBk = $bkClassesList->isNotEmpty();
    $hasTeachingSchedules = \Illuminate\Support\Facades\Schema::hasTable('schedules')
        ? \Illuminate\Support\Facades\DB::table('schedules')->where('teacher_id', $currentUserId)->exists()
        : false;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kesiswaan - School System')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #fff7ed 0%, #f8fafc 100%);
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
            border-color: #d97706;
            outline: none;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15);
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
            border-color: #d97706;
            outline: none;
            box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.15);
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
    <header class="lg:hidden sticky top-0 z-40 flex items-center justify-between bg-amber-950 px-4 py-3 text-white shadow-md">
        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleSidebar()" aria-label="Buka Menu" class="rounded-xl bg-amber-900 p-2 text-amber-200 hover:bg-amber-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <img src="{{ asset('images/logo.png') }}" alt="Logo SCP" class="h-9 w-9 rounded-full border border-amber-400/40 object-cover shadow-xs">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-amber-300">SCP UKK</span>
                <h1 class="text-sm font-bold leading-tight text-white">Kesiswaan</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('profile') }}" class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-xs font-bold text-white shadow" title="Profil">
                {{ strtoupper(substr($user->username ?? Auth::user()->username ?? 'K', 0, 1)) }}
            </a>
        </div>
    </header>

    <div class="flex min-h-screen">
        <!-- Backdrop for mobile drawer -->
        <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-xs transition-opacity lg:hidden"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 shrink-0 -translate-x-full flex-col justify-between overflow-y-auto bg-amber-950 p-6 text-white transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:min-h-screen">
            <div>
                <div class="mb-8 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo SCP" class="h-11 w-11 shrink-0 rounded-full border-2 border-amber-400/50 object-cover shadow-md">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-[0.25em] text-amber-200">SCP UKK</div>
                            <h2 class="mt-0.5 text-xl font-bold tracking-tight text-white">Kesiswaan</h2>
                        </div>
                    </div>
                    <button type="button" onclick="toggleSidebar()" aria-label="Tutup Menu" class="rounded-lg p-1.5 text-amber-300 hover:bg-amber-900 hover:text-white lg:hidden">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-1.5 text-sm font-medium">
                    <a href="{{ route('dashboard.role', ['role' => 'kesiswaan']) }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('dashboard*') ? 'bg-amber-600 font-semibold text-white shadow' : 'text-amber-100 hover:bg-amber-900 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'dashboard'])
                        Dashboard
                    </a>
                    <a href="{{ route('kesiswaan.izin') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('kesiswaan.izin*') ? 'bg-amber-600 font-semibold text-white shadow' : 'text-amber-100 hover:bg-amber-900 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'leave'])
                        Riwayat Izin Siswa
                    </a>
                    <a href="{{ route('guru.jadwal') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('guru.jadwal*') ? 'bg-amber-600 font-semibold text-white shadow' : 'text-amber-100 hover:bg-amber-900 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'schedule'])
                        Jadwal Mengajar
                    </a>
                    <a href="{{ route('guru.jurnal') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('guru.jurnal*') ? 'bg-amber-600 font-semibold text-white shadow' : 'text-amber-100 hover:bg-amber-900 hover:text-white' }}">
                        @include('partials.waka_nav_icon', ['icon' => 'journal'])
                        Isi Jurnal
                    </a>

                    @if($isBk)
                        <div class="pt-4 pb-1">
                            <span class="px-4 text-[10px] font-bold uppercase tracking-wider text-amber-300">Guru BK</span>
                        </div>
                        <div class="rounded-xl bg-amber-900/60 p-3 mx-2 border border-emerald-500/20">
                            <p class="text-[11px] font-bold text-emerald-300 mb-1">Kelas Bimbingan ({{ count($bkClassesList) }}):</p>
                            <div class="flex flex-wrap gap-1 max-h-32 overflow-y-auto">
                                @foreach($bkClassesList as $cName)
                                    <span class="rounded bg-amber-950 px-1.5 py-0.5 text-[10px] font-medium text-emerald-200 border border-emerald-400/30">{{ $cName }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </nav>
            </div>

            <div class="mt-8 rounded-2xl bg-amber-900/80 p-4 border border-amber-800/60">
                <p class="text-[10px] uppercase tracking-[0.25em] text-amber-200">Akun</p>
                <div class="mt-2 text-base font-semibold truncate">{{ $user->fullname ?? (Auth::user()->fullname ?? Auth::user()->username ?? 'Kesiswaan') }}</div>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('profile') }}" class="w-1/2 text-center rounded-lg bg-amber-600 px-2 py-2 text-xs font-semibold text-white hover:bg-amber-500 transition">
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
