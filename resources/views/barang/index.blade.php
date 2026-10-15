@extends('layouts.app')

@section('title', 'Daftar Aset & Inventaris - FTI UKSW')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Inventaris Aset Lab & Kantor</h1>
            <p class="text-xs text-slate-400 mt-0.5">Daftar seluruh barang inventaris FTI UKSW beserta status penempatan dan kondisinya.</p>
        </div>

        @if(in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN', 'TEKNISI']))
            <div class="flex items-center gap-2">
                <a href="{{ route('barang.recentlyDeleted') }}" class="btn-secondary text-xs text-amber-400 hover:text-amber-300">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Recently Deleted
                </a>
                <a href="{{ route('barang.create') }}" class="btn-blue text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Barang
                </a>
            </div>
        @endif
    </div>

    <!-- Quick Status Filter Pills & Search -->
    <div class="panel-card p-4 space-y-3">
        <!-- Quick Status Filter Pills -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs pb-3 border-b border-slate-800/80">
            <span class="text-slate-400 font-medium mr-1">Status:</span>
            <a href="{{ route('barang.index') }}" class="px-2.5 py-1 rounded-md transition {{ !request('status') ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-900 text-slate-300 hover:bg-slate-800' }}">
                Semua
            </a>
            <a href="{{ route('barang.index', array_merge(request()->query(), ['status' => 'TERSEDIA'])) }}" class="px-2.5 py-1 rounded-md transition {{ request('status') === 'TERSEDIA' ? 'bg-emerald-600 text-white font-semibold' : 'bg-slate-900 text-slate-300 hover:bg-slate-800' }}">
                Tersedia
            </a>
            <a href="{{ route('barang.index', array_merge(request()->query(), ['status' => 'DIPINJAM'])) }}" class="px-2.5 py-1 rounded-md transition {{ request('status') === 'DIPINJAM' ? 'bg-amber-600 text-white font-semibold' : 'bg-slate-900 text-slate-300 hover:bg-slate-800' }}">
                Sedang Dipinjam
            </a>
            <a href="{{ route('barang.index', array_merge(request()->query(), ['status' => 'MAINTENANCE'])) }}" class="px-2.5 py-1 rounded-md transition {{ request('status') === 'MAINTENANCE' ? 'bg-rose-600 text-white font-semibold' : 'bg-slate-900 text-slate-300 hover:bg-slate-800' }}">
                Perbaikan / Maintenance
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('barang.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, label FLAS-SAN-..., SN, merek..." class="ui-input w-full text-xs">
            </div>

            <div>
                <select name="kategori_id" class="ui-input w-full text-xs bg-slate-900" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="ruangan_id" class="ui-input w-full text-xs bg-slate-900" onchange="this.form.submit()">
                    <option value="">Semua Ruangan</option>
                    @foreach($ruangans as $ruang)
                        <option value="{{ $ruang->id }}" {{ request('ruangan_id') == $ruang->id ? 'selected' : '' }}>{{ $ruang->nama }} ({{ $ruang->kode }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <select name="kondisi" class="ui-input w-full text-xs bg-slate-900" onchange="this.form.submit()">
                    <option value="">Semua Kondisi</option>
                    <option value="BAIK" {{ request('kondisi') == 'BAIK' ? 'selected' : '' }}>BAIK</option>
                    <option value="RUSAK_RINGAN" {{ request('kondisi') == 'RUSAK_RINGAN' ? 'selected' : '' }}>RUSAK RINGAN</option>
                    <option value="RUSAK_BERAT" {{ request('kondisi') == 'RUSAK_BERAT' ? 'selected' : '' }}>RUSAK BERAT</option>
                </select>
                @if(request()->anyFilled(['search', 'kategori_id', 'ruangan_id', 'kondisi', 'status']))
                    <a href="{{ route('barang.index') }}" class="btn-secondary !p-2 !rounded-md text-xs" title="Reset Filter">
                        &times;
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="panel-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Identitas Aset</th>
                        <th>Kategori & Lokasi</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th>Progdi</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangs as $item)
                        <tr>
                            <td>
                                <div class="font-bold text-slate-100 hover:text-blue-400 transition">
                                    <a href="{{ route('barang.show', $item->id) }}">{{ $item->nama_barang }}</a>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="font-mono text-[11px] text-blue-400 bg-blue-950/60 px-1.5 py-0.5 rounded border border-blue-500/20">
                                        {{ $item->label }}
                                    </span>
                                    @if($item->label_aset)
                                        <span class="font-mono text-[11px] text-slate-400">Univ: {{ $item->label_aset }}</span>
                                    @endif
                                    @if($item->sn)
                                        <span class="text-[10px] text-slate-500">SN: {{ $item->sn }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-xs font-medium text-slate-300">{{ $item->kategori->nama ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $item->ruangan ? $item->ruangan->nama . ' (' . $item->ruangan->kode . ')' : 'Belum Ditentukan' }}</div>
                            </td>
                            <td>
                                @if($item->kondisi === 'BAIK')
                                    <span class="pill-green text-[10px]">BAIK</span>
                                @elseif($item->kondisi === 'RUSAK_RINGAN')
                                    <span class="pill-yellow text-[10px]">R. RINGAN</span>
                                @else
                                    <span class="pill-red text-[10px]">R. BERAT</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status === 'TERSEDIA')
                                    <span class="pill-green text-[10px]">Tersedia</span>
                                @elseif($item->status === 'DIPINJAM')
                                    <span class="pill-yellow text-[10px]">Dipinjam</span>
                                @else
                                    <span class="pill-red text-[10px]">Maintenance</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-xs text-slate-400">{{ $item->progdi ?: 'Fakultas' }}</div>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('barang.show', $item->id) }}" class="p-1.5 rounded bg-slate-900 text-slate-300 hover:text-white hover:bg-slate-800 transition" title="Detail">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>

                                    @if(in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN', 'TEKNISI']))
                                        <a href="{{ route('barang.edit', $item->id) }}" class="p-1.5 rounded bg-slate-900 text-slate-300 hover:text-blue-400 hover:bg-slate-800 transition" title="Edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    @endif

                                    @if(in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN']))
                                        <form method="POST" action="{{ route('barang.destroy', $item->id) }}" onsubmit="return confirm('Pindahkan barang ini ke Recently Deleted (retensi 6 hari)?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded bg-slate-900 text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition" title="Hapus">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-500 text-xs">
                                Tidak ada barang yang cocok dengan kriteria pencarian saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($barangs->hasPages())
            <div class="p-3.5 border-t border-slate-800">
                {{ $barangs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
