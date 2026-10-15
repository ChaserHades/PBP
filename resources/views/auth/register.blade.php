@extends('layouts.app')

@section('title', 'Daftar Akun Baru')

@section('content')
<div class="max-w-md mx-auto mt-8">
    <div class="panel-card p-7 sm:p-8 border border-slate-800 shadow-2xl">
        <div class="mb-5">
            <h1 class="text-lg font-bold text-white tracking-tight">Pendaftaran Akun Baru</h1>
            <p class="text-xs text-slate-400 mt-1">Pengajuan akses staf lab, teknisi, atau perwakilan universitas.</p>
        </div>

        <div class="mb-5 p-3 rounded-md bg-blue-950/40 border border-blue-500/20 text-blue-300 text-xs flex items-start gap-2.5">
            <svg class="w-4 h-4 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                Akun baru akan berstatus <strong>PENDING</strong> dan perlu disetujui Admin sebelum dapat digunakan untuk login.
            </div>
        </div>

        <form method="POST" action="{{ route('register.post') }}" class="space-y-3.5">
            @csrf
            <div>
                <label for="name" class="block text-xs font-medium text-slate-300 mb-1">Nama Lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="ui-input w-full" placeholder="Nama staf / dosen">
                @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-slate-300 mb-1">Email UKSW</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="ui-input w-full" placeholder="nama@staff.uksw.edu">
                @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="block text-xs font-medium text-slate-300 mb-1">Peran Akun</label>
                <select id="role" name="role" class="ui-input w-full bg-slate-900">
                    <option value="TEKNISI">TEKNISI (Petugas Inventaris / Lab)</option>
                    <option value="UNIVERSITAS">UNIVERSITAS (Auditor / Pimpinan)</option>
                </select>
                @error('role') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-slate-300 mb-1">Password</label>
                <input id="password" type="password" name="password" required class="ui-input w-full" placeholder="Minimal 6 karakter">
                @error('password') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-slate-300 mb-1">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="ui-input w-full" placeholder="Ulangi password">
            </div>

            <button type="submit" class="btn-blue w-full py-2.5 text-sm mt-3">
                Kirim Pengajuan
            </button>
        </form>

        <div class="mt-5 pt-4 border-t border-slate-800 text-center text-xs text-slate-400">
            Sudah ada akun? <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-semibold underline">Masuk di sini</a>
        </div>
    </div>
</div>
@endsection
