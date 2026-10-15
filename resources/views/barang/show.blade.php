@extends('layouts.app')

@section('title', $barang->nama_barang . ' - Detail Aset')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl font-bold text-white">{{ $barang->nama_barang }}</h1>
                @if($barang->trashed())
                    <span class="pill-red text-[10px]">RECENTLY DELETED</span>
                @elseif($barang->status === 'TERSEDIA')
                    <span class="pill-green text-[10px]">TERSEDIA</span>
                @elseif($barang->status === 'DIPINJAM')
                    <span class="pill-yellow text-[10px]">DIPINJAM</span>
                @else
                    <span class="pill-red text-[10px]">MAINTENANCE</span>
                @endif
            </div>
            <div class="flex items-center gap-2 mt-1 text-xs text-slate-400">
                <span class="font-mono text-blue-400 font-semibold">{{ $barang->label }}</span>
                @if($barang->label_aset)
                    <span>&bull;</span>
                    <span class="font-mono text-slate-300">Univ: {{ $barang->label_aset }}</span>
                @endif
                <span>&bull;</span>
                <span>{{ $barang->kategori->nama ?? '-' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('barang.index') }}" class="btn-secondary text-xs">&larr; Kembali</a>
            @if(!$barang->trashed() && in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN', 'TEKNISI']))
                <a href="{{ route('barang.edit', $barang->id) }}" class="btn-blue text-xs">Edit Aset</a>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Specs & Info -->
        <div class="lg:col-span-2 space-y-4">
            <div class="panel-card p-5">
                <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                    Spesifikasi & Penempatan
                </h2>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-500 block mb-0.5">Merek & Model</span>
                        <div class="font-semibold text-slate-200">{{ $barang->merek ?: '-' }} {{ $barang->model ? '('.$barang->model.')' : '' }}</div>
                    </div>

                    <div>
                        <span class="text-slate-500 block mb-0.5">Serial Number (SN)</span>
                        <div class="font-mono font-medium text-slate-200">{{ $barang->sn ?: '-' }}</div>
                    </div>

                    <div>
                        <span class="text-slate-500 block mb-0.5">Ruangan Lokasi</span>
                        <div class="font-medium text-emerald-400">
                            {{ $barang->ruangan ? $barang->ruangan->nama . ' (' . $barang->ruangan->kode . ')' : 'Belum Ditentukan' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-500 block mb-0.5">Program Studi</span>
                        <div class="font-medium text-slate-200">{{ $barang->progdi ?: 'Fakultas Teknologi Informasi' }}</div>
                    </div>

                    <div>
                        <span class="text-slate-500 block mb-0.5">Kondisi Fisik</span>
                        <div>
                            @if($barang->kondisi === 'BAIK')
                                <span class="pill-green text-[10px]">BAIK</span>
                            @elseif($barang->kondisi === 'RUSAK_RINGAN')
                                <span class="pill-yellow text-[10px]">RUSAK RINGAN</span>
                            @else
                                <span class="pill-red text-[10px]">RUSAK BERAT</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-500 block mb-0.5">Harga Beli Tercatat</span>
                        <div class="font-mono font-bold text-blue-400">
                            {{ $barang->harga ? 'Rp ' . number_format($barang->harga, 0, ',', '.') : 'Tidak ada' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-500 block mb-0.5">Tanggal Kedatangan</span>
                        <div class="text-slate-200">{{ $barang->tanggal_datang ? $barang->tanggal_datang->format('d F Y') : '-' }}</div>
                    </div>

                    <div>
                        <span class="text-slate-500 block mb-0.5">Batch / Kelompok</span>
                        <div class="font-mono text-slate-300">{{ $barang->kelompokPembelian->kode ?? '-' }}</div>
                    </div>
                </div>

                @if($barang->keterangan)
                    <div class="mt-4 pt-3 border-t border-slate-800 text-xs">
                        <span class="text-slate-500 block mb-1">Catatan Tambahan:</span>
                        <p class="text-slate-300">{{ $barang->keterangan }}</p>
                    </div>
                @endif
            </div>

            <!-- Riwayat Peminjaman -->
            <div class="panel-card p-5">
                <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                    Riwayat Peminjaman Unit Ini
                </h2>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Peminjam</th>
                                <th>Ruangan Tujuan</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($barang->peminjaman as $loan)
                                <tr>
                                    <td class="font-medium text-slate-200">{{ $loan->nama_peminjam }}</td>
                                    <td class="text-slate-300">{{ $loan->ruangan_tujuan }}</td>
                                    <td>{{ $loan->tanggal_pinjam->format('d/m/Y') }}</td>
                                    <td>{{ $loan->tanggal_kembali ? $loan->tanggal_kembali->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @if($loan->status === 'AKTIF')
                                            <span class="pill-yellow text-[10px]">Aktif</span>
                                        @else
                                            <span class="pill-green text-[10px]">Selesai</span>
                                        @endif
                                        @if($loan->terlambat)
                                            <span class="pill-red text-[10px] ml-1">Terlambat</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-slate-500 text-xs">Belum ada riwayat peminjaman untuk barang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- QR Code & Soft Delete Panel -->
        <div class="space-y-4">
            <div class="panel-card p-5 text-center">
                <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Identitas Stiker & QR</h2>

                <div class="p-5 rounded-lg bg-white text-slate-900 inline-block shadow-lg mx-auto my-1">
                    <div class="w-32 h-32 flex flex-col items-center justify-center border border-dashed border-slate-400 rounded-md">
                        <svg class="w-12 h-12 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        <span class="text-[9px] font-mono mt-1 text-slate-700 font-bold uppercase">FTI UKSW</span>
                    </div>
                </div>

                <div class="mt-3 text-xs font-mono text-slate-400">
                    ID: <span class="text-slate-200">{{ $barang->id }}</span>
                </div>

                @if(in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN', 'TEKNISI']))
                    <form method="POST" action="{{ route('barang.qr', $barang->id) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-secondary w-full text-xs">
                            Generate Ulang QR
                        </button>
                    </form>
                @endif
            </div>

            <!-- Soft Delete Status Notice -->
            @if($barang->trashed())
                <div class="panel-card p-4 border border-amber-500/30 bg-amber-950/20">
                    <div class="text-xs font-bold text-amber-300 mb-1">Status: Terhapus Sementara</div>
                    <p class="text-xs text-slate-300 mb-3">
                        Dihapus: {{ $barang->deleted_at->format('d/m/Y H:i') }}.
                        @if($barang->isRecoverable())
                            <span class="text-emerald-400 block mt-1 font-semibold">Dapat dipulihkan (retensi 6 hari aktif).</span>
                        @else
                            <span class="text-rose-400 block mt-1 font-semibold">Telah expired (> 6 hari).</span>
                        @endif
                    </p>

                    @if($barang->isRecoverable() && in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN', 'TEKNISI']))
                        <form method="POST" action="{{ route('barang.restore', $barang->id) }}">
                            @csrf
                            <button type="submit" class="btn-blue w-full text-xs">
                                Pulihkan Barang Ini
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
