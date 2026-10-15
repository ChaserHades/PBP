@extends('layouts.app')

@section('title', 'Catat Peminjaman Barang')

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
            <h1 class="text-lg font-bold text-white tracking-tight">Peminjaman Barang Lab</h1>
            <p class="text-xs text-slate-400 mt-0.5">Catat detail peminjam dan batas waktu pengembalian alat.</p>
        </div>
        <a href="{{ route('peminjaman.index') }}" class="btn-secondary text-xs">&larr; Kembali</a>
    </div>

    <div class="panel-card p-6 sm:p-7">
        <form method="POST" action="{{ route('peminjaman.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Pilih Barang (Status: Tersedia) <span class="text-rose-400">*</span></label>
                <select name="barang_id" required class="ui-input w-full bg-slate-900 text-xs">
                    <option value="">-- Pilih Barang Tersedia --</option>
                    @foreach($barangs as $b)
                        <option value="{{ $b->id }}" {{ old('barang_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->nama_barang }} ({{ $b->label }}) - {{ $b->ruangan->nama ?? 'Tanpa Ruangan' }}
                        </option>
                    @endforeach
                </select>
                @error('barang_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Nama Peminjam <span class="text-rose-400">*</span></label>
                <input type="text" name="nama_peminjam" value="{{ old('nama_peminjam') }}" required class="ui-input w-full" placeholder="Nama mahasiswa / dosen / asisten lab">
                @error('nama_peminjam') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Ruangan / Tempat Pemakaian <span class="text-rose-400">*</span></label>
                <input type="text" name="ruangan_tujuan" value="{{ old('ruangan_tujuan') }}" required class="ui-input w-full" placeholder="Contoh: Lab Komputer 2, Ruang Sidang E126">
                @error('ruangan_tujuan') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Batas Waktu Pengembalian <span class="text-rose-400">*</span></label>
                <input type="datetime-local" name="deadline_kembali" value="{{ old('deadline_kembali', date('Y-m-d\TH:i', strtotime('+1 day 18:00'))) }}" required class="ui-input w-full">
                <p class="text-[11px] text-slate-500 mt-1">SOP Lab: pengembalian harian maksimal jam 18:00 WIB.</p>
                @error('deadline_kembali') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Catatan / Keperluan</label>
                <textarea name="catatan" rows="2" class="ui-input w-full" placeholder="Keperluan praktikum, kegiatan seminar, dll.">{{ old('catatan') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                <a href="{{ route('peminjaman.index') }}" class="btn-secondary text-xs">Batal</a>
                <button type="submit" class="btn-blue text-xs">
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
