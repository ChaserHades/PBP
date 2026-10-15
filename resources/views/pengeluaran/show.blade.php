@extends('layouts.app')

@section('title', 'Detail Belanja - ' . $pengeluaran->judul)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
            <h1 class="text-lg font-bold text-white tracking-tight">{{ $pengeluaran->judul }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Kode: <span class="font-mono text-blue-400 font-semibold">{{ $pengeluaran->kode_pembelian ?: '-' }}</span></p>
        </div>
        <a href="{{ route('pengeluaran.index') }}" class="btn-secondary text-xs">&larr; Kembali</a>
    </div>

    <div class="panel-card p-6 sm:p-7 space-y-5">
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-500 block mb-0.5">Nominal Belanja</span>
                <div class="text-2xl font-bold text-blue-400 font-mono">
                    Rp {{ number_format($pengeluaran->nominal, 0, ',', '.') }}
                </div>
            </div>

            <div>
                <span class="text-slate-500 block mb-0.5">Tanggal Transaksi</span>
                <div class="text-sm font-semibold text-slate-200">{{ $pengeluaran->tanggal->format('d F Y') }}</div>
                <div class="text-[10px] text-slate-500">Dicatat oleh: {{ $pengeluaran->user->name ?? 'Admin' }}</div>
            </div>

            <div>
                <span class="text-slate-500 block mb-0.5">Jenis & Nomor Bukti</span>
                <div class="text-slate-200">
                    {{ str_replace('_', ' ', $pengeluaran->jenis) }} &bull;
                    <span class="font-semibold">{{ $pengeluaran->jenis_bukti ?: 'NOTA' }}</span>
                    @if($pengeluaran->nomor_bukti)
                        ({{ $pengeluaran->nomor_bukti }})
                    @endif
                </div>
            </div>

            <div>
                <span class="text-slate-500 block mb-0.5">Batch / Kelompok Pembelian</span>
                <div class="font-mono text-slate-300">{{ $pengeluaran->kelompokPembelian->kode ?? '-' }}</div>
            </div>
        </div>

        @if($pengeluaran->deskripsi)
            <div class="pt-3 border-t border-slate-800 text-xs">
                <span class="text-slate-500 block mb-1">Catatan:</span>
                <p class="text-slate-200">{{ $pengeluaran->deskripsi }}</p>
            </div>
        @endif

        @if(!empty($pengeluaran->nota_urls))
            <div class="pt-3 border-t border-slate-800">
                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Dokumen Bukti Nota</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($pengeluaran->nota_urls as $idx => $url)
                        <a href="{{ $url }}" target="_blank" class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs text-slate-300 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Lampiran #{{ $idx + 1 }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="pt-3 border-t border-slate-800">
            <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Aset Fisik Terhubung ({{ $pengeluaran->barang->count() }})</h3>
            <div class="space-y-1.5">
                @forelse($pengeluaran->barang as $b)
                    <div class="p-2.5 rounded-md bg-slate-950/60 border border-slate-800 flex items-center justify-between text-xs">
                        <div>
                            <a href="{{ route('barang.show', $b->id) }}" class="font-medium text-slate-200 hover:text-blue-400">
                                {{ $b->nama_barang }}
                            </a>
                            <span class="font-mono text-[10px] text-blue-400 ml-2">{{ $b->label }}</span>
                        </div>
                        <span class="pill-green text-[10px]">{{ $b->status }}</span>
                    </div>
                @empty
                    <div class="text-xs text-slate-500">Tidak ada aset fisik yang ditautkan ke transaksi ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
