<?php

use App\Http\Controllers\DatabaseDownloadController;
use App\Http\Controllers\NotamController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [NotamController::class, 'index'])->name('notam.index');
Route::post('/', [NotamController::class, 'store'])->name('notam.store');
Route::get('/download/{cacheKey}', [NotamController::class, 'show'])->name('notam.show');
Route::get('/db', [DatabaseDownloadController::class, 'index'])->name('database-download.index');

Route::get('/home', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => false,
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
