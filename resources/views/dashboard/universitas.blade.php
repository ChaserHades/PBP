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
        </div>
    </div>
</div>
@endsection
