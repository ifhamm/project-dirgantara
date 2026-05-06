<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\ImportGanttRequest;
use App\Services\Import\GanttImportService;
use Illuminate\Http\RedirectResponse;

class GanttImportController extends Controller
{
    public function __construct(
        private readonly GanttImportService $importService
    ) {
    }

    public function store(ImportGanttRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        $path = $file->store('gantt_imports', 'local');

        $absolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            return back()->withErrors(['file' => 'File gagal diupload, coba lagi.']);
        }

        $project = $this->importService->import($absolutePath);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "Project \"{$project->aircraft_reg}\" berhasil diimport.");
    }
}