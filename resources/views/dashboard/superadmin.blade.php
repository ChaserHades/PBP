@extends('layouts.app')

@section('title', 'Superadmin Dashboard - Inventaris FTI UKSW')

@section('content')
<div class="space-y-5">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-white tracking-tight">Pusat Kendali Sistem (Superadmin)</h1>
                <span class="pill-blue text-[10px] font-mono font-semibold">SYSTEM OVERVIEW</span>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Statistik menyeluruh sistem inventaris, tata kelola pengguna & RBAC, jejak audit, dan ringkasan operasional.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-dashboard.period-filter :periode="$periode" />
            <a href="{{ route('users.index') }}" class="btn-blue text-xs">Kelola Pengguna</a>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-xs">Dashboard Pengelola</a>
        </div>
    </div>

    <!-- Primary System KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="panel-card p-4 border-l-2 border-l-blue-500">
            <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Aset Sistem</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($totalAset) }}</div>
            <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                <span class="text-emerald-400 font-medium">{{ $statusDistribusi['TERSEDIA'] }} tersedia</span> &bull;
                <span class="text-rose-400 font-medium">{{ $totalAsetTerhapus }} di recycle bin</span>
            </div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-sky-500">
            <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Kategori & Ruangan</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($totalKategori) }} / {{ number_format($totalRuangan) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">
                <span>{{ $totalKategori }} kategori</span> &bull;
                <span>{{ $totalRuangan }} ruangan lab</span>
            </div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-emerald-500">
            <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Pengguna</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($totalPengguna) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">
                <span class="text-emerald-400 font-medium">{{ $penggunaPerStatus['APPROVED'] }} aktif</span> &bull;
                @if($totalUserPending > 0)
                    <span class="text-amber-400 font-semibold">{{ $totalUserPending }} pending</span>
                @else
                    <span>0 pending</span>
                @endif
            </div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-violet-500">
            <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Aktivitas Audit ({{ $periode }} Bln)</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($totalLogPeriode) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Rekaman operasi di log sistem</div>
        </div>
    </div>

    <!-- Secondary Operational Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <x-dashboard.stat-card label="Peminjaman Aktif" :value="number_format($peminjamanAktif)" accent="border-l-amber-500">
            @if($peminjamanLewatDeadline > 0)
                <span class="text-rose-400 font-semibold">{{ $peminjamanLewatDeadline }} melewati deadline</span>
            @else
                <span>Seluruh peminjaman sesuai jadwal</span>
            @endif
        </x-dashboard.stat-card>
        <x-dashboard.stat-card label="Aset Perlu Servis" :value="number_format($kondisiDistribusi['RUSAK_RINGAN'] + $kondisiDistribusi['RUSAK_BERAT'])" accent="border-l-rose-500">
            <span class="text-amber-400">{{ $kondisiDistribusi['RUSAK_RINGAN'] }} ringan</span> &bull;
            <span class="text-rose-400">{{ $kondisiDistribusi['RUSAK_BERAT'] }} berat</span>
        </x-dashboard.stat-card>
        <x-dashboard.stat-card label="Total Kas Keluar" :value="'Rp ' . number_format($totalPengeluaran, 0, ',', '.')" accent="border-l-slate-600" value-class="text-xl font-bold text-slate-200 font-mono" hint="Akumulasi seluruh pengeluaran" />
        <x-dashboard.stat-card label="Belanja ({{ $periode }} Bulan)" :value="'Rp ' . number_format($pengeluaranPeriode, 0, ',', '.')" accent="border-l-teal-500" value-class="text-xl font-bold text-emerald-400 font-mono" hint="Realisasi pada periode terpilih" />
    </div>

    <!-- Row 1: System Activity Area Chart + User Role Distribution Doughnut -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <x-dashboard.chart-card
            class="lg:col-span-2"
            title="Tren Aktivitas Sistem (Audit Trail)"
            subtitle="Frekuensi operasi CREATE, UPDATE, DELETE, dan IMPORT per bulan"
            type="line"
            :labels="$bulanLabels"
            :datasets="[
                ['label' => 'CREATE', 'data' => $trenAktivitasPerAksi['CREATE'], 'color' => 'emerald', 'fill' => true],
                ['label' => 'UPDATE', 'data' => $trenAktivitasPerAksi['UPDATE'], 'color' => 'blue', 'fill' => true],
                ['label' => 'DELETE', 'data' => $trenAktivitasPerAksi['DELETE'], 'color' => 'rose', 'fill' => true],
                ['label' => 'IMPORT', 'data' => $trenAktivitasPerAksi['IMPORT'], 'color' => 'violet', 'fill' => true],
            ]"
            :options="['area' => true]"
            empty-message="Belum ada aktivitas sistem yang tercatat pada periode ini."
        />

        <x-dashboard.chart-card
            title="Distribusi Role Pengguna"
            subtitle="Komposisi akun berdasarkan peran RBAC"
            type="doughnut"
            :labels="array_keys($penggunaPerRole)"
            :datasets="[['label' => 'Pengguna', 'data' => array_values($penggunaPerRole), 'colors' => ['violet', 'blue', 'emerald', 'sky']]]"
            :links="array_map(fn ($role) => route('users.index', ['role' => $role]), array_keys($penggunaPerRole))"
            empty-message="Belum ada pengguna terdaftar."
        />
    </div>

    <!-- Row 2: Role x Verification Status Stacked Bar + Activity by Entity Horizontal Bar -->
    @php
        $roleLabels = array_keys($matriksRoleStatus);
        $approvedPerRole = array_map(fn ($role) => $matriksRoleStatus[$role]['APPROVED'], $roleLabels);
        $pendingPerRole = array_map(fn ($role) => $matriksRoleStatus[$role]['PENDING'], $roleLabels);
        $rejectedPerRole = array_map(fn ($role) => $matriksRoleStatus[$role]['REJECTED'], $roleLabels);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <x-dashboard.chart-card
            title="Status Verifikasi per Role"
            subtitle="Sebaran akun APPROVED, PENDING, dan REJECTED pada setiap peran"
            type="bar"
            height="h-72"
            :labels="$roleLabels"
            :datasets="[
                ['label' => 'Approved', 'data' => $approvedPerRole, 'color' => 'emerald'],
                ['label' => 'Pending', 'data' => $pendingPerRole, 'color' => 'amber'],
                ['label' => 'Rejected', 'data' => $rejectedPerRole, 'color' => 'rose'],
            ]"
            :options="['stacked' => true]"
            empty-message="Belum ada data verifikasi pengguna."
        />

        <x-dashboard.chart-card
            title="Aktivitas Sistem per Entitas"
            subtitle="Distribusi modul yang paling sering diakses dalam {{ $periode }} bulan terakhir"
            type="bar"
            height="h-72"
            :labels="array_keys($aktivitasPerEntitas)"
            :datasets="[['label' => 'Jumlah Log', 'data' => array_values($aktivitasPerEntitas), 'color' => 'sky']]"
            :options="['horizontal' => true]"
            :links="array_map(fn ($entitas) => route('log.index', ['entitas' => $entitas]), array_keys($aktivitasPerEntitas))"
            empty-message="Belum ada log aktivitas per entitas pada periode ini."
        />
    </div>

    <!-- Row 3: Multi-Metric Operational Trend + Asset Condition Pie -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <x-dashboard.chart-card
            class="lg:col-span-2"
            title="Ringkasan Operasional Bulanan"
            subtitle="Perbandingan aset baru masuk, transaksi peminjaman, dan pencatatan pengeluaran"
            type="bar"
            :labels="$bulanLabels"
            :datasets="[
                ['label' => 'Aset Baru', 'data' => $trenAsetMasuk, 'color' => 'blue'],
                ['label' => 'Transaksi Peminjaman', 'data' => $trenPeminjaman, 'color' => 'amber'],
                ['label' => 'Dokumen Pengeluaran', 'data' => $trenTransaksiPengeluaran, 'color' => 'emerald', 'type' => 'line'],
            ]"
            empty-message="Belum ada aktivitas operasional pada periode ini."
        />

        <x-dashboard.chart-card
            title="Kesehatan Fisik Inventaris"
            subtitle="Proporsi kondisi fisik seluruh aset aktif"
            type="pie"
            :labels="['Baik', 'Rusak Ringan', 'Rusak Berat']"
            :datasets="[['label' => 'Jumlah Aset', 'data' => array_values($kondisiDistribusi), 'colors' => ['emerald', 'amber', 'rose']]]"
            empty-message="Belum ada data kondisi aset."
        />
    </div>

    <!-- Row 4: Most Active Users & Latest Audit Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="panel-card p-5">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-800">
                <h2 class="text-sm font-bold text-white">Pengguna Paling Aktif</h2>
                <a href="{{ route('users.index') }}" class="text-xs text-blue-400 hover:underline">Kelola &rarr;</a>
            </div>

            <div class="space-y-2.5">
                @forelse($penggunaPalingAktif as $item)
                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-800/60 last:border-0">
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-200 truncate">{{ $item->user->name ?? 'Pengguna Sistem' }}</div>
                            <span class="pill-blue text-[10px] font-mono">{{ $item->user->role ?? 'SYSTEM' }}</span>
                        </div>
                        <span class="font-mono font-bold text-emerald-400 shrink-0">{{ number_format($item->total) }} aksi</span>
                    </div>
                @empty
                    <div class="text-center py-6 text-xs text-slate-500">Belum ada aktivitas pengguna pada periode ini.</div>
                @endforelse
            </div>
        </div>

        <div class="panel-card p-5 lg:col-span-2">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-800">
                <h2 class="text-sm font-bold text-white">Jejak Audit Sistem Terbaru</h2>
                <a href="{{ route('log.index') }}" class="text-xs text-blue-400 hover:underline">Buka Semua Log &rarr;</a>
            </div>

            <div class="divide-y divide-slate-800/60">
                @forelse($recentLogs as $log)
                    <div class="py-2.5 flex items-start justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="font-mono text-[10px] text-blue-400 font-semibold">{{ $log->aksi }} &bull; {{ $log->entitas }}</span>
                                <span class="text-[11px] text-slate-400">oleh <strong class="text-slate-200">{{ $log->user->name ?? 'Sistem' }}</strong></span>
                            </div>
                            <p class="text-slate-300 text-xs">{{ $log->detail }}</p>
                        </div>
                        <span class="text-[10px] text-slate-500 whitespace-nowrap shrink-0">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="text-center py-6 text-xs text-slate-500">Belum ada catatan log aktivitas sistem.</div>
                @endforelse
            </div>

            @if($recentLogs->total() > 0)
                <div class="pt-3 mt-3 border-t border-slate-800/80">
                    {{ $recentLogs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@vite('resources/js/dashboard.js')
@endsection

