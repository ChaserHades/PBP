<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Default untuk 4 Role
        $defaultPassword = Hash::make('password123');

        User::firstOrCreate(
            ['email' => 'admin@uksw.edu'],
            [
                'name' => 'Super Administrator FTI',
                'password' => $defaultPassword,
                'role' => 'SUPERADMIN',
                'status_verifikasi' => 'APPROVED',
                'must_change_password' => false,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin.fti@uksw.edu'],
            [
                'name' => 'Admin Fakultas FTI',
                'password' => $defaultPassword,
                'role' => 'ADMIN',
                'status_verifikasi' => 'APPROVED',
                'must_change_password' => false,
            ]
        );

        User::firstOrCreate(
            ['email' => 'teknisi.fti@uksw.edu'],
            [
                'name' => 'Petugas Teknisi Lab',
                'password' => $defaultPassword,
                'role' => 'TEKNISI',
                'status_verifikasi' => 'APPROVED',
                'must_change_password' => false,
            ]
        );

        User::firstOrCreate(
            ['email' => 'universitas@uksw.edu'],
            [
                'name' => 'Pimpinan & Auditor Universitas',
                'password' => $defaultPassword,
                'role' => 'UNIVERSITAS',
                'status_verifikasi' => 'APPROVED',
                'must_change_password' => false,
            ]
        );

        // 2. Kategori Default
        $kategoris = [
            ['nama' => 'Flashdisk / Storage', 'keterangan' => 'Media penyimpanan portabel dan drive eksternal'],
            ['nama' => 'Laptop / Notebook', 'keterangan' => 'Komputer portabel staf dan laboratorium'],
            ['nama' => 'Komputer PC', 'keterangan' => 'Workstation desktop lab komputer'],
            ['nama' => 'Monitor', 'keterangan' => 'Layar display LED/LCD'],
            ['nama' => 'Proyektor', 'keterangan' => 'Perangkat presentasi dan display ruang kelas'],
            ['nama' => 'Kamera & Audio', 'keterangan' => 'Perlengkapan multimedia dan dokumentasi'],
            ['nama' => 'Perangkat Jaringan', 'keterangan' => 'Router, Switch, Access Point, dan kabel jaringan'],
        ];

        foreach ($kategoris as $k) {
            Kategori::firstOrCreate(['nama' => $k['nama']], ['keterangan' => $k['keterangan']]);
        }

        // 3. Ruangan Default
        $ruangans = [
            ['kode' => 'LAB-01', 'nama' => 'Lab Komputer Dasar 1', 'lantai' => 'Lantai 1'],
            ['kode' => 'LAB-02', 'nama' => 'Lab Pemrograman & Basis Data', 'lantai' => 'Lantai 2'],
            ['kode' => 'LAB-NET', 'nama' => 'Lab Jaringan & Keamanan Siber', 'lantai' => 'Lantai 2'],
            ['kode' => 'R-DOSEN', 'nama' => 'Ruang Dosen & Administrasi', 'lantai' => 'Lantai 1'],
            ['kode' => 'GUDANG', 'nama' => 'Gudang Logistik & Inventaris', 'lantai' => 'Basement'],
        ];

        foreach ($ruangans as $r) {
            Ruangan::firstOrCreate(['kode' => $r['kode']], ['nama' => $r['nama'], 'lantai' => $r['lantai']]);
        }
    }
}
