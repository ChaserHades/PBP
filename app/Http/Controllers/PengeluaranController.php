<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KelompokPembelian;
use App\Models\LogAktivitas;
use App\Models\Pengeluaran;
use App\Services\LabelGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengeluaran::with(['user', 'kelompokPembelian.barang']);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('nama_barang', 'like', "%{$search}%")
                  ->orWhere('nomor_bukti', 'like', "%{$search}%")
                  ->orWhere('kode_pembelian', 'like', "%{$search}%");
            });
        }

        $pengeluarans = $query->latest('tanggal')->paginate(15)->withQueryString();
        $totalNominal = Pengeluaran::sum('nominal');

        return view('pengeluaran.index', compact('pengeluarans', 'totalNominal'));
    }

    public function create()
    {
        // Candidate assets yang belum memiliki pengeluaran
        $candidateBarang = Barang::whereNull('pengeluaran_id')->latest()->get();

        return view('pengeluaran.create', compact('candidateBarang'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'nama_barang' => 'nullable|string|max:255',
            'progdi' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'nominal' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:PEMBELIAN_ASET,MAINTENANCE,LAINNYA',
            'jenis_bukti' => 'nullable|string|max:50', // SPB, KWITANSI, NOTA
            'nomor_bukti' => 'nullable|string|max:100',
            'nota_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx|max:10240',
            'barang_ids' => 'nullable|array',
            'barang_ids.*' => 'exists:barang,id',
        ]);

        $tanggal = Carbon::parse($validated['tanggal']);
        $kelompokPembelian = LabelGeneratorService::getOrCreateKelompokPembelian($tanggal);

        // Upload nota jika ada
        $notaUrls = [];
        if ($request->hasFile('nota_files')) {
            foreach ($request->file('nota_files') as $file) {
                $path = $file->store('nota', 'public');
                $notaUrls[] = Storage::url($path);
            }
        }

        // Auto Kode Pembelian: {JENIS}-{NOMOR}-{MMYYYY}-{NNN}
        $jenisBukti = strtoupper($validated['jenis_bukti'] ?? 'NOTA');
        $nomorBukti = preg_replace('/[^a-zA-Z0-9\-\/]/', '', $validated['nomor_bukti'] ?? '01');
        $kodePembelian = "{$jenisBukti}-{$nomorBukti}-" . $kelompokPembelian->kode;

        $pengeluaran = Pengeluaran::create([
            'judul' => $validated['judul'],
            'nama_barang' => $validated['nama_barang'] ?? null,
            'progdi' => $validated['progdi'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'nominal' => $validated['nominal'],
            'tanggal' => $tanggal,
            'jenis' => $validated['jenis'],
            'jenis_bukti' => $validated['jenis_bukti'] ?? null,
            'nomor_bukti' => $validated['nomor_bukti'] ?? null,
            'nota_urls' => $notaUrls,
            'kode_pembelian' => $kodePembelian,
            'pembelian_ke' => $kelompokPembelian->pembelian_ke,
            'user_id' => Auth::id(),
            'kelompok_pembelian_id' => $kelompokPembelian->id,
        ]);

        // Sinkronisasi aset terkait
        if (!empty($validated['barang_ids'])) {
            Barang::whereIn('id', $validated['barang_ids'])->update([
                'pengeluaran_id' => $pengeluaran->id,
                'kelompok_pembelian_id' => $kelompokPembelian->id,
            ]);
        }

        LogAktivitas::catat('CREATE', 'PENGELUARAN', $pengeluaran->id, "Pencatatan pengeluaran: {$pengeluaran->judul} (Nominal: Rp " . number_format($pengeluaran->nominal, 0, ',', '.') . ")");

        return redirect()->route('pengeluaran.index')->with('success', 'Data pengeluaran berhasil dicatat!');
    }

    public function show($id)
    {
        $pengeluaran = Pengeluaran::with(['user', 'kelompokPembelian', 'barang.kategori', 'barang.ruangan'])->findOrFail($id);

        return view('pengeluaran.show', compact('pengeluaran'));
    }

    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        $judul = $pengeluaran->judul;

        // Lepaskan relasi aset tapi jangan hapus asetnya (aturan keamanan cashflow)
        Barang::where('pengeluaran_id', $pengeluaran->id)->update(['pengeluaran_id' => null]);

        $pengeluaran->delete();

        LogAktivitas::catat('DELETE', 'PENGELUARAN', $id, "Menghapus pengeluaran: {$judul}");

        return redirect()->route('pengeluaran.index')->with('success', 'Data pengeluaran berhasil dihapus.');
    }
}
