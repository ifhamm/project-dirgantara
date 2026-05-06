<?php

namespace App\Services;

use App\Models\DockPhase;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    // ─────────────────────────────────────────────────────────
    // index — list semua project, paginated
    // ─────────────────────────────────────────────────────────
    public function index(): LengthAwarePaginator
    {
        return Project::query()
            ->withCount([
                'dockPhases',
            ])
            ->with([
                'dockPhases' => fn($q) => $q->select('id', 'project_id', 'type', 'progress'),
            ])
            ->latest()
            ->paginate(15);
    }

    // ─────────────────────────────────────────────────────────
    // store — create project manual (tanpa import)
    // ─────────────────────────────────────────────────────────
    public function store(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create([
                'customer' => $data['customer'],
                'contract_no' => $data['contract_no'] ?? null,
                'aircraft_type' => $data['aircraft_type'],
                'aircraft_reg' => $data['aircraft_reg'],
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'finish_date' => $data['finish_date'] ?? null,
                'work_days' => $data['work_days'] ?? null,
                'progress' => 0,
            ]);

            // Buat 3 dock phase sesuai input
            $phaseTypes = ['predock', 'indock', 'postdock'];
            $phaseLabels = [
                'predock' => ['no' => 'A', 'name' => 'PRE DOCK'],
                'indock' => ['no' => 'B', 'name' => 'IN DOCK'],
                'postdock' => ['no' => 'C', 'name' => 'POST DOCK'],
            ];

            foreach ($phaseTypes as $type) {
                $phaseData = $data['phases'][$type] ?? [];

                DockPhase::create([
                    'project_id' => $project->id,
                    'type' => $type,
                    'no' => $phaseLabels[$type]['no'],
                    'name' => $phaseLabels[$type]['name'],
                    'start_date' => $phaseData['start_date'] ?? null,
                    'finish_date' => $phaseData['finish_date'] ?? null,
                    'work_days' => $phaseData['work_days'] ?? null,
                    'progress' => 0,
                    'allocation' => 0,
                ]);
            }

            return $project;
        });
    }

    // ─────────────────────────────────────────────────────────
    // show — load relasi lengkap untuk halaman detail
    // ─────────────────────────────────────────────────────────
    public function show(Project $project): Project
    {
        return $project->load([
            'dockPhases' => function ($q) {
                $q->orderByRaw("CASE type 
            WHEN 'predock'  THEN 1 
            WHEN 'indock'   THEN 2 
            WHEN 'postdock' THEN 3 
            ELSE 4 END");
            },
            'dockPhases.taskGroups' => fn($q) => $q->orderBy('no'),
            'dockPhases.taskGroups.tasks' => fn($q) => $q->orderBy('no'),
            'dockPhases.taskGroups.tasks.mwsParts',
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // update — update data project + dock phases
    // ─────────────────────────────────────────────────────────
    public function update(Project $project, array $data): Project
    {
        DB::transaction(function () use ($project, $data) {
            $project->update([
                'customer' => $data['customer'],
                'contract_no' => $data['contract_no'] ?? null,
                'aircraft_type' => $data['aircraft_type'],
                'aircraft_reg' => $data['aircraft_reg'],
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'finish_date' => $data['finish_date'] ?? null,
                'work_days' => $data['work_days'] ?? null,
            ]);

            // Update dock phase dates jika dikirim
            if (isset($data['phases'])) {
                foreach ($data['phases'] as $type => $phaseData) {
                    $project->dockPhases()
                        ->where('type', $type)
                        ->update([
                            'start_date' => $phaseData['start_date'] ?? null,
                            'finish_date' => $phaseData['finish_date'] ?? null,
                            'work_days' => $phaseData['work_days'] ?? null,
                        ]);
                }
            }
        });

        return $project->fresh();
    }

    public function destroy(Project $project): void
    {
        $project->delete();
    }
}