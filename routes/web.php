<?php

use App\Http\Controllers\ChordBrowserController;
use App\Http\Controllers\ChordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServicePlanController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('chords/search', [ChordController::class, 'search'])->name('chords.search');
    Route::get('chords/diagrams', [ChordController::class, 'diagrams'])->name('chords.diagrams');
    Route::get('chords/guitar', [ChordBrowserController::class, 'guitar'])->name('chords.guitar');
    Route::get('chords/keyboard', [ChordBrowserController::class, 'keyboard'])->name('chords.keyboard');
    Route::get('tools/circle-of-fifths', [ChordBrowserController::class, 'circleOfFifths'])->name('tools.circle-of-fifths');

    Route::get('songs/{song}/export', [SongController::class, 'export'])->name('songs.export');
    Route::resource('songs', SongController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);

    Route::resource('service-plans', ServicePlanController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('service-plans/{service_plan}/songs', [ServicePlanController::class, 'attachSong'])
        ->name('service-plans.songs.attach');
});

require __DIR__.'/auth.php';
