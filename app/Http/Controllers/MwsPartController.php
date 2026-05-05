<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mws\CancelSignMwsPartRequest;
use App\Http\Requests\Mws\SignMwsPartRequest;
use App\Http\Requests\Mws\StoreMwsPartRequest;
use App\Http\Requests\Mws\UpdateMwsDatesRequest;
use App\Http\Requests\Mws\UpdateMwsPartRequest;
use App\Http\Requests\Mws\UpdateMwsStepRequest;
use App\Models\MwsPart;
use App\Services\MwsPartService;
use Illuminate\Support\Facades\Auth;

class MwsPartController extends Controller
{
    public function __construct(
        private readonly MwsPartService $parts,
    ) {
    }

    public function index()
    {
        return response()->json($this->parts->index());
    }

    public function create()
    {
        return view('mws.create');
    }

    public function store(StoreMwsPartRequest $request)
    {
        $mwsPart = $this->parts->store($request->validated());

        return redirect()->route('mws.show', $mwsPart->id)
            ->with('success', 'MWS berhasil dibuat');
    }

    public function generateSteps(int $id)
    {
        if (!$this->parts->generateSteps($id)) {
            return back()->with('error', 'Template tidak ditemukan');
        }

        return back()->with('success', 'Steps berhasil dibuat');
    }

    public function updateStep(UpdateMwsStepRequest $request, int $stepNo)
    {
        $validated = $request->validated();
        $step = $this->parts->updateStep((int) $validated['mws_part_id'], $stepNo, $validated);

        return response()->json([
            'success' => true,
            'value' => $step->{$validated['field']},
        ]);
    }

    public function show(int $id)
    {
        return view('mws.show', $this->parts->show($id));
    }

    public function update(UpdateMwsPartRequest $request, int $id)
    {
        $mws = $this->parts->update($id, $request->validated());

        return response()->json([
            'message' => 'MWS berhasil diupdate',
            'data' => $mws,
        ]);
    }

    public function destroy(int $id)
    {
        $this->parts->delete($id);

        return response()->json([
            'message' => 'MWS berhasil dihapus',
        ]);
    }

    public function print(MwsPart $mwsPart)
    {
        $mwsPart = $this->parts->print($mwsPart);

        return view('mws.print', compact('mwsPart'));
    }

    public function sign(SignMwsPartRequest $request, MwsPart $mwsPart)
    {
        $user = Auth::user();
        $allowed = [
            'prepared' => ['admin', 'superadmin'],
            'approved' => ['admin', 'superadmin'],
            'verified' => ['quality2'],
        ];

        if (!$user || !isset($allowed[$request->type]) || !in_array($user->role, $allowed[$request->type], true)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->parts->sign($mwsPart, $request->type, $user->name);

        return response()->json(['message' => ucfirst($request->type) . ' berhasil di-sign!']);
    }

    public function cancelSign(CancelSignMwsPartRequest $request, MwsPart $mwsPart)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role, ['admin', 'superadmin'], true)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->parts->cancelSign($mwsPart, $request->type);

        return response()->json(['message' => ucfirst($request->type) . ' signature dibatalkan.']);
    }

    public function updateDates(UpdateMwsDatesRequest $request, MwsPart $mwsPart)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'superadmin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->parts->updateDates($mwsPart, $request->validated());

        return response()->json(['message' => 'Tanggal berhasil diperbarui!']);
    }
}
