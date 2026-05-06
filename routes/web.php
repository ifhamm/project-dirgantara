<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MwsPartController;
use App\Http\Controllers\MwsWorkflowController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Import\GanttImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// ══════════════════════════════════════════════════════════════
// PROJECT ROUTES
// ══════════════════════════════════════════════════════════════
Route::prefix('projects')->middleware(['auth'])->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/import', [GanttImportController::class, 'create'])->name('projects.import.create');
    Route::post('/import', [GanttImportController::class, 'store'])->name('projects.import');

    Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
});


// ══════════════════════════════════════════════════════════════
// MWS ROUTES
// ══════════════════════════════════════════════════════════════
Route::prefix('mws')->middleware(['auth'])->group(function () {

    // ── Tracking List ─────────────────────────────────────
    Route::get('/tracking', [MwsPartController::class, 'tracking'])->name('mws.tracking');

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
    Route::post('/{mwsPartId}/steps', [MwsWorkflowController::class, 'storeStep'])
        ->name('mws.steps.store');
    Route::post('/{mwsPartId}/steps/{stepNo}/insert-after', [MwsWorkflowController::class, 'insertStepAfter'])
        ->name('mws.steps.insertAfter');
    Route::delete('/{mwsPartId}/steps/{stepNo}', [MwsWorkflowController::class, 'destroyStep'])
        ->name('mws.steps.destroy');
    Route::delete('/{mwsPartId}/steps/bulk-delete', [MwsWorkflowController::class, 'bulkDeleteSteps'])
        ->name('mws.steps.bulkDelete');

    // ── Details ───────────────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/details', [MwsWorkflowController::class, 'storeDetail'])
        ->name('mws.details.store');
    Route::put('/{mwsPartId}/steps/{stepNo}/details/{detailIndex}', [MwsWorkflowController::class, 'updateDetail'])
        ->name('mws.details.update');
    Route::delete('/{mwsPartId}/steps/{stepNo}/details/{detailIndex}', [MwsWorkflowController::class, 'destroyDetail'])
        ->name('mws.details.destroy');

    // ── Caution & Note per Step ───────────────────────────
    Route::put('/{mwsPartId}/steps/{stepNo}/caution', [MwsWorkflowController::class, 'updateStepCaution'])
        ->name('mws.steps.caution');

    // ── Sub-Steps ─────────────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/substeps', [MwsWorkflowController::class, 'storeSubStep'])
        ->name('mws.substeps.store');
    Route::put('/{mwsPartId}/steps/{stepNo}/substeps/{subStepId}', [MwsWorkflowController::class, 'updateSubStep'])
        ->name('mws.substeps.update');
    Route::delete('/{mwsPartId}/steps/{stepNo}/substeps/{subStepId}', [MwsWorkflowController::class, 'destroySubStep'])
        ->name('mws.substeps.destroy');

    // ── Mechanics ─────────────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/sign-on', [MwsWorkflowController::class, 'signOn'])
        ->name('mws.mechanics.signOn');
    Route::post('/{mwsPartId}/steps/{stepNo}/assign-mechanic', [MwsWorkflowController::class, 'assignMechanic'])
        ->name('mws.mechanics.assign');
    Route::delete('/{mwsPartId}/steps/{stepNo}/remove-mechanic', [MwsWorkflowController::class, 'removeMechanic'])
        ->name('mws.mechanics.remove');

    // ── Timer ─────────────────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/timer/start', [MwsWorkflowController::class, 'startTimer'])
        ->name('mws.timer.start');
    Route::post('/{mwsPartId}/steps/{stepNo}/timer/stop', [MwsWorkflowController::class, 'stopTimer'])
        ->name('mws.timer.stop');

    // ── Approval & Finish ─────────────────────────────────
    Route::post('/{mwsPartId}/steps/{stepNo}/approve', [MwsWorkflowController::class, 'approveStep'])
        ->name('mws.steps.approve');
    Route::post('/{mwsPartId}/steps/{stepNo}/unapprove', [MwsWorkflowController::class, 'unapproveStep'])
        ->name('mws.steps.unapprove');
    Route::post('/{mwsPartId}/steps/{stepNo}/finish', [MwsWorkflowController::class, 'finishStep'])
        ->name('mws.steps.finish');
    Route::post('/{mwsPartId}/steps/{stepNo}/unfinish', [MwsWorkflowController::class, 'unfinishStep'])
        ->name('mws.steps.unfinish');
    Route::post('/{mwsPartId}/steps/{stepNo}/finish-final', [MwsWorkflowController::class, 'finishFinalInspection'])
        ->name('mws.steps.finishFinal');

    // ── Consumables ───────────────────────────────────────
    Route::post('/{mwsPartId}/consumables', [MwsWorkflowController::class, 'storeConsumable'])
        ->name('mws.consumables.store');
    Route::put('/{mwsPartId}/consumables/{consumableId}', [MwsWorkflowController::class, 'updateConsumable'])
        ->name('mws.consumables.update');
    Route::delete('/{mwsPartId}/consumables/{consumableId}', [MwsWorkflowController::class, 'destroyConsumable'])
        ->name('mws.consumables.destroy');

    // ── Attachments (placeholder) ─────────────────────────
    Route::post('/{mwsPartId}/attachments', [MwsWorkflowController::class, 'storeAttachment'])
        ->name('mws.attachments.store');
    Route::delete('/{mwsPartId}/attachments/{publicId}', [MwsWorkflowController::class, 'destroyAttachment'])
        ->name('mws.attachments.destroy');
    Route::post('/{mwsPartId}/steps/{stepNo}/attachments', [MwsWorkflowController::class, 'storeStepAttachment'])
        ->name('mws.stepAttachments.store');
    Route::delete('/{mwsPartId}/steps/{stepNo}/attachments/{publicId}', [MwsWorkflowController::class, 'destroyStepAttachment'])
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
