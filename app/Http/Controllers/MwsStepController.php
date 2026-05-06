<?php

namespace App\Http\Controllers;

use App\Models\MwsStep;
use App\Services\MwsServices;
use Illuminate\Http\Request;

class MwsStepController extends Controller
{
    public function index($mws_part_id)
    {
        $steps = MwsStep::where('mws_part_id', $mws_part_id)
            ->orderBy('no')
            ->get();

        return response()->json($steps);
    }

    public function store(Request $request)
    {
        $step = MwsStep::create([
            'mws_part_id' => $request->mws_part_id,
            'no' => $request->no,
            'description' => $request->description,
            'details' => $request->details ?? [],
            'plan_man' => $request->plan_man,
            'plan_hours' => $request->plan_hours,
            'man' => $request->man ?? [],
            'hours' => $request->hours,
            'tech' => $request->tech,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Step berhasil dibuat',
            'data' => $step
        ]);
    }

    public function update(Request $request, $id)
    {
        $step = MwsStep::findOrFail($id);

        $step->update([
            'description' => $request->description,
            'details' => $request->details,
            'plan_man' => $request->plan_man,
            'plan_hours' => $request->plan_hours,
            'man' => $request->man,
            'hours' => $request->hours,
            'tech' => $request->tech,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Step berhasil diupdate',
            'data' => $step
        ]);
    }

    public function destroy($id)
    {
        MwsStep::destroy($id);

        return response()->json([
            'message' => 'Step berhasil dihapus'
        ]);
    }

    public function complete($id)
    {
        $step = MwsStep::findOrFail($id);

        $step->update([
            'status' => 'completed',
            'completed_date' => now()
        ]);

        $mwsPart = $step->Part;

        MwsServices::updateStatus($mwsPart);
        MwsServices::calculateMenPowers($mwsPart);
        MwsServices::updateSchedule($mwsPart);
        MwsServices::updateDuration($mwsPart);

        return response()->json([
            'message' => 'Step completed'
        ]);
    }

    public function addMechanic(Request $request, $id)
    {
        $step = MwsStep::findOrFail($id);

        $man = $step->man ?? [];

        if (!in_array($request->nik, $man)) {
            $man[] = $request->nik;
        }

        $step->update([
            'man' => $man
        ]);

        return response()->json([
            'message' => 'Mechanic ditambahkan',
            'data' => $step
        ]);
    }

    public function addAttachment(Request $request, $id)
    {
        $step = MwsStep::findOrFail($id);

        $attachments = $step->attachments ?? [];

        $attachments[] = $request->attachment;

        $step->update([
            'attachments' => $attachments
        ]);

        return response()->json([
            'message' => 'Attachment ditambahkan',
            'data' => $step
        ]);
    }
}