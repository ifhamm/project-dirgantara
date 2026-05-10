<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskGroup;

class TaskService
{
    public function store(TaskGroup $taskGroup, array $data): Task
    {
        return $taskGroup->tasks()->create([
            'no'          => $data['no'] ?? null,
            'name'        => $data['name'],
            'start_date'  => $data['start_date'] ?? null,
            'finish_date' => $data['finish_date'] ?? null,
            'work_days'   => $data['work_days'] ?? null,
            'progress'    => 0,
            'allocation'  => 0,
        ]);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update([
            'no'          => $data['no'] ?? null,
            'name'        => $data['name'],
            'start_date'  => $data['start_date'] ?? null,
            'finish_date' => $data['finish_date'] ?? null,
            'work_days'   => $data['work_days'] ?? null,
        ]);

        return $task->fresh();
    }

    public function destroy(Task $task): void
    {
        $task->delete();
    }
}