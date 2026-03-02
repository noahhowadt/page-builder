<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['guest', 'setup.access'])->group(function () {
    Route::get('setup', [SetupController::class, 'create'])
        ->name('setup');

    Route::post('setup', [SetupController::class, 'store']);
});

Route::prefix('admin')
    ->middleware(['web', 'auth']) // or whatever admin auth middleware you want
    ->group(function () {
        Route::get('/home', function () {
            return redirect()->route('dashboard');
        })->name('home');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });


require __DIR__ . '/settings.php';
