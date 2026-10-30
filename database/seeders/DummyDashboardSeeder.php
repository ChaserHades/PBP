<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\KelompokPembelian;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengeluaran;
use App\Models\Ruangan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDashboardSeeder extends Seeder
{
    /**
     * Isi data dummy komprehensif (12 bulan terakhir) untuk menguji seluruh
     * grafik dan metrik pada dashboard SUPERADMIN, ADMIN, TEKNISI, dan UNIVERSITAS.
     */
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        $defaultPassword = Hash::make('password123');

        // 1. Tambahan Pengguna untuk variasi Role & Status Verifikasi (APPROVED, PENDING, REJECTED)
        $extraUsers = [
            ['email' => 'admin.lab@uksw.edu', 'name' => 'hendra Wijaya (Admin Lab)', 'role' => 'ADMIN', 'status_verifikasi' => 'APPROVED'],
            ['email' => 'teknisi.jar@uksw.edu', 'name' => 'Riko Pratama (Teknisi Jaringan)', 'role' => 'TEKNISI', 'status_verifikasi' => 'APPROVED'],
            ['email' => 'teknisi.multimedia@uksw.edu', 'name' => 'Sinta Dewi (Teknisi Multimedia)', 'role' => 'TEKNISI', 'status_verifikasi' => 'APPROVED'],
            ['email' => 'teknisi.baru1@uksw.edu', 'name' => 'Andreas Saputra (Asisten Teknisi)', 'role' => 'TEKNISI', 'status_verifikasi' => 'PENDING'],
            ['email' => 'teknisi.baru2@uksw.edu', 'name' => 'Kevin Christianto (Staf Lab)', 'role' => 'TEKNISI', 'status_verifikasi' => 'PENDING'],
            ['email' => 'staf.admin.pending@uksw.edu', 'name' => 'Maria Natalia (Calon Admin)', 'role' => 'ADMIN', 'status_verifikasi' => 'PENDING'],
            ['email' => 'auditor.rektorat@uksw.edu', 'name' => 'Dr. Yohanes Setiawan (Auditor Univ)', 'role' => 'UNIVERSITAS', 'status_verifikasi' => 'APPROVED'],
            ['email' => 'biro.keuangan@uksw.edu', 'name' => 'Biro Keuangan UKSW', 'role' => 'UNIVERSITAS', 'status_verifikasi' => 'PENDING'],
            ['email' => 'akun.eksternal@gmail.com', 'name' => 'Tamu Eksternal', 'role' => 'TEKNISI', 'status_verifikasi' => 'REJECTED'],
            ['email' => 'magang.lama@uksw.edu', 'name' => 'Staf Magang 2025', 'role' => 'UNIVERSITAS', 'status_verifikasi' => 'REJECTED'],
        ];

        foreach ($extraUsers as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => $defaultPassword,
                    'role' => $u['role'],
                    'status_verifikasi' => $u['status_verifikasi'],
                    'must_change_password' => false,
                ]
            );
        }

        $superadmin = User::where('email', 'admin@uksw.edu')->first();
        $adminFti = User::where('email', 'admin.fti@uksw.edu')->first();
        $adminLab = User::where('email', 'admin.lab@uksw.edu')->first();
        $teknisi1 = User::where('email', 'teknisi.fti@uksw.edu')->first();
        $teknisi2 = User::where('email', 'teknisi.jar@uksw.edu')->first();
        $teknisi3 = User::where('email', 'teknisi.multimedia@uksw.edu')->first();

        $kategoris = Kategori::all()->keyBy('nama');
        $ruangans = Ruangan::all()->keyBy('kode');

        // 2. Kelompok Pembelian & Pengeluaran selama 12 bulan terakhir
        $monthlyExpensesSpec = [
            // [monthsAgo, jenis, judul, progdi, nominal, bukti, nomor]
            [11, 'PEMBELIAN_ASET', 'Pengadaan PC Workstation Lab Dasar', 'S1 Teknik Informatika', 42000000, 'SPB', 'SPB-01/112025'],
            [10, 'PEMBELIAN_ASET', 'Pembelian Switch Managed Cisco & Patch Cord', 'S1 Teknik Informatika', 18500000, 'NOTA', 'INV-122025-09'],
            [9, 'MAINTENANCE', 'Servis Berkala AC & Proyektor Lab 1-2', 'Fakultas / Lab Bersama', 4750000, 'KWITANSI', 'KWT-012026-03'],
            [8, 'PEMBELIAN_ASET', 'Pengadaan Monitor Dell 27 Inch IPS', 'S1 Desain Komunikasi Visual', 24000000, 'SPB', 'SPB-02/022026'],
            [7, 'LAINNYA', 'Pembelian Habis Pakai Kabel HDMI & Thermal Paste', 'Fakultas / Lab Bersama', 2150000, 'NOTA', 'NT-032026-11'],
            [6, 'PEMBELIAN_ASET', 'Pengadaan Laptop Asus ExpertBook Dosen & Lab', 'S1 Sistem Informasi', 38000000, 'SPB', 'SPB-04/042026'],
            [5, 'MAINTENANCE', 'Penggantian Lampu Proyektor Epson & Cleaning Lensa', 'S1 Sistem Informasi', 3600000, 'KWITANSI', 'KWT-052026-07'],
            [5, 'PEMBELIAN_ASET', 'Pembelian SSD Eksternal Samsung T7 untuk Backup Lab', 'D3 Teknik Informatika', 9600000, 'NOTA', 'INV-052026-18'],
            [4, 'PEMBELIAN_ASET', 'Pengadaan Kamera Mirrorless Sony & Wireless Mic', 'S1 Desain Komunikasi Visual', 29500000, 'SPB', 'SPB-06/062026'],
            [3, 'MAINTENANCE', 'Perbaikan Power Supply & Upgrade RAM PC Lab 01', 'S1 Teknik Informatika', 6800000, 'NOTA', 'SRV-072026-04'],
            [3, 'LAINNYA', 'Langganan Lisensi Software & Label Stiker Aset QR', 'Fakultas / Lab Bersama', 3200000, 'KWITANSI', 'KWT-072026-12'],
            [2, 'PEMBELIAN_ASET', 'Pengadaan Router MikroTik CCR & Access Point WiFi 6', 'S1 Teknik Informatika', 22400000, 'SPB', 'SPB-08/082026'],
            [2, 'MAINTENANCE', 'Servis Engsel & Keyboard Laptop Lab Pemrograman', 'S1 Sistem Informasi', 2900000, 'NOTA', 'SRV-082026-15'],
            [1, 'PEMBELIAN_ASET', 'Pengadaan Proyektor Laser Epson EB-L200F', 'S2 Magister Sistem Informasi', 31500000, 'SPB', 'SPB-09/092026'],
            [1, 'MAINTENANCE', 'Kalibrasi Monitor & Perbaikan Audio Mixer Studio', 'S1 Desain Komunikasi Visual', 4100000, 'KWITANSI', 'KWT-092026-08'],
            [0, 'PEMBELIAN_ASET', 'Pengadaan MacBook Pro M3 untuk Lab Mobile & AI', 'S1 Teknik Informatika', 54000000, 'SPB', 'SPB-10/102026'],
            [0, 'MAINTENANCE', 'Servis Darurat Switch Distribusi Lab Jaringan', 'S1 Teknik Informatika', 3850000, 'NOTA', 'SRV-102026-02'],
            [0, 'LAINNYA', 'Pengadaan的工具 Kit Teknisi & Tester Jaringan', 'Fakultas / Lab Bersama', 2750000, 'NOTA', 'NT-102026-09'],
        ];

        $createdPengeluarans = [];

        foreach ($monthlyExpensesSpec as $idx => [$monthsAgo, $jenis, $judul, $progdi, $nominal, $jenisBukti, $nomorBukti]) {
            $tanggal = Carbon::now()->subMonths($monthsAgo)->startOfMonth()->addDays(($idx * 3) % 24 + 1);
            $kodeKp = 'KP-'.$tanggal->format('mY').'-'.str_pad(($idx % 3) + 1, 3, '0', STR_PAD_LEFT);

            $kp = KelompokPembelian::firstOrCreate(
                ['kode' => $kodeKp],
                ['tanggal' => $tanggal, 'pembelian_ke' => ($idx % 3) + 1]
            );

            $pengeluaran = Pengeluaran::firstOrCreate(
                ['nomor_bukti' => $nomorBukti],
                [
                    'judul' => $judul,
                    'nama_barang' => $judul,
                    'progdi' => $progdi,
                    'deskripsi' => "Realisasi anggaran {$jenis} periode ".$tanggal->translatedFormat('F Y'),
                    'nominal' => $nominal,
                    'tanggal' => $tanggal,
                    'jenis' => $jenis,
                    'jenis_bukti' => $jenisBukti,
                    'kode_pembelian' => "{$jenisBukti}-{$nomorBukti}-{$kp->kode}",
                    'pembelian_ke' => $kp->pembelian_ke,
                    'user_id' => $idx % 2 === 0 ? $adminFti->id : $adminLab->id,
                    'kelompok_pembelian_id' => $kp->id,
                ]
            );

            $createdPengeluarans[$monthsAgo] = $pengeluaran;
        }

        // 3. Data Aset (Barang) tersebar di seluruh kategori, ruangan, progdi, kondisi, status, & 12 bulan
        $assetsSpec = [
            // [label, nama, kategori, ruangan, progdi, merek, kondisi, status, harga, monthsAgo]
            ['KOMP-LEN-112025-001', 'PC Lenovo ThinkCentre M70t #01', 'Komputer PC', 'LAB-01', 'S1 Teknik Informatika', 'Lenovo', 'BAIK', 'TERSEDIA', 10500000, 11],
            ['KOMP-LEN-112025-002', 'PC Lenovo ThinkCentre M70t #02', 'Komputer PC', 'LAB-01', 'S1 Teknik Informatika', 'Lenovo', 'BAIK', 'DIPINJAM', 10500000, 11],
            ['KOMP-LEN-112025-003', 'PC Lenovo ThinkCentre M70t #03', 'Komputer PC', 'LAB-01', 'S1 Teknik Informatika', 'Lenovo', 'RUSAK_RINGAN', 'TERSEDIA', 10500000, 11],
            ['KOMP-LEN-112025-004', 'PC Lenovo ThinkCentre M70t #04', 'Komputer PC', 'LAB-01', 'S1 Teknik Informatika', 'Lenovo', 'RUSAK_BERAT', 'MAINTENANCE', 10500000, 11],

            ['PERA-CIS-122025-001', 'Cisco Catalyst 2960-X 24 Port #01', 'Perangkat Jaringan', 'LAB-NET', 'S1 Teknik Informatika', 'Cisco', 'BAIK', 'TERSEDIA', 9250000, 10],
            ['PERA-CIS-122025-002', 'Cisco Catalyst 2960-X 24 Port #02', 'Perangkat Jaringan', 'LAB-NET', 'S1 Teknik Informatika', 'Cisco', 'RUSAK_RINGAN', 'MAINTENANCE', 9250000, 10],

            ['PROY-EPS-012026-001', 'Proyektor Epson EB-X500 R.101', 'Proyektor', 'LAB-01', 'Fakultas / Lab Bersama', 'Epson', 'BAIK', 'TERSEDIA', 6500000, 9],
            ['PROY-EPS-012026-002', 'Proyektor Epson EB-X500 R.202', 'Proyektor', 'LAB-02', 'Fakultas / Lab Bersama', 'Epson', 'RUSAK_RINGAN', 'TERSEDIA', 6500000, 9],

            ['MONI-DEL-022026-001', 'Monitor Dell UltraSharp 27" U2723QE #01', 'Monitor', 'LAB-02', 'S1 Desain Komunikasi Visual', 'Dell', 'BAIK', 'TERSEDIA', 8000000, 8],
            ['MONI-DEL-022026-002', 'Monitor Dell UltraSharp 27" U2723QE #02', 'Monitor', 'LAB-02', 'S1 Desain Komunikasi Visual', 'Dell', 'BAIK', 'TERSEDIA', 8000000, 8],
            ['MONI-DEL-022026-003', 'Monitor Dell UltraSharp 27" U2723QE #03', 'Monitor', 'LAB-02', 'S1 Desain Komunikasi Visual', 'Dell', 'RUSAK_BERAT', 'MAINTENANCE', 8000000, 8],

            ['FLAS-SAN-032026-001', 'SanDisk Extreme Pro USB 3.2 256GB #01', 'Flashdisk / Storage', 'GUDANG', 'D3 Teknik Informatika', 'SanDisk', 'BAIK', 'TERSEDIA', 750000, 7],
            ['FLAS-SAN-032026-002', 'SanDisk Extreme Pro USB 3.2 256GB #02', 'Flashdisk / Storage', 'GUDANG', 'D3 Teknik Informatika', 'SanDisk', 'BAIK', 'DIPINJAM', 750000, 7],

            ['LAPT-ASU-042026-001', 'Laptop ASUS ExpertBook B5 #01', 'Laptop / Notebook', 'R-DOSEN', 'S1 Sistem Informasi', 'ASUS', 'BAIK', 'TERSEDIA', 12500000, 6],
            ['LAPT-ASU-042026-002', 'Laptop ASUS ExpertBook B5 #02', 'Laptop / Notebook', 'R-DOSEN', 'S1 Sistem Informasi', 'ASUS', 'BAIK', 'DIPINJAM', 12500000, 6],
            ['LAPT-ASU-042026-003', 'Laptop ASUS ExpertBook B5 #03', 'Laptop / Notebook', 'LAB-02', 'S1 Sistem Informasi', 'ASUS', 'RUSAK_RINGAN', 'TERSEDIA', 13000000, 6],

            ['FLAS-SAM-052026-001', 'SSD Eksternal Samsung T7 Shield 2TB #01', 'Flashdisk / Storage', 'GUDANG', 'D3 Teknik Informatika', 'Samsung', 'BAIK', 'TERSEDIA', 3200000, 5],
            ['FLAS-SAM-052026-002', 'SSD Eksternal Samsung T7 Shield 2TB #02', 'Flashdisk / Storage', 'GUDANG', 'D3 Teknik Informatika', 'Samsung', 'BAIK', 'DIPINJAM', 3200000, 5],
            ['FLAS-SAM-052026-003', 'SSD Eksternal Samsung T7 Shield 2TB #03', 'Flashdisk / Storage', 'LAB-02', 'D3 Teknik Informatika', 'Samsung', 'RUSAK_RINGAN', 'TERSEDIA', 3200000, 5],

            ['KAME-SON-062026-001', 'Kamera Mirrorless Sony Alpha a6400 Kit', 'Kamera & Audio', 'R-DOSEN', 'S1 Desain Komunikasi Visual', 'Sony', 'BAIK', 'DIPINJAM', 16500000, 4],
            ['KAME-ROD-062026-002', 'Microphone Wireless Rode GO II Dual', 'Kamera & Audio', 'R-DOSEN', 'S1 Desain Komunikasi Visual', 'Rode', 'BAIK', 'TERSEDIA', 4800000, 4],
            ['KAME-YAM-062026-003', 'Audio Mixer Yamaha MG10XU', 'Kamera & Audio', 'LAB-01', 'S1 Desain Komunikasi Visual', 'Yamaha', 'RUSAK_RINGAN', 'MAINTENANCE', 8200000, 4],

            ['KOMP-HP-072026-001', 'Workstation HP Z2 Tower G9 #01', 'Komputer PC', 'LAB-02', 'S1 Teknik Informatika', 'HP', 'BAIK', 'TERSEDIA', 19500000, 3],
            ['KOMP-HP-072026-002', 'Workstation HP Z2 Tower G9 #02', 'Komputer PC', 'LAB-02', 'S1 Teknik Informatika', 'HP', 'BAIK', 'TERSEDIA', 19500000, 3],
            ['MONI-LG-072026-003', 'Monitor LG UltraGear 24" IPS #01', 'Monitor', 'LAB-01', 'S1 Teknik Informatika', 'LG', 'RUSAK_RINGAN', 'TERSEDIA', 2850000, 3],

            ['PERA-MIK-082026-001', 'Router MikroTik CCR2004-16G-2S+', 'Perangkat Jaringan', 'LAB-NET', 'S1 Teknik Informatika', 'MikroTik', 'BAIK', 'TERSEDIA', 11200000, 2],
            ['PERA-UBI-082026-002', 'Ubiquiti UniFi U6-Pro Access Point #01', 'Perangkat Jaringan', 'LAB-NET', 'S1 Teknik Informatika', 'Ubiquiti', 'BAIK', 'TERSEDIA', 5600000, 2],
            ['PERA-UBI-082026-003', 'Ubiquiti UniFi U6-Pro Access Point #02', 'Perangkat Jaringan', 'LAB-NET', 'S1 Teknik Informatika', 'Ubiquiti', 'RUSAK_BERAT', 'MAINTENANCE', 5600000, 2],

            ['PROY-EPS-092026-001', 'Proyektor Laser Epson EB-L200F #01', 'Proyektor', 'LAB-01', 'S2 Magister Sistem Informasi', 'Epson', 'BAIK', 'TERSEDIA', 15750000, 1],
            ['PROY-EPS-092026-002', 'Proyektor Laser Epson EB-L200F #02', 'Proyektor', 'LAB-02', 'S2 Magister Sistem Informasi', 'Epson', 'BAIK', 'DIPINJAM', 15750000, 1],
            ['PROY-BEN-092026-003', 'Proyektor BenQ MH560 Portable', 'Proyektor', 'GUDANG', 'Fakultas / Lab Bersama', 'BenQ', 'RUSAK_BERAT', 'MAINTENANCE', 6900000, 1],

            ['LAPT-APP-102026-001', 'MacBook Pro 14" M3 Pro 18GB #01', 'Laptop / Notebook', 'LAB-02', 'S1 Teknik Informatika', 'Apple', 'BAIK', 'TERSEDIA', 27000000, 0],
            ['LAPT-APP-102026-002', 'MacBook Pro 14" M3 Pro 18GB #02', 'Laptop / Notebook', 'LAB-02', 'S1 Teknik Informatika', 'Apple', 'BAIK', 'DIPINJAM', 27000000, 0],
            ['LAPT-LEN-102026-003', 'Lenovo ThinkPad T14 Gen 4 Lab', 'Laptop / Notebook', null, 'S1 Sistem Informasi', 'Lenovo', 'BAIK', 'TERSEDIA', 16200000, 0],
            ['MONI-SAM-102026-004', 'Monitor Samsung Odyssey 27" Cadangan', 'Monitor', null, 'Fakultas / Lab Bersama', 'Samsung', 'RUSAK_RINGAN', 'TERSEDIA', 3900000, 0],
        ];

        $createdBarangs = [];

        foreach ($assetsSpec as $idx => [$label, $nama, $katNama, $ruangKode, $progdi, $merek, $kondisi, $status, $harga, $monthsAgo]) {
            $tanggalDatang = Carbon::now()->subMonths($monthsAgo)->startOfMonth()->addDays(($idx * 2) % 22 + 2);
            $pengeluaran = $createdPengeluarans[$monthsAgo] ?? null;

            $barang = Barang::withTrashed()->firstOrCreate(
                ['label' => $label],
                [
                    'nama_barang' => $nama,
                    'progdi' => $progdi,
                    'label_aset' => 'UKSW-FTI-'.str_pad($idx + 101, 4, '0', STR_PAD_LEFT),
                    'sn' => 'SN'.strtoupper(substr(md5($label), 0, 10)),
                    'merek' => $merek,
                    'model' => 'Series '.(2025 + ($idx % 2)),
                    'vendor' => $idx % 2 === 0 ? 'PT Mitra Komputindo Salatiga' : 'CV Solusi Digital Nusantara',
                    'keterangan' => "Unit inventaris laboratorium ({$progdi})",
                    'kondisi' => $kondisi,
                    'status' => $status,
                    'jumlah' => 1,
                    'tanggal_datang' => $tanggalDatang,
                    'harga' => $harga,
                    'sumber_tahun' => (string) $tanggalDatang->year,
                    'pembelian_ke' => 1,
                    'kategori_id' => $kategoris[$katNama]->id,
                    'ruangan_id' => $ruangKode ? ($ruangans[$ruangKode]->id ?? null) : null,
                    'pengeluaran_id' => $pengeluaran?->id,
                    'kelompok_pembelian_id' => $pengeluaran?->kelompok_pembelian_id,
                ]
            );

            $createdBarangs[] = $barang;
        }

        // Tambahan 30 unit aset lab agar total aset > 50 item (untuk demonstrasi multi-halaman 50 item / halaman)
        $kategoriValues = $kategoris->values();
        $ruanganValues = $ruangans->values();
        $progdiChoices = ['S1 Teknik Informatika', 'S1 Sistem Informasi', 'S1 Desain Komunikasi Visual', 'D3 Teknik Informatika', 'S2 Magister Sistem Informasi', 'Fakultas / Lab Bersama'];

        for ($extra = 1; $extra <= 30; $extra++) {
            $monthsAgo = $extra % 12;
            $tanggalDatang = Carbon::now()->subMonths($monthsAgo)->startOfMonth()->addDays(($extra % 20) + 1);
            $kat = $kategoriValues[$extra % $kategoriValues->count()];
            $ruang = $ruanganValues[$extra % $ruanganValues->count()];
            $label = 'UNIT-LAB-'.$tanggalDatang->format('mY').'-'.str_pad($extra + 50, 3, '0', STR_PAD_LEFT);

            $createdBarangs[] = Barang::withTrashed()->firstOrCreate(
                ['label' => $label],
                [
                    'nama_barang' => "{$kat->nama} Workstation Unit #".str_pad($extra, 2, '0', STR_PAD_LEFT),
                    'progdi' => $progdiChoices[$extra % count($progdiChoices)],
                    'label_aset' => 'UKSW-FTI-'.str_pad($extra + 200, 4, '0', STR_PAD_LEFT),
                    'sn' => 'SN'.strtoupper(substr(md5($label), 0, 10)),
                    'merek' => ['Lenovo', 'Dell', 'HP', 'ASUS', 'Epson', 'Cisco'][$extra % 6],
                    'model' => 'Lab Edition '.(2025 + ($extra % 2)),
                    'vendor' => 'PT Mitra Komputindo Salatiga',
                    'kondisi' => $extra % 9 === 0 ? 'RUSAK_RINGAN' : 'BAIK',
                    'status' => 'TERSEDIA',
                    'jumlah' => 1,
                    'tanggal_datang' => $tanggalDatang,
                    'harga' => 4500000 + (($extra % 8) * 1250000),
                    'sumber_tahun' => (string) $tanggalDatang->year,
                    'pembelian_ke' => 2,
                    'kategori_id' => $kat->id,
                    'ruangan_id' => $ruang->id,
                    'pengeluaran_id' => ($createdPengeluarans[$monthsAgo] ?? null)?->id,
                    'kelompok_pembelian_id' => ($createdPengeluarans[$monthsAgo] ?? null)?->kelompok_pembelian_id,
                ]
            );
        }

        // Tambahan 30 unit aset lab agar total aset > 50 item (untuk demonstrasi multi-halaman 50 item / halaman)
        $kategoriValues = $kategoris->values();
        $ruanganValues = $ruangans->values();
        $progdiChoices = ['S1 Teknik Informatika', 'S1 Sistem Informasi', 'S1 Desain Komunikasi Visual', 'D3 Teknik Informatika', 'S2 Magister Sistem Informasi', 'Fakultas / Lab Bersama'];

        for ($extra = 1; $extra <= 30; $extra++) {
            $monthsAgo = $extra % 12;
            $tanggalDatang = Carbon::now()->subMonths($monthsAgo)->startOfMonth()->addDays(($extra % 20) + 1);
            $kat = $kategoriValues[$extra % $kategoriValues->count()];
            $ruang = $ruanganValues[$extra % $ruanganValues->count()];
            $label = 'UNIT-LAB-'.$tanggalDatang->format('mY').'-'.str_pad($extra + 50, 3, '0', STR_PAD_LEFT);

            $createdBarangs[] = Barang::withTrashed()->firstOrCreate(
                ['label' => $label],
                [
                    'nama_barang' => "{$kat->nama} Workstation Unit #".str_pad($extra, 2, '0', STR_PAD_LEFT),
                    'progdi' => $progdiChoices[$extra % count($progdiChoices)],
                    'label_aset' => 'UKSW-FTI-'.str_pad($extra + 200, 4, '0', STR_PAD_LEFT),
                    'sn' => 'SN'.strtoupper(substr(md5($label), 0, 10)),
                    'merek' => ['Lenovo', 'Dell', 'HP', 'ASUS', 'Epson', 'Cisco'][$extra % 6],
                    'model' => 'Lab Edition '.(2025 + ($extra % 2)),
                    'vendor' => 'PT Mitra Komputindo Salatiga',
                    'kondisi' => $extra % 9 === 0 ? 'RUSAK_RINGAN' : 'BAIK',
                    'status' => 'TERSEDIA',
                    'jumlah' => 1,
                    'tanggal_datang' => $tanggalDatang,
                    'harga' => 4500000 + (($extra % 8) * 1250000),
                    'sumber_tahun' => (string) $tanggalDatang->year,
                    'pembelian_ke' => 2,
                    'kategori_id' => $kat->id,
                    'ruangan_id' => $ruang->id,
                    'pengeluaran_id' => ($createdPengeluarans[$monthsAgo] ?? null)?->id,
                    'kelompok_pembelian_id' => ($createdPengeluarans[$monthsAgo] ?? null)?->kelompok_pembelian_id,
                ]
            );
        }

        // Tambahkan 2 aset Soft-Deleted (Recently Deleted dalam 6 hari terakhir) agar metrik Recycle Bin / Nonaktif terlihat
        $softDeletedSpecs = [
            ['MONI-AOC-102026-091', 'Monitor AOC 22B2H (Layar Pecah)', 'Monitor', 'GUDANG', 'Fakultas / Lab Bersama', 'AOC', 'RUSAK_BERAT', 1650000, 2],
            ['FLAS-KIN-102026-092', 'Flashdisk Kingston DataTraveler 64GB', 'Flashdisk / Storage', 'GUDANG', 'D3 Teknik Informatika', 'Kingston', 'RUSAK_RINGAN', 180000, 4],
        ];

        foreach ($softDeletedSpecs as [$label, $nama, $katNama, $ruangKode, $progdi, $merek, $kondisi, $harga, $daysAgo]) {
            $item = Barang::withTrashed()->firstOrCreate(
                ['label' => $label],
                [
                    'nama_barang' => $nama,
                    'progdi' => $progdi,
                    'merek' => $merek,
                    'kondisi' => $kondisi,
                    'status' => 'TERSEDIA',
                    'jumlah' => 1,
                    'tanggal_datang' => Carbon::now()->subMonths(3),
                    'harga' => $harga,
                    'kategori_id' => $kategoris[$katNama]->id,
                    'ruangan_id' => $ruangans[$ruangKode]->id,
                    'deleted_at' => Carbon::now()->subDays($daysAgo),
                ]
            );

            if (! $item->trashed()) {
                $item->update(['deleted_at' => Carbon::now()->subDays($daysAgo)]);
            }
        }

        // 4. Transaksi Peminjaman selama 12 bulan terakhir (SELESAI tepat waktu, SELESAI terlambat, AKTIF berjalan, AKTIF lewat deadline)
        if (Peminjaman::count() < 55) {
            $peminjamList = [
                ['Dr. Adi Nugroho, M.Cs.', 'R. Kuliah GK1-302'],
                ['Prof. Danny Manongga', 'R. Seminar FTI Lt.3'],
                ['Himpunan Mahasiswa TI (HMPSTI)', 'Aula Gedung FTI'],
                ['Kelompok Riset AI & IoT', 'LAB-02 (Lab Pemrograman)'],
                ['Tim Dokumentasi Dies Natalis FTI', 'Lapangan Basket UKSW'],
                ['Asisten Dosen Basis Data', 'LAB-01 (Lab Dasar 1)'],
                ['Panitia Workshop Cyber Security', 'LAB-NET (Lab Jaringan)'],
                ['Yohanes Kurniawan (Mahasiswa S1 TI)', 'R. Sidang Skripsi FTI'],
            ];

            // Riwayat selesai di bulan-bulan sebelumnya (11 bulan lalu s/d bulan ini)
            for ($m = 11; $m >= 0; $m--) {
                $jumlahTransaksiBulanIni = ($m % 3) + 4; // 4-6 transaksi per bulan (~60 transaksi)

                for ($t = 0; $t < $jumlahTransaksiBulanIni; $t++) {
                    $barang = $createdBarangs[($m * 3 + $t) % count($createdBarangs)];
                    [$namaPeminjam, $ruanganTujuan] = $peminjamList[($m + $t) % count($peminjamList)];

                    $tglPinjam = Carbon::now()->subMonths($m)->startOfMonth()->addDays(($t * 6) + 2)->setTime(9, 0);
                    if ($tglPinjam->gt(Carbon::now())) {
                        $tglPinjam = Carbon::now()->subDays(5);
                    }

                    $deadline = $tglPinjam->copy()->addDays(2)->setTime(18, 0);
                    $isLate = ($t === 1 && $m % 2 === 0); // Sebagian dikembalikan terlambat
                    $tglKembali = $isLate ? $deadline->copy()->addDays(2)->setTime(14, 30) : $deadline->copy()->subHours(3);

                    if ($tglKembali->gt(Carbon::now())) {
                        $tglKembali = Carbon::now()->subHours(2);
                    }

                    Peminjaman::create([
                        'barang_id' => $barang->id,
                        'user_id' => $t % 2 === 0 ? $teknisi1->id : $teknisi2->id,
                        'nama_peminjam' => $namaPeminjam,
                        'ruangan_tujuan' => $ruanganTujuan,
                        'tanggal_pinjam' => $tglPinjam,
                        'deadline_kembali' => $deadline,
                        'tanggal_kembali' => $tglKembali,
                        'status' => 'SELESAI',
                        'terlambat' => $isLate,
                        'catatan' => $isLate ? 'Dikembalikan terlambat karena kegiatan praktikum tambahan.' : 'Dikembalikan dalam kondisi baik.',
                    ]);
                }
            }

            // Peminjaman yang sedang AKTIF (sebagian masih dalam batas waktu, sebagian sudah melewati deadline)
            $barangDipinjam = Barang::where('status', 'DIPINJAM')->get();
            foreach ($barangDipinjam as $idx => $barang) {
                [$namaPeminjam, $ruanganTujuan] = $peminjamList[$idx % count($peminjamList)];
                $isOverdue = $idx < 3; // 3 peminjaman aktif sudah lewat deadline

                Peminjaman::create([
                    'barang_id' => $barang->id,
                    'user_id' => $idx % 2 === 0 ? $teknisi1->id : $teknisi3->id,
                    'nama_peminjam' => $namaPeminjam,
                    'ruangan_tujuan' => $ruanganTujuan,
                    'tanggal_pinjam' => Carbon::now()->subDays($isOverdue ? 5 : 1)->setTime(10, 0),
                    'deadline_kembali' => $isOverdue ? Carbon::now()->subDays($idx + 1)->setTime(18, 0) : Carbon::now()->addDays(2)->setTime(18, 0),
                    'tanggal_kembali' => null,
                    'status' => 'AKTIF',
                    'terlambat' => $isOverdue,
                    'catatan' => 'Peminjaman operasional kegiatan laboratorium & perkuliahan.',
                ]);
            }
        }

        // 5. Log Aktivitas (Audit Trail) selama 12 bulan terakhir (CREATE, UPDATE, DELETE, IMPORT)
        if (LogAktivitas::count() < 25) {
            $actors = [$superadmin, $adminFti, $adminLab, $teknisi1, $teknisi2, $teknisi3];
            $logTemplates = [
                ['CREATE', 'BARANG', 'Menambahkan aset baru ke inventaris laboratorium FTI'],
                ['UPDATE', 'BARANG', 'Memperbarui status & penempatan ruangan aset laboratorium'],
                ['CREATE', 'PEMINJAMAN', 'Mencatat transaksi peminjaman perangkat praktikum'],
                ['UPDATE', 'PEMINJAMAN', 'Memproses pengembalian peminjaman alat laboratorium'],
                ['CREATE', 'PENGELUARAN', 'Mencatat dokumen realisasi belanja & nota pengadaan'],
                ['IMPORT', 'BARANG', 'Import massal data inventaris semester dari file Excel (.xlsx)'],
                ['DELETE', 'BARANG', 'Memindahkan aset rusak ke Recently Deleted (Soft Delete)'],
                ['UPDATE', 'USER', 'Memverifikasi dan menyetujui pendaftaran akun teknisi baru'],
                ['CREATE', 'RUANGAN', 'Menambahkan data master ruangan laboratorium baru'],
                ['CREATE', 'KATEGORI', 'Menambahkan klasifikasi kategori perangkat inventaris'],
            ];

            for ($m = 11; $m >= 0; $m--) {
                $logsInMonth = 6 + ($m % 4);
                for ($i = 0; $i < $logsInMonth; $i++) {
                    [$aksi, $entitas, $detail] = $logTemplates[($m + $i) % count($logTemplates)];
                    $actor = $actors[($m + $i) % count($actors)];
                    $waktu = Carbon::now()->subMonths($m)->startOfMonth()->addDays(($i * 3) % 25 + 1)->setTime(8 + ($i % 9), ($i * 13) % 60);

                    if ($waktu->gt(Carbon::now())) {
                        $waktu = Carbon::now()->subMinutes(($i + 1) * 15);
                    }

                    LogAktivitas::create([
                        'aksi' => $aksi,
                        'entitas' => $entitas,
                        'entitas_id' => $createdBarangs[$i % count($createdBarangs)]->id,
                        'detail' => "{$detail} (Periode ".$waktu->translatedFormat('M Y').')',
                        'user_id' => $actor->id,
                        'created_at' => $waktu,
                    ]);
                }
            }
        }
    }
}
