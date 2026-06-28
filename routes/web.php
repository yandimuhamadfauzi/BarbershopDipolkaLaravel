<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// PUBLIC
Route::get('/', [HomeController::class, 'index'])->name('home');

// AUTH
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// BOOKING
Route::post('/booking', [HomeController::class, 'booking'])->name('booking')->middleware('auth');

// USER
Route::middleware('auth')->prefix('profil')->name('user.')->group(function () {
    Route::get('/',             [UserController::class, 'profil'])->name('profil');
    Route::post('/update',      [UserController::class, 'updateProfil'])->name('update');
    Route::post('/batal/{id}',  [UserController::class, 'batalAntrian'])->name('batal');
    Route::post('/clear-notif', [UserController::class, 'clearNotif'])->name('clearNotif');
    Route::get('/cek-notif',    [UserController::class, 'cekNotif'])->name('cekNotif');
});

// ADMIN LOGIN
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');

// ADMIN
Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                        [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/panggil/{id}',           [AdminController::class, 'panggil'])->name('panggil');
    Route::post('/selesai/{id}',           [AdminController::class, 'selesai'])->name('selesai');
    Route::post('/batal-antrian/{id}',     [AdminController::class, 'batalAntrian'])->name('batalAntrian');
    Route::delete('/hapus-antrian/{id}',   [AdminController::class, 'hapusAntrian'])->name('hapusAntrian');

    Route::get('/layanan',                 [AdminController::class, 'layanan'])->name('layanan');
    Route::post('/layanan',                [AdminController::class, 'simpanLayanan'])->name('layanan.simpan');
    Route::delete('/layanan/{id}',         [AdminController::class, 'hapusLayanan'])->name('layanan.hapus');
    Route::get('/layanan/toggle/{id}',     [AdminController::class, 'toggleLayanan'])->name('layanan.toggle');

    Route::get('/users',                   [AdminController::class, 'users'])->name('users');
    Route::delete('/users/{id}',           [AdminController::class, 'hapusUser'])->name('users.hapus');
    Route::post('/users/{id}/reset-pass',  [AdminController::class, 'resetPassword'])->name('users.resetPass');

    Route::get('/laporan',                 [AdminController::class, 'laporan'])->name('laporan');
});
