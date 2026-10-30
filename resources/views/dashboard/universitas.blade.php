@extends('layouts.app')

@section('title', 'Laporan Eksekutif Universitas - FTI UKSW')

@section('content')
<div class="space-y-5">
    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-xl font-bold text-white tracking-tight">Rekapitulasi Aset & Inventaris FTI</h1>
        <p class="text-xs text-slate-400 mt-0.5">Laporan inventarisasi dan akumulasi belanja aset untuk pimpinan & auditor universitas.</p>
    </div>

    <!-- Executive Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="panel-card p-5 border-l-2 border-l-blue-500">
            <span class="text-[11px] text-slate-400 uppercase tracking-wider font-medium">Total Unit Terdata</span>
            <div class="text-3xl font-black text-white mt-1">{{ number_format($totalAset) }}</div>
            <div class="text-xs text-slate-500 mt-1">Unit aset di seluruh laboratorium FTI</div>
        </div>

        <div class="panel-card p-5 border-l-2 border-l-emerald-500">
            <span class="text-[11px] text-slate-400 uppercase tracking-wider font-medium">Nilai Tercatat Pengadaan</span>
            <div class="text-2xl font-bold text-emerald-400 mt-1 font-mono">Rp {{ number_format($totalNilaiAset, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-1">Total harga beli dari aset yang memiliki data harga</div>
        </div>

        <div class="panel-card p-5 border-l-2 border-l-slate-600">
            <span class="text-[11px] text-slate-400 uppercase tracking-wider font-medium">Akumulasi Dokumen Belanja</span>
            <div class="text-2xl font-bold text-slate-200 mt-1 font-mono">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-1">Realisasi nota, SPB, dan kwitansi kas</div>
        </div>
    </div>

    <!-- Graphical Dashboard: Visualisasi Laporan Eksekutif Universitas -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
        <div>
            <h2 class="text-sm font-bold text-white">Visualisasi Laporan Inventaris & Pengeluaran</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Ringkasan analitik selama {{ $periode }} bulan terakhir untuk audit dan evaluasi pengadaan universitas.</p>
        </div>
        <x-dashboard.period-filter :periode="$periode" />
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <x-dashboard.stat-card label="Kondisi Aset Baik" :value="$persentaseKondisiBaik . '%'" accent="border-l-emerald-500" :hint="number_format($kondisiDistribusi['BAIK']) . ' dari ' . number_format($totalAset) . ' unit'" />
        <x-dashboard.stat-card label="Perlu Perhatian" :value="number_format($kondisiDistribusi['RUSAK_RINGAN'] + $kondisiDistribusi['RUSAK_BERAT'])" accent="border-l-amber-500">
            <span class="text-amber-400">{{ $kondisiDistribusi['RUSAK_RINGAN'] }} ringan</span> &bull;
            <span class="text-rose-400">{{ $kondisiDistribusi['RUSAK_BERAT'] }} berat</span>
        </x-dashboard.stat-card>
        <x-dashboard.stat-card label="Kategori / Ruangan" :value="number_format($totalKategori) . ' / ' . number_format($totalRuangan)" accent="border-l-sky-500" hint="Sebaran klasifikasi & lokasi" />
        <x-dashboard.stat-card label="Belanja ({{ $periode }} Bulan)" :value="'Rp ' . number_format($pengeluaranPeriode, 0, ',', '.')" accent="border-l-violet-500" value-class="text-xl font-bold text-slate-200 font-mono" hint="Realisasi pada periode terpilih" />
    </div>

    @php
        $progdiLabels = $asetPerProgdi->map(fn ($item) => $item->progdi ?: 'Fakultas / Lab Bersama')->all();
        $progdiUnitData = $asetPerProgdi->pluck('count')->map(fn ($count) => (int) $count)->all();
        $progdiNilaiData = $asetPerProgdi->pluck('total_nilai')->map(fn ($nilai) => round((float) ($nilai ?? 0), 2))->all();
        $kategoriTeratas = $kategoriStatistik->take(10);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <x-dashboard.chart-card
            class="lg:col-span-2"
            title="Tren Realisasi Pengeluaran"
            subtitle="Akumulasi kas keluar per bulan beserta rincian jenis pengeluaran"
            type="line"
            :labels="$bulanLabels"
            :datasets="[
                ['label' => 'Total Pengeluaran', 'data' => $trenPengeluaran, 'color' => 'blue', 'fill' => true],
                ['label' => 'Pembelian Aset', 'data' => $trenPengeluaranPerJenis['PEMBELIAN_ASET'], 'color' => 'emerald'],
                ['label' => 'Maintenance', 'data' => $trenPengeluaranPerJenis['MAINTENANCE'], 'color' => 'amber'],
            ]"
            :options="['area' => true, 'currency' => true]"
            empty-message="Belum ada realisasi pengeluaran pada periode ini."
        />

        <x-dashboard.chart-card
            title="Komposisi Belanja"
            subtitle="Proporsi pengeluaran per jenis pada periode terpilih"
            type="doughnut"
            :labels="['Pembelian Aset', 'Maintenance', 'Lainnya']"
            :datasets="[['label' => 'Nominal', 'data' => array_values($pengeluaranPerJenis), 'colors' => ['emerald', 'amber', 'slate']]]"
            :options="['currency' => true]"
            empty-message="Belum ada data pengeluaran pada periode ini."
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <x-dashboard.chart-card
            title="Distribusi Inventaris per Kategori"
            subtitle="Jumlah unit aset pada 10 kategori teratas"
            type="bar"
            height="h-72"
            :labels="$kategoriTeratas->pluck('nama')->all()"
            :datasets="[['label' => 'Jumlah Unit', 'data' => $kategoriTeratas->pluck('barang_count')->all(), 'color' => 'blue']]"
            :options="['horizontal' => true]"
            :links="$kategoriTeratas->map(fn ($kategori) => route('barang.index', ['kategori_id' => $kategori->id]))->all()"
            empty-message="Belum ada aset yang diklasifikasikan ke kategori."
        />

        <x-dashboard.chart-card
            title="Nilai Pengadaan per Program Studi"
            subtitle="Akumulasi harga beli aset terdaftar per program studi"
            type="bar"
            height="h-72"
            :labels="$progdiLabels"
            :datasets="[['label' => 'Nilai Aset', 'data' => $progdiNilaiData, 'color' => 'emerald']]"
            :options="['currency' => true]"
            empty-message="Belum ada nilai pengadaan aset per program studi."
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <x-dashboard.chart-card
            class="lg:col-span-2"
            title="Tren Penambahan Aset & Nilai Pengadaan"
            subtitle="Unit aset baru (batang) dan nilai pengadaan (garis) per bulan"
            type="bar"
            :labels="$bulanLabels"
            :datasets="[
                ['label' => 'Unit Aset Baru', 'data' => $trenAsetMasuk, 'color' => 'sky', 'axis' => 'y'],
                ['label' => 'Nilai Pengadaan (Rp)', 'data' => $trenNilaiAsetMasuk, 'color' => 'emerald', 'type' => 'line', 'axis' => 'y1'],
            ]"
            :options="['secondaryCurrency' => true]"
            empty-message="Belum ada penambahan aset baru pada periode ini."
        />

        <x-dashboard.chart-card
            title="Kondisi Fisik Inventaris"
            subtitle="Kelayakan fisik seluruh unit aset terdaftar"
            type="pie"
            :labels="['Baik', 'Rusak Ringan', 'Rusak Berat']"
            :datasets="[['label' => 'Jumlah Unit', 'data' => array_values($kondisiDistribusi), 'colors' => ['emerald', 'amber', 'rose']]]"
            empty-message="Belum ada data kondisi fisik inventaris."
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Distribusi per Program Studi -->
        <div class="panel-card p-5">
            <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                Aset Berdasarkan Program Studi
            </h2>
            <div class="divide-y divide-slate-800/60">
                @forelse($asetPerProgdi as $item)
                    <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-semibold text-slate-200">{{ $item->progdi ?: 'Fakultas / Lab Bersama' }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $item->count }} unit</div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono font-medium text-emerald-400">Rp {{ number_format($item->total_nilai ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-xs text-slate-500">Data belum tersedia.</div>
                @endforelse
            </div>
        </div>

        <!-- Dokumen Pengeluaran Terakhir -->
        <div class="panel-card p-5">
            <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                Pengeluaran Pembelian Terakhir
            </h2>
            <div class="divide-y divide-slate-800/60">
                @forelse($recentPengeluaran as $pengeluaran)
                    <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-semibold text-slate-200">{{ $pengeluaran->judul }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $pengeluaran->tanggal->format('d/m/Y') }} &bull; {{ $pengeluaran->jenis }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono font-medium text-blue-400">Rp {{ number_format($pengeluaran->nominal, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-slate-500">{{ $pengeluaran->nomor_bukti ?: 'Tanpa nomor' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-xs text-slate-500">Belum ada catatan belanja.</div>
                @endforelse
            </div>

            @if($recentPengeluaran->total() > 0)
                <div class="pt-3 mt-3 border-t border-slate-800/80">
                    {{ $recentPengeluaran->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@vite('resources/js/dashboard.js')
@endsection
