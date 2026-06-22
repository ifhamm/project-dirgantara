<?php

namespace App\Services;

use App\Models\DockPhase;
use App\Models\Project;
use App\Models\TaskGroup;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
                'aircraft_series' => $data['aircraft_series'] ?? null,
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

            // Default allocation percentages jika tidak diberikan
            $defaultAllocations = [
                'predock' => $data['phases']['predock']['allocation_percentage'] ?? 15,
                'indock' => $data['phases']['indock']['allocation_percentage'] ?? 70,
                'postdock' => $data['phases']['postdock']['allocation_percentage'] ?? 15,
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
                    'allocation_percentage' => $defaultAllocations[$type],
                ]);
            }

            // Auto-generate allocation untuk phases berdasarkan working days
            if ($project->work_days && $project->start_date && $project->finish_date) {
                $this->distributeAllocationPercentage($project);
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
                'aircraft_series' => $data['aircraft_series'] ?? null,
                'aircraft_reg' => $data['aircraft_reg'],
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'finish_date' => $data['finish_date'] ?? null,
                'work_days' => $data['work_days'] ?? null,
            ]);

            // Update dock phase dates & allocation percentages jika dikirim
            if (isset($data['phases'])) {
                foreach ($data['phases'] as $type => $phaseData) {
                    $project->dockPhases()
                        ->where('type', $type)
                        ->update([
                            'start_date' => $phaseData['start_date'] ?? null,
                            'finish_date' => $phaseData['finish_date'] ?? null,
                            'work_days' => $phaseData['work_days'] ?? null,
                            'allocation_percentage' => $phaseData['allocation_percentage'] ?? 0,
                        ]);
                }

                // Re-distribute allocations jika ada perubahan
                if ($project->work_days && $project->start_date && $project->finish_date) {
                    $this->distributeAllocationPercentage($project);
                }
            }
        });

        return $project->fresh();
    }

    // ─────────────────────────────────────────────────────────
    // destroy
    // ─────────────────────────────────────────────────────────
    public function destroy(Project $project): void
    {
        $project->delete();
    }

    // ─────────────────────────────────────────────────────────
    // Requirement #1 & #2: Distribute Allocation Percentage & Plan Dates
    // ─────────────────────────────────────────────────────────
    public function distributeAllocationPercentage(Project $project): void
    {
        DB::transaction(function () use ($project) {
            $project->load('dockPhases');

            // Ambil allocation percentages dari dock phases
            $phases = $project->dockPhases;
            $totalPercentage = $phases->sum('allocation_percentage');

            if ($totalPercentage <= 0) {
                return; // Skip jika tidak ada percentage
            }

            $startDate = Carbon::parse($project->start_date);
            $finishDate = Carbon::parse($project->finish_date);
            $totalDays = $startDate->diffInDays($finishDate);
            $totalWorkDays = $project->work_days ?? $totalDays;

            $currentDate = $startDate->clone();

            // Distribute dates & working days berdasarkan allocation percentage
            foreach ($phases as $phase) {
                $phasePercentage = $phase->allocation_percentage / $totalPercentage;
                $phaseWorkDays = round($totalWorkDays * $phasePercentage);
                $phaseFinishDate = $currentDate->clone()->addDays($phaseWorkDays);

                $phase->update([
                    'work_days' => $phaseWorkDays,
                    'start_date' => $currentDate->format('Y-m-d'),
                    'finish_date' => $phaseFinishDate->format('Y-m-d'),
                    'allocation' => $phasePercentage,
                ]);

                // Auto-generate task groups untuk in-dock saja
                if ($phase->type === 'indock' && $phase->taskGroups->count() === 0) {
                    $this->autoGenerateTaskStructure($phase);
                }

                $currentDate = $phaseFinishDate->clone()->addDay();
            }
        });
    }

    // ─────────────────────────────────────────────────────────
    // Requirement #5 & #6: Calculate Working Days & Generate Allocation
    // ─────────────────────────────────────────────────────────
    public function calculateWorkingDays(
        Carbon $startDate,
        Carbon $finishDate,
        array $excludedDates = []
    ): int {
        $count = 0;
        $current = $startDate->clone();

        while ($current->lte($finishDate)) {
            // Skip weekends (Sabtu & Minggu)
            if ($current->isWeekday() && !in_array($current->format('Y-m-d'), $excludedDates)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────
    // Requirement #7: Duplicate Project
    // ─────────────────────────────────────────────────────────
    public function duplicateProject(
        Project $sourceProject,
        array $overrides = []
    ): Project {
        return DB::transaction(function () use ($sourceProject, $overrides) {
            // Ambil data source project
            $sourceData = [
                'customer' => $overrides['customer'] ?? $sourceProject->customer,
                'contract_no' => $overrides['contract_no'] ?? $sourceProject->contract_no,
                'aircraft_type' => $overrides['aircraft_type'] ?? $sourceProject->aircraft_type,
                'aircraft_series' => $overrides['aircraft_series'] ?? $sourceProject->aircraft_series,
                'aircraft_reg' => $overrides['aircraft_reg'] ?? $sourceProject->aircraft_reg,
                'description' => $overrides['description'] ?? $sourceProject->description,
                'start_date' => $overrides['start_date'] ?? $sourceProject->start_date,
                'finish_date' => $overrides['finish_date'] ?? $sourceProject->finish_date,
                'work_days' => $overrides['work_days'] ?? $sourceProject->work_days,
                'progress' => 0,
            ];

            // Create new project
            $newProject = Project::create($sourceData);

            // Load source dock phases dengan relasi
            $sourceProject->load([
                'dockPhases.taskGroups.tasks.mwsParts'
            ]);

            // Duplicate dock phases, task groups, & tasks
            foreach ($sourceProject->dockPhases as $sourceDockPhase) {
                $newDockPhase = DockPhase::create([
                    'project_id' => $newProject->id,
                    'type' => $sourceDockPhase->type,
                    'no' => $sourceDockPhase->no,
                    'name' => $sourceDockPhase->name,
                    'progress' => 0,
                    'allocation' => $sourceDockPhase->allocation,
                    'allocation_percentage' => $sourceDockPhase->allocation_percentage,
                    'start_date' => $sourceDockPhase->start_date,
                    'finish_date' => $sourceDockPhase->finish_date,
                    'work_days' => $sourceDockPhase->work_days,
                ]);

                // Duplicate task groups
                foreach ($sourceDockPhase->taskGroups as $sourceTaskGroup) {
                    $newTaskGroup = TaskGroup::create([
                        'dock_phase_id' => $newDockPhase->id,
                        'no' => $sourceTaskGroup->no,
                        'name' => $sourceTaskGroup->name,
                        'progress' => 0,
                        'allocation' => $sourceTaskGroup->allocation,
                        'allocation_percentage' => $sourceTaskGroup->allocation_percentage,
                        'start_date' => $sourceTaskGroup->start_date,
                        'finish_date' => $sourceTaskGroup->finish_date,
                        'work_days' => $sourceTaskGroup->work_days,
                    ]);

                    // Duplicate tasks
                    foreach ($sourceTaskGroup->tasks as $sourceTask) {
                        Task::create([
                            'task_group_id' => $newTaskGroup->id,
                            'no' => $sourceTask->no,
                            'name' => $sourceTask->name,
                            'progress' => 0,
                            'allocation' => $sourceTask->allocation,
                            'allocation_percentage' => $sourceTask->allocation_percentage,
                            'start_date' => $sourceTask->start_date,
                            'finish_date' => $sourceTask->finish_date,
                            'work_days' => $sourceTask->work_days,
                        ]);

                        // Note: MWS parts tidak di-duplicate
                        // karena setiap MWS adalah unique dan tidak boleh duplikat
                    }
                }
            }

            return $newProject;
        });
    }
}