<?php

namespace Tests\Feature;

use App\Models\DockPhase;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_project_create_page_and_store_a_project(): void
    {
        $admin = User::factory()->create([
            'name' => 'Project Admin',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('projects.create'))
            ->assertOk();

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'customer' => 'PT Dirgantara Indonesia',
            'aircraft_type' => 'A320',
            'aircraft_reg' => 'PK-DGN-001',
            'contract_no' => 'CN-2026-001',
            'description' => 'Routine maintenance project',
            'start_date' => '2026-06-02',
            'finish_date' => '2026-06-30',
            'work_days' => 20,
        ]);

        $project = Project::query()->where('aircraft_reg', 'PK-DGN-001')->firstOrFail();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'customer' => 'PT Dirgantara Indonesia',
            'aircraft_reg' => 'PK-DGN-001',
        ]);

        $this->assertSame(3, DockPhase::query()->where('project_id', $project->id)->count());
        $this->assertDatabaseHas('dock_phases', [
            'project_id' => $project->id,
            'type' => 'predock',
            'name' => 'PRE DOCK',
        ]);
        $this->assertDatabaseHas('dock_phases', [
            'project_id' => $project->id,
            'type' => 'indock',
            'name' => 'IN DOCK',
        ]);
        $this->assertDatabaseHas('dock_phases', [
            'project_id' => $project->id,
            'type' => 'postdock',
            'name' => 'POST DOCK',
        ]);
    }

    public function test_admin_can_update_a_project_and_phase_dates(): void
    {
        $admin = User::factory()->create([
            'name' => 'Project Admin',
            'role' => 'admin',
        ]);

        $project = $this->actingAs($admin)->post(route('projects.store'), [
            'customer' => 'PT Dirgantara Indonesia',
            'aircraft_type' => 'A320',
            'aircraft_reg' => 'PK-DGN-UPDATE',
            'contract_no' => 'CN-2026-010',
            'description' => 'Original project',
            'start_date' => '2026-06-02',
            'finish_date' => '2026-06-30',
            'work_days' => 20,
        ]);

        $project = Project::query()->where('aircraft_reg', 'PK-DGN-UPDATE')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('projects.update', $project), [
            'customer' => 'PT Dirgantara Updated',
            'aircraft_type' => 'A321',
            'aircraft_reg' => 'PK-DGN-UPDATED',
            'contract_no' => 'CN-2026-011',
            'description' => 'Updated project',
            'start_date' => '2026-07-01',
            'finish_date' => '2026-07-31',
            'work_days' => 22,
            'phases' => [
                'predock' => [
                    'start_date' => '2026-07-01',
                    'finish_date' => '2026-07-05',
                    'work_days' => 3,
                ],
            ],
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'customer' => 'PT Dirgantara Updated',
            'aircraft_reg' => 'PK-DGN-UPDATED',
            'work_days' => 22,
        ]);

        $this->assertDatabaseHas('dock_phases', [
            'project_id' => $project->id,
            'type' => 'predock',
            'work_days' => 3,
        ]);
    }

    public function test_admin_can_delete_a_project(): void
    {
        $admin = User::factory()->create([
            'name' => 'Project Admin',
            'role' => 'admin',
        ]);

        $project = $this->actingAs($admin)->post(route('projects.store'), [
            'customer' => 'PT Dirgantara Indonesia',
            'aircraft_type' => 'A320',
            'aircraft_reg' => 'PK-DGN-DEL',
            'contract_no' => 'CN-2026-020',
            'description' => 'Delete project',
            'start_date' => '2026-06-02',
            'finish_date' => '2026-06-30',
            'work_days' => 20,
        ]);

        $project = Project::query()->where('aircraft_reg', 'PK-DGN-DEL')->firstOrFail();

        $response = $this->actingAs($admin)->delete(route('projects.destroy', $project));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_admin_can_view_project_index_and_show_page(): void
    {
        $admin = User::factory()->create([
            'name' => 'Project Admin',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)->post(route('projects.store'), [
            'customer' => 'PT Dirgantara Indonesia',
            'aircraft_type' => 'A320',
            'aircraft_reg' => 'PK-DGN-SHOW',
            'contract_no' => 'CN-2026-030',
            'description' => 'Show project',
            'start_date' => '2026-06-02',
            'finish_date' => '2026-06-30',
            'work_days' => 20,
        ]);

        $project = Project::query()->where('aircraft_reg', 'PK-DGN-SHOW')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertViewIs('project.index');

        $this->actingAs($admin)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertViewIs('project.show')
            ->assertViewHas('project', fn (Project $loadedProject) => $loadedProject->id === $project->id);
    }
}