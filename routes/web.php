<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MwsPartController;
use App\Http\Controllers\MwsWorkflowController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Import\GanttImportController;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════
// PUBLIC & ALL AUTHENTICATED USERS ROUTES
// ══════════════════════════════════════════════════════════════
Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Semua role yang login bisa akses Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Print tidak butuh auth ketat, atau bisa ditaruh di dalam auth jika dokumen rahasia
Route::get('/mws/{mwsPart}/print', [MwsPartController::class, 'print'])->name('mws.print');


// ══════════════════════════════════════════════════════════════
// PROJECT ROUTES
// ══════════════════════════════════════════════════════════════
Route::prefix('projects')->middleware(['auth'])->group(function () {

    // ── [View] Quality 1, Quality 2, Admin, Superadmin ────────
    Route::middleware(['role:superadmin,admin,quality1,quality2'])->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show');
    });

    // ── [Manage] Admin & Superadmin Saja ──────────────────────
    Route::middleware(['role:superadmin,admin'])->group(function () {
        Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/import', [GanttImportController::class, 'create'])->name('projects.import.create');
        Route::post('/import', [GanttImportController::class, 'store'])->name('projects.import');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });
});


// ══════════════════════════════════════════════════════════════
// MWS ROUTES
// ══════════════════════════════════════════════════════════════
Route::prefix('mws')->middleware(['auth'])->group(function () {

    // ── [View] Bisa diakses semua role (Mechanic, Q1, Q2, Admin) ──
    Route::get('/tracking', [MwsPartController::class, 'tracking'])->name('mws.tracking');
    // Tambahkan regex whereNumber agar tidak bentrok dengan /create
    Route::get('/{id}', [MwsPartController::class, 'show'])->whereNumber('id')->name('mws.show');


    // ── [Mechanic Actions] Mechanic, Admin, Superadmin ────────────
    Route::middleware(['role:superadmin,admin,mechanic'])->group(function () {
        // Timer
        Route::post('/{mwsPartId}/steps/{stepNo}/timer/start', [MwsWorkflowController::class, 'startTimer'])->name('mws.timer.start');
        Route::post('/{mwsPartId}/steps/{stepNo}/timer/stop', [MwsWorkflowController::class, 'stopTimer'])->name('mws.timer.stop');

        // Attachments (Upload Lampiran)
        Route::post('/{mwsPartId}/attachments', [MwsWorkflowController::class, 'storeAttachment'])->name('mws.attachments.store');
        Route::delete('/{mwsPartId}/attachments/{publicId}', [MwsWorkflowController::class, 'destroyAttachment'])->name('mws.attachments.destroy');
        Route::post('/{mwsPartId}/steps/{stepNo}/attachments', [MwsWorkflowController::class, 'storeStepAttachment'])->name('mws.stepAttachments.store');
        Route::delete('/{mwsPartId}/steps/{stepNo}/attachments/{publicId}', [MwsWorkflowController::class, 'destroyStepAttachment'])->name('mws.stepAttachments.destroy');

        // Mechanic sign on & progress (Stripping)
        Route::post('/{mwsPartId}/steps/{stepNo}/sign-on', [MwsWorkflowController::class, 'signOn'])->name('mws.mechanics.signOn');
        Route::post('/step/{stepNo}/update', [MwsPartController::class, 'updateStep']);

        // Detail & Substep (Untuk pelaporan stripping oleh mekanik)
        Route::post('/{mwsPartId}/steps/{stepNo}/details', [MwsWorkflowController::class, 'storeDetail'])->name('mws.details.store');
        Route::put('/{mwsPartId}/steps/{stepNo}/details/{detailIndex}', [MwsWorkflowController::class, 'updateDetail'])->name('mws.details.update');
        Route::delete('/{mwsPartId}/steps/{stepNo}/details/{detailIndex}', [MwsWorkflowController::class, 'destroyDetail'])->name('mws.details.destroy');
        Route::post('/{mwsPartId}/steps/{stepNo}/substeps', [MwsWorkflowController::class, 'storeSubStep'])->name('mws.substeps.store');
        Route::put('/{mwsPartId}/steps/{stepNo}/substeps/{subStepId}', [MwsWorkflowController::class, 'updateSubStep'])->name('mws.substeps.update');
        Route::delete('/{mwsPartId}/steps/{stepNo}/substeps/{subStepId}', [MwsWorkflowController::class, 'destroySubStep'])->name('mws.substeps.destroy');
    });


    // ── [Finish MWS] Quality 1, Admin, Superadmin ─────────────────
    Route::middleware(['role:superadmin,admin,quality1'])->group(function () {
        Route::post('/{mwsPartId}/steps/{stepNo}/finish', [MwsWorkflowController::class, 'finishStep'])->name('mws.steps.finish');
        Route::post('/{mwsPartId}/steps/{stepNo}/unfinish', [MwsWorkflowController::class, 'unfinishStep'])->name('mws.steps.unfinish');
        Route::post('/{mwsPartId}/steps/{stepNo}/finish-final', [MwsWorkflowController::class, 'finishFinalInspection'])->name('mws.steps.finishFinal');
    });


    // ── [Sign / Verify MWS] Quality 2, Admin, Superadmin ──────────
    Route::middleware(['role:superadmin,admin,quality2'])->group(function () {
        Route::post('/{mwsPart}/sign', [MwsPartController::class, 'sign'])->name('mws.sign');
        Route::post('/{mwsPart}/cancel-sign', [MwsPartController::class, 'cancelSign'])->name('mws.cancelSign');
        Route::post('/{mwsPartId}/steps/{stepNo}/approve', [MwsWorkflowController::class, 'approveStep'])->name('mws.steps.approve');
        Route::post('/{mwsPartId}/steps/{stepNo}/unapprove', [MwsWorkflowController::class, 'unapproveStep'])->name('mws.steps.unapprove');
    });


    // ── [Manage MWS] Admin & Superadmin Saja ──────────────────────
    Route::middleware(['role:superadmin,admin'])->group(function () {
        // CRUD Utama
        Route::get('/create', [MwsPartController::class, 'create'])->name('mws.create');
        Route::post('/', [MwsPartController::class, 'store'])->name('mws.store');
        Route::put('/{id}', [MwsPartController::class, 'update'])->whereNumber('id')->name('mws.update');
        Route::delete('/{id}', [MwsPartController::class, 'destroy'])->whereNumber('id')->name('mws.destroy');

        // Fitur MWS
        Route::post('/{id}/generate-steps', [MwsPartController::class, 'generateSteps'])->whereNumber('id')->name('mws.generateSteps');
        Route::post('/{mwsPartId}/duplicate', [MwsPartController::class, 'duplicate'])->name('mws.duplicate');

        // Manajemen Step Inti
        Route::post('/{mwsPartId}/steps', [MwsWorkflowController::class, 'storeStep'])->name('mws.steps.store');
        Route::post('/{mwsPartId}/steps/{stepNo}/insert-after', [MwsWorkflowController::class, 'insertStepAfter'])->name('mws.steps.insertAfter');
        Route::delete('/{mwsPartId}/steps/{stepNo}', [MwsWorkflowController::class, 'destroyStep'])->name('mws.steps.destroy');
        Route::delete('/{mwsPartId}/steps/bulk-delete', [MwsWorkflowController::class, 'bulkDeleteSteps'])->name('mws.steps.bulkDelete');
        Route::put('/{mwsPartId}/steps/{stepNo}/caution', [MwsWorkflowController::class, 'updateStepCaution'])->name('mws.steps.caution');

        // Assign Mechanic Paksa
        Route::post('/{mwsPartId}/steps/{stepNo}/assign-mechanic', [MwsWorkflowController::class, 'assignMechanic'])->name('mws.mechanics.assign');
        Route::delete('/{mwsPartId}/steps/{stepNo}/remove-mechanic', [MwsWorkflowController::class, 'removeMechanic'])->name('mws.mechanics.remove');

        // Consumables
        Route::post('/{mwsPartId}/consumables', [MwsWorkflowController::class, 'storeConsumable'])->name('mws.consumables.store');
        Route::put('/{mwsPartId}/consumables/{consumableId}', [MwsWorkflowController::class, 'updateConsumable'])->name('mws.consumables.update');
        Route::delete('/{mwsPartId}/consumables/{consumableId}', [MwsWorkflowController::class, 'destroyConsumable'])->name('mws.consumables.destroy');
    });
});

require __DIR__ . '/auth.php';
