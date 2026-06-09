<?php

use Illuminate\Support\Facades\Route;

// CONTROLLERS
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\ServiceController;

// ================= AUTH =================

// Redirect awal
Route::get('/', function () {
    return redirect('/login');
});

// ================= GUEST =================
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ================= LOGOUT =================
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ================= SEMUA KARYAWAN LOGIN =================
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::view('/profile', 'auth.profile')->name('profile');
    Route::view('/ganti-password', 'auth.password')->name('password');

    // ✔ semua karyawan
    Route::resource('customer', CustomerController::class);
    Route::resource('barang', BarangController::class);
    Route::get('transaksi/scan', [TransaksiController::class, 'scanForm'])->name('transaksi.scan');
    Route::post('transaksi/scan', [TransaksiController::class, 'scanProcess'])->name('transaksi.scan.process');
    Route::resource('transaksi', TransaksiController::class);
    Route::resource('service', ServiceController::class);

});

// ================= 🔥 KHUSUS ADMIN =================
Route::middleware(['auth','role:admin'])->group(function () {

    Route::resource('pembelian', PembelianController::class);

});