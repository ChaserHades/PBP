@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Staf - FTI UKSW')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-800 gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-white tracking-tight">Otoritas & Akun Staf</h1>
                <span class="pill-blue text-[10px] font-mono font-semibold">Closed-Environment</span>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Kelola akses sistem inventaris, persetujuan pendaftaran akun baru, dan perizinan peran kerja (RBAC).</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-medium">Total Akun: <strong class="text-white">{{ $users->total() }}</strong></span>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="panel-card p-3.5">
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="ui-input w-full text-xs">
            </div>

            <div>
                <select name="status" class="ui-input w-full text-xs bg-slate-900" onchange="this.form.submit()">
                    <option value="">Semua Status Pendaftaran</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>PENDING (Menunggu Approval)</option>
                    <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>APPROVED (Aktif)</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>REJECTED (Ditolak)</option>
                </select>
            </div>

            <div>
                <select name="role" class="ui-input w-full text-xs bg-slate-900" onchange="this.form.submit()">
                    <option value="">Semua Peran (Role)</option>
                    <option value="ADMIN" {{ request('role') == 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                    <option value="TEKNISI" {{ request('role') == 'TEKNISI' ? 'selected' : '' }}>TEKNISI</option>
                    <option value="UNIVERSITAS" {{ request('role') == 'UNIVERSITAS' ? 'selected' : '' }}>UNIVERSITAS</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="panel-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Peran (Role)</th>
                        <th>Status Akun</th>
                        <th>Status Sandi</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-md bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-blue-400 shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-white text-sm">{{ $u->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="pill-blue text-[11px] font-mono font-semibold">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td>
                                @if($u->status_verifikasi === 'APPROVED')
                                    <span class="pill-green text-[11px]">Aktif (Approved)</span>
                                @elseif($u->status_verifikasi === 'PENDING')
                                    <span class="pill-yellow text-[11px]">Menunggu Approval</span>
                                @else
                                    <span class="pill-red text-[11px]">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                @if($u->must_change_password)
                                    <span class="pill-yellow text-[10px]">Wajib Ganti Password</span>
                                @else
                                    <span class="text-xs text-slate-500">Normal</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    @if($u->status_verifikasi === 'PENDING')
                                        <form method="POST" action="{{ route('users.approve', $u->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-blue text-[11px] py-1 px-2.5">
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('users.reject', $u->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-secondary text-[11px] py-1 px-2.5 text-rose-400 hover:text-rose-300">
                                                Tolak
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Reset Password Prompt -->
                                    <button type="button" onclick="const p = prompt('Masukkan password sementara baru untuk {{ $u->name }}:'); if(p) { document.getElementById('pw-{{ $u->id }}').value = p; document.getElementById('form-reset-{{ $u->id }}').submit(); }" class="btn-secondary text-[11px] py-1 px-2.5">
                                        Reset Sandi
                                    </button>

                                    <form id="form-reset-{{ $u->id }}" method="POST" action="{{ route('users.resetPassword', $u->id) }}" class="hidden">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="new_password" id="pw-{{ $u->id }}">
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-500 text-xs">
                                Tidak ada akun yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-3.5 border-t border-slate-800/80 bg-slate-900/40">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
