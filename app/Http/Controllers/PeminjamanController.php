<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = Peminjaman::with(['barang', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('terlambat')) {
            $query->where('terlambat', true);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_peminjam', 'like', "%{$search}%")
                  ->orWhere('ruangan_tujuan', 'like', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('nama_barang', 'like', "%{$search}%")
                         ->orWhere('label', 'like', "%{$search}%")
                         ->orWhere('label_aset', 'like', "%{$search}%");
                  });
            });
        }

        $peminjamans = $query->latest('tanggal_pinjam')->paginate(15)->withQueryString();

        return view('peminjaman.index', compact('peminjamans'));
    }

    public function create()
    {
        $barangs = Barang::where('status', 'TERSEDIA')->orderBy('nama_barang')->get();

        return view('peminjaman.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'nama_peminjam' => 'required|string|max:255',
            'ruangan_tujuan' => 'required|string|max:255',
            'deadline_kembali' => 'required|date|after_or_equal:today',
            'catatan' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $barang = Barang::lockForUpdate()->findOrFail($validated['barang_id']);

            if ($barang->status !== 'TERSEDIA') {
                return back()->with('error', 'Barang ini sedang tidak berstatus TERSEDIA.');
            }

            $peminjaman = Peminjaman::create([
                'barang_id' => $barang->id,
                'user_id' => Auth::id(),
                'nama_peminjam' => $validated['nama_peminjam'],
                'ruangan_tujuan' => $validated['ruangan_tujuan'],
                'tanggal_pinjam' => Carbon::now(),
                'deadline_kembali' => Carbon::parse($validated['deadline_kembali']),
                'status' => 'AKTIF',
                'terlambat' => false,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $barang->update(['status' => 'DIPINJAM']);

            LogAktivitas::catat('CREATE', 'PEMINJAMAN', $peminjaman->id, "Pencatatan peminjaman: {$barang->nama_barang} dipinjam oleh {$peminjaman->nama_peminjam} ke {$peminjaman->ruangan_tujuan}");

            return redirect()->route('peminjaman.index')->with('success', 'Peminjaman berhasil dicatat!');
        });
    }

    public function kembalikan(Request $request, $id)
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::lockForUpdate()->findOrFail($id);

            if ($peminjaman->status === 'SELESAI') {
                return back()->with('error', 'Transaksi peminjaman ini sudah selesai sebelumnya.');
            }

            $now = Carbon::now();
            $isTerlambat = $now->gt($peminjaman->deadline_kembali);

            $peminjaman->update([
                'status' => 'SELESAI',
                'tanggal_kembali' => $now,
                'terlambat' => $isTerlambat || $peminjaman->terlambat,
            ]);

            $barang = Barang::withTrashed()->find($peminjaman->barang_id);

            // Sesuai aturan: jika aset sedang soft-deleted, statusnya TIDAK diubah kembali ke TERSEDIA
            if ($barang && !$barang->trashed()) {
                $barang->update(['status' => 'TERSEDIA']);
            }

            $ketTerlambat = $isTerlambat ? ' [TERLAMBAT]' : '';
            LogAktivitas::catat('UPDATE', 'PEMINJAMAN', $peminjaman->id, "Pengembalian peminjaman aset {$barang?->nama_barang} oleh {$peminjaman->nama_peminjam}{$ketTerlambat}");

            return redirect()->route('peminjaman.index')->with('success', 'Pengembalian barang berhasil diproses' . ($isTerlambat ? ' (Status: Terlambat).' : '.'));
        });
    }
}
