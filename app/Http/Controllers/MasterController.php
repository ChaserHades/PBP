<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Kategori
    |--------------------------------------------------------------------------
    */

    public function kategoriIndex()
    {
        $kategoris = Kategori::withCount('barang')->orderBy('nama')->paginate(15);
        return view('master.kategori', compact('kategoris'));
    }

    public function kategoriStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:kategori,nama',
            'keterangan' => 'nullable|string',
        ]);

        $kategori = Kategori::create($validated);
        LogAktivitas::catat('CREATE', 'KATEGORI', $kategori->id, "Menambahkan kategori baru: {$kategori->nama}");

        return back()->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function kategoriDestroy($id)
    {
        $kategori = Kategori::withCount('barang')->findOrFail($id);

        if ($kategori->barang_count > 0) {
            return back()->with('error', "Kategori '{$kategori->nama}' tidak dapat dihapus karena masih digunakan oleh {$kategori->barang_count} barang.");
        }

        $nama = $kategori->nama;
        $kategori->delete();
        LogAktivitas::catat('DELETE', 'KATEGORI', $id, "Menghapus kategori: {$nama}");

        return back()->with('success', 'Kategori berhasil dihapus!');
    }

    /*
    |--------------------------------------------------------------------------
    | Ruangan
    |--------------------------------------------------------------------------
    */

    public function ruanganIndex()
    {
        $ruangans = Ruangan::withCount('barang')->orderBy('nama')->paginate(15);
        return view('master.ruangan', compact('ruangans'));
    }

    public function ruanganStore(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:ruangan,kode',
            'nama' => 'required|string|max:100',
            'lantai' => 'nullable|string|max:20',
        ]);

        $ruangan = Ruangan::create($validated);
        LogAktivitas::catat('CREATE', 'RUANGAN', $ruangan->id, "Menambahkan ruangan baru: {$ruangan->nama} ({$ruangan->kode})");

        return back()->with('success', 'Ruangan berhasil ditambahkan!');
    }

    public function ruanganDestroy($id)
    {
        $ruangan = Ruangan::withCount('barang')->findOrFail($id);

        if ($ruangan->barang_count > 0) {
            return back()->with('error', "Ruangan '{$ruangan->nama}' tidak dapat dihapus karena masih ada {$ruangan->barang_count} barang di ruangan ini.");
        }

        $nama = $ruangan->nama;
        $ruangan->delete();
        LogAktivitas::catat('DELETE', 'RUANGAN', $id, "Menghapus ruangan: {$nama}");

        return back()->with('success', 'Ruangan berhasil dihapus!');
    }
}
