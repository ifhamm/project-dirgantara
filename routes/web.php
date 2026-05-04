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

Route::prefix('mws')->middleware(['auth'])->group(function () {

    // ── Route statis (harus di atas wildcard) ─────────────
    Route::get('/create', [MwsPartController::class, 'create'])->name('mws.create');

    // ── MwsPart CRUD ──────────────────────────────────────
    Route::post('/', [MwsPartController::class, 'store'])->name('mws.store');
    Route::get('/{id}', [MwsPartController::class, 'show'])->name('mws.show');
    Route::put('/{id}', [MwsPartController::class, 'update'])->name('mws.update');
    Route::delete('/{id}', [MwsPartController::class, 'destroy'])->name('mws.destroy');
    Route::post('/{id}/generate-steps', [MwsPartController::class, 'generateSteps'])
        ->name('mws.generateSteps');

    // ── Duplicate ─────────────────────────────────────────
    Route::post('/{mwsPartId}/duplicate', [MwsPartController::class, 'duplicate'])
        ->name('mws.duplicate');

    // ── Step update (existing) ────────────────────────────
    Route::post('/step/{stepNo}/update', [MwsPartController::class, 'updateStep']);

    // ── Steps Management ──────────────────────────────────
    Route::post('/{mwsPartId}/steps', [MwsPartController::class, 'storeStep'])
        ->name('mws.steps.store');
    Route::post('/{mwsPartId}/steps/{stepNo}/insert-after', [MwsPartController::class, 'insertStepAfter'])
        ->name('mws.steps.insertAfter');
    Route::delete('/{mwsPartId}/steps/{stepNo}', [MwsPartController::class, 'destroyStep'])
        ->name('mws.steps.destroy');
    Route::delete('/{mwsPartId}/steps/bulk-delete', [MwsPartController::class, 'bulkDeleteSteps'])
        ->name('mws.steps.bulkDelete');

    // ── Details ───────────────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/details', [MwsPartController::class, 'storeDetail'])
        ->name('mws.details.store');
    Route::put('/{mwsPartId}/steps/{stepNo}/details/{detailIndex}', [MwsPartController::class, 'updateDetail'])
        ->name('mws.details.update');
    Route::delete('/{mwsPartId}/steps/{stepNo}/details/{detailIndex}', [MwsPartController::class, 'destroyDetail'])
        ->name('mws.details.destroy');

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

    // ── Mechanics ─────────────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/sign-on', [MwsPartController::class, 'signOn'])
        ->name('mws.mechanics.signOn');
    Route::post('/{mwsPartId}/steps/{stepNo}/assign-mechanic', [MwsPartController::class, 'assignMechanic'])
        ->name('mws.mechanics.assign');
    Route::delete('/{mwsPartId}/steps/{stepNo}/remove-mechanic', [MwsPartController::class, 'removeMechanic'])
        ->name('mws.mechanics.remove');

    // ── Timer ─────────────────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/timer/start', [MwsPartController::class, 'startTimer'])
        ->name('mws.timer.start');
    Route::post('/{mwsPartId}/steps/{stepNo}/timer/stop', [MwsPartController::class, 'stopTimer'])
        ->name('mws.timer.stop');

    // ── Approval & Finish ─────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/approve', [MwsPartController::class, 'approveStep'])
        ->name('mws.steps.approve');
    Route::post('/{mwsPartId}/steps/{stepNo}/unapprove', [MwsPartController::class, 'unapproveStep'])
        ->name('mws.steps.unapprove');
    Route::post('/{mwsPartId}/steps/{stepNo}/finish', [MwsPartController::class, 'finishStep'])
        ->name('mws.steps.finish');
    Route::post('/{mwsPartId}/steps/{stepNo}/unfinish', [MwsPartController::class, 'unfinishStep'])
        ->name('mws.steps.unfinish');
    Route::post('/{mwsPartId}/steps/{stepNo}/finish-final', [MwsPartController::class, 'finishFinalInspection'])
        ->name('mws.steps.finishFinal');

    // ── Consumables ───────────────────────────────────────
    Route::post('/{mwsPartId}/consumables', [MwsPartController::class, 'storeConsumable'])
        ->name('mws.consumables.store');
    Route::put('/{mwsPartId}/consumables/{consumableId}', [MwsPartController::class, 'updateConsumable'])
        ->name('mws.consumables.update');
    Route::delete('/{mwsPartId}/consumables/{consumableId}', [MwsPartController::class, 'destroyConsumable'])
        ->name('mws.consumables.destroy');

    // ── Attachments (placeholder) ─────────────────────────
    Route::post('/{mwsPartId}/attachments', [MwsPartController::class, 'storeAttachment'])
        ->name('mws.attachments.store');
    Route::delete('/{mwsPartId}/attachments/{publicId}', [MwsPartController::class, 'destroyAttachment'])
        ->name('mws.attachments.destroy');
    Route::post('/{mwsPartId}/steps/{stepNo}/attachments', [MwsPartController::class, 'storeStepAttachment'])
        ->name('mws.stepAttachments.store');
    Route::delete('/{mwsPartId}/steps/{stepNo}/attachments/{publicId}', [MwsPartController::class, 'destroyStepAttachment'])
        ->name('mws.stepAttachments.destroy');

    // ── Sign ──────────────────────────────────────────────
    Route::post('/{mwsPart}/sign', [MwsPartController::class, 'sign'])
        ->name('mws.sign');
    Route::post('/{mwsPart}/cancel-sign', [MwsPartController::class, 'cancelSign'])
        ->name('mws.cancelSign');
});

// ── Print (di luar prefix karena tidak butuh auth khusus) ─
Route::get('/mws/{mwsPart}/print', [MwsPartController::class, 'print'])
    ->name('mws.print');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
