<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mws\AssignMechanicRequest;
use App\Http\Requests\Mws\BulkDeleteMwsStepsRequest;
use App\Http\Requests\Mws\FinishFinalInspectionRequest;
use App\Http\Requests\Mws\MwsStepDetailRequest;
use App\Http\Requests\Mws\MwsSubStepRequest;
use App\Http\Requests\Mws\StoreConsumableRequest;
use App\Http\Requests\Mws\StoreMwsWorkflowStepRequest;
use App\Http\Requests\Mws\UpdateConsumableRequest;
use App\Http\Requests\Mws\UpdateStepCautionRequest;
use App\Services\MwsWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MwsWorkflowController extends Controller
{
    public function __construct(
        private readonly MwsWorkflowService $workflow,
    ) {
    }

    public function storeStep(StoreMwsWorkflowStepRequest $request, string|int $mwsPartId)
    {
        $step = $this->workflow->storeStep($mwsPartId, $request->validated('description'));

        return response()->json(['success' => true, 'step' => $step]);
    }

    public function insertStepAfter(StoreMwsWorkflowStepRequest $request, string|int $mwsPartId, int $stepNo)
    {
        $newStep = $this->workflow->insertStepAfter($mwsPartId, $stepNo, $request->validated('description'));

        return response()->json(['success' => true, 'step' => $newStep]);
    }

    public function destroyStep(string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->destroyStep($mwsPartId, $stepNo);

        return response()->json(['success' => true]);
    }

    public function bulkDeleteSteps(BulkDeleteMwsStepsRequest $request, string|int $mwsPartId)
    {
        $this->workflow->bulkDeleteSteps($mwsPartId, $request->validated('step_nos'));

        return response()->json(['success' => true]);
    }

    public function storeDetail(MwsStepDetailRequest $request, string|int $mwsPartId, int $stepNo)
    {
        $details = $this->workflow->storeDetail($mwsPartId, $stepNo, $request->validated('detail'));

        return response()->json(['success' => true, 'details' => $details]);
    }

    public function updateDetail(MwsStepDetailRequest $request, string|int $mwsPartId, int $stepNo, int $detailIndex)
    {
        $this->workflow->updateDetail($mwsPartId, $stepNo, $detailIndex, $request->validated('detail'));

        return response()->json(['success' => true]);
    }

    public function destroyDetail(string|int $mwsPartId, int $stepNo, int $detailIndex)
    {
        $this->workflow->destroyDetail($mwsPartId, $stepNo, $detailIndex);

        return response()->json(['success' => true]);
    }

    public function signOn(Request $request, string|int $mwsPartId, int $stepNo)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->workflow->signOn($mwsPartId, $stepNo, $user);

        return response()->json(['success' => true]);
    }

    public function assignMechanic(AssignMechanicRequest $request, string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->assignMechanic($mwsPartId, $stepNo, $request->validated('nik'));

        return response()->json(['success' => true]);
    }

    public function removeMechanic(Request $request, string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->removeMechanic($mwsPartId, $stepNo, (string) $request->input('nik'));

        return response()->json(['success' => true]);
    }

    public function startTimer(Request $request, string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->startTimer($mwsPartId, $stepNo);

        return response()->json(['success' => true]);
    }

    public function stopTimer(Request $request, string|int $mwsPartId, int $stepNo)
    {
        try {
            $newHours = $this->workflow->stopTimer($mwsPartId, $stepNo);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'hours' => $newHours,
        ]);
    }

    public function approveStep(Request $request, string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->approveStep($mwsPartId, $stepNo);

        return response()->json(['success' => true]);
    }

    public function unapproveStep(Request $request, string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->unapproveStep($mwsPartId, $stepNo);

        return response()->json(['success' => true]);
    }

    public function finishStep(Request $request, string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->finishStep($mwsPartId, $stepNo);

        return response()->json(['success' => true]);
    }

    public function unfinishStep(Request $request, string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->unfinishStep($mwsPartId, $stepNo);

        return response()->json(['success' => true]);
    }

    public function finishFinalInspection(FinishFinalInspectionRequest $request, string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->finishFinalInspection($mwsPartId, $stepNo, $request->validated('status_s_us') ?? '');

        return response()->json(['success' => true]);
    }

    public function storeAttachment(Request $request, string|int $mwsPartId)
    {
        return response()->json(['success' => true]);
    }

    public function destroyAttachment(string|int $mwsPartId, string $publicId)
    {
        return response()->json(['success' => true]);
    }

    public function storeStepAttachment(Request $request, string|int $mwsPartId, int $stepNo)
    {
        return response()->json(['success' => true]);
    }

    public function destroyStepAttachment(string|int $mwsPartId, int $stepNo, string $publicId)
    {
        return response()->json(['success' => true]);
    }

    public function duplicate(string|int $mwsPartId)
    {
        $duplicate = $this->workflow->duplicate($mwsPartId);

        return response()->json(['success' => true, 'mwsPart' => $duplicate]);
    }

    public function storeConsumable(StoreConsumableRequest $request, string|int $mwsPartId)
    {
        $consumable = $this->workflow->storeConsumable($mwsPartId, $request->validated());

        return response()->json(['success' => true, 'consumable' => $consumable]);
    }

    public function updateConsumable(UpdateConsumableRequest $request, string|int $mwsPartId, int $consumableId)
    {
        $consumable = $this->workflow->updateConsumable($mwsPartId, $consumableId, $request->validated());

        return response()->json(['success' => true, 'consumable' => $consumable]);
    }

    public function destroyConsumable(string|int $mwsPartId, int $consumableId)
    {
        $this->workflow->destroyConsumable($mwsPartId, $consumableId);

        return response()->json(['success' => true]);
    }

    public function updateStepCaution(UpdateStepCautionRequest $request, string|int $mwsPartId, int $stepNo)
    {
        $this->workflow->updateStepCaution(
            $mwsPartId,
            $stepNo,
            $request->validated('caution'),
            $request->validated('note'),
        );

        return response()->json(['success' => true]);
    }

    public function storeSubStep(MwsSubStepRequest $request, string|int $mwsPartId, int $stepNo)
    {
        $subStep = $this->workflow->storeSubStep($mwsPartId, $stepNo, $request->validated('description'));

        return response()->json(['success' => true, 'subStep' => $subStep]);
    }

    public function updateSubStep(MwsSubStepRequest $request, string|int $mwsPartId, int $stepNo, int $subStepId)
    {
        $subStep = $this->workflow->updateSubStep($mwsPartId, $stepNo, $subStepId, $request->validated('description'));

        return response()->json(['success' => true, 'subStep' => $subStep]);
    }

    public function destroySubStep(string|int $mwsPartId, int $stepNo, int $subStepId)
    {
        $this->workflow->destroySubStep($mwsPartId, $stepNo, $subStepId);

        return response()->json(['success' => true]);
    }
}
