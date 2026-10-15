<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Role UNIVERSITAS tidak diizinkan mengakses log aktivitas
        if ($user->role === 'UNIVERSITAS') {
            abort(403, 'Akses Ditolak: Peran UNIVERSITAS tidak memiliki izin mengakses Audit Trail.');
        }

        $query = LogAktivitas::with('user');

        // Jika TEKNISI: hanya melihat log dari pengguna ber-role TEKNISI
        if ($user->role === 'TEKNISI') {
            $query->whereHas('user', function ($uq) {
                $uq->where('role', 'TEKNISI');
            });
        }

        // Filter entitas
        if ($request->filled('entitas')) {
            $query->where('entitas', $request->entitas);
        }

        // Filter aksi
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('log.index', compact('logs'));
    }
}
