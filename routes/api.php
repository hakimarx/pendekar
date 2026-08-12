<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - PENDEKAR
|--------------------------------------------------------------------------
*/

Route::get('/pesantren', [DashboardController::class, 'apiPesantren'])->name('api.pesantren');
Route::get('/stats', [DashboardController::class, 'apiStats'])->name('api.stats');
