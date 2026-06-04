<?php

namespace Database\Seeders;

use App\Models\MwsPart;
use App\Models\MwsStep;
use App\Models\Project;
use App\Services\MwsTemplateServices;
use Illuminate\Database\Seeder;

class MwsPartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::with('dockPhases.taskGroups.tasks')->get();

        if ($projects->isEmpty()) {
            $this->command->warn('No projects found. Please run DashboardTestSeeder first.');
            return;
        }

        $templates = MwsTemplateServices::getTemplates();
        $partIndex = 1;

        foreach ($projects as $project) {
            // Find some tasks from this project to associate with MWS
            $tasks = [];
            foreach ($project->dockPhases as $phase) {
                foreach ($phase->taskGroups as $group) {
                    foreach ($group->tasks as $task) {
                        $tasks[] = $task;
                    }
                }
            }

            // Pick up to 3 tasks per project to attach MWS parts
            $selectedTasks = array_slice($tasks, 0, min(3, count($tasks)));

            foreach ($selectedTasks as $index => $task) {
                $jobTypes = ['Repair', 'Overhaul', 'F.Test'];
                $jobType = $jobTypes[$index % count($jobTypes)];

                $shopAreas = ['FO', 'FAB', 'MECH'];
                $shopArea = $shopAreas[$index % count($shopAreas)];

                // If it is the first MWS part of a project, let's mark it as approved
                // and seed completed steps. We make 50% of MWS parts completed examples.
                $isCompletedExample = ($partIndex % 2 === 1);
                $status = $isCompletedExample ? 'completed' : 'pending';

                $partId = 'MWS-' . str_pad($partIndex, 3, '0', STR_PAD_LEFT);
                $iwoNo = '2606-' . str_pad($partIndex, 5, '0', STR_PAD_LEFT);

                $mwsPart = MwsPart::create([
                    'part_id' => $partId,
                    'iwo_no' => $iwoNo,
                    'title' => 'MWS for ' . $task->name,
                    'part_number' => 'PN-' . rand(100000, 999999),
                    'serial_number' => 'SN-' . rand(10000, 99999),
                    'job_type' => $jobType,
                    'customer_name' => $project->customer,
                    'status' => $status,
                    'start_date' => now()->subDays(rand(5, 10)),
                    'current_step' => $isCompletedExample ? 10 : 1,
                    'task_id' => $task->id,
                    'ref_logistic_ppc' => 'REF-' . rand(100, 999),
                    'wbs_no' => 'WBS-' . rand(1000, 9999),
                    'shop_area' => $shopArea,
                    'remark_mws' => 'Seeded MWS record for task association.',
                    'ac_type' => $project->aircraft_type,
                    'worksheet_no' => 'WS-' . rand(100, 999),
                    'revision' => '1',
                    'zone' => 'Zone ' . chr(65 + $index),
                    // If it is our completed example, let's also sign the MWS Part itself
                    'prepared_by' => $isCompletedExample ? 'Admin' : null,
                    'prepared_date' => $isCompletedExample ? now()->subDays(4) : null,
                    'approved_by' => $isCompletedExample ? 'Superadmin' : null,
                    'approved_date' => $isCompletedExample ? now()->subDays(4) : null,
                    'verified_by' => $isCompletedExample ? 'Quality 1' : null,
                    'verified_at' => $isCompletedExample ? now()->subDays(4) : null,
                ]);

                // Create steps for this MWS Part
                $steps = $templates[$jobType] ?? $templates['Repair'];
                foreach ($steps as $stepIndex => $desc) {
                    $stepStatus = 'pending';
                    $man = null;
                    $hours = null;
                    $tech = null;
                    $insp = null;
                    $completedBy = null;
                    $completedDate = null;
                    $attachments = [];

                    if ($isCompletedExample) {
                        $stepStatus = 'completed';
                        $man = ['M001'];
                        $hours = sprintf('%02d:%02d', rand(1, 4), rand(0, 5) * 10);
                        $tech = 'Approved';
                        $insp = 'Approved';
                        $completedBy = 'M001';
                        $completedDate = now()->subDays(rand(1, 3));

                        if ($stepIndex === 0) {
                            $attachments = [[
                                'file_url' => asset('logo-di.png'),
                                'original_filename' => 'diagram_schematics.png',
                                'public_id' => 'seeded_diagram'
                            ]];
                        }
                    }

                    $step = MwsStep::create([
                        'mws_part_id' => $mwsPart->id,
                        'no' => $stepIndex + 1,
                        'description' => $desc,
                        'status' => $stepStatus,
                        'man' => $man,
                        'hours' => $hours,
                        'tech' => $tech,
                        'insp' => $insp,
                        'completedBy' => $completedBy,
                        'completed_date' => $completedDate,
                        'details' => $isCompletedExample && $stepIndex === 0 ? ['Verify safety pins are in place.', 'Check hydraulic reservoir levels.'] : [],
                        'attachments' => $attachments,
                    ]);

                    if ($isCompletedExample && $stepIndex === 1) {
                        \App\Models\MwsSubstep::create([
                            'mws_step_id' => $step->id,
                            'label' => 'a',
                            'description' => 'De-pressurize the hydraulic accumulator.',
                            'order' => 1
                        ]);
                        \App\Models\MwsSubstep::create([
                            'mws_step_id' => $step->id,
                            'label' => 'b',
                            'description' => 'Disconnect return line fittings.',
                            'order' => 2
                        ]);
                    }
                }

                $partIndex++;
            }
        }

        $this->command->info("Successfully seeded " . ($partIndex - 1) . " MWS parts with their template steps (including completed/signed step examples).");
    }
}
