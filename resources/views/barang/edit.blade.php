@extends('layouts.app')

@section('title', 'Edit Barang - ' . $barang->nama_barang)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
            <h1 class="text-lg font-bold text-white tracking-tight">Edit Data Barang</h1>
            <p class="text-xs text-slate-400 mt-0.5">Label: <span class="text-blue-400 font-mono font-bold">{{ $barang->label }}</span></p>
        </div>
        <a href="{{ route('barang.show', $barang->id) }}" class="btn-secondary text-xs">&larr; Kembali</a>
    </div>

    <div class="panel-card p-6 sm:p-7">
        <form method="POST" action="{{ route('barang.update', $barang->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-300 mb-1">Nama Barang <span class="text-rose-400">*</span></label>
                    <input type="text" name="nama_barang" value="{{ old('nama_barang', $barang->nama_barang) }}" required class="ui-input w-full">
                    @error('nama_barang') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Kategori <span class="text-rose-400">*</span></label>
                    <select name="kategori_id" required class="ui-input w-full bg-slate-900">
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}" {{ old('kategori_id', $barang->kategori_id) == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Ruangan (Mutasi Lokasi)</label>
                    <select name="ruangan_id" class="ui-input w-full bg-slate-900">
                        <option value="">-- Belum Ditentukan / Gudang --</option>
                        @foreach($ruangans as $r)
                            <option value="{{ $r->id }}" {{ old('ruangan_id', $barang->ruangan_id) == $r->id ? 'selected' : '' }}>{{ $r->nama }} ({{ $r->kode }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Kondisi Fisik <span class="text-rose-400">*</span></label>
                    <select name="kondisi" required class="ui-input w-full bg-slate-900">
                        <option value="BAIK" {{ old('kondisi', $barang->kondisi) == 'BAIK' ? 'selected' : '' }}>BAIK</option>
                        <option value="RUSAK_RINGAN" {{ old('kondisi', $barang->kondisi) == 'RUSAK_RINGAN' ? 'selected' : '' }}>RUSAK RINGAN</option>
                        <option value="RUSAK_BERAT" {{ old('kondisi', $barang->kondisi) == 'RUSAK_BERAT' ? 'selected' : '' }}>RUSAK BERAT</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Status Ketersediaan <span class="text-rose-400">*</span></label>
                    <select name="status" required class="ui-input w-full bg-slate-900">
                        <option value="TERSEDIA" {{ old('status', $barang->status) == 'TERSEDIA' ? 'selected' : '' }}>TERSEDIA</option>
                        <option value="DIPINJAM" {{ old('status', $barang->status) == 'DIPINJAM' ? 'selected' : '' }}>DIPINJAM</option>
                        <option value="MAINTENANCE" {{ old('status', $barang->status) == 'MAINTENANCE' ? 'selected' : '' }}>MAINTENANCE</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Merek</label>
                    <input type="text" name="merek" value="{{ old('merek', $barang->merek) }}" class="ui-input w-full">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Model / Tipe</label>
                    <input type="text" name="model" value="{{ old('model', $barang->model) }}" class="ui-input w-full">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Serial Number (SN)</label>
                    <input type="text" name="sn" value="{{ old('sn', $barang->sn) }}" class="ui-input w-full font-mono text-xs">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Label Aset Universitas</label>
                    <input type="text" name="label_aset" value="{{ old('label_aset', $barang->label_aset) }}" class="ui-input w-full font-mono text-xs">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Program Studi</label>
                    <input type="text" name="progdi" value="{{ old('progdi', $barang->progdi) }}" class="ui-input w-full">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Harga Beli (Rp)</label>
                    <input type="number" step="0.01" name="harga" value="{{ old('harga', $barang->harga) }}" class="ui-input w-full font-mono">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-300 mb-1">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="2" class="ui-input w-full">{{ old('keterangan', $barang->keterangan) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-800">
                <a href="{{ route('barang.show', $barang->id) }}" class="btn-secondary text-xs">Batal</a>
                <button type="submit" class="btn-blue text-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
