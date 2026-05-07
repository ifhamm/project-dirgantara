<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MwsPartController;
use App\Http\Controllers\MwsWorkflowController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Import\GanttImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ── Print (di luar prefix karena tidak butuh auth khusus) ─
Route::get('/mws/{mwsPart}/print', [MwsPartController::class, 'print'])
    ->name('mws.print');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('users', UserController::class);
});

// ══════════════════════════════════════════════════════════════
// PROJECT ROUTES
// ══════════════════════════════════════════════════════════════
Route::prefix('projects')->middleware(['auth'])->group(function () {

    Route::get('/', [ProjectController::class, 'index'])->name('projects.index');

    // ── [Manage] Only Admin & Superadmin ──────────────────────
    Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
        Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/', [ProjectController::class, 'store'])->name('projects.store');

        // Letakkan route statis import sebelum route dinamis edit/update/destroy
        Route::get('/import', [GanttImportController::class, 'create'])->name('projects.import.create');
        Route::post('/import', [GanttImportController::class, 'store'])->name('projects.import');

        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });

    // ── Route Wildcard (Dinamis) ──────────────────────
    Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show');
});


// ══════════════════════════════════════════════════════════════
// MWS ROUTES
// ══════════════════════════════════════════════════════════════
Route::prefix('mws')->middleware(['auth'])->group(function () {

    // ── Tracking List ─────────────────────────────────────
    Route::get('/tracking', [MwsPartController::class, 'tracking'])->name('mws.tracking');
    Route::get('/{id}', [MwsPartController::class, 'show'])->name('mws.show');

    // ── [Manage] Only Superadmin and Admin can Access  ─────────────────────────────────────
    Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {

        // ── MwsPart CRUD ──────────────────────────────────────
        Route::get('/create', [MwsPartController::class, 'create'])->name('mws.create');
        Route::post('/', [MwsPartController::class, 'store'])->name('mws.store');
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

        // ── Unapprove & UnfinishStep ─────────────────────────────────────────
        Route::post('/{mwsPartId}/steps/{stepNo}/unapprove', [MwsWorkflowController::class, 'unapproveStep'])
            ->name('mws.steps.unapprove');
        Route::post('/{mwsPartId}/steps/{stepNo}/unfinish', [MwsWorkflowController::class, 'unfinishStep'])
            ->name('mws.steps.unfinish');

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

        // ── Cancel Sign ──────────────────────────────────────────────
        Route::post('/{mwsPart}/cancel-sign', [MwsPartController::class, 'cancelSign'])
            ->name('mws.cancelSign');
    });

    // ── [Manage] Only Superadmin, Admin, and Quality 2 can Access  ─────────────────────────────────────
    Route::middleware(['auth', 'role:superadmin,admin,quality2'])->group(function () {

        // ── Approval ─────────────────────────────────
        Route::post('/{mwsPart}/sign', [MwsPartController::class, 'sign'])
            ->name('mws.sign');
    });

    // ── [Manage] Only Mechanic can Access  ─────────────────────────────────────
    Route::middleware(['auth', 'role:mechanic'])->group(function () {

        // ── Timer ─────────────────────────────────────────────
        Route::post('/{mwsPartId}/steps/{stepNo}/timer/start', [MwsWorkflowController::class, 'startTimer'])
            ->name('mws.timer.start');
        Route::post('/{mwsPartId}/steps/{stepNo}/timer/stop', [MwsWorkflowController::class, 'stopTimer'])
            ->name('mws.timer.stop');
    });

    // ── [Manage] Only Quality 2 can Access  ─────────────────────────────────────
    Route::middleware(['auth', 'role:quality2'])->group(function () {

        // ── Approval & Finish ─────────────────────────────────
        Route::post('/{mwsPartId}/steps/{stepNo}/approve', [MwsWorkflowController::class, 'approveStep'])
            ->name('mws.steps.approve');

        Route::post('/{mwsPartId}/steps/{stepNo}/finish', [MwsWorkflowController::class, 'finishStep'])
            ->name('mws.steps.finish');
    });

    // ── [Manage] Only Quality 1 can Access  ─────────────────────────────────────
    Route::middleware(['auth', 'role:quality1'])->group(function () {

        // ── Approval Final WMS ─────────────────────────────────
        Route::post('/{mwsPartId}/steps/{stepNo}/finish-final', [MwsWorkflowController::class, 'finishFinalInspection'])
            ->name('mws.steps.finishFinal');
    });
});

// ── [Manage] Only Superadmin and Admin can Access ───────────────────────────────────────────
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    });
});
require __DIR__ . '/auth.php';
