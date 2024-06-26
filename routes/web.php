<?php

use App\Http\Controllers\DatabaseDownloadController;
use App\Http\Controllers\NotamController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Playground;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//These routes are the main entry point for this Notam app.
Route::get('/', [NotamController::class, 'index'])->name('notam.index');
Route::post('/', [NotamController::class, 'store'])->name('notam.store');
Route::get('/download/{cacheKey}', [NotamController::class, 'show'])->name('notam.show');
Route::get('/db', [DatabaseDownloadController::class, 'index'])->name('database-download.index');

Route::get('play/{session?}', Playground::class)->name('playground');

//All routes below are the boilerplate for logins and profiles etc.
Route::get('/home', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
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
