<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Ruangan;
use App\Services\LabelGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::with(['kategori', 'ruangan']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('label', 'like', "%{$search}%")
                  ->orWhere('label_aset', 'like', "%{$search}%")
                  ->orWhere('sn', 'like', "%{$search}%")
                  ->orWhere('merek', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->ruangan_id);
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $barangs = $query->latest()->paginate(15)->withQueryString();
        $kategoris = Kategori::orderBy('nama')->get();
        $ruangans = Ruangan::orderBy('nama')->get();

        return view('barang.index', compact('barangs', 'kategoris', 'ruangans'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama')->get();
        $ruangans = Ruangan::orderBy('nama')->get();

        return view('barang.create', compact('kategoris', 'ruangans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'progdi' => 'nullable|string|max:255',
            'label_aset' => 'nullable|string|max:255',
            'sn' => 'nullable|string|max:255',
            'merek' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'tipe' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'kondisi' => 'required|in:BAIK,RUSAK_RINGAN,RUSAK_BERAT',
            'jumlah' => 'nullable|integer|min:1',
            'tanggal_datang' => 'nullable|date',
            'harga' => 'nullable|numeric|min:0',
            'sumber_tahun' => 'nullable|string|max:50',
            'pembelian_ke' => 'nullable|integer|min:1',
            'kategori_id' => 'required|exists:kategori,id',
            'ruangan_id' => 'nullable|exists:ruangan,id',
        ]);

        $tanggalDatang = !empty($validated['tanggal_datang']) ? Carbon::parse($validated['tanggal_datang']) : Carbon::now();
        $pembelianKe = $validated['pembelian_ke'] ?? null;

        // Auto-generate Label (format: KAT-MRK-MMYYYY-NNN misal FLAS-SAN-102026-001)
        $autoLabel = LabelGeneratorService::generate($validated['kategori_id'], $validated['merek'] ?? null, $tanggalDatang, $pembelianKe);

        // Auto KelompokPembelian
        $kelompokPembelian = LabelGeneratorService::getOrCreateKelompokPembelian($tanggalDatang, $pembelianKe);

        $barang = Barang::create([
            'nama_barang' => $validated['nama_barang'],
            'progdi' => $validated['progdi'] ?? null,
            'label' => $autoLabel,
            'label_aset' => $validated['label_aset'] ?? null,
            'sn' => $validated['sn'] ?? null,
            'merek' => $validated['merek'] ?? null,
            'model' => $validated['model'] ?? null,
            'tipe' => $validated['tipe'] ?? null,
            'vendor' => $validated['vendor'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'kondisi' => $validated['kondisi'],
            'status' => 'TERSEDIA',
            'jumlah' => $validated['jumlah'] ?? 1,
            'tanggal_datang' => $tanggalDatang,
            'harga' => $validated['harga'] ?? null,
            'sumber_tahun' => $validated['sumber_tahun'] ?? null,
            'pembelian_ke' => $pembelianKe,
            'kategori_id' => $validated['kategori_id'],
            'ruangan_id' => $validated['ruangan_id'] ?? null,
            'kelompok_pembelian_id' => $kelompokPembelian->id,
        ]);

        LogAktivitas::catat('CREATE', 'BARANG', $barang->id, "Menambahkan aset baru: {$barang->nama_barang} (Label: {$barang->label})");

        return redirect()->route('barang.show', $barang->id)->with('success', "Aset berhasil ditambahkan dengan Label: {$barang->label}");
    }

    public function show($id)
    {
        $barang = Barang::withTrashed()->with(['kategori', 'ruangan', 'pengeluaran', 'kelompokPembelian', 'peminjaman.user'])->findOrFail($id);

        return view('barang.show', compact('barang'));
    }

    public function edit($id)
    {
        $barang = Barang::findOrFail($id);
        $kategoris = Kategori::orderBy('nama')->get();
        $ruangans = Ruangan::orderBy('nama')->get();

        return view('barang.edit', compact('barang', 'kategoris', 'ruangans'));
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'progdi' => 'nullable|string|max:255',
            'label_aset' => 'nullable|string|max:255',
            'sn' => 'nullable|string|max:255',
            'merek' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'tipe' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'kondisi' => 'required|in:BAIK,RUSAK_RINGAN,RUSAK_BERAT',
            'status' => 'required|in:TERSEDIA,DIPINJAM,MAINTENANCE',
            'jumlah' => 'nullable|integer|min:1',
            'tanggal_datang' => 'nullable|date',
            'harga' => 'nullable|numeric|min:0',
            'kategori_id' => 'required|exists:kategori,id',
            'ruangan_id' => 'nullable|exists:ruangan,id',
        ]);

        $oldRuanganId = $barang->ruangan_id;
        $barang->update($validated);

        // Jika ruangan berubah, catat log mutasi pindah ruangan
        if ($oldRuanganId !== $barang->ruangan_id) {
            $namaRuangan = $barang->ruangan ? $barang->ruangan->nama : 'Tanpa Ruangan';
            LogAktivitas::catat('UPDATE', 'BARANG', $barang->id, "Pindah ruangan aset {$barang->nama_barang} ke {$namaRuangan}");
        } else {
            LogAktivitas::catat('UPDATE', 'BARANG', $barang->id, "Memperbarui data aset {$barang->nama_barang} ({$barang->label})");
        }

        return redirect()->route('barang.show', $barang->id)->with('success', 'Data aset berhasil diperbarui.');
    }

    /**
     * Soft delete barang (Retention Window 6 Hari)
     */
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        LogAktivitas::catat('DELETE', 'BARANG', $barang->id, "Soft delete aset {$barang->nama_barang} (Label: {$barang->label}) ke Recently Deleted");

        return redirect()->route('barang.index')->with('success', 'Aset dipindahkan ke Recently Deleted (dapat dipulihkan dalam 6 hari).');
    }

    /**
     * Daftar aset di Recently Deleted
     */
    public function recentlyDeleted()
    {
        $barangs = Barang::onlyTrashed()->with(['kategori', 'ruangan'])->latest('deleted_at')->paginate(15);

        return view('barang.recently-deleted', compact('barangs'));
    }

    /**
     * Restore aset yang terhapus (hanya jika <= 6 hari)
     */
    public function restore($id)
    {
        $barang = Barang::onlyTrashed()->findOrFail($id);

        if (!$barang->isRecoverable()) {
            return back()->with('error', 'Aset tidak dapat dipulihkan karena telah melampaui batas retensi 6 hari (EXPIRED).');
        }

        $barang->restore();
        LogAktivitas::catat('UPDATE', 'BARANG', $barang->id, "Memulihkan (restore) aset {$barang->nama_barang} dari Recently Deleted");

        return redirect()->route('barang.show', $barang->id)->with('success', 'Aset berhasil dipulihkan!');
    }

    /**
     * Hard Delete permanen (Hanya jika tidak memiliki riwayat peminjaman)
     */
    public function forceDelete($id)
    {
        $barang = Barang::onlyTrashed()->findOrFail($id);

        if ($barang->peminjaman()->exists()) {
            return back()->with('error', 'Aset ini memiliki riwayat peminjaman historis sehingga tidak boleh dihapus secara permanen demi integritas audit.');
        }

        $nama = $barang->nama_barang;
        $label = $barang->label;
        $barang->forceDelete();

        LogAktivitas::catat('DELETE', 'BARANG', $id, "Hard delete permanen aset {$nama} ({$label})");

        return redirect()->route('barang.recentlyDeleted')->with('success', 'Aset telah dihapus permanen dari sistem.');
    }

    /**
     * Generate / update QR Code Data
     */
    public function generateQr($id)
    {
        $barang = Barang::findOrFail($id);
        $payload = json_encode([
            'id' => $barang->id,
            'kode' => $barang->label_aset ?: $barang->label,
            'nama' => $barang->nama_barang,
        ]);

        $barang->update(['qr_code' => $payload]);

        LogAktivitas::catat('UPDATE', 'BARANG', $barang->id, "Generate QR Code aset {$barang->nama_barang}");

        return back()->with('success', 'QR Code berhasil dibuat!');
    }
}
