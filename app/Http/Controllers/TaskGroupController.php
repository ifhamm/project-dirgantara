<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskGroup\StoreTaskGroupRequest;
use App\Http\Requests\TaskGroup\UpdateTaskGroupRequest;
use App\Models\DockPhase;
use App\Models\TaskGroup;
use App\Services\TaskGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskGroupController extends Controller
{
    public function __construct(
        private readonly TaskGroupService $taskGroupService
    ) {}

    public function create(DockPhase $dockPhase): View
    {
        return view('task_groups.create', compact('dockPhase'));
    }

    public function store(StoreTaskGroupRequest $request, DockPhase $dockPhase): RedirectResponse
    {
        $this->taskGroupService->store($dockPhase, $request->validated());

        return redirect()
            ->route('projects.show', $dockPhase->project_id)
            ->with('success', 'Task Group berhasil ditambahkan.');
    }

    public function edit(DockPhase $dockPhase, TaskGroup $taskGroup): View
    {
        return view('task_groups.edit', compact('dockPhase', 'taskGroup'));
    }

    public function update(UpdateTaskGroupRequest $request, DockPhase $dockPhase, TaskGroup $taskGroup): RedirectResponse
    {
        $this->taskGroupService->update($taskGroup, $request->validated());

        return redirect()
            ->route('projects.show', $dockPhase->project_id)
            ->with('success', 'Task Group berhasil diperbarui.');
    }

    public function destroy(DockPhase $dockPhase, TaskGroup $taskGroup): RedirectResponse
    {
        $this->taskGroupService->destroy($taskGroup);

        return redirect()
            ->route('projects.show', $dockPhase->project_id)
            ->with('success', 'Task Group berhasil dihapus.');
    }
}