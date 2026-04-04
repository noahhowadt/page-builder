<?php

use App\Http\Controllers\ComponentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SetupController;
use App\Models\Page;
use App\Support\PageStructureComponentResolver;
use App\Support\PageStructureLinkResolver;
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
        // Pages
        Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
        Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
        Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
        // Components
        Route::get('/components', [ComponentController::class, 'index'])->name('components.index');
        Route::post('/components', [ComponentController::class, 'store'])->name('components.store');
        Route::get('/components/{component}/edit', [ComponentController::class, 'edit'])->name('components.edit');
        Route::put('/components/{component}', [ComponentController::class, 'update'])->name('components.update');
        Route::delete('/components/{component}', [ComponentController::class, 'destroy'])->name('components.destroy');
    });

require __DIR__.'/settings.php';

Route::get('{slug?}', function ($slug = '') {
    $page = Page::where('slug', '/'.$slug)
        ->where('is_published', true)
        ->first();

    if (! $page) {
        abort(404);
    }

    $structure = PageStructureComponentResolver::resolve($page->structure ?? []);
    $structure = PageStructureLinkResolver::resolve($structure);

    return view('page', [
        'page' => $page,
        'structure' => $structure,
    ]);
})->where('slug', '.*');
