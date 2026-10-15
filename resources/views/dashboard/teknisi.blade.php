@extends('layouts.app')

@section('title', 'Dashboard Petugas Teknisi')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Meja Kerja Teknisi Lab</h1>
            <p class="text-xs text-slate-400 mt-0.5">Layanan peminjaman alat, pengecekan ketersediaan aset, dan pengembalian.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('peminjaman.create') }}" class="btn-blue text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Peminjaman Baru
            </a>
            <a href="{{ route('barang.create') }}" class="btn-secondary text-xs">
                Tambah Aset Lab
            </a>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="panel-card p-4 border-l-2 border-l-emerald-500">
            <span class="text-[11px] text-slate-400 uppercase font-medium">Aset Tersedia di Lab</span>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($asetTersedia) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Siap digunakan untuk kegiatan praktikum</div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-amber-500">
            <span class="text-[11px] text-slate-400 uppercase font-medium">Sedang Dipinjam</span>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($peminjamanAktif) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Dalam status peminjaman aktif</div>
        </div>

        <div class="panel-card p-4 border-l-2 border-l-rose-500">
            <span class="text-[11px] text-slate-400 uppercase font-medium">Melewati Batas Waktu</span>
            <div class="text-2xl font-black text-rose-400 mt-1">{{ number_format($peminjamanTerlambat) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Perlu konfirmasi ke peminjam</div>
        </div>
    </div>

    <!-- Active Loans Table -->
    <div class="panel-card p-5">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-800">
            <h2 class="text-sm font-bold text-white">Daftar Peminjaman Aktif</h2>
            <a href="{{ route('peminjaman.index') }}" class="text-xs text-blue-400 hover:underline">Lihat Semua &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barang & Label</th>
                        <th>Peminjam</th>
                        <th>Ruangan Pemakaian</th>
                        <th>Batas Waktu</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPeminjaman as $p)
                        @php $isTelat = $p->checkIsOverdue(); @endphp
                        <tr>
                            <td>
                                <div class="font-semibold text-slate-200">{{ $p->barang->nama_barang ?? 'Aset' }}</div>
                                <div class="text-xs font-mono text-blue-400">{{ $p->barang->label }}</div>
                            </td>
                            <td class="text-slate-300 font-medium">{{ $p->nama_peminjam }}</td>
                            <td class="text-slate-400">{{ $p->ruangan_tujuan }}</td>
                            <td>
                                <div class="text-xs {{ $isTelat ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                                    {{ $p->deadline_kembali->format('d/m/Y H:i') }}
                                </div>
                                @if($isTelat)
                                    <span class="text-[10px] text-rose-400 font-medium">Terlambat {{ now()->diffForHumans($p->deadline_kembali, true) }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('peminjaman.kembalikan', $p->id) }}">
                                    @csrf
                                    <button type="submit" class="btn-blue !py-1 !px-2.5 !text-xs">
                                        Proses Kembali
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-500 text-xs">Tidak ada barang yang sedang dipinjam saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
