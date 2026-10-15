<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
            ]);
        }

        // Closed-Environment check
        if ($user->status_verifikasi === 'PENDING') {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Akun Anda sedang menunggu persetujuan (PENDING) dari Admin sebelum dapat digunakan.',
            ]);
        }

        if ($user->status_verifikasi === 'REJECTED') {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Akun Anda ditolak (REJECTED) oleh Admin. Hubungi pengelola untuk informasi lebih lanjut.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Check if forced password change is flagged
        if ($user->must_change_password) {
            return redirect()->route('password.force_change');
        }

        return $this->redirectBasedOnRole($user);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|in:TEKNISI,UNIVERSITAS',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'TEKNISI',
            'status_verifikasi' => 'PENDING',
            'must_change_password' => false,
        ]);

        LogAktivitas::catat('CREATE', 'USER', $user->id, "Registrasi akun baru: {$user->name} ({$user->email}) - status PENDING", $user->id);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Akun Anda sedang menunggu persetujuan Admin.');
    }

    public function showForceChangePassword()
    {
        return view('auth.force-change-password');
    }

    public function updateForceChangePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        LogAktivitas::catat('UPDATE', 'USER', $user->id, "Pengguna {$user->name} memperbarui kata sandi wajib (force change password)");

        return $this->redirectBasedOnRole($user)->with('success', 'Kata sandi Anda berhasil diperbarui!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    protected function redirectBasedOnRole(User $user)
    {
        return match ($user->role) {
            'SUPERADMIN', 'ADMIN' => redirect()->route('admin.dashboard'),
            'TEKNISI' => redirect()->route('petugas.dashboard'),
            'UNIVERSITAS' => redirect()->route('universitas.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
