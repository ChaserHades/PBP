@extends('layouts.app')

@section('title', 'Cashflow & Belanja Aset - FTI UKSW')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Buku Kas & Pengeluaran Aset</h1>
            <p class="text-xs text-slate-400 mt-0.5">Catatan belanja pengadaan barang, perbaikan, dan pengelompokan batch pembelian.</p>
        </div>

        @if(in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN']))
            <a href="{{ route('pengeluaran.create') }}" class="btn-blue text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Catat Belanja Baru
            </a>
        @endif
    </div>

    <!-- Total Nominal Summary Strip -->
    <div class="panel-card p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <span class="text-[11px] uppercase tracking-wider text-slate-400 font-medium">Akumulasi Belanja Tercatat</span>
            <div class="text-2xl font-bold text-blue-400 mt-0.5 font-mono">
                Rp {{ number_format($totalNominal, 0, ',', '.') }}
            </div>
        </div>

        <form method="GET" action="{{ route('pengeluaran.index') }}" class="flex items-center gap-2">
            <select name="jenis" class="ui-input text-xs bg-slate-900" onchange="this.form.submit()">
                <option value="">Semua Jenis Pengeluaran</option>
                <option value="PEMBELIAN_ASET" {{ request('jenis') == 'PEMBELIAN_ASET' ? 'selected' : '' }}>Pembelian Aset</option>
                <option value="MAINTENANCE" {{ request('jenis') == 'MAINTENANCE' ? 'selected' : '' }}>Maintenance / Servis</option>
                <option value="LAINNYA" {{ request('jenis') == 'LAINNYA' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="panel-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Dokumen & Judul Belanja</th>
                        <th>Tanggal</th>
                        <th>Kategori Belanja</th>
                        <th>Nominal</th>
                        <th>Unit Terkait</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengeluarans as $exp)
                        <tr>
                            <td>
                                <div class="font-bold text-slate-100 text-sm">
                                    <a href="{{ route('pengeluaran.show', $exp->id) }}" class="hover:text-blue-400 transition">
                                        {{ $exp->judul }}
                                    </a>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                                    <span class="font-mono text-[11px] text-blue-400">{{ $exp->kode_pembelian ?: '-' }}</span>
                                    @if($exp->nomor_bukti)
                                        <span>&bull;</span>
                                        <span>No: {{ $exp->nomor_bukti }} ({{ $exp->jenis_bukti ?: 'NOTA' }})</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-xs text-slate-300">{{ $exp->tanggal->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-500">Staf: {{ $exp->user->name ?? 'Admin' }}</div>
                            </td>
                            <td>
                                <span class="pill-blue text-[10px]">
                                    {{ str_replace('_', ' ', $exp->jenis) }}
                                </span>
                            </td>
                            <td>
                                <div class="font-mono font-bold text-sm text-slate-200">
                                    Rp {{ number_format($exp->nominal, 0, ',', '.') }}
                                </div>
                            </td>
                            <td>
                                <span class="text-xs text-slate-300 font-medium">
                                    {{ $exp->barang->count() }} unit
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('pengeluaran.show', $exp->id) }}" class="p-1.5 rounded bg-slate-900 text-slate-300 hover:text-white hover:bg-slate-800 transition" title="Detail">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>

                                    @if(in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN']))
                                        <form method="POST" action="{{ route('pengeluaran.destroy', $exp->id) }}" onsubmit="return confirm('Hapus catatan pengeluaran ini?')">
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
                            <td colspan="6" class="text-center py-8 text-slate-500 text-xs">Belum ada catatan pengeluaran belanja.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengeluarans->hasPages())
            <div class="p-3.5 border-t border-slate-800">
                {{ $pengeluarans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
