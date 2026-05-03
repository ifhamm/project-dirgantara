<?php

namespace App\Http\Controllers;

use App\Models\MwsPart;
use App\Models\MwsStep;
use App\Models\Customer;
use App\Models\MwsConsumable;
use App\Models\MwsSubstep;
use App\Services\MwsTemplateServices;
use App\Services\IwoNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MwsPartController extends Controller
{
    public function index()
    {
        return response()->json(
            MwsPart::with('steps')->latest()->get()
        );
    }

    public function create()
    {
        return view('mws.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'job_type' => 'required|string|max:100',
            ]);

            $last = MwsPart::lockForUpdate()->orderBy('part_id', 'desc')->first();

            $next = 1;
            if ($last && preg_match('/(\d+)$/', $last->part_id, $match)) {
                $next = (int) $match[1] + 1;
            }

            $partId = 'MWS-' . str_pad($next, 3, '0', STR_PAD_LEFT);

            $customer = Customer::where('company_name', $request->customer_name)->first();

            $status = $request->shop_area === 'FO' ? 'Form Out' : 'pending';

            $now = now()->timezone('Asia/Jakarta');

            $iwoNo = IwoNumberService::generate();

            $mws = MwsPart::create([
                'part_id' => $partId,
                'iwo_no' => $iwoNo,
                'title' => $request->title,
                'part_number' => $request->part_number,
                'serial_number' => $request->serial_number,
                'job_type' => $request->job_type,
                'customer_id' => $customer?->id,
                'status' => $status,
                'start_date' => $now,
                'current_step' => 1,
                'indock_task_id' => $request->indock_task_id,
            ]);

            $templates = MwsTemplateServices::getTemplates();
            $steps = $templates[$mws->job_type] ?? null;

            if (!$steps) {
                foreach ($templates as $key => $value) {
                    if (str_starts_with($mws->job_type, $key)) {
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
                    'mws_part_id' => $mws->id,
                    'no' => $index + 1,
                    'description' => $desc,
                    'status' => 'pending',
                    'details' => [],
                    'man' => [],
                ]);
            }

            DB::commit();

            return redirect()->route('mws.show', $mws->id)
                ->with('success', 'MWS berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Gagal membuat MWS',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateSteps($id)
    {
        $mws = MwsPart::findOrFail($id);

        $templates = MwsTemplateServices::getTemplates();

        $jobType = $mws->job_type;

        if (!isset($templates[$jobType])) {
            return back()->with('error', 'Template tidak ditemukan');
        }

        MwsStep::where('mws_part_id', $mws->id)->delete();

        foreach ($templates[$jobType] as $index => $desc) {
            MwsStep::create([
                'mws_part_id' => $mws->id,
                'no' => $index + 1,
                'description' => $desc,
                'status' => 'pending'
            ]);
        }

        return back()->with('success', 'Steps berhasil dibuat');
    }

    public function updateStep(Request $request, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $request->mws_part_id)
            ->where('no', $stepNo)
            ->firstOrFail();

        $field = $request->field;
        $value = $request->value;

        if (!in_array($field, ['description', 'plan_man', 'plan_hours'])) {
            return response()->json(['error' => 'Invalid field'], 400);
        }

        $step->$field = $value;
        $step->save();

        return response()->json([
            'success' => true,
            'value' => $value
        ]);
    }

    public function show($id)
    {
        $mwsPart = MwsPart::with([
            'customer',
            'steps.subSteps',
            'consumables',
        ])->findOrFail($id);

        $availableMechanics = \App\Models\User::where('role', 'mechanic')
            ->select('nik', 'name')
            ->get();

        return view('mws.show', compact('mwsPart', 'availableMechanics'));
    }

    public function update(Request $request, $id)
    {
        $mws = MwsPart::findOrFail($id);

        $mws->update([
            'title' => $request->title,
            'part_number' => $request->part_number,
            'serial_number' => $request->serial_number,
            'job_type' => $request->job_type,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'MWS berhasil diupdate',
            'data' => $mws
        ]);
    }

    public function destroy($id)
    {
        MwsPart::destroy($id);

        return response()->json([
            'message' => 'MWS berhasil dihapus'
        ]);
    }

    public function print(MwsPart $mwsPart)
    {
        $mwsPart->load(['customer', 'steps.SubSteps', 'consumables']);

        return view('mws.print', compact('mwsPart'));
    }

    public function sign(Request $request, MwsPart $mwsPart)
    {
        $type = $request->input('type');
        $user = auth()->user();

        $allowed = [
            'prepared' => ['admin', 'superadmin'],
            'approved' => ['admin', 'superadmin'],
            'verified' => ['quality2'],
        ];

        if (!isset($allowed[$type]) || !in_array($user->role, $allowed[$type])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $field = $type . 'By';      // preparedBy, approvedBy, verifiedBy
        $dateField = $type . 'At';  // preparedAt, approvedAt, verifiedAt

        $mwsPart->update([
            $field => $user->name,
            $dateField => now(),
        ]);

        return response()->json(['message' => ucfirst($type) . ' berhasil di-sign!']);
    }

    public function cancelSign(Request $request, MwsPart $mwsPart)
    {
        $type = $request->input('type');

        if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $field = $type . 'By';
        $dateField = $type . 'At';

        $mwsPart->update([
            $field => null,
            $dateField => null,
        ]);

        return response()->json(['message' => ucfirst($type) . ' signature dibatalkan.']);
    }

    public function updateDates(Request $request, MwsPart $mwsPart)
    {
        if (auth()->user()->role !== 'superadmin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = [];
        if ($request->has('start_date'))
            $data['start_date'] = $request->start_date;
        if ($request->has('finish_date'))
            $data['finish_date'] = $request->finish_date;

        $mwsPart->update($data);

        return response()->json(['message' => 'Tanggal berhasil diperbarui!']);
    }

    // ════════════════════════════════════════════════════════════
    // STEPS MANAGEMENT (untuk tombol +, insert, delete, bulk delete)
    // ════════════════════════════════════════════════════════════

    public function storeStep(Request $request, $mwsPartId)
    {
        $lastStep = MwsStep::where('mws_part_id', $mwsPartId)->max('no') ?? 0;

        $step = MwsStep::create([
            'mws_part_id' => $mwsPartId,
            'no' => $lastStep + 1,
            'description' => $request->description,
            'status' => 'pending',
            'details' => [],
            'man' => [],
        ]);

        return response()->json(['success' => true, 'step' => $step]);
    }

    public function insertStepAfter(Request $request, $mwsPartId, $stepNo)
    {
        MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', '>', $stepNo)
            ->increment('no');

        $newStep = MwsStep::create([
            'mws_part_id' => $mwsPartId,
            'no' => $stepNo + 1,
            'description' => $request->description,
            'status' => 'pending',
            'details' => [],
            'man' => [],
        ]);

        return response()->json(['success' => true, 'step' => $newStep]);
    }

    public function destroyStep($mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $step->delete();

        // Reorder
        $steps = MwsStep::where('mws_part_id', $mwsPartId)->orderBy('no')->get();
        foreach ($steps as $index => $s) {
            $s->update(['no' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function bulkDeleteSteps(Request $request, $mwsPartId)
    {
        $stepNos = $request->step_nos ?? [];

        MwsStep::where('mws_part_id', $mwsPartId)
            ->whereIn('no', $stepNos)
            ->delete();

        // Reorder
        $steps = MwsStep::where('mws_part_id', $mwsPartId)->orderBy('no')->get();
        foreach ($steps as $index => $s) {
            $s->update(['no' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════
    // DETAILS (catatan per step)
    // ════════════════════════════════════════════════════════════

    public function storeDetail(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $details = $step->details ?? [];
        $details[] = $request->detail;

        $step->update(['details' => $details]);

        return response()->json(['success' => true, 'details' => $details]);
    }

    public function updateDetail(Request $request, $mwsPartId, $stepNo, $detailIndex)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $details = $step->details ?? [];
        if (isset($details[$detailIndex])) {
            $details[$detailIndex] = $request->detail;
        }

        $step->update(['details' => $details]);

        return response()->json(['success' => true]);
    }

    public function destroyDetail($mwsPartId, $stepNo, $detailIndex)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $details = $step->details ?? [];
        if (isset($details[$detailIndex])) {
            unset($details[$detailIndex]);
            $details = array_values($details);
        }

        $step->update(['details' => $details]);

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════
    // MECHANIC (sign on, assign, remove)
    // ════════════════════════════════════════════════════════════

    public function signOn(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $user = auth()->user();
        $man = $step->man ?? [];

        if (!in_array($user->nik, $man)) {
            $man[] = $user->nik;
            $step->update(['man' => $man]);
        }

        return response()->json(['success' => true]);
    }

    public function assignMechanic(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $man = $step->man ?? [];
        if (!in_array($request->nik, $man)) {
            $man[] = $request->nik;
            $step->update(['man' => $man]);
        }

        return response()->json(['success' => true]);
    }

    public function removeMechanic(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $man = array_values(array_diff($step->man ?? [], [$request->nik]));
        $step->update(['man' => $man]);

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════
    // TIMER (start, stop)
    // ════════════════════════════════════════════════════════════

    public function startTimer(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $step->update([
            'timer_start_time' => now(),
            'status' => 'in_progress'
        ]);

        return response()->json(['success' => true]);
    }

    public function stopTimer(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        if (!$step->timer_start_time) {
            return response()->json([
                'success' => false,
                'message' => 'Timer belum dimulai'
            ], 400);
        }

        $start = \Carbon\Carbon::parse($step->timer_start_time);
        $elapsedMinutes = $start->diffInMinutes(now());

        // Parse current hours "HH:MM"
        $currentHours = $step->hours ?? '00:00';
        list($currentH, $currentM) = explode(':', $currentHours);
        $currentTotalMinutes = ((int) $currentH * 60) + (int) $currentM;

        // Add elapsed
        $totalMinutes = $currentTotalMinutes + $elapsedMinutes;
        $newH = floor($totalMinutes / 60);
        $newM = $totalMinutes % 60;
        $newHours = sprintf('%02d:%02d', $newH, $newM);

        $step->update([
            'hours' => $newHours,
            'timer_start_time' => null
        ]);

        return response()->json([
            'success' => true,
            'hours' => $newHours
        ]);
    }

    // ════════════════════════════════════════════════════════════
    // APPROVAL & FINISH (tech approve, insp finish, final inspection)
    // ════════════════════════════════════════════════════════════

    public function approveStep(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $step->update(['tech' => 'Approved']);

        return response()->json(['success' => true]);
    }

    public function unapproveStep(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $step->update(['tech' => null]);

        return response()->json(['success' => true]);
    }

    public function finishStep(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $step->update([
            'status' => 'completed',
            'insp' => 'Approved'
        ]);

        return response()->json(['success' => true]);
    }

    public function unfinishStep(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $step->update([
            'status' => 'in_progress',
            'insp' => null
        ]);

        return response()->json(['success' => true]);
    }

    public function finishFinalInspection(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $step->update([
            'status' => 'completed',
            'insp' => 'Approved',
            'status_s_us' => $request->status_s_us
        ]);

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════
    // ATTACHMENTS (placeholder - sesuaikan dengan storage Anda)
    // ════════════════════════════════════════════════════════════

    public function storeAttachment(Request $request, $mwsPartId)
    {
        // TODO: Implement file upload
        return response()->json(['success' => true]);
    }

    public function destroyAttachment($mwsPartId, $publicId)
    {
        // TODO: Implement file delete
        return response()->json(['success' => true]);
    }

    public function storeStepAttachment(Request $request, $mwsPartId, $stepNo)
    {
        // TODO: Implement file upload
        return response()->json(['success' => true]);
    }

    public function destroyStepAttachment($mwsPartId, $stepNo, $publicId)
    {
        // TODO: Implement file delete
        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════
    // DUPLICATE MWS
    // ════════════════════════════════════════════════════════════

    public function duplicate($mwsPartId)
    {
        $original = MwsPart::with('steps.subSteps')->findOrFail($mwsPartId);

        DB::beginTransaction();
        try {
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

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => route('mws.show', $new->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ════════════════════════════════════════════════════════════
    // CONSUMABLES
    // ════════════════════════════════════════════════════════════

    public function storeConsumable(Request $request, $mwsPartId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'identification' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:50',
        ]);

        $lastOrder = MwsConsumable::where('mws_part_id', $mwsPartId)->max('order') ?? 0;

        $consumable = MwsConsumable::create([
            'mws_part_id' => $mwsPartId,
            'name' => $request->name,
            'identification' => $request->identification,
            'quantity' => $request->quantity ?? 'AR',
            'order' => $lastOrder + 1,
        ]);

        return response()->json(['success' => true, 'consumable' => $consumable]);
    }
    public function updateConsumable(Request $request, $mwsPartId, $consumableId)
    {
        $consumable = MwsConsumable::where('mws_part_id', $mwsPartId)
            ->findOrFail($consumableId);

        $consumable->update($request->only(['name', 'identification', 'quantity']));

        return response()->json(['success' => true, 'consumable' => $consumable]);
    }

    public function destroyConsumable($mwsPartId, $consumableId)
    {
        MwsConsumable::where('mws_part_id', $mwsPartId)
            ->findOrFail($consumableId)
            ->delete();

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════
    // CAUTION & NOTE per STEP
    // ════════════════════════════════════════════════════════════
    public function updateStepCaution(Request $request, $mwsPartId, $stepNo)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $step->update([
            'caution' => $request->caution,
            'note' => $request->note,
        ]);

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════════════════
    // SUB-STEPS
    // ════════════════════════════════════════════════════════════

    public function storeSubStep(Request $request, $mwsPartId, $stepNo)
    {
        $request->validate([
            'description' => 'required|string',
        ]);

        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $lastOrder = MwsSubStep::where('mws_step_id', $step->id)->max('order') ?? 0;
        $label = $this->generateSubStepLabel($step->id);

        $subStep = MwsSubStep::create([
            'mws_step_id' => $step->id,
            'label' => $label,
            'description' => $request->description,
            'order' => $lastOrder + 1,
        ]);

        return response()->json(['success' => true, 'subStep' => $subStep]);
    }

    public function updateSubStep(Request $request, $mwsPartId, $stepNo, $subStepId)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        $subStep = MwsSubStep::where('mws_step_id', $step->id)
            ->findOrFail($subStepId);

        $subStep->update(['description' => $request->description]);

        return response()->json(['success' => true, 'subStep' => $subStep]);
    }

    public function destroySubStep($mwsPartId, $stepNo, $subStepId)
    {
        $step = MwsStep::where('mws_part_id', $mwsPartId)
            ->where('no', $stepNo)
            ->firstOrFail();

        MwsSubStep::where('mws_step_id', $step->id)
            ->findOrFail($subStepId)
            ->delete();

        // Re-label ulang: a, b, c, ...
        MwsSubStep::where('mws_step_id', $step->id)
            ->orderBy('order')
            ->get()
            ->each(function ($sub, $index) {
                $sub->update(['label' => chr(97 + $index)]);
            });

        return response()->json(['success' => true]);
    }

    // ── Helper ───────────────────────────────────────────────
    private function generateSubStepLabel(int $stepId): string
    {
        $count = MwsSubStep::where('mws_step_id', $stepId)->count();
        return chr(97 + $count); // 0→a, 1→b, dst.
    }
}