@extends('layouts.app')

@section('title', 'Tambah Aset Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
            <h1 class="text-lg font-bold text-white tracking-tight">Tambah Barang Baru</h1>
            <p class="text-xs text-slate-400 mt-0.5">Input data aset laboratorium atau perlengkapan fakultas.</p>
        </div>
        <a href="{{ route('barang.index') }}" class="btn-secondary text-xs">&larr; Kembali</a>
    </div>

    <div class="panel-card p-6 sm:p-7">
        <form method="POST" action="{{ route('barang.store') }}" class="space-y-5">
            @csrf

            <!-- Format Label Notice -->
            <div class="p-3 rounded-lg bg-blue-950/40 border border-blue-500/25 flex items-start gap-3">
                <div class="w-7 h-7 rounded-md bg-blue-600/20 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
                <div class="text-xs text-slate-300">
                    <span class="text-white font-semibold">Auto-Generate Label Sistem:</span>
                    Kode label berformat <code>[KAT]-[MRK]-[MMYYYY]-[NNN]</code> (misal: <code class="text-blue-400 font-mono">FLAS-SAN-102026-001</code>) akan digenerate otomatis saat data disimpan.
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-300 mb-1">Nama Barang / Spesifikasi <span class="text-rose-400">*</span></label>
                    <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" required class="ui-input w-full" placeholder="Contoh: Flashdisk Sandisk Ultra 64GB USB 3.0">
                    @error('nama_barang') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Kategori <span class="text-rose-400">*</span></label>
                    <select name="kategori_id" required class="ui-input w-full bg-slate-900">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('kategori_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Merek</label>
                    <input type="text" name="merek" value="{{ old('merek') }}" class="ui-input w-full" placeholder="Contoh: Sandisk, Apple, Logitech">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Model / Tipe</label>
                    <input type="text" name="model" value="{{ old('model') }}" class="ui-input w-full" placeholder="Contoh: CZ48 / Pro Series">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Serial Number (SN)</label>
                    <input type="text" name="sn" value="{{ old('sn') }}" class="ui-input w-full font-mono text-xs" placeholder="Nomor seri pada unit">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Label Aset Universitas (Resmi)</label>
                    <input type="text" name="label_aset" value="{{ old('label_aset') }}" class="ui-input w-full font-mono text-xs" placeholder="Kode stiker inventaris universitas (jika ada)">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Program Studi / Pengguna</label>
                    <input type="text" name="progdi" value="{{ old('progdi') }}" class="ui-input w-full" placeholder="Teknik Informatika, Sistem Informasi, dsb.">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Ruangan Penempatan</label>
                    <select name="ruangan_id" class="ui-input w-full bg-slate-900">
                        <option value="">-- Belum Ditentukan / Gudang --</option>
                        @foreach($ruangans as $r)
                            <option value="{{ $r->id }}" {{ old('ruangan_id') == $r->id ? 'selected' : '' }}>{{ $r->nama }} ({{ $r->kode }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Kondisi Barang <span class="text-rose-400">*</span></label>
                    <select name="kondisi" required class="ui-input w-full bg-slate-900">
                        <option value="BAIK" {{ old('kondisi') == 'BAIK' ? 'selected' : '' }}>BAIK</option>
                        <option value="RUSAK_RINGAN" {{ old('kondisi') == 'RUSAK_RINGAN' ? 'selected' : '' }}>RUSAK RINGAN</option>
                        <option value="RUSAK_BERAT" {{ old('kondisi') == 'RUSAK_BERAT' ? 'selected' : '' }}>RUSAK BERAT</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Kedatangan</label>
                    <input type="date" name="tanggal_datang" value="{{ old('tanggal_datang', date('Y-m-d')) }}" class="ui-input w-full">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Harga Beli / Unit (Rp)</label>
                    <input type="number" step="0.01" name="harga" value="{{ old('harga') }}" class="ui-input w-full font-mono" placeholder="0">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-300 mb-1">Catatan</label>
                    <textarea name="keterangan" rows="2" class="ui-input w-full" placeholder="Kelengkapan kabel, adaptor, kondisi fisik tambahan...">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-800">
                <a href="{{ route('barang.index') }}" class="btn-secondary text-xs">Batal</a>
                <button type="submit" class="btn-blue text-xs">
                    Simpan & Buat Label Otomatis
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
