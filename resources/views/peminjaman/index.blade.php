@extends('layouts.app')

@section('title', 'Transaksi Peminjaman - FTI UKSW')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Peminjaman & Pengembalian Alat Lab</h1>
            <p class="text-xs text-slate-400 mt-0.5">Catatan peminjaman alat inventaris untuk keperluan praktikum, riset, atau kegiatan fakultas.</p>
        </div>

        <a href="{{ route('peminjaman.create') }}" class="btn-blue text-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Catat Peminjaman Baru
        </a>
    </div>

    <!-- Filter Card -->
    <div class="panel-card p-4">
        <form method="GET" action="{{ route('peminjaman.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam, barang, label, ruangan..." class="ui-input w-full text-xs">
            </div>

            <div>
                <select name="status" class="ui-input w-full text-xs bg-slate-900" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="AKTIF" {{ request('status') == 'AKTIF' ? 'selected' : '' }}>AKTIF (Sedang Dipinjam)</option>
                    <option value="SELESAI" {{ request('status') == 'SELESAI' ? 'selected' : '' }}>SELESAI (Sudah Dikembalikan)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer px-3 ui-input w-full">
                    <input type="checkbox" name="terlambat" value="1" {{ request('terlambat') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded bg-slate-900 border-slate-700 text-blue-600">
                    <span>Hanya Terlambat</span>
                </label>
                @if(request()->anyFilled(['search', 'status', 'terlambat']))
                    <a href="{{ route('peminjaman.index') }}" class="btn-secondary !p-2 !rounded-md text-xs" title="Reset">
                        &times;
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Loans Table -->
    <div class="panel-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barang & Label</th>
                        <th>Peminjam & Ruangan</th>
                        <th>Waktu Pinjam</th>
                        <th>Tenggat Pengembalian</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamans as $loan)
                        @php $isOverdue = $loan->checkIsOverdue(); @endphp
                        <tr>
                            <td>
                                <div class="font-bold text-slate-100 text-sm">
                                    {{ $loan->barang->nama_barang ?? 'Aset Dihapus' }}
                                </div>
                                <div class="font-mono text-xs text-blue-400 mt-0.5">
                                    {{ $loan->barang->label ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="text-xs font-semibold text-slate-200">{{ $loan->nama_peminjam }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Ruangan: <span class="text-slate-300">{{ $loan->ruangan_tujuan }}</span></div>
                            </td>
                            <td>
                                <div class="text-xs text-slate-300">{{ $loan->tanggal_pinjam->format('d/m/Y H:i') }}</div>
                                <div class="text-[10px] text-slate-500">Petugas: {{ $loan->user->name ?? 'System' }}</div>
                            </td>
                            <td>
                                <div class="text-xs {{ $isOverdue && $loan->status === 'AKTIF' ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                                    {{ $loan->deadline_kembali->format('d/m/Y H:i') }}
                                </div>
                                @if($loan->status === 'AKTIF')
                                    <div class="text-[10px] {{ $isOverdue ? 'text-rose-400 font-medium' : 'text-slate-500' }}">
                                        {{ $isOverdue ? 'Telat ' . now()->diffForHumans($loan->deadline_kembali, true) : 'Sisa ' . now()->diffForHumans($loan->deadline_kembali, true) }}
                                    </div>
                                @else
                                    <div class="text-[10px] text-slate-500">
                                        Kembali: {{ $loan->tanggal_kembali?->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    @if($loan->status === 'AKTIF')
                                        <span class="pill-yellow text-[10px]">DIPINJAM</span>
                                    @else
                                        <span class="pill-green text-[10px]">SELESAI</span>
                                    @endif

                                    @if($loan->terlambat || ($loan->status === 'AKTIF' && $isOverdue))
                                        <span class="pill-red text-[10px] font-bold">TERLAMBAT</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right">
                                @if($loan->status === 'AKTIF')
                                    <form method="POST" action="{{ route('peminjaman.kembalikan', $loan->id) }}" onsubmit="return confirm('Konfirmasi pengembalian barang ini?')">
                                        @csrf
                                        <button type="submit" class="btn-blue !py-1 !px-2.5 !text-xs">
                                            Kembalikan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500 font-medium">&check; Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-500 text-xs">
                                Belum ada data transaksi peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($peminjamans->hasPages())
            <div class="p-3.5 border-t border-slate-800">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
