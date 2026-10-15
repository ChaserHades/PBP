<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status_verifikasi' => 'APPROVED']);

        LogAktivitas::catat('UPDATE', 'USER', $user->id, "Menyetujui akun pengguna: {$user->name} ({$user->email})");

        return back()->with('success', "Akun {$user->name} telah disetujui (APPROVED)!");
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status_verifikasi' => 'REJECTED']);

        LogAktivitas::catat('UPDATE', 'USER', $user->id, "Menolak akun pengguna: {$user->name} ({$user->email})");

        return back()->with('success', "Akun {$user->name} telah ditolak (REJECTED).");
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($request->new_password),
            'must_change_password' => true,
        ]);

        LogAktivitas::catat('UPDATE', 'USER', $user->id, "Admin mereset kata sandi pengguna {$user->name} (flag must_change_password diaktifkan)");

        return back()->with('success', "Sandi pengguna {$user->name} berhasil direset. Pengguna wajib mengganti sandi pada login berikutnya.");
    }
}
