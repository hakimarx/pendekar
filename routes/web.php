<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PendataanController;
use App\Http\Controllers\RekapController;

/*
|--------------------------------------------------------------------------
| Web Routes - PENDEKAR (PD PONTREN JAWA TIMUR)
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/pendataan', [PendataanController::class, 'index'])->name('pendataan.index');
Route::post('/pendataan', [PendataanController::class, 'store'])->name('pendataan.store');

Route::prefix('rekap')->name('rekap.')->group(function () {
    Route::get('/pesantren', [RekapController::class, 'pesantren'])->name('pesantren');
    Route::get('/bencana', [RekapController::class, 'bencana'])->name('bencana');
    Route::get('/ramah', [RekapController::class, 'ramah'])->name('ramah');
    Route::get('/digital', [RekapController::class, 'digital'])->name('digital');
    Route::get('/ecoteologi', [RekapController::class, 'ecoteologi'])->name('ecoteologi');
    Route::get('/karyailmiah', [RekapController::class, 'karyailmiah'])->name('karyailmiah');
    Route::get('/kitab', [RekapController::class, 'karyailmiah'])->name('kitab');
    Route::get('/produk', [RekapController::class, 'produk'])->name('produk');
});

use App\Http\Controllers\LoginController;
use App\Http\Controllers\VerifikatorController;
use App\Http\Controllers\AdminController;

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/verifikator', [VerifikatorController::class, 'index'])->name('verifikator.index');
Route::post('/verifikator/approve', [VerifikatorController::class, 'approve'])->name('verifikator.approve');
Route::post('/verifikator/reject', [VerifikatorController::class, 'reject'])->name('verifikator.reject');

Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::post('/admin/users', [AdminController::class, 'addUser'])->name('admin.users');
