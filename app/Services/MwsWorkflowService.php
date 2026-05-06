<?php

namespace App\Services;

use App\Models\MwsConsumable;
use App\Models\MwsPart;
use App\Models\MwsStep;
use App\Models\MwsSubstep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MwsWorkflowService
{
    private function step(string|int $mwsPartId, int $stepNo): MwsStep
    {
        return MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();
    }

    private function reorderSteps(string|int $mwsPartId): void
    {
        $steps = MwsStep::where('mws_part_id', $mwsPartId)->orderBy('no')->get();

        foreach ($steps as $index => $step) {
            $step->update(['no' => $index + 1]);
        }
    }

    public function storeStep(string|int $mwsPartId, string $description): MwsStep
    {
        $lastStep = MwsStep::where('mws_part_id', $mwsPartId)->max('no') ?? 0;

        return MwsStep::create([
            'mws_part_id' => $mwsPartId,
            'no' => $lastStep + 1,
            'description' => $description,
            'status' => 'pending',
            'details' => [],
            'man' => [],
        ]);
    }

    public function insertStepAfter(string|int $mwsPartId, int $stepNo, string $description): MwsStep
    {
        MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', '>', $stepNo)
            ->increment('no');

        return MwsStep::create([
            'mws_part_id' => $mwsPartId,
            'no' => $stepNo + 1,
            'description' => $description,
            'status' => 'pending',
            'details' => [],
            'man' => [],
        ]);
    }

    public function destroyStep(string|int $mwsPartId, int $stepNo): void
    {
        $this->step($mwsPartId, $stepNo)->delete();
        $this->reorderSteps($mwsPartId);
    }

    public function bulkDeleteSteps(string|int $mwsPartId, array $stepNos): void
    {
        MwsStep::where('mws_part_id', $mwsPartId)
            ->whereIn('no', $stepNos)
            ->delete();

        $this->reorderSteps($mwsPartId);
    }

    public function storeDetail(string|int $mwsPartId, int $stepNo, string $detail): array
    {
        $step = $this->step($mwsPartId, $stepNo);
        $details = $step->details ?? [];
        $details[] = $detail;
        $step->update(['details' => $details]);

        return $details;
    }

    public function updateDetail(string|int $mwsPartId, int $stepNo, int $detailIndex, string $detail): void
    {
        $step = $this->step($mwsPartId, $stepNo);
        $details = $step->details ?? [];

        if (isset($details[$detailIndex])) {
            $details[$detailIndex] = $detail;
        }

        $step->update(['details' => $details]);
    }

    public function destroyDetail(string|int $mwsPartId, int $stepNo, int $detailIndex): void
    {
        $step = $this->step($mwsPartId, $stepNo);
        $details = $step->details ?? [];

        if (isset($details[$detailIndex])) {
            unset($details[$detailIndex]);
            $details = array_values($details);
        }

        $step->update(['details' => $details]);
    }

    public function signOn(string|int $mwsPartId, int $stepNo, User $user): void
    {
        $step = $this->step($mwsPartId, $stepNo);
        $man = $step->man ?? [];

        if (!in_array($user->nik, $man)) {
            $man[] = $user->nik;
            $step->update(['man' => $man]);
        }
    }

    public function assignMechanic(string|int $mwsPartId, int $stepNo, string $nik): void
    {
        $step = $this->step($mwsPartId, $stepNo);
        $mechanic = User::where('nik', $nik)->where('role', 'mechanic')->firstOrFail();

        $man = $step->man ?? [];
        if (!in_array($mechanic->nik, $man)) {
            $man[] = $mechanic->nik;
            $step->update(['man' => $man]);
        }
    }

    public function removeMechanic(string|int $mwsPartId, int $stepNo, string $nik): void
    {
        $step = $this->step($mwsPartId, $stepNo);
        $man = array_values(array_diff($step->man ?? [], [$nik]));
        $step->update(['man' => $man]);
    }

    public function startTimer(string|int $mwsPartId, int $stepNo): void
    {
        $this->step($mwsPartId, $stepNo)->update([
            'timer_start_time' => now(),
            'status' => 'in_progress',
        ]);
    }

    public function stopTimer(string|int $mwsPartId, int $stepNo): string
    {
        $step = $this->step($mwsPartId, $stepNo);

        if (!$step->timer_start_time) {
            throw new RuntimeException('Timer belum dimulai');
        }

        $start = \Carbon\Carbon::parse($step->timer_start_time);
        $elapsedMinutes = $start->diffInMinutes(now());

        $currentHours = $step->hours ?? '00:00';
        [$currentH, $currentM] = explode(':', $currentHours);
        $currentTotalMinutes = ((int) $currentH * 60) + (int) $currentM;

        $totalMinutes = $currentTotalMinutes + $elapsedMinutes;
        $newHours = sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);

        $step->update([
            'hours' => $newHours,
            'timer_start_time' => null,
        ]);

        return $newHours;
    }

    public function approveStep(string|int $mwsPartId, int $stepNo): void
    {
        $this->step($mwsPartId, $stepNo)->update(['tech' => 'Approved']);
    }

    public function unapproveStep(string|int $mwsPartId, int $stepNo): void
    {
        $this->step($mwsPartId, $stepNo)->update(['tech' => null]);
    }

    public function finishStep(string|int $mwsPartId, int $stepNo): void
    {
        $this->step($mwsPartId, $stepNo)->update([
            'status' => 'completed',
            'insp' => 'Approved',
        ]);
    }

    public function unfinishStep(string|int $mwsPartId, int $stepNo): void
    {
        $this->step($mwsPartId, $stepNo)->update([
            'status' => 'in_progress',
            'insp' => null,
        ]);
    }

    public function finishFinalInspection(string|int $mwsPartId, int $stepNo, string $statusSus): void
    {
        $this->step($mwsPartId, $stepNo)->update([
            'status' => 'completed',
            'insp' => 'Approved',
            'status_s_us' => $statusSus,
        ]);
    }

    public function storeConsumable(string|int $mwsPartId, array $data): MwsConsumable
    {
        $lastOrder = MwsConsumable::where('mws_part_id', $mwsPartId)->max('order') ?? 0;

        return MwsConsumable::create([
            'mws_part_id' => $mwsPartId,
            'name' => $data['name'],
            'identification' => $data['identification'] ?? null,
            'quantity' => $data['quantity'] ?? 'AR',
            'order' => $lastOrder + 1,
        ]);
    }

    public function updateConsumable(string|int $mwsPartId, int $consumableId, array $data): MwsConsumable
    {
        $consumable = MwsConsumable::where('mws_part_id', $mwsPartId)->findOrFail($consumableId);
        $consumable->update($data);

        return $consumable;
    }

    public function destroyConsumable(string|int $mwsPartId, int $consumableId): void
    {
        MwsConsumable::where('mws_part_id', $mwsPartId)
            ->findOrFail($consumableId)
            ->delete();
    }

    public function updateStepCaution(string|int $mwsPartId, int $stepNo, ?string $caution, ?string $note): void
    {
        $this->step($mwsPartId, $stepNo)->update([
            'caution' => $caution,
            'note' => $note,
        ]);
    }

    public function storeSubStep(string|int $mwsPartId, int $stepNo, string $description): MwsSubstep
    {
        $step = $this->step($mwsPartId, $stepNo);
        $lastOrder = MwsSubstep::where('mws_step_id', $step->id)->max('order') ?? 0;
        $label = $this->generateSubStepLabel($step->id);

        return MwsSubstep::create([
            'mws_step_id' => $step->id,
            'label' => $label,
            'description' => $description,
            'order' => $lastOrder + 1,
        ]);
    }

    public function updateSubStep(string|int $mwsPartId, int $stepNo, int $subStepId, string $description): MwsSubstep
    {
        $step = $this->step($mwsPartId, $stepNo);
        $subStep = MwsSubstep::where('mws_step_id', $step->id)->findOrFail($subStepId);
        $subStep->update(['description' => $description]);

        return $subStep;
    }

    public function destroySubStep(string|int $mwsPartId, int $stepNo, int $subStepId): void
    {
        $step = $this->step($mwsPartId, $stepNo);

        MwsSubstep::where('mws_step_id', $step->id)
            ->where('id', $subStepId)
            ->delete();

        MwsSubstep::where('mws_step_id', $step->id)
            ->orderBy('order')
            ->get()
            ->each(function ($sub, $index) {
                $sub->update(['label' => chr(97 + $index)]);
            });
    }

    public function duplicate(MwsPart|int $original): MwsPart
    {
        $original = $original instanceof MwsPart
            ? $original
            : MwsPart::with('steps.subSteps')->findOrFail($original);

        return DB::transaction(function () use ($original) {
            $new = $original->replicate();
            $new->part_id = 'MWS-' . str_pad(MwsPart::max('id') + 1, 3, '0', STR_PAD_LEFT);
            $new->status = 'pending';
            $new->preparedBy = null;
            $new->approvedBy = null;
            $new->verifiedBy = null;
            $new->start_date = now();
            $new->finish_date = null;
            $new->save();

            foreach ($original->steps as $step) {
                $newStep = $step->replicate();
                $newStep->mws_part_id = $new->id;
                $newStep->status = 'pending';
                $newStep->tech = null;
                $newStep->insp = null;
                $newStep->hours = 0;
                $newStep->timer_start_time = null;
                $newStep->save();

                foreach ($step->subSteps as $sub) {
                    $newSub = $sub->replicate();
                    $newSub->mws_step_id = $newStep->id;
                    $newSub->save();
                }
            }

            return $new;
        });
    }

    private function generateSubStepLabel(int $stepId): string
    {
        $count = MwsSubstep::where('mws_step_id', $stepId)->count();
        return chr(97 + $count);
    }
}