<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MwsPartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('mws')->group(function () {

    Route::get('/{id}', [MwsPartController::class, 'show'])->name('mws.show');

    Route::post('/{id}/generate-steps', [MwsPartController::class, 'generateSteps'])
        ->name('mws.generateSteps');

    Route::post('/step/{id}/update', [MwsPartController::class, 'updateStep']);
});

Route::get('/mws/{mwsPart}/print', [MwsPartController::class, 'print'])
    ->name('mws.print');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
