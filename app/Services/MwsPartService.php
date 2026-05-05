<?php

namespace App\Services;

use App\Models\MwsPart;
use App\Models\MwsStep;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MwsPartService
{
    public function index(): Collection
    {
        return MwsPart::with('steps')->latest()->get();
    }

    public function store(array $data): MwsPart
    {
        return DB::transaction(function () use ($data) {
            $last = MwsPart::lockForUpdate()->orderByDesc('id')->first();

            $next = 1;
            if ($last && preg_match('/(\d+)$/', $last->part_id, $match)) {
                $next = (int) $match[1] + 1;
            }

            $partId = 'MWS-' . str_pad($next, 3, '0', STR_PAD_LEFT);
            // we store customer name directly; no FK lookup
            $status = ($data['shop_area'] ?? null) === 'FO' ? 'Form Out' : 'pending';

            $mws = MwsPart::create([
                'part_id' => $partId,
                'iwo_no' => IwoNumberService::generate(),
                'title' => $data['title'],
                'part_number' => $data['part_number'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
                'job_type' => $data['job_type'],
                'customer_name' => $data['customer_name'] ?? null,
                'status' => $status,
                'start_date' => now()->timezone('Asia/Jakarta'),
                'current_step' => 1,
                'indock_task_id' => $data['indock_task_id'] ?? null,
                // Additional fields from form
                'refLogisticPPC' => $data['ref_logistic_ppc'] ?? null,
                'wbsNO' => $data['wbs_no'] ?? null,
                'mdrDocDeffect' => $data['mdr_doc_defect'] ?? null,
                'capability' => $data['capability'] ?? null,
                'shopArea' => $data['shop_area'] ?? null,
                'remarkMWS' => $data['remark_mws'] ?? null,
                'testResult' => $data['test_result'] ?? null,
                'ref' => $data['ref'] ?? null,
                'acType' => $data['ac_type'] ?? null,
                'wroksheetNo' => $data['worksheet_no'] ?? null,
                'revision' => $data['revision'] ?? '1',
                'zone' => $data['zone'] ?? null,
            ]);

            $this->syncTemplateSteps($mws);

            return $mws;
        });
    }

    public function generateSteps(int $id): bool
    {
        $mws = MwsPart::findOrFail($id);

        $templates = MwsTemplateServices::getTemplates();
        $jobType = $mws->job_type;

        if (!isset($templates[$jobType])) {
            return false;
        }

        MwsStep::where('mws_part_id', $mws->id)->delete();

        foreach ($templates[$jobType] as $index => $desc) {
            MwsStep::create([
                'mws_part_id' => $mws->id,
                'no' => $index + 1,
                'description' => $desc,
                'status' => 'pending',
            ]);
        }

        return true;
    }

    public function updateStep(int $mwsPartId, int $stepNo, array $data): MwsStep
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $field = $data['field'];
        $value = $data['value'];

        if (!in_array($field, ['description', 'plan_man', 'plan_hours'], true)) {
            abort(400, 'Invalid field');
        }

        $step->update([$field => $value]);

        return $step->fresh();
    }

    public function show(int $id): array
    {
        $mwsPart = MwsPart::with([
            'steps.subSteps',
            'consumables',
        ])->findOrFail($id);

        $availableMechanics = User::query()
            ->where('role', 'mechanic')
            ->select('nik', 'name')
            ->orderBy('name')
            ->get();

        return compact('mwsPart', 'availableMechanics');
    }

    public function update(int $id, array $data): MwsPart
    {
        $mws = MwsPart::findOrFail($id);

        $mws->update([
            'title' => $data['title'] ?? $mws->title,
            'part_number' => $data['part_number'] ?? $mws->part_number,
            'serial_number' => $data['serial_number'] ?? $mws->serial_number,
            'job_type' => $data['job_type'] ?? $mws->job_type,
            'status' => $data['status'] ?? $mws->status,
            'ref' => $data['ref'] ?? $mws->ref,
            'acType' => $data['acType'] ?? $mws->acType,
            'wbsNO' => $data['wbsNO'] ?? $mws->wbsNO,
            'wroksheetNo' => $data['wroksheetNo'] ?? $mws->wroksheetNo,
            'shopArea' => $data['shopArea'] ?? $mws->shopArea,
            'revision' => $data['revision'] ?? $mws->revision,
            'zone' => $data['zone'] ?? $mws->zone,
            'start_date' => $data['start_date'] ?? $mws->start_date,
        ]);

        return $mws->fresh();
    }

    public function delete(int $id): void
    {
        MwsPart::destroy($id);
    }

    public function print(MwsPart $mwsPart): MwsPart
    {
        return $mwsPart->load(['steps.subSteps', 'consumables']);
    }

    public function sign(MwsPart $mwsPart, string $type, string $signedBy): void
    {
        $field = $type . 'By';
        $dateField = $type . 'At';

        $mwsPart->update([
            $field => $signedBy,
            $dateField => now(),
        ]);
    }

    public function cancelSign(MwsPart $mwsPart, string $type): void
    {
        $field = $type . 'By';
        $dateField = $type . 'At';

        $mwsPart->update([
            $field => null,
            $dateField => null,
        ]);
    }

    public function updateDates(MwsPart $mwsPart, array $data): void
    {
        $mwsPart->update(array_filter([
            'start_date' => $data['start_date'] ?? null,
            'finish_date' => $data['finish_date'] ?? null,
        ], static fn ($value) => $value !== null));
    }

    private function syncTemplateSteps(MwsPart $mwsPart): void
    {
        $templates = MwsTemplateServices::getTemplates();
        $steps = $templates[$mwsPart->job_type] ?? null;

        if (!$steps) {
            foreach ($templates as $key => $value) {
                if (str_starts_with($mwsPart->job_type, $key)) {
                    $steps = $value;
                    break;
                }
            }
        }

        if (!$steps) {
            $steps = $templates['Repair'] ?? [];
        }

        foreach ($steps as $index => $desc) {
            MwsStep::create([
                'mws_part_id' => $mwsPart->id,
                'no' => $index + 1,
                'description' => $desc,
                'status' => 'pending',
                'details' => [],
                'man' => [],
            ]);
        }
    }
}