<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventaris FTI') - FTI UKSW</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col antialiased bg-[#0B0F19] text-slate-100 selection:bg-blue-600 selection:text-white">
    <!-- Main Top Navigation -->
    <header class="app-header sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <!-- Brand & Links -->
                <div class="flex items-center gap-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 font-bold tracking-tight text-white hover:opacity-90 transition">
                        <div class="w-7 h-7 rounded-md bg-blue-600 flex items-center justify-center text-white text-xs font-black shadow-md shadow-blue-600/30">
                            FTI
                        </div>
                        <div class="text-sm">
                            <span class="text-white">Inventaris</span>
                            <span class="text-blue-400">UKSW</span>
                        </div>
                    </a>

                    @auth
                    <nav class="hidden md:flex items-center gap-1 text-xs font-medium text-slate-300">
                        @php $role = auth()->user()->role; @endphp

                        @if(in_array($role, ['SUPERADMIN', 'ADMIN']))
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-md transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'hover:text-white hover:bg-slate-800/60' }}">Dashboard</a>
                        @elseif($role === 'TEKNISI')
                            <a href="{{ route('petugas.dashboard') }}" class="px-3 py-1.5 rounded-md transition {{ request()->routeIs('petugas.dashboard') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'hover:text-white hover:bg-slate-800/60' }}">Dashboard</a>
                        @elseif($role === 'UNIVERSITAS')
                            <a href="{{ route('universitas.dashboard') }}" class="px-3 py-1.5 rounded-md transition {{ request()->routeIs('universitas.dashboard') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'hover:text-white hover:bg-slate-800/60' }}">Dashboard</a>
                        @endif

                        <a href="{{ route('barang.index') }}" class="px-3 py-1.5 rounded-md transition {{ request()->routeIs('barang.*') && !request()->routeIs('barang.recentlyDeleted') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'hover:text-white hover:bg-slate-800/60' }}">Aset & Barang</a>

                        @if(in_array($role, ['SUPERADMIN', 'ADMIN', 'TEKNISI']))
                            <a href="{{ route('peminjaman.index') }}" class="px-3 py-1.5 rounded-md transition {{ request()->routeIs('peminjaman.*') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'hover:text-white hover:bg-slate-800/60' }}">Peminjaman</a>
                        @endif

                        <a href="{{ route('pengeluaran.index') }}" class="px-3 py-1.5 rounded-md transition {{ request()->routeIs('pengeluaran.*') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'hover:text-white hover:bg-slate-800/60' }}">Cashflow</a>

                        @if(in_array($role, ['SUPERADMIN', 'ADMIN', 'TEKNISI']))
                            <a href="{{ route('log.index') }}" class="px-3 py-1.5 rounded-md transition {{ request()->routeIs('log.*') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'hover:text-white hover:bg-slate-800/60' }}">Log Aktivitas</a>
                        @endif

                        @if(in_array($role, ['SUPERADMIN', 'ADMIN']))
                            <div class="relative group">
                                <button class="px-3 py-1.5 rounded-md transition flex items-center gap-1 {{ request()->routeIs('master.*', 'users.*', 'barang.recentlyDeleted') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'hover:text-white hover:bg-slate-800/60' }}">
                                    Pengaturan
                                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div class="absolute left-0 mt-1 w-44 bg-[#111827] border border-slate-800 rounded-lg shadow-xl py-1.5 hidden group-hover:block z-50">
                                    <a href="{{ route('master.kategori') }}" class="block px-3.5 py-1.5 text-xs text-slate-300 hover:text-white hover:bg-blue-600/20">Kategori Barang</a>
                                    <a href="{{ route('master.ruangan') }}" class="block px-3.5 py-1.5 text-xs text-slate-300 hover:text-white hover:bg-blue-600/20">Daftar Ruangan</a>
                                    <a href="{{ route('users.index') }}" class="block px-3.5 py-1.5 text-xs text-slate-300 hover:text-white hover:bg-blue-600/20">Kelola Akun Tim</a>
                                    <div class="border-t border-slate-800 my-1"></div>
                                    <a href="{{ route('barang.recentlyDeleted') }}" class="block px-3.5 py-1.5 text-xs text-amber-400 hover:bg-amber-500/10">Recently Deleted</a>
                                </div>
                            </div>
                        @endif
                    </nav>
                    @endauth
                </div>

                <!-- Right Profile / Auth Area -->
                <div class="flex items-center gap-3">
                    @auth
                        <div class="flex items-center gap-2.5">
                            <span class="pill-blue text-[10px] uppercase font-mono tracking-wider font-semibold">
                                {{ auth()->user()->role }}
                            </span>
                            <span class="text-xs text-slate-300 font-medium hidden sm:inline-block">
                                {{ auth()->user()->name }}
                            </span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-400 rounded-md hover:bg-slate-800 transition" title="Keluar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn-secondary text-xs">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-blue text-xs">Daftar Akun</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Subheader / Location indicator -->
    @auth
    <div class="bg-[#0E1424] border-b border-slate-800/80 px-4 sm:px-6 lg:px-8 py-2">
        <div class="max-w-7xl mx-auto flex items-center justify-between text-[11px] text-slate-400">
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span class="font-medium text-slate-300">Lab FTI UKSW</span>
                <span>&bull;</span>
                <span>Koneksi Database: Aktif</span>
            </div>
            <div class="font-mono text-slate-500">
                {{ date('l, d F Y') }}
            </div>
        </div>
    </div>
    @endauth

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Flash Notifications -->
        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-lg bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white text-base leading-none">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-3.5 rounded-lg bg-rose-950/40 border border-rose-500/30 text-rose-300 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white text-base leading-none">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-5 p-3.5 rounded-lg bg-amber-950/40 border border-amber-500/30 text-amber-300 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-amber-400 hover:text-white text-base leading-none">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-5 border-t border-slate-800/80 text-center text-xs text-slate-500">
        Fakultas Teknologi Informasi &bull; Universitas Kristen Satya Wacana
    </footer>
</body>
</html>
