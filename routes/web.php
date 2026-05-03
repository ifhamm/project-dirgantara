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

    // ── Route statis (harus di atas wildcard) ─────────────
    Route::get('/create', [MwsPartController::class, 'create'])->name('mws.create');

    // ── MwsPart CRUD ──────────────────────────────────────
    Route::get('/{id}', [MwsPartController::class, 'show'])->name('mws.show');
    Route::put('/{id}', [MwsPartController::class, 'update'])->name('mws.update');
    Route::delete('/{id}', [MwsPartController::class, 'destroy'])->name('mws.destroy');
    Route::post('/{id}/generate-steps', [MwsPartController::class, 'generateSteps'])
        ->name('mws.generateSteps');

    // ── Step update (existing) ────────────────────────────
    Route::post('/step/{id}/update', [MwsPartController::class, 'updateStep']);

    // ── Sign ──────────────────────────────────────────────
    Route::post('/{mwsPart}/sign', [MwsPartController::class, 'sign'])
        ->name('mws.sign');
    Route::post('/{mwsPart}/cancel-sign', [MwsPartController::class, 'cancelSign'])
        ->name('mws.cancelSign');

    // ── Consumables ───────────────────────────────────────
    Route::post('/{mwsPartId}/consumables', [MwsPartController::class, 'storeConsumable'])
        ->name('mws.consumables.store');
    Route::put('/{mwsPartId}/consumables/{consumableId}', [MwsPartController::class, 'updateConsumable'])
        ->name('mws.consumables.update');
    Route::delete('/{mwsPartId}/consumables/{consumableId}', [MwsPartController::class, 'destroyConsumable'])
        ->name('mws.consumables.destroy');

    // ── Caution & Note per Step ───────────────────────────
    Route::put('/{mwsPartId}/steps/{stepNo}/caution', [MwsPartController::class, 'updateStepCaution'])
        ->name('mws.steps.caution');

    // ── Sub-Steps ─────────────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/substeps', [MwsPartController::class, 'storeSubStep'])
        ->name('mws.substeps.store');
    Route::put('/{mwsPartId}/steps/{stepNo}/substeps/{subStepId}', [MwsPartController::class, 'updateSubStep'])
        ->name('mws.substeps.update');
    Route::delete('/{mwsPartId}/steps/{stepNo}/substeps/{subStepId}', [MwsPartController::class, 'destroySubStep'])
        ->name('mws.substeps.destroy');
});

Route::get('/mws/{mwsPart}/print', [MwsPartController::class, 'print'])
    ->name('mws.print');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';