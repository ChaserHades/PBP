<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengeluaran;
use App\Models\Ruangan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Model::preventLazyLoading();
    }

    private function createVerifiedUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'status_verifikasi' => 'APPROVED',
            'must_change_password' => false,
        ]);
    }

    private function seedSampleInventoryData(User $actor): void
    {
        $kategoriLaptop = Kategori::create([
            'nama' => 'Laptop Lab',
            'keterangan' => 'Perangkat laptop laboratorium',
        ]);

        $ruanganLab = Ruangan::create([
            'kode' => 'LAB-TEST',
            'nama' => 'Lab Pengujian',
            'lantai' => 'Lantai 2',
        ]);

        $barangBaik = Barang::create([
            'nama_barang' => 'ThinkPad X1 Carbon',
            'progdi' => 'S1 Teknik Informatika',
            'label' => 'LAP-LEN-102026-001',
            'kondisi' => 'BAIK',
            'status' => 'TERSEDIA',
            'jumlah' => 1,
            'harga' => 18500000,
            'tanggal_datang' => Carbon::now()->subMonth(),
            'kategori_id' => $kategoriLaptop->id,
            'ruangan_id' => $ruanganLab->id,
        ]);

        $barangRusak = Barang::create([
            'nama_barang' => 'Proyektor Epson EB-X51',
            'progdi' => 'S1 Sistem Informasi',
            'label' => 'PRO-EPS-102026-002',
            'kondisi' => 'RUSAK_BERAT',
            'status' => 'MAINTENANCE',
            'jumlah' => 1,
            'harga' => 7500000,
            'tanggal_datang' => Carbon::now(),
            'kategori_id' => $kategoriLaptop->id,
            'ruangan_id' => $ruanganLab->id,
        ]);

        Peminjaman::create([
            'barang_id' => $barangBaik->id,
            'user_id' => $actor->id,
            'nama_peminjam' => 'Budi Santoso',
            'ruangan_tujuan' => 'LAB-TEST',
            'tanggal_pinjam' => Carbon::now()->subDays(2),
            'deadline_kembali' => Carbon::now()->subDay(),
            'status' => 'AKTIF',
            'terlambat' => true,
        ]);

        Pengeluaran::create([
            'judul' => 'Pengadaan Laptop & Servis Proyektor',
            'nominal' => 26000000,
            'tanggal' => Carbon::now(),
            'jenis' => 'PEMBELIAN_ASET',
            'jenis_bukti' => 'NOTA',
            'nomor_bukti' => 'INV-001',
            'user_id' => $actor->id,
        ]);

        LogAktivitas::catat('CREATE', 'BARANG', $barangBaik->id, 'Menambahkan aset uji ThinkPad', $actor->id);
        LogAktivitas::catat('UPDATE', 'BARANG', $barangRusak->id, 'Memperbarui status proyektor ke maintenance', $actor->id);
    }

    public function test_root_redirects_each_role_to_its_designated_dashboard(): void
    {
        $superadmin = $this->createVerifiedUser('SUPERADMIN');
        $admin = $this->createVerifiedUser('ADMIN');
        $teknisi = $this->createVerifiedUser('TEKNISI');
        $universitas = $this->createVerifiedUser('UNIVERSITAS');

        $this->actingAs($superadmin)->get('/')->assertRedirect(route('superadmin.dashboard'));
        $this->actingAs($admin)->get('/')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($teknisi)->get('/')->assertRedirect(route('petugas.dashboard'));
        $this->actingAs($universitas)->get('/')->assertRedirect(route('universitas.dashboard'));
    }

    public function test_superadmin_dashboard_renders_system_metrics_and_charts_without_lazy_loading(): void
    {
        $superadmin = $this->createVerifiedUser('SUPERADMIN');
        $this->createVerifiedUser('ADMIN');
        $this->createVerifiedUser('TEKNISI');
        $this->seedSampleInventoryData($superadmin);

        $response = $this->actingAs($superadmin)->get(route('superadmin.dashboard', ['periode' => 12]));

        $response->assertOk()
            ->assertViewIs('dashboard.superadmin')
            ->assertViewHas('periode', 12)
            ->assertViewHas('totalAset', 2)
            ->assertSee('Pusat Kendali Sistem (Superadmin)')
            ->assertSee('Tren Aktivitas Sistem (Audit Trail)')
            ->assertSee('Distribusi Role Pengguna')
            ->assertSee('data-chart=', false);
    }

    public function test_admin_dashboard_renders_existing_features_and_new_charts(): void
    {
        $admin = $this->createVerifiedUser('ADMIN');
        $this->seedSampleInventoryData($admin);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['periode' => 3]));

        $response->assertOk()
            ->assertViewIs('dashboard.admin')
            ->assertViewHas('periode', 3)
            ->assertViewHas('totalAset', 2)
            ->assertViewHas('asetAktif', 1)
            ->assertViewHas('asetNonaktif', 1)
            ->assertSeeText('Dashboard Pengelola')
            ->assertSeeText('Analitik Inventaris & Operasional')
            ->assertSeeText('Distribusi Kategori')
            ->assertSeeText('Distribusi Ruangan')
            ->assertSee('data-chart=', false);
    }

    public function test_teknisi_dashboard_renders_items_needing_attention_and_operational_charts(): void
    {
        $teknisi = $this->createVerifiedUser('TEKNISI');
        $this->seedSampleInventoryData($teknisi);

        $response = $this->actingAs($teknisi)->get(route('petugas.dashboard', ['periode' => 6]));

        $response->assertOk()
            ->assertViewIs('dashboard.teknisi')
            ->assertViewHas('totalPerluDitangani', 1)
            ->assertSee('Meja Kerja Teknisi Lab')
            ->assertSee('Barang Perlu Ditangani')
            ->assertSee('Proyektor Epson EB-X51')
            ->assertSee('Aset Rusak per Ruangan')
            ->assertSee('data-chart=', false);
    }

    public function test_universitas_dashboard_renders_executive_charts_and_respects_empty_and_populated_states(): void
    {
        $universitas = $this->createVerifiedUser('UNIVERSITAS');

        // Empty state
        $emptyResponse = $this->actingAs($universitas)->get(route('universitas.dashboard'));
        $emptyResponse->assertOk()
            ->assertViewIs('dashboard.universitas')
            ->assertSee('data-chart-empty', false);

        // Populated state + invalid period falls back to default 6 months
        $this->seedSampleInventoryData($universitas);

        $populatedResponse = $this->actingAs($universitas)->get(route('universitas.dashboard', ['periode' => 99]));
        $populatedResponse->assertOk()
            ->assertViewHas('periode', 6)
            ->assertViewHas('totalAset', 2)
            ->assertSeeText('Visualisasi Laporan Inventaris & Pengeluaran')
            ->assertSeeText('Tren Realisasi Pengeluaran')
            ->assertSee('data-chart=', false);
    }

    public function test_role_permissions_restrict_unauthorized_dashboard_access(): void
    {
        $admin = $this->createVerifiedUser('ADMIN');
        $teknisi = $this->createVerifiedUser('TEKNISI');
        $universitas = $this->createVerifiedUser('UNIVERSITAS');

        // ADMIN cannot access SUPERADMIN dashboard
        $this->actingAs($admin)
            ->get(route('superadmin.dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        // TEKNISI cannot access SUPERADMIN, ADMIN, or UNIVERSITAS dashboards
        $this->actingAs($teknisi)
            ->get(route('superadmin.dashboard'))
            ->assertRedirect(route('petugas.dashboard'));
        $this->actingAs($teknisi)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('petugas.dashboard'));

        // UNIVERSITAS cannot access SUPERADMIN, ADMIN, or TEKNISI dashboards
        $this->actingAs($universitas)
            ->get(route('superadmin.dashboard'))
            ->assertRedirect(route('universitas.dashboard'));
        $this->actingAs($universitas)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('universitas.dashboard'));
        $this->actingAs($universitas)
            ->get(route('petugas.dashboard'))
            ->assertRedirect(route('universitas.dashboard'));
    }

    public function test_pagination_ui_uses_50_items_per_page_across_all_roles_and_pages_without_lazy_loading(): void
    {
        $superadmin = $this->createVerifiedUser('SUPERADMIN');
        $admin = $this->createVerifiedUser('ADMIN');
        $teknisi = $this->createVerifiedUser('TEKNISI');
        $universitas = $this->createVerifiedUser('UNIVERSITAS');

        $this->seedSampleInventoryData($admin);

        // Dashboards for all 4 roles render 50-item pagination UI
        $this->actingAs($superadmin)->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee('50 item / halaman')
            ->assertSee('data-pagination-ui', false);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('50 item / halaman')
            ->assertSee('data-pagination-ui', false);

        $this->actingAs($teknisi)->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('50 item / halaman')
            ->assertSee('data-pagination-ui', false);

        $this->actingAs($universitas)->get(route('universitas.dashboard'))
            ->assertOk()
            ->assertSee('50 item / halaman')
            ->assertSee('data-pagination-ui', false);

        // Module index pages across roles use 50 items per page and zero N+1 queries
        $this->actingAs($universitas)->get(route('barang.index'))
            ->assertOk()
            ->assertViewHas('barangs', fn ($p) => $p->perPage() === 50)
            ->assertSee('50 item / halaman');

        $this->actingAs($universitas)->get(route('pengeluaran.index'))
            ->assertOk()
            ->assertViewHas('pengeluarans', fn ($p) => $p->perPage() === 50)
            ->assertSee('50 item / halaman');

        $this->actingAs($teknisi)->get(route('peminjaman.index'))
            ->assertOk()
            ->assertViewHas('peminjamans', fn ($p) => $p->perPage() === 50)
            ->assertSee('50 item / halaman');

        $this->actingAs($teknisi)->get(route('log.index'))
            ->assertOk()
            ->assertViewHas('logs', fn ($p) => $p->perPage() === 50)
            ->assertSee('50 item / halaman');

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()
            ->assertViewHas('users', fn ($p) => $p->perPage() === 50)
            ->assertSee('50 item / halaman');

        $this->actingAs($admin)->get(route('master.kategori'))
            ->assertOk()
            ->assertViewHas('kategoris', fn ($p) => $p->perPage() === 50)
            ->assertSee('50 item / halaman');

        $this->actingAs($admin)->get(route('master.ruangan'))
            ->assertOk()
            ->assertViewHas('ruangans', fn ($p) => $p->perPage() === 50)
            ->assertSee('50 item / halaman');
    }
}
