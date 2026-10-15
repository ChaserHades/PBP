<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Guest Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->role) {
            'SUPERADMIN', 'ADMIN' => redirect()->route('admin.dashboard'),
            'TEKNISI' => redirect()->route('petugas.dashboard'),
            'UNIVERSITAS' => redirect()->route('universitas.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Forced Password Change (Bypass verify middleware)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/force-change-password', [AuthController::class, 'showForceChangePassword'])->name('password.force_change');
    Route::post('/force-change-password', [AuthController::class, 'updateForceChangePassword'])->name('password.force_change.update');
});

/*
|--------------------------------------------------------------------------
| Protected Application Routes (Verified + Changed Password)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified_account', 'force_password_change'])->group(function () {

    // Role-specific Dashboards
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:ADMIN,SUPERADMIN')
        ->name('admin.dashboard');

    Route::get('/petugas/dashboard', [DashboardController::class, 'teknisi'])
        ->middleware('role:TEKNISI,ADMIN,SUPERADMIN')
        ->name('petugas.dashboard');

    Route::get('/universitas/dashboard', [DashboardController::class, 'universitas'])
        ->middleware('role:UNIVERSITAS,ADMIN,SUPERADMIN')
        ->name('universitas.dashboard');

    // Barang (Aset) - Read-only for all roles
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::get('/barang/{id}', [BarangController::class, 'show'])->name('barang.show');

    // Barang - Create, Edit & QR for ADMIN and TEKNISI
    Route::middleware('role:ADMIN,TEKNISI,SUPERADMIN')->group(function () {
        Route::get('/barang-create/new', [BarangController::class, 'create'])->name('barang.create');
        Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/barang/{id}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
        Route::post('/barang/{id}/qr', [BarangController::class, 'generateQr'])->name('barang.qr');

        // Recently Deleted & Restore
        Route::get('/recently-deleted', [BarangController::class, 'recentlyDeleted'])->name('barang.recentlyDeleted');
        Route::post('/barang/{id}/restore', [BarangController::class, 'restore'])->name('barang.restore');
    });

    // Barang - Delete (Soft & Hard Delete) strictly for ADMIN
    Route::middleware('role:ADMIN,SUPERADMIN')->group(function () {
        Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');
        Route::delete('/barang/{id}/force', [BarangController::class, 'forceDelete'])->name('barang.forceDelete');
    });

    // Peminjaman (ADMIN & TEKNISI)
    Route::middleware('role:ADMIN,TEKNISI,SUPERADMIN')->group(function () {
        Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
        Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
        Route::post('/peminjaman/{id}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembalikan');
    });

    // Pengeluaran / Cashflow - View accessible by all
    Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
    Route::get('/pengeluaran/{id}', [PengeluaranController::class, 'show'])->name('pengeluaran.show');

    // Pengeluaran / Cashflow - Mutations strictly for ADMIN
    Route::middleware('role:ADMIN,SUPERADMIN')->group(function () {
        Route::get('/pengeluaran-create/new', [PengeluaranController::class, 'create'])->name('pengeluaran.create');
        Route::post('/pengeluaran', [PengeluaranController::class, 'store'])->name('pengeluaran.store');
        Route::delete('/pengeluaran/{id}', [PengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');
    });

    // Audit Trail Log Aktivitas (ADMIN & TEKNISI, Universitas gets 403)
    Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])
        ->middleware('role:ADMIN,TEKNISI,SUPERADMIN')
        ->name('log.index');

    // Master Data: Kategori & Ruangan (ADMIN only)
    Route::middleware('role:ADMIN,SUPERADMIN')->group(function () {
        Route::get('/master/kategori', [MasterController::class, 'kategoriIndex'])->name('master.kategori');
        Route::post('/master/kategori', [MasterController::class, 'kategoriStore'])->name('master.kategori.store');
        Route::delete('/master/kategori/{id}', [MasterController::class, 'kategoriDestroy'])->name('master.kategori.destroy');

        Route::get('/master/ruangan', [MasterController::class, 'ruanganIndex'])->name('master.ruangan');
        Route::post('/master/ruangan', [MasterController::class, 'ruanganStore'])->name('master.ruangan.store');
        Route::delete('/master/ruangan/{id}', [MasterController::class, 'ruanganDestroy'])->name('master.ruangan.destroy');

        // Team & User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{id}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::patch('/users/{id}/reject', [UserController::class, 'reject'])->name('users.reject');
        Route::patch('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');
    });
});
