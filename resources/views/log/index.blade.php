@extends('layouts.app')

@section('title', 'Audit Trail - Log Aktivitas')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Audit Trail & Log Aktivitas</h1>
            <p class="text-xs text-slate-400 mt-0.5">Riwayat mutasi data barang, ruangan, kategori, peminjaman, dan keuangan.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="panel-card p-4">
        <form method="GET" action="{{ route('log.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <div>
                <select name="entitas" class="ui-input w-full text-xs bg-slate-900" onchange="this.form.submit()">
                    <option value="">Semua Entitas</option>
                    <option value="BARANG" {{ request('entitas') == 'BARANG' ? 'selected' : '' }}>BARANG (Aset)</option>
                    <option value="PEMINJAMAN" {{ request('entitas') == 'PEMINJAMAN' ? 'selected' : '' }}>PEMINJAMAN</option>
                    <option value="PENGELUARAN" {{ request('entitas') == 'PENGELUARAN' ? 'selected' : '' }}>PENGELUARAN</option>
                    <option value="RUANGAN" {{ request('entitas') == 'RUANGAN' ? 'selected' : '' }}>RUANGAN</option>
                    <option value="KATEGORI" {{ request('entitas') == 'KATEGORI' ? 'selected' : '' }}>KATEGORI</option>
                    <option value="USER" {{ request('entitas') == 'USER' ? 'selected' : '' }}>USER / TIM</option>
                </select>
            </div>

            <div>
                <select name="aksi" class="ui-input w-full text-xs bg-slate-900" onchange="this.form.submit()">
                    <option value="">Semua Aksi</option>
                    <option value="CREATE" {{ request('aksi') == 'CREATE' ? 'selected' : '' }}>CREATE (Tambah)</option>
                    <option value="UPDATE" {{ request('aksi') == 'UPDATE' ? 'selected' : '' }}>UPDATE (Perbarui / Restore)</option>
                    <option value="DELETE" {{ request('aksi') == 'DELETE' ? 'selected' : '' }}>DELETE (Hapus)</option>
                    <option value="IMPORT" {{ request('aksi') == 'IMPORT' ? 'selected' : '' }}>IMPORT (Excel)</option>
                </select>
            </div>

            <div class="flex items-center justify-end">
                @if(request()->anyFilled(['entitas', 'aksi']))
                    <a href="{{ route('log.index') }}" class="text-xs text-slate-400 hover:text-white underline">Reset Filter</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="panel-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu Kejadian</th>
                        <th>Aksi & Entitas</th>
                        <th>Detail Aktivitas</th>
                        <th>User Pelaku</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap">
                                <div class="text-xs font-mono text-slate-300">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                <div class="text-[10px] text-slate-500">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-[10px] px-1.5 py-0.5 rounded {{ $log->aksi === 'CREATE' ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : ($log->aksi === 'DELETE' ? 'bg-rose-950 text-rose-400 border border-rose-500/30' : 'bg-blue-950 text-blue-400 border border-blue-500/30') }}">
                                        {{ $log->aksi }}
                                    </span>
                                    <span class="font-mono text-xs text-slate-300">{{ $log->entitas }}</span>
                                </div>
                            </td>
                            <td>
                                <p class="text-xs text-slate-200 leading-relaxed">{{ $log->detail }}</p>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-200">{{ $log->user->name ?? 'System' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $log->user->role ?? '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-slate-500 text-xs">Belum ada riwayat aktivitas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-3.5 border-t border-slate-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
