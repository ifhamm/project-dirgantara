<?php

namespace App\Services;

use App\Models\DockPhase;
use App\Models\TaskGroup;

class TaskGroupService
{
    public function store(DockPhase $dockPhase, array $data): TaskGroup
    {
        return $dockPhase->taskGroups()->create([
            'no'          => $data['no'] ?? null,
            'name'        => $data['name'],
            'start_date'  => $data['start_date'] ?? null,
            'finish_date' => $data['finish_date'] ?? null,
            'work_days'   => $data['work_days'] ?? null,
            'progress'    => 0,
            'allocation'  => 0,
        ]);
    }

    public function update(TaskGroup $taskGroup, array $data): TaskGroup
    {
        $taskGroup->update([
            'no'          => $data['no'] ?? null,
            'name'        => $data['name'],
            'start_date'  => $data['start_date'] ?? null,
            'finish_date' => $data['finish_date'] ?? null,
            'work_days'   => $data['work_days'] ?? null,
        ]);

        return $taskGroup->fresh();
    }

    public function destroy(TaskGroup $taskGroup): void
    {
        $taskGroup->delete();
    }
}