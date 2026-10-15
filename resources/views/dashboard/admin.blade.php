@extends('layouts.app')

@section('title', 'Admin Dashboard - Inventaris FTI')

@section('content')
<div class="space-y-5">
    <!-- Action & Context Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Dashboard Pengelola</h1>
            <p class="text-xs text-slate-400 mt-0.5">Monitoring pergerakan aset, peminjaman lab, dan verifikasi akun staf.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('barang.create') }}" class="btn-blue text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Aset Baru
            </a>
            <a href="{{ route('peminjaman.create') }}" class="btn-secondary text-xs">
                Pinjamkan
            </a>
            <a href="{{ route('pengeluaran.create') }}" class="btn-secondary text-xs">
                Catat Belanja
            </a>
        </div>
    </div>

    <!-- KPI Summary Bar (Asymmetric hierarchy) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="panel-card p-4 border-l-2 border-l-blue-500">
            <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Aset Aktif</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($totalAset) }}</div>
            <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                <span class="text-emerald-400 font-medium">{{ $asetTersedia }} ada</span> &bull;
                <span class="text-amber-400 font-medium">{{ $asetDipinjam }} dipinjam</span>
            </div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-emerald-500">
            <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Peminjaman Berjalan</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($peminjamanAktif) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">
                @if($peminjamanTerlambat > 0)
                    <span class="text-rose-400 font-semibold">{{ $peminjamanTerlambat }} telat kembali</span>
                @else
                    <span class="text-slate-400">Tidak ada tunggakan</span>
                @endif
            </div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-amber-500">
            <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Perlu Servis</div>
            <div class="text-2xl font-black text-white mt-1">{{ $kondisiDistribusi['RUSAK_RINGAN'] + $kondisiDistribusi['RUSAK_BERAT'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">
                <span class="text-amber-400">{{ $kondisiDistribusi['RUSAK_RINGAN'] }} ringan</span> &bull;
                <span class="text-rose-400">{{ $kondisiDistribusi['RUSAK_BERAT'] }} berat</span>
            </div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-slate-600">
            <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Kas Keluar</div>
            <div class="text-xl font-bold text-slate-200 mt-1 font-mono">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Pengadaan & perbaikan</div>
        </div>
    </div>

    <!-- Asymmetrical Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Left Column: Operational Focus (2 cols) -->
        <div class="lg:col-span-2 space-y-5">
            <!-- Overdue or Active Loans -->
            <div class="panel-card p-5">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <h2 class="text-sm font-bold text-white">Peminjaman Lab Terbaru</h2>
                    </div>
                    <a href="{{ route('peminjaman.index') }}" class="text-xs text-blue-400 hover:text-blue-300 font-medium">Buka Semua Peminjaman &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Peminjam</th>
                                <th>Ruangan</th>
                                <th>Batas Kembali</th>
                                <th class="text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPeminjaman as $p)
                                @php $isTelat = $p->checkIsOverdue(); @endphp
                                <tr>
                                    <td>
                                        <div class="font-semibold text-slate-200">{{ $p->barang->nama_barang ?? 'Aset' }}</div>
                                        <div class="font-mono text-[10px] text-blue-400">{{ $p->barang->label }}</div>
                                    </td>
                                    <td class="text-slate-300">{{ $p->nama_peminjam }}</td>
                                    <td class="text-slate-400">{{ $p->ruangan_tujuan }}</td>
                                    <td>
                                        <span class="text-xs {{ $isTelat && $p->status === 'AKTIF' ? 'text-rose-400 font-semibold' : 'text-slate-300' }}">
                                            {{ $p->deadline_kembali->format('d/m H:i') }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @if($p->status === 'AKTIF')
                                            @if($isTelat)
                                                <span class="pill-red text-[10px]">Telat</span>
                                            @else
                                                <span class="pill-yellow text-[10px]">Dipinjam</span>
                                            @endif
                                        @else
                                            <span class="pill-green text-[10px]">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-500 text-xs">Belum ada transaksi peminjaman tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kondisi dan Distribusi Lab -->
            <div class="panel-card p-5">
                <h2 class="text-sm font-bold text-white mb-3">Distribusi Kondisi Fisik</h2>
                @php
                    $totalKondisi = max($totalAset, 1);
                    $pctBaik = round(($kondisiDistribusi['BAIK'] / $totalKondisi) * 100);
                    $pctRingan = round(($kondisiDistribusi['RUSAK_RINGAN'] / $totalKondisi) * 100);
                    $pctBerat = round(($kondisiDistribusi['RUSAK_BERAT'] / $totalKondisi) * 100);
                @endphp
                <div class="space-y-2">
                    <div class="h-3 w-full bg-slate-900 rounded-full overflow-hidden flex">
                        <div style="width: {{ $pctBaik }}%" class="bg-emerald-500" title="Baik: {{ $pctBaik }}%"></div>
                        <div style="width: {{ $pctRingan }}%" class="bg-amber-500" title="Rusak Ringan: {{ $pctRingan }}%"></div>
                        <div style="width: {{ $pctBerat }}%" class="bg-rose-500" title="Rusak Berat: {{ $pctBerat }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Baik: {{ $kondisiDistribusi['BAIK'] }} ({{ $pctBaik }}%)</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Rusak Ringan: {{ $kondisiDistribusi['RUSAK_RINGAN'] }} ({{ $pctRingan }}%)</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Rusak Berat: {{ $kondisiDistribusi['RUSAK_BERAT'] }} ({{ $pctBerat }}%)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Verification & System Activity (1 col) -->
        <div class="space-y-5">
            <!-- Pending Users Section -->
            @if($totalUserPending > 0)
                <div class="panel-card p-4 border border-amber-500/30 bg-amber-950/20">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-bold text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            Pengajuan Akun Baru ({{ $totalUserPending }})
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mb-3">Ada staf/teknisi baru yang mendaftar dan menunggu persetujuan.</p>
                    <a href="{{ route('users.index', ['status' => 'PENDING']) }}" class="btn-secondary w-full text-xs text-amber-300 border-amber-500/30 hover:bg-amber-500/10">
                        Buka Verifikasi Akun &rarr;
                    </a>
                </div>
            @endif

            <!-- Quick Management Shortcuts -->
            <div class="panel-card p-4">
                <div class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2.5">Akses Cepat</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('master.kategori') }}" class="p-2.5 rounded bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white transition flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <span>Kategori</span>
                    </a>
                    <a href="{{ route('master.ruangan') }}" class="p-2.5 rounded bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white transition flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span>Ruangan</span>
                    </a>
                    <a href="{{ route('users.index') }}" class="p-2.5 rounded bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white transition flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span>Tim Staf</span>
                    </a>
                    <a href="{{ route('barang.recentlyDeleted') }}" class="p-2.5 rounded bg-slate-900 hover:bg-slate-800 text-amber-400 hover:text-amber-300 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span>Recycle Bin</span>
                    </a>
                </div>
            </div>

            <!-- Recent Log Feed -->
            <div class="panel-card p-4">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-800">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Log Aktivitas Terbaru</span>
                    <a href="{{ route('log.index') }}" class="text-[11px] text-blue-400 hover:underline">Semua &rarr;</a>
                </div>

                <div class="space-y-2.5">
                    @forelse($recentLogs as $log)
                        <div class="text-xs pb-2 border-b border-slate-800/60 last:border-0 last:pb-0">
                            <div class="flex items-center justify-between text-[10px] text-slate-500 mb-0.5">
                                <span class="font-mono text-blue-400">{{ $log->aksi }} &bull; {{ $log->entitas }}</span>
                                <span>{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-300 text-[11px] line-clamp-2 leading-relaxed">{{ $log->detail }}</p>
                        </div>
                    @empty
                        <div class="text-center py-4 text-xs text-slate-500">Belum ada aktivitas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
