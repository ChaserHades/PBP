<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\KelompokPembelian;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LabelGeneratorService
{
    /**
     * Generate Label Otomatis untuk Barang:
     * Format: [KAT]-[MRK]-[MMYYYY]-[NNN]
     * Contoh: FLAS-SAN-102026-001 atau LAP-MAC-072026-001
     */
    public static function generate(string $kategoriId, ?string $merek, ?Carbon $tanggalDatang = null, ?int $pembelianKe = null): string
    {
        $kategori = Kategori::find($kategoriId);
        $katCode = 'GEN';

        if ($kategori && !empty($kategori->nama)) {
            // Bersihkan nama kategori dan ambil 3-4 huruf pertama
            $cleanKat = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($kategori->nama));
            $katCode = substr($cleanKat, 0, 4) ?: 'GEN';
        }

        // 3-4 huruf pertama dari merek
        $cleanMerek = !empty($merek) ? preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($merek)) : 'GEN';
        $mrkCode = substr($cleanMerek, 0, 3) ?: 'GEN';

        // Bulan dan tahun: MMYYYY (misal: 102026)
        $date = $tanggalDatang ?? Carbon::now();
        $dateCode = $date->format('mY');

        // Nomor urut pembelian ke-berapa
        if ($pembelianKe === null) {
            // Hitung urutan dari KelompokPembelian atau Barang di bulan tersebut
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $maxPembelianKe = Barang::withTrashed()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->max('pembelian_ke') ?? 0;

            $pembelianKe = $maxPembelianKe + 1;
        }

        $seqCode = str_pad($pembelianKe, 3, '0', STR_PAD_LEFT);

        return "{$katCode}-{$mrkCode}-{$dateCode}-{$seqCode}";
    }

    /**
     * Dapatkan atau buat KelompokPembelian untuk bulan tertentu
     */
    public static function getOrCreateKelompokPembelian(?Carbon $tanggal = null, ?int $pembelianKe = null): KelompokPembelian
    {
        $date = $tanggal ?? Carbon::now();
        $dateCode = $date->format('mY');

        if ($pembelianKe === null) {
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $maxKe = KelompokPembelian::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->max('pembelian_ke') ?? 0;

            $pembelianKe = $maxKe + 1;
        }

        $kode = "KP-{$dateCode}-" . str_pad($pembelianKe, 3, '0', STR_PAD_LEFT);

        return KelompokPembelian::firstOrCreate(
            ['kode' => $kode],
            [
                'tanggal' => $date,
                'pembelian_ke' => $pembelianKe,
            ]
        );
    }
}
