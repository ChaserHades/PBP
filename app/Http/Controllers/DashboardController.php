<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin()
    {
        $totalAset = Barang::count();
        $asetTersedia = Barang::where('status', 'TERSEDIA')->count();
        $asetDipinjam = Barang::where('status', 'DIPINJAM')->count();
        $asetMaintenance = Barang::where('status', 'MAINTENANCE')->count();

        $peminjamanAktif = Peminjaman::where('status', 'AKTIF')->count();
        $peminjamanTerlambat = Peminjaman::where('terlambat', true)->count();

        $totalPengeluaran = Pengeluaran::sum('nominal');
        $totalUserPending = User::where('status_verifikasi', 'PENDING')->count();

        $kondisiDistribusi = [
            'BAIK' => Barang::where('kondisi', 'BAIK')->count(),
            'RUSAK_RINGAN' => Barang::where('kondisi', 'RUSAK_RINGAN')->count(),
            'RUSAK_BERAT' => Barang::where('kondisi', 'RUSAK_BERAT')->count(),
        ];

        $recentLogs = LogAktivitas::with('user')->latest('created_at')->take(8)->get();
        $recentPeminjaman = Peminjaman::with(['barang', 'user'])->latest('tanggal_pinjam')->take(5)->get();

        return view('dashboard.admin', compact(
            'totalAset',
            'asetTersedia',
            'asetDipinjam',
            'asetMaintenance',
            'peminjamanAktif',
            'peminjamanTerlambat',
            'totalPengeluaran',
            'totalUserPending',
            'kondisiDistribusi',
            'recentLogs',
            'recentPeminjaman'
        ));
    }

    public function teknisi()
    {
        $totalAset = Barang::count();
        $asetTersedia = Barang::where('status', 'TERSEDIA')->count();
        $peminjamanAktif = Peminjaman::where('status', 'AKTIF')->count();
        $peminjamanTerlambat = Peminjaman::where('terlambat', true)->count();

        $kondisiDistribusi = [
            'BAIK' => Barang::where('kondisi', 'BAIK')->count(),
            'RUSAK_RINGAN' => Barang::where('kondisi', 'RUSAK_RINGAN')->count(),
            'RUSAK_BERAT' => Barang::where('kondisi', 'RUSAK_BERAT')->count(),
        ];

        $recentPeminjaman = Peminjaman::with(['barang', 'user'])->where('status', 'AKTIF')->latest('tanggal_pinjam')->take(6)->get();
        $recentLogs = LogAktivitas::whereHas('user', fn($q) => $q->where('role', 'TEKNISI'))->with('user')->latest('created_at')->take(6)->get();

        return view('dashboard.teknisi', compact(
            'totalAset',
            'asetTersedia',
            'peminjamanAktif',
            'peminjamanTerlambat',
            'kondisiDistribusi',
            'recentPeminjaman',
            'recentLogs'
        ));
    }

    public function universitas()
    {
        $totalAset = Barang::count();
        $totalNilaiAset = Barang::sum('harga') ?? 0;
        $totalPengeluaran = Pengeluaran::sum('nominal');

        $asetPerProgdi = Barang::selectRaw('progdi, count(*) as count, sum(harga) as total_nilai')
            ->groupBy('progdi')
            ->get();

        $asetPerKategori = Barang::with('kategori')
            ->selectRaw('kategori_id, count(*) as count')
            ->groupBy('kategori_id')
            ->get();

        $recentPengeluaran = Pengeluaran::with('user')->latest('tanggal')->take(5)->get();

        return view('dashboard.universitas', compact(
            'totalAset',
            'totalNilaiAset',
            'totalPengeluaran',
            'asetPerProgdi',
            'asetPerKategori',
            'recentPengeluaran'
        ));
    }
}
