<?php

namespace Tests\Feature;

use App\Models\DockPhase;
use App\Models\MwsPart;
use App\Models\MwsStep;
use App\Models\MwsConsumable;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectExportTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectFixture(): Project
    {
        $project = Project::create([
            'customer' => 'PT Dirgantara Indonesia',
            'contract_no' => 'CN-001',
            'aircraft_reg' => 'PK-DIR',
            'aircraft_type' => 'CN235-110',
            'description' => 'Test project',
            'progress' => 0.45,
            'start_date' => now(),
            'finish_date' => now()->addDays(30),
            'work_days' => 22,
        ]);

        $dockPhase = DockPhase::create([
            'project_id' => $project->id,
            'type' => 'indock',
            'no' => 'B',
            'name' => 'In Dock Phase',
            'progress' => 0.50,
            'allocation' => 60,
            'start_date' => now(),
            'finish_date' => now()->addDays(20),
            'work_days' => 15,
        ]);

        $taskGroup = TaskGroup::create([
            'dock_phase_id' => $dockPhase->id,
            'no' => '1.1',
            'name' => 'Structure Inspection Group',
            'progress' => 0.60,
            'allocation' => 40,
            'start_date' => now(),
            'finish_date' => now()->addDays(10),
            'work_days' => 8,
        ]);

        $task = Task::create([
            'task_group_id' => $taskGroup->id,
            'no' => '1.1.1',
            'name' => 'Wing Main Spar Check',
            'progress' => 0.80,
            'allocation' => 30,
            'start_date' => now(),
            'finish_date' => now()->addDays(5),
            'work_days' => 4,
        ]);

        $mwsPart = MwsPart::create([
            'task_id' => $task->id,
            'part_id' => 'MWS-999',
            'title' => 'Wing Main Spar Worksheet',
            'part_number' => 'PN-WING-999',
            'serial_number' => 'SN-WING-999',
            'job_type' => 'Repair',
            'wbs_no' => 'WBS-WING-999',
            'worksheet_no' => 'WS-WING-999',
            'status' => 'in_progress',
            'progress' => 0.50,
            'start_date' => now(),
            'finish_date' => now()->addDays(3),
            'ecd_finish_workdays' => 3,
            'man_hours' => '12:30',
            'men_powers' => 2,
        ]);

        // Add a step
        $step = MwsStep::create([
            'mws_part_id' => $mwsPart->id,
            'no' => 1,
            'description' => 'Visual Inspection Wing Spar',
            'caution' => 'Wear safety harness',
            'note' => 'Use inspection light',
            'details' => ['Check for hairline cracks', 'Inspect fasteners'],
            'plan_man' => 2,
            'plan_hours' => '02:00',
            'man' => ['NIK001', 'NIK002'],
            'hours' => '01:30',
            'tech' => 'Approved',
            'status' => 'completed',
        ]);

        // Add step substep
        $step->subSteps()->create([
            'label' => 'a',
            'description' => 'Inspect upper surface',
            'order' => 1,
        ]);

        // Add a consumable
        MwsConsumable::create([
            'mws_part_id' => $mwsPart->id,
            'name' => 'Alodine 1200',
            'identification' => 'REF-ALODINE',
            'quantity' => '1 Can',
            'order' => 1,
        ]);

        return $project;
    }

    public function test_guest_is_redirected_to_login_when_exporting_project_to_excel(): void
    {
        $project = $this->createProjectFixture();

        $response = $this->get(route('projects.export-excel', $project));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_export_project_to_excel(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);
        $project = $this->createProjectFixture();

        $response = $this->actingAs($user)->get(route('projects.export-excel', $project));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="Project_Export_' . str_replace(' ', '_', $project->customer) . '_' . date('Ymd_His') . '.xlsx"');
        
        // Assert the streamed content is not empty
        $this->assertNotEmpty($response->streamedContent());
    }
}
