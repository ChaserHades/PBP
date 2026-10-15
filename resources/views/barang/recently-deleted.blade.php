@extends('layouts.app')

@section('title', 'Recently Deleted Assets - Retensi 6 Hari')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
        <div>
            <h1 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                Recently Deleted Assets
                <span class="pill-yellow text-[10px]">Jendela Retensi 6 Hari</span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Barang yang di-soft delete dapat dipulihkan dalam 6 hari sebelum berstatus EXPIRED.</p>
        </div>
        <a href="{{ route('barang.index') }}" class="btn-secondary text-xs">&larr; Kembali</a>
    </div>

    <div class="panel-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Identitas Barang</th>
                        <th>Kategori & Ruangan</th>
                        <th>Waktu Dihapus</th>
                        <th>Sisa Waktu Retensi</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangs as $item)
                        @php
                            $deletedAt = $item->deleted_at;
                            $expiresAt = $deletedAt->copy()->addDays(6);
                            $isExpired = now()->gt($expiresAt);
                            $diffDays = $isExpired ? 0 : now()->diffInDays($expiresAt);
                            $diffHours = $isExpired ? 0 : now()->diffInHours($expiresAt) % 24;
                        @endphp
                        <tr>
                            <td>
                                <div class="font-bold text-slate-200">{{ $item->nama_barang }}</div>
                                <div class="font-mono text-xs text-blue-400 mt-0.5">{{ $item->label }}</div>
                            </td>
                            <td>
                                <div class="text-xs text-slate-300">{{ $item->kategori->nama ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $item->ruangan->nama ?? 'Tanpa Ruangan' }}</div>
                            </td>
                            <td>
                                <div class="text-xs text-slate-300">{{ $item->deleted_at->format('d/m/Y H:i') }}</div>
                                <div class="text-[10px] text-slate-500">{{ $item->deleted_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if(!$isExpired)
                                    <span class="pill-green text-[10px]">
                                        RECOVERABLE ({{ $diffDays }}h {{ $diffHours }}j)
                                    </span>
                                @else
                                    <span class="pill-red text-[10px]">
                                        EXPIRED (> 6 Hari)
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    @if(!$isExpired)
                                        <form method="POST" action="{{ route('barang.restore', $item->id) }}">
                                            @csrf
                                            <button type="submit" class="btn-blue !py-1 !px-2.5 !text-xs">
                                                Restore
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN']))
                                        <form method="POST" action="{{ route('barang.forceDelete', $item->id) }}" onsubmit="return confirm('Hapus permanen unit ini dari basis data?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded bg-slate-900 text-slate-400 hover:text-rose-400 hover:bg-slate-800 text-xs transition" title="Hapus Permanen">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-500 text-xs">
                                Tidak ada barang dalam status Recently Deleted saat ini.
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
