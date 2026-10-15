@extends('layouts.app')

@section('title', 'Pembaruan Kata Sandi Wajib - FTI UKSW')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="panel-card p-7 sm:p-8 border border-amber-500/30 shadow-2xl relative">
        <div class="mb-6">
            <div class="w-10 h-10 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-base mb-3 border border-amber-500/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h1 class="text-lg font-bold text-white tracking-tight">Perbarui Password Akun</h1>
            <p class="text-xs text-slate-400 mt-1">Akun Anda disetel untuk wajib memperbarui kata sandi sebelum dapat mengakses data inventaris.</p>
        </div>

        <form method="POST" action="{{ route('password.force_change.update') }}" class="space-y-4">
            @csrf
            <div>
                <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Kata Sandi Baru</label>
                <input id="password" type="password" name="password" required autofocus class="ui-input w-full" placeholder="Minimal 6 karakter">
                @error('password')
                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-slate-300 mb-1.5">Ulangi Kata Sandi Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="ui-input w-full" placeholder="Ketik ulang kata sandi">
            </div>

            <button type="submit" class="btn-blue w-full py-2.5 text-sm mt-3">
                Simpan & Masuk ke Dashboard
            </button>
        </form>
    </div>
</div>
@endsection
