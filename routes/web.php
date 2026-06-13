<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChordBrowserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ChordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServicePlanController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/p/{token}', [ServicePlanController::class, 'publicShow'])
    ->name('service-plans.public');

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
    Route::get('tools/tuner', [ChordBrowserController::class, 'tuner'])->name('tools.tuner');
    Route::get('tools/metronome', [ChordBrowserController::class, 'metronome'])->name('tools.metronome');

    Route::get('songs/{song}/export', [SongController::class, 'export'])->name('songs.export');
    Route::resource('songs', SongController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);

    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::resource('contacts', ContactController::class)->except(['show']);

    Route::get('service-plans/songs/search', [ServicePlanController::class, 'searchSongs'])
        ->name('service-plans.songs.search');
    Route::resource('service-plans', ServicePlanController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('service-plans/{service_plan}/export', [ServicePlanController::class, 'export'])
        ->name('service-plans.export');
    Route::get('service-plans/{service_plan}/share', [ServicePlanController::class, 'share'])
        ->name('service-plans.share');
    Route::post('service-plans/{service_plan}/publish', [ServicePlanController::class, 'publish'])
        ->name('service-plans.publish');
    Route::delete('service-plans/{service_plan}/publish', [ServicePlanController::class, 'unpublish'])
        ->name('service-plans.unpublish');
    Route::put('service-plans/{service_plan}/setlist', [ServicePlanController::class, 'syncSetlist'])
        ->name('service-plans.setlist.sync');
});

require __DIR__.'/auth.php';
