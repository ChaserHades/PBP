@extends('layouts.app')

@section('title', 'Masuk - Inventaris FTI UKSW')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="panel-card p-7 sm:p-8 border border-slate-800 shadow-2xl relative">
        <div class="mb-6">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white text-base font-bold shadow-md shadow-blue-600/30 mb-3">
                FTI
            </div>
            <h1 class="text-lg font-bold text-white tracking-tight">Masuk ke Portal Inventaris</h1>
            <p class="text-xs text-slate-400 mt-1">Gunakan akun staf atau teknisi FTI UKSW yang telah terdaftar.</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="ui-input w-full" placeholder="nama@uksw.edu">
                @error('email')
                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-medium text-slate-300">Password</label>
                </div>
                <input id="password" type="password" name="password" required class="ui-input w-full" placeholder="••••••••">
                @error('password')
                    <p class="text-xs text-rose-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
                    <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-blue-600 focus:ring-blue-500">
                    <span>Ingat sesi masuk</span>
                </label>
            </div>

            <button type="submit" class="btn-blue w-full py-2.5 text-sm mt-2">
                Masuk
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
            Belum terdaftar? <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 font-semibold underline">Daftar akun staf/teknisi</a>
        </div>
    </div>
</div>
@endsection
