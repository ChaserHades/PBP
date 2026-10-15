@extends('layouts.app')

@section('title', 'Kategori Barang')

@section('content')
<div class="space-y-4">
    <div class="pb-3 border-b border-slate-800">
        <h1 class="text-lg font-bold text-white tracking-tight">Kategori Inventaris</h1>
        <p class="text-xs text-slate-400 mt-0.5">Kelola kategori barang untuk kode klasifikasi label sistem.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Form Tambah Kategori -->
        <div class="panel-card p-5 h-fit">
            <h2 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">Tambah Kategori</h2>
            <form method="POST" action="{{ route('master.kategori.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Nama Kategori <span class="text-rose-400">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required class="ui-input w-full" placeholder="Misal: Flashdisk, Proyektor">
                    @error('nama') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Keterangan Singkat</label>
                    <textarea name="keterangan" rows="2" class="ui-input w-full" placeholder="Catatan jenis perlengkapan...">{{ old('keterangan') }}</textarea>
                </div>

                <button type="submit" class="btn-blue w-full text-xs">
                    Simpan Kategori
                </button>
            </form>
        </div>

        <!-- Tabel Kategori -->
        <div class="lg:col-span-2 panel-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Keterangan</th>
                            <th>Aset Terdaftar</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoris as $kat)
                            <tr>
                                <td class="font-semibold text-slate-100 text-sm">{{ $kat->nama }}</td>
                                <td class="text-xs text-slate-300">{{ $kat->keterangan ?: '-' }}</td>
                                <td>
                                    <span class="pill-blue text-[10px]">{{ $kat->barang_count }} unit</span>
                                </td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('master.kategori.destroy', $kat->id) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded bg-slate-900 text-slate-400 hover:text-rose-400 hover:bg-slate-800 text-xs transition" title="Hapus">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-slate-500 text-xs">Belum ada kategori terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($kategoris->hasPages())
                <div class="p-3.5 border-t border-slate-800">
                    {{ $kategoris->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
