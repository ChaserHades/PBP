<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengeluaran;
use App\Models\Ruangan;
use App\Models\User;
use App\Services\DashboardStatisticsService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Daftar nilai enum yang dipakai sebagai kunci grafik.
     */
    private const STATUS_BARANG = ['TERSEDIA', 'DIPINJAM', 'MAINTENANCE'];

    private const KONDISI_BARANG = ['BAIK', 'RUSAK_RINGAN', 'RUSAK_BERAT'];

    private const JENIS_PENGELUARAN = ['PEMBELIAN_ASET', 'MAINTENANCE', 'LAINNYA'];

    private const ROLE_PENGGUNA = ['SUPERADMIN', 'ADMIN', 'TEKNISI', 'UNIVERSITAS'];

    private const STATUS_VERIFIKASI = ['APPROVED', 'PENDING', 'REJECTED'];

    private const AKSI_LOG = ['CREATE', 'UPDATE', 'DELETE', 'IMPORT'];

    public function superadmin(Request $request): View
    {
        $periode = DashboardStatisticsService::resolvePeriod($request->integer('periode', DashboardStatisticsService::DEFAULT_PERIOD));
        $periodeMulai = DashboardStatisticsService::periodStart($periode);
        $bulanLabels = array_values(DashboardStatisticsService::monthBuckets($periode));

        // Ringkasan entitas sistem
        $totalAset = Barang::count();
        $totalAsetTerhapus = Barang::onlyTrashed()->count();
        $totalKategori = Kategori::count();
        $totalRuangan = Ruangan::count();
        $totalPengguna = User::count();
        $peminjamanAktif = Peminjaman::where('status', 'AKTIF')->count();
        $peminjamanLewatDeadline = Peminjaman::where('status', 'AKTIF')->where('deadline_kembali', '<', Carbon::now())->count();
        $totalPengeluaran = Pengeluaran::sum('nominal');
        $pengeluaranPeriode = Pengeluaran::where('tanggal', '>=', $periodeMulai)->sum('nominal');

        // Pengguna: distribusi role dan status verifikasi (1 query untuk matriks role x status)
        $penggunaPerStatus = DashboardStatisticsService::aggregateBy(User::query(), 'status_verifikasi', self::STATUS_VERIFIKASI);
        $totalUserPending = $penggunaPerStatus['PENDING'];
        $matriksRoleStatus = $this->roleStatusMatrix();
        $penggunaPerRole = array_map(fn (array $perStatus) => array_sum($perStatus), $matriksRoleStatus);

        // Aset
        $statusDistribusi = DashboardStatisticsService::aggregateBy(Barang::query(), 'status', self::STATUS_BARANG);
        $kondisiDistribusi = DashboardStatisticsService::aggregateBy(Barang::query(), 'kondisi', self::KONDISI_BARANG);

        // Aktivitas sistem (audit trail) dalam periode
        $totalLogPeriode = LogAktivitas::where('created_at', '>=', $periodeMulai)->count();
        $trenAktivitasPerAksi = DashboardStatisticsService::monthlyAggregateGrouped(LogAktivitas::query(), 'created_at', 'aksi', self::AKSI_LOG, $periode);
        $aktivitasPerEntitas = LogAktivitas::where('created_at', '>=', $periodeMulai)
            ->selectRaw('entitas, COUNT(*) as total')
            ->groupBy('entitas')
            ->orderByDesc('total')
            ->pluck('total', 'entitas')
            ->map(fn ($total) => (int) $total)
            ->all();
        $penggunaPalingAktif = LogAktivitas::with('user:id,name,role')
            ->where('created_at', '>=', $periodeMulai)
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();
        $recentLogs = LogAktivitas::with('user:id,name,role')->latest('created_at')->paginate(50)->withQueryString();

        // Ringkasan operasional bulanan
        $trenAsetMasuk = DashboardStatisticsService::monthlyAggregate(Barang::query(), 'tanggal_datang', $periode);
        $trenPeminjaman = DashboardStatisticsService::monthlyAggregate(Peminjaman::query(), 'tanggal_pinjam', $periode);
        $trenTransaksiPengeluaran = DashboardStatisticsService::monthlyAggregate(Pengeluaran::query(), 'tanggal', $periode);
        $trenNominalPengeluaran = DashboardStatisticsService::monthlyAggregate(Pengeluaran::query(), 'tanggal', $periode, 'nominal');

        return view('dashboard.superadmin', compact(
            'periode',
            'bulanLabels',
            'totalAset',
            'totalAsetTerhapus',
            'totalKategori',
            'totalRuangan',
            'totalPengguna',
            'peminjamanAktif',
            'peminjamanLewatDeadline',
            'totalPengeluaran',
            'pengeluaranPeriode',
            'penggunaPerStatus',
            'totalUserPending',
            'matriksRoleStatus',
            'penggunaPerRole',
            'statusDistribusi',
            'kondisiDistribusi',
            'totalLogPeriode',
            'trenAktivitasPerAksi',
            'aktivitasPerEntitas',
            'penggunaPalingAktif',
            'recentLogs',
            'trenAsetMasuk',
            'trenPeminjaman',
            'trenTransaksiPengeluaran',
            'trenNominalPengeluaran'
        ));
    }

    public function admin(Request $request): View
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

        $recentLogs = LogAktivitas::with('user:id,name')->latest('created_at')->paginate(50, ['*'], 'log_page')->withQueryString();
        $recentPeminjaman = Peminjaman::with(['barang:id,nama_barang,label,deleted_at', 'user:id,name'])->latest('tanggal_pinjam')->paginate(50)->withQueryString();

        // Graphical dashboard
        $periode = DashboardStatisticsService::resolvePeriod($request->integer('periode', DashboardStatisticsService::DEFAULT_PERIOD));
        $periodeMulai = DashboardStatisticsService::periodStart($periode);
        $bulanLabels = array_values(DashboardStatisticsService::monthBuckets($periode));

        $statusDistribusi = [
            'TERSEDIA' => $asetTersedia,
            'DIPINJAM' => $asetDipinjam,
            'MAINTENANCE' => $asetMaintenance,
        ];
        $asetTerhapus = Barang::onlyTrashed()->count();
        $asetAktif = $asetTersedia + $asetDipinjam;
        $asetNonaktif = $asetMaintenance + $asetTerhapus;
        $totalKategori = Kategori::count();
        $totalRuangan = Ruangan::count();
        $totalNilaiAset = (float) Barang::sum('harga');

        $kategoriDistribusi = Kategori::select(['id', 'nama'])->withCount('barang')->orderByDesc('barang_count')->orderBy('nama')->get();
        $ruanganDistribusi = Ruangan::select(['id', 'nama', 'kode'])->withCount('barang')->orderByDesc('barang_count')->orderBy('nama')->get();
        $asetTanpaRuangan = Barang::whereNull('ruangan_id')->count();

        $trenPeminjaman = DashboardStatisticsService::monthlyAggregate(Peminjaman::query(), 'tanggal_pinjam', $periode);
        $trenPengembalian = DashboardStatisticsService::monthlyAggregate(Peminjaman::query()->whereNotNull('tanggal_kembali'), 'tanggal_kembali', $periode);
        $trenPengeluaran = DashboardStatisticsService::monthlyAggregate(Pengeluaran::query(), 'tanggal', $periode, 'nominal');
        $trenAsetMasuk = DashboardStatisticsService::monthlyAggregate(Barang::query(), 'tanggal_datang', $periode);
        $pengeluaranPerJenis = DashboardStatisticsService::aggregateBy(
            Pengeluaran::query()->where('tanggal', '>=', $periodeMulai),
            'jenis',
            self::JENIS_PENGELUARAN,
            'nominal'
        );

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
            'recentPeminjaman',
            'periode',
            'bulanLabels',
            'statusDistribusi',
            'asetTerhapus',
            'asetAktif',
            'asetNonaktif',
            'totalKategori',
            'totalRuangan',
            'totalNilaiAset',
            'kategoriDistribusi',
            'ruanganDistribusi',
            'asetTanpaRuangan',
            'trenPeminjaman',
            'trenPengembalian',
            'trenPengeluaran',
            'trenAsetMasuk',
            'pengeluaranPerJenis'
        ));
    }

    public function teknisi(Request $request): View
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

        $recentPeminjaman = Peminjaman::with(['barang:id,nama_barang,label,deleted_at', 'user:id,name'])->where('status', 'AKTIF')->latest('tanggal_pinjam')->paginate(50)->withQueryString();
        $recentLogs = LogAktivitas::whereHas('user', fn ($q) => $q->where('role', 'TEKNISI'))->with('user:id,name')->latest('created_at')->paginate(50, ['*'], 'log_page')->withQueryString();

        // Graphical dashboard
        $periode = DashboardStatisticsService::resolvePeriod($request->integer('periode', DashboardStatisticsService::DEFAULT_PERIOD));
        $bulanLabels = array_values(DashboardStatisticsService::monthBuckets($periode));

        $statusDistribusi = DashboardStatisticsService::aggregateBy(Barang::query(), 'status', self::STATUS_BARANG);
        $peminjamanLewatDeadline = Peminjaman::where('status', 'AKTIF')->where('deadline_kembali', '<', Carbon::now())->count();

        $asetPerluDitanganiQuery = Barang::query()->where(function ($query) {
            $query->whereIn('kondisi', ['RUSAK_RINGAN', 'RUSAK_BERAT'])
                ->orWhere('status', 'MAINTENANCE');
        });
        $totalPerluDitangani = (clone $asetPerluDitanganiQuery)->count();
        $asetPerluDitangani = (clone $asetPerluDitanganiQuery)
            ->select(['id', 'nama_barang', 'label', 'kondisi', 'status', 'kategori_id', 'ruangan_id', 'updated_at'])
            ->with(['kategori:id,nama', 'ruangan:id,nama,kode'])
            ->orderByRaw("CASE kondisi WHEN 'RUSAK_BERAT' THEN 0 WHEN 'RUSAK_RINGAN' THEN 1 ELSE 2 END")
            ->latest('updated_at')
            ->paginate(50, ['*'], 'servis_page')
            ->withQueryString();

        $kerusakanPerRuangan = $this->kerusakanPerRuangan();

        $trenPeminjaman = DashboardStatisticsService::monthlyAggregate(Peminjaman::query(), 'tanggal_pinjam', $periode);
        $trenPengembalian = DashboardStatisticsService::monthlyAggregate(Peminjaman::query()->whereNotNull('tanggal_kembali'), 'tanggal_kembali', $periode);
        $trenTerlambat = DashboardStatisticsService::monthlyAggregate(Peminjaman::query()->where('terlambat', true)->whereNotNull('tanggal_kembali'), 'tanggal_kembali', $periode);
        $trenMaintenance = DashboardStatisticsService::monthlyAggregate(Pengeluaran::query()->where('jenis', 'MAINTENANCE'), 'tanggal', $periode);
        $trenAktivitasTeknisi = DashboardStatisticsService::monthlyAggregate(
            LogAktivitas::query()->whereHas('user', fn ($query) => $query->where('role', 'TEKNISI')),
            'created_at',
            $periode
        );

        return view('dashboard.teknisi', compact(
            'totalAset',
            'asetTersedia',
            'peminjamanAktif',
            'peminjamanTerlambat',
            'kondisiDistribusi',
            'recentPeminjaman',
            'recentLogs',
            'periode',
            'bulanLabels',
            'statusDistribusi',
            'peminjamanLewatDeadline',
            'totalPerluDitangani',
            'asetPerluDitangani',
            'kerusakanPerRuangan',
            'trenPeminjaman',
            'trenPengembalian',
            'trenTerlambat',
            'trenMaintenance',
            'trenAktivitasTeknisi'
        ));
    }

    public function universitas(Request $request): View
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

        $recentPengeluaran = Pengeluaran::with('user:id,name')->latest('tanggal')->paginate(50)->withQueryString();

        // Graphical dashboard (read-only: aset & pengeluaran, tanpa data peminjaman/log sesuai matriks RBAC)
        $periode = DashboardStatisticsService::resolvePeriod($request->integer('periode', DashboardStatisticsService::DEFAULT_PERIOD));
        $periodeMulai = DashboardStatisticsService::periodStart($periode);
        $bulanLabels = array_values(DashboardStatisticsService::monthBuckets($periode));

        $totalKategori = Kategori::count();
        $totalRuangan = Ruangan::count();
        $kondisiDistribusi = DashboardStatisticsService::aggregateBy(Barang::query(), 'kondisi', self::KONDISI_BARANG);
        $persentaseKondisiBaik = $totalAset > 0 ? round(($kondisiDistribusi['BAIK'] / $totalAset) * 100, 1) : 0;

        $kategoriStatistik = Kategori::select(['id', 'nama'])
            ->withCount('barang')
            ->withSum('barang', 'harga')
            ->orderByDesc('barang_count')
            ->orderBy('nama')
            ->get();

        $trenPengeluaranPerJenis = DashboardStatisticsService::monthlyAggregateGrouped(
            Pengeluaran::query(),
            'tanggal',
            'jenis',
            self::JENIS_PENGELUARAN,
            $periode,
            'nominal'
        );
        $trenPengeluaran = array_map(
            fn (int $index) => round(array_sum(array_column($trenPengeluaranPerJenis, $index)), 2),
            array_keys($bulanLabels)
        );
        $pengeluaranPeriode = array_sum($trenPengeluaran);
        $pengeluaranPerJenis = array_map(fn (array $series) => round(array_sum($series), 2), $trenPengeluaranPerJenis);
        $trenAsetMasuk = DashboardStatisticsService::monthlyAggregate(Barang::query(), 'tanggal_datang', $periode);
        $trenNilaiAsetMasuk = DashboardStatisticsService::monthlyAggregate(Barang::query(), 'tanggal_datang', $periode, 'harga');

        return view('dashboard.universitas', compact(
            'totalAset',
            'totalNilaiAset',
            'totalPengeluaran',
            'asetPerProgdi',
            'asetPerKategori',
            'recentPengeluaran',
            'periode',
            'periodeMulai',
            'bulanLabels',
            'totalKategori',
            'totalRuangan',
            'kondisiDistribusi',
            'persentaseKondisiBaik',
            'kategoriStatistik',
            'trenPengeluaranPerJenis',
            'trenPengeluaran',
            'pengeluaranPeriode',
            'pengeluaranPerJenis',
            'trenAsetMasuk',
            'trenNilaiAsetMasuk'
        ));
    }

    /**
     * Matriks jumlah pengguna per role dan status verifikasi (satu query GROUP BY).
     *
     * @return array<string, array<string, int>>
     */
    private function roleStatusMatrix(): array
    {
        $rows = User::query()
            ->selectRaw('role, status_verifikasi, COUNT(*) as total')
            ->groupBy('role', 'status_verifikasi')
            ->toBase()
            ->get();

        $matrix = array_fill_keys(self::ROLE_PENGGUNA, array_fill_keys(self::STATUS_VERIFIKASI, 0));

        foreach ($rows as $row) {
            if (isset($matrix[$row->role][$row->status_verifikasi])) {
                $matrix[$row->role][$row->status_verifikasi] = (int) $row->total;
            }
        }

        return $matrix;
    }

    /**
     * Jumlah aset rusak per ruangan, dipecah per tingkat kerusakan (2 query, tanpa N+1).
     *
     * @return array{labels: array<int, string>, ringan: array<int, int>, berat: array<int, int>}
     */
    private function kerusakanPerRuangan(): array
    {
        $rows = Barang::query()
            ->selectRaw('ruangan_id, kondisi, COUNT(*) as total')
            ->whereIn('kondisi', ['RUSAK_RINGAN', 'RUSAK_BERAT'])
            ->groupBy('ruangan_id', 'kondisi')
            ->toBase()
            ->get();

        $namaRuangan = Ruangan::whereIn('id', $rows->pluck('ruangan_id')->filter()->unique())->pluck('nama', 'id');

        $perRuangan = [];

        foreach ($rows as $row) {
            $label = $row->ruangan_id ? ($namaRuangan[$row->ruangan_id] ?? 'Ruangan Tidak Dikenal') : 'Belum Ditentukan';
            $perRuangan[$label] ??= ['RUSAK_RINGAN' => 0, 'RUSAK_BERAT' => 0];
            $perRuangan[$label][$row->kondisi] = (int) $row->total;
        }

        uasort($perRuangan, fn (array $a, array $b) => array_sum($b) <=> array_sum($a));

        return [
            'labels' => array_keys($perRuangan),
            'ringan' => array_values(array_column($perRuangan, 'RUSAK_RINGAN')),
            'berat' => array_values(array_column($perRuangan, 'RUSAK_BERAT')),
        ];
    }
}
