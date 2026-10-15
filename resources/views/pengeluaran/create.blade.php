@extends('layouts.app')

@section('title', 'Catat Pengeluaran Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
            <h1 class="text-lg font-bold text-white tracking-tight">Catat Belanja & Pengeluaran</h1>
            <p class="text-xs text-slate-400 mt-0.5">Input bukti SPB, kwitansi, atau nota dan hubungkan dengan aset yang diadakan.</p>
        </div>
        <a href="{{ route('pengeluaran.index') }}" class="btn-secondary text-xs">&larr; Kembali</a>
    </div>

    <div class="panel-card p-6 sm:p-7">
        <form method="POST" action="{{ route('pengeluaran.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-300 mb-1">Judul / Pengadaan Belanja <span class="text-rose-400">*</span></label>
                    <input type="text" name="judul" value="{{ old('judul') }}" required class="ui-input w-full" placeholder="Contoh: Pembelian 10 Unit Flashdisk Sandisk Lab 1">
                    @error('judul') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Nominal Biaya (Rp) <span class="text-rose-400">*</span></label>
                    <input type="number" step="0.01" name="nominal" value="{{ old('nominal') }}" required class="ui-input w-full font-mono" placeholder="Contoh: 1500000">
                    @error('nominal') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Transaksi <span class="text-rose-400">*</span></label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required class="ui-input w-full">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Jenis Belanja <span class="text-rose-400">*</span></label>
                    <select name="jenis" required class="ui-input w-full bg-slate-900">
                        <option value="PEMBELIAN_ASET">PEMBELIAN ASET BARU</option>
                        <option value="MAINTENANCE">MAINTENANCE / SERVIS</option>
                        <option value="LAINNYA">LAINNYA / OPERASIONAL</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Jenis Dokumen Bukti</label>
                    <select name="jenis_bukti" class="ui-input w-full bg-slate-900">
                        <option value="NOTA">NOTA PEMBELIAN</option>
                        <option value="SPB">SPB (Surat Perintah Bayar)</option>
                        <option value="KWITANSI">KWITANSI RESMI</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Nomor Dokumen Bukti</label>
                    <input type="text" name="nomor_bukti" value="{{ old('nomor_bukti') }}" class="ui-input w-full font-mono text-xs" placeholder="Contoh: 03/290-12 atau INV-2026-001">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Upload File Nota (PDF/Foto)</label>
                    <input type="file" name="nota_files[]" multiple class="ui-input w-full text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-slate-800 file:text-slate-200">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-300 mb-1">Deskripsi Tambahan</label>
                    <textarea name="deskripsi" rows="2" class="ui-input w-full" placeholder="Nama toko penyedia, nomor nota fisik, keterangan garansi...">{{ old('deskripsi') }}</textarea>
                </div>
            </div>

            <!-- Two-Way Sync Asset Checklist -->
            @if($candidateBarang->count() > 0)
                <div class="pt-3 border-t border-slate-800">
                    <label class="block text-xs font-medium text-slate-300 mb-2">Tautkan ke Aset Fisik (Two-Way Sync):</label>
                    <div class="max-h-44 overflow-y-auto space-y-1.5 p-2.5 rounded-lg bg-slate-950/60 border border-slate-800 text-xs">
                        @foreach($candidateBarang as $cb)
                            <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-800/60 cursor-pointer">
                                <input type="checkbox" name="barang_ids[]" value="{{ $cb->id }}" class="rounded bg-slate-900 border-slate-700 text-blue-600">
                                <div class="flex-1">
                                    <span class="font-medium text-slate-200">{{ $cb->nama_barang }}</span>
                                    <span class="font-mono text-[10px] text-blue-400 ml-1.5">{{ $cb->label }}</span>
                                </div>
                                <span class="text-slate-500 font-mono text-[11px]">{{ $cb->harga ? 'Rp ' . number_format($cb->harga, 0, ',', '.') : '-' }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                <a href="{{ route('pengeluaran.index') }}" class="btn-secondary text-xs">Batal</a>
                <button type="submit" class="btn-blue text-xs">
                    Simpan Catatan Belanja
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
