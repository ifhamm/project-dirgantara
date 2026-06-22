<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use App\Services\Export\ProjectExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService
    ) {}

    public function index(): View
    {
        $projects = $this->projectService->index();

        return view('project.index', compact('projects'));
    }

    public function create(): View
    {
        return view('project.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $this->projectService->store($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "Project \"{$project->aircraft_reg}\" berhasil dibuat.");
    }

    public function show(Project $project): View
    {
        $project = $this->projectService->show($project);

        return view('project.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $project->load('dockPhases');

        return view('project.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->update($project, $request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project berhasil diperbarui.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $reg = $project->aircraft_reg;

        $this->projectService->destroy($project);

        return redirect()
            ->route('projects.index')
            ->with('success', "Project \"{$reg}\" berhasil dihapus.");
    }

    public function exportExcel(Project $project, ProjectExportService $exportService): StreamedResponse
    {
        $spreadsheet = $exportService->export($project);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Project_Export_' . str_replace(' ', '_', $project->customer) . '_' . date('Ymd_His') . '.xlsx';

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}