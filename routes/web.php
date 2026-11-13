<?php

use App\Http\Controllers\AccessLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HandoverController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Guest / Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated Logistics Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 1 & 6. Master Barang & Mode Pembuat Barcode (beserta pencatatan user pembuat)
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/items/generate-code', [ItemController::class, 'generateCode'])->name('items.generate-code');
    Route::get('/barcode-studio', [ItemController::class, 'barcodeStudio'])->name('items.barcode-studio');
    Route::get('/items/barcode-studio', [ItemController::class, 'barcodeStudio']);
    Route::post('/categories', [ItemController::class, 'storeCategory'])->name('categories.store');

    // 2 & 6. Terminal Scan Barcode Masuk & Keluar ke Database Penyimpanan (beserta user petugas scan)
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::get('/scan/lookup', [ScanController::class, 'lookup'])->name('scan.lookup');
    Route::post('/scan/process', [ScanController::class, 'process'])->name('scan.process');

    // 3. Laporan Barang Keluar & Masuk melalui Scan Barcode
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');

    // 8. Berita Acara Serah Terima (BAST) & Berita Acara Pengembalian (BAP)
    Route::get('/handovers', [HandoverController::class, 'index'])->name('handovers.index');
    Route::get('/handovers/create', [HandoverController::class, 'create'])->name('handovers.create');
    Route::post('/handovers', [HandoverController::class, 'store'])->name('handovers.store');
    Route::get('/handovers/{handover}', [HandoverController::class, 'show'])->name('handovers.show');
    Route::get('/handovers/{handover}/print', [HandoverController::class, 'print'])->name('handovers.print');
    Route::post('/handovers/{handover}/photos', [HandoverController::class, 'uploadPhotos'])->name('handovers.photos.store');
    Route::delete('/handovers/{handover}/photos/{photo}', [HandoverController::class, 'destroyPhoto'])->name('handovers.photos.destroy');

    // 7. History Akses Keluar-Masuk Web (Audit Log)
    Route::get('/access-logs', [AccessLogController::class, 'index'])->name('access-logs.index');

    // 5. Pengaturan Profil & Manajemen Akun Petugas
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/users', [ProfileController::class, 'storeUser'])->name('profile.users.store');
    Route::patch('/profile/users/{targetUser}/toggle', [ProfileController::class, 'toggleUser'])->name('profile.users.toggle');
});
