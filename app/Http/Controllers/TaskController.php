<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function create(TaskGroup $taskGroup): View
    {
        $taskGroup->load('dockPhase.project');

        return view('tasks.create', compact('taskGroup'));
    }

    public function store(StoreTaskRequest $request, TaskGroup $taskGroup): RedirectResponse
    {
        $this->taskService->store($taskGroup, $request->validated());

        return redirect()
            ->route('projects.show', $taskGroup->dockPhase->project_id)
            ->with('success', 'Task berhasil ditambahkan.');
    }

    public function edit(TaskGroup $taskGroup, Task $task): View
    {
        $taskGroup->load('dockPhase.project');

        return view('tasks.edit', compact('taskGroup', 'task'));
    }

    public function update(UpdateTaskRequest $request, TaskGroup $taskGroup, Task $task): RedirectResponse
    {
        $this->taskService->update($task, $request->validated());

        return redirect()
            ->route('projects.show', $taskGroup->dockPhase->project_id)
            ->with('success', 'Task berhasil diperbarui.');
    }

    public function destroy(TaskGroup $taskGroup, Task $task): RedirectResponse
    {
        $projectId = $taskGroup->dockPhase->project_id;

        $this->taskService->destroy($task);

        return redirect()
            ->route('projects.show', $projectId)
            ->with('success', 'Task berhasil dihapus.');
    }
}