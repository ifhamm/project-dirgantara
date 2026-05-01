<?php

namespace App\Http\Controllers;

use App\Models\MwsPart;
use App\Models\MwsStep;
use App\Models\Customer;
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

    public function updateStep(Request $request, $id)
    {
        $step = MwsStep::findOrFail($id);

        $field = $request->field;
        $value = $request->value;

        // whitelist
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
        $mwsPart = MwsPart::with(['customer', 'steps'])->findOrFail($id);

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
        $mwsPart->load(['customer', 'steps']);

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
}