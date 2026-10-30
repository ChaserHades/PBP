@extends('layouts.app')

@section('title', 'Dashboard Petugas Teknisi')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Meja Kerja Teknisi Lab</h1>
            <p class="text-xs text-slate-400 mt-0.5">Layanan peminjaman alat, pengecekan ketersediaan aset, dan pengembalian.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('peminjaman.create') }}" class="btn-blue text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Peminjaman Baru
            </a>
            <a href="{{ route('barang.create') }}" class="btn-secondary text-xs">
                Tambah Aset Lab
            </a>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="panel-card p-4 border-l-2 border-l-emerald-500">
            <span class="text-[11px] text-slate-400 uppercase font-medium">Aset Tersedia di Lab</span>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($asetTersedia) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Siap digunakan untuk kegiatan praktikum</div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-amber-500">
            <span class="text-[11px] text-slate-400 uppercase font-medium">Sedang Dipinjam</span>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($peminjamanAktif) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Dalam status peminjaman aktif</div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-rose-500">
            <span class="text-[11px] text-slate-400 uppercase font-medium">Melewati Batas Waktu</span>
            <div class="text-2xl font-black text-rose-400 mt-1">{{ number_format($peminjamanTerlambat) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Perlu konfirmasi ke peminjam</div>
        </div>
    </div>

    <!-- Graphical Dashboard: Ringkasan Operasional Teknisi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
        <div>
            <h2 class="text-sm font-bold text-white">Ringkasan Operasional Lab</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Grafik tren menampilkan data {{ $periode }} bulan terakhir.</p>
        </div>
        <x-dashboard.period-filter :periode="$periode" />
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <x-dashboard.stat-card label="Perlu Ditangani" :value="number_format($totalPerluDitangani)" accent="border-l-amber-500" hint="Rusak ringan/berat atau maintenance" />
        <x-dashboard.stat-card label="Sedang Maintenance" :value="number_format($statusDistribusi['MAINTENANCE'])" accent="border-l-violet-500" hint="Status aset MAINTENANCE" />
        <x-dashboard.stat-card label="Rusak Berat" :value="number_format($kondisiDistribusi['RUSAK_BERAT'])" accent="border-l-rose-500" value-class="text-2xl font-black text-rose-400" hint="Prioritas perbaikan / penggantian" />
        <x-dashboard.stat-card label="Lewat Deadline (Aktif)" :value="number_format($peminjamanLewatDeadline)" accent="border-l-slate-600" hint="Belum dikembalikan melewati batas waktu" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <x-dashboard.chart-card
            title="Kondisi Fisik Aset"
            subtitle="Proporsi aset baik dan rusak"
            type="doughnut"
            :labels="['Baik', 'Rusak Ringan', 'Rusak Berat']"
            :datasets="[['label' => 'Jumlah Aset', 'data' => array_values($kondisiDistribusi), 'colors' => ['emerald', 'amber', 'rose']]]"
            empty-message="Belum ada aset yang terdaftar."
        />

        <x-dashboard.chart-card
            class="lg:col-span-2"
            title="Tren Peminjaman & Keterlambatan"
            subtitle="Transaksi dipinjam, dikembalikan, dan pengembalian terlambat per bulan"
            type="line"
            :labels="$bulanLabels"
            :datasets="[
                ['label' => 'Dipinjam', 'data' => $trenPeminjaman, 'color' => 'blue'],
                ['label' => 'Dikembalikan', 'data' => $trenPengembalian, 'color' => 'emerald'],
                ['label' => 'Kembali Terlambat', 'data' => $trenTerlambat, 'color' => 'rose'],
            ]"
            empty-message="Belum ada transaksi peminjaman pada periode ini."
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <x-dashboard.chart-card
            title="Aset Rusak per Ruangan"
            subtitle="Lokasi yang paling membutuhkan penanganan"
            type="bar"
            height="h-72"
            :labels="$kerusakanPerRuangan['labels']"
            :datasets="[
                ['label' => 'Rusak Ringan', 'data' => $kerusakanPerRuangan['ringan'], 'color' => 'amber'],
                ['label' => 'Rusak Berat', 'data' => $kerusakanPerRuangan['berat'], 'color' => 'rose'],
            ]"
            :options="['horizontal' => true, 'stacked' => true]"
            empty-message="Tidak ada aset rusak. Semua aset dalam kondisi baik."
        />

        <x-dashboard.chart-card
            title="Aktivitas Pemeliharaan"
            subtitle="Catatan pengeluaran maintenance & aktivitas tim teknisi per bulan"
            type="bar"
            height="h-72"
            :labels="$bulanLabels"
            :datasets="[
                ['label' => 'Catatan Maintenance', 'data' => $trenMaintenance, 'color' => 'violet'],
                ['label' => 'Aktivitas Tim Teknisi', 'data' => $trenAktivitasTeknisi, 'color' => 'sky', 'type' => 'line'],
            ]"
            empty-message="Belum ada aktivitas pemeliharaan pada periode ini."
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <x-dashboard.chart-card
            title="Status Aset"
            subtitle="Ketersediaan aset untuk layanan peminjaman"
            type="bar"
            :labels="['Tersedia', 'Dipinjam', 'Maintenance']"
            :datasets="[['label' => 'Jumlah Aset', 'data' => array_values($statusDistribusi), 'color' => 'emerald']]"
            empty-message="Belum ada aset yang terdaftar."
        />

        <!-- Barang Perlu Ditangani -->
        <div class="panel-card p-5 lg:col-span-2 min-w-0">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-800">
                <div>
                    <h2 class="text-sm font-bold text-white">Barang Perlu Ditangani</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Diurutkan dari rusak berat, rusak ringan, lalu maintenance</p>
                </div>
                <a href="{{ route('barang.index', ['kondisi' => 'RUSAK_BERAT']) }}" class="text-xs text-blue-400 hover:underline">Lihat Rusak Berat &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Barang & Label</th>
                            <th>Ruangan</th>
                            <th>Kondisi</th>
                            <th class="text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asetPerluDitangani as $barang)
                            <tr>
                                <td>
                                    <a href="{{ route('barang.show', $barang->id) }}" class="font-semibold text-slate-200 hover:text-blue-400">{{ $barang->nama_barang ?? 'Aset' }}</a>
                                    <div class="text-[10px] font-mono text-blue-400">{{ $barang->label }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $barang->kategori->nama ?? '-' }}</div>
                                </td>
                                <td class="text-slate-400 text-xs">{{ $barang->ruangan ? $barang->ruangan->nama : 'Belum Ditentukan' }}</td>
                                <td>
                                    @if($barang->kondisi === 'RUSAK_BERAT')
                                        <span class="pill-red text-[10px]">RUSAK BERAT</span>
                                    @elseif($barang->kondisi === 'RUSAK_RINGAN')
                                        <span class="pill-yellow text-[10px]">RUSAK RINGAN</span>
                                    @else
                                        <span class="pill-green text-[10px]">BAIK</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($barang->status === 'MAINTENANCE')
                                        <span class="pill-red text-[10px]">MAINTENANCE</span>
                                    @elseif($barang->status === 'DIPINJAM')
                                        <span class="pill-yellow text-[10px]">DIPINJAM</span>
                                    @else
                                        <span class="pill-green text-[10px]">TERSEDIA</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-slate-500 text-xs">Tidak ada barang yang perlu ditangani saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($asetPerluDitangani->total() > 0)
                <div class="pt-3 mt-3 border-t border-slate-800/80">
                    {{ $asetPerluDitangani->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Active Loans Table -->
    <div class="panel-card p-5">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-800">
            <h2 class="text-sm font-bold text-white">Daftar Peminjaman Aktif</h2>
            <a href="{{ route('peminjaman.index') }}" class="text-xs text-blue-400 hover:underline">Lihat Semua &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barang & Label</th>
                        <th>Peminjam</th>
                        <th>Ruangan Pemakaian</th>
                        <th>Batas Waktu</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPeminjaman as $p)
                        @php $isTelat = $p->checkIsOverdue(); @endphp
                        <tr>
                            <td>
                                <div class="font-semibold text-slate-200">{{ $p->barang->nama_barang ?? 'Aset' }}</div>
                                <div class="text-xs font-mono text-blue-400">{{ $p->barang->label }}</div>
                            </td>
                            <td class="text-slate-300 font-medium">{{ $p->nama_peminjam }}</td>
                            <td class="text-slate-400">{{ $p->ruangan_tujuan }}</td>
                            <td>
                                <div class="text-xs {{ $isTelat ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                                    {{ $p->deadline_kembali->format('d/m/Y H:i') }}
                                </div>
                                @if($isTelat)
                                    <span class="text-[10px] text-rose-400 font-medium">Terlambat {{ now()->diffForHumans($p->deadline_kembali, true) }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('peminjaman.kembalikan', $p->id) }}">
                                    @csrf
                                    <button type="submit" class="btn-blue !py-1 !px-2.5 !text-xs">
                                        Proses Kembali
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-500 text-xs">Tidak ada barang yang sedang dipinjam saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentPeminjaman->total() > 0)
            <div class="pt-3 mt-3 border-t border-slate-800/80">
                {{ $recentPeminjaman->links() }}
            </div>
        @endif
    </div>
</div>

@vite('resources/js/dashboard.js')
@endsection
