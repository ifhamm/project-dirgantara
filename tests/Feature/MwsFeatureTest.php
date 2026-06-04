<?php

namespace Tests\Feature;

use App\Models\DockPhase;
use App\Models\MwsPart;
use App\Models\MwsConsumable;
use App\Models\MwsStep;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MwsFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createTaskFixture(string $name = 'MWS Task'): Task
    {
        $project = Project::create([
            'customer' => 'PT Dirgantara Indonesia',
            'contract_no' => 'CN-001',
            'aircraft_reg' => 'PK-DIR',
            'aircraft_type' => 'CN235-110',
            'description' => 'Test project',
        ]);

        $dockPhase = DockPhase::create([
            'project_id' => $project->id,
            'type' => 'indock',
            'no' => '1',
            'name' => 'Indock Phase',
        ]);

        $taskGroup = TaskGroup::create([
            'dock_phase_id' => $dockPhase->id,
            'no' => '1',
            'name' => 'Task Group',
        ]);

        return Task::create([
            'task_group_id' => $taskGroup->id,
            'no' => '1',
            'name' => $name,
        ]);
    }

    public function test_admin_can_open_mws_create_page_and_store_a_new_mws_part(): void
    {
        $admin = User::factory()->create([
            'name' => 'MWS Admin',
            'role' => 'admin',
        ]);
        $task = $this->createTaskFixture('Wing Inspection Task');

        $this->actingAs($admin)
            ->get(route('mws.create'))
            ->assertOk();

        $response = $this->actingAs($admin)->post(route('mws.store'), [
            'title' => 'Wing Inspection Sheet',
            'job_type' => 'Repair',
            'customer_name' => 'PT Dirgantara Indonesia',
            'part_number' => 'PN-001',
            'serial_number' => 'SN-001',
            'shop_area' => 'FO',
            'wbs_no' => 'WBS-001',
            'ref' => 'REF-001',
            'worksheet_no' => 'WS-001',
            'revision' => '1',
            'task_id' => $task->id,
        ]);

        $mwsPart = MwsPart::query()->where('part_number', 'PN-001')->firstOrFail();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('mws.show', $mwsPart));

        $this->assertDatabaseHas('mws_parts', [
            'id' => $mwsPart->id,
            'part_id' => 'MWS-001',
            'title' => 'Wing Inspection Sheet',
            'status' => 'Form Out',
        ]);

        $this->assertSame(10, $mwsPart->steps()->count());
        $this->assertSame('Incoming Record', $mwsPart->steps()->orderBy('no')->firstOrFail()->description);
    }

    public function test_admin_can_update_mws_metadata(): void
    {
        $admin = User::factory()->create([
            'name' => 'MWS Admin',
            'role' => 'admin',
        ]);
        $task = $this->createTaskFixture('Wing Inspection Task');

        $this->actingAs($admin)->post(route('mws.store'), [
            'title' => 'Wing Inspection Sheet',
            'job_type' => 'Repair',
            'customer_name' => 'PT Dirgantara Indonesia',
            'part_number' => 'PN-UPDATE',
            'serial_number' => 'SN-UPDATE',
            'shop_area' => 'FO',
            'wbs_no' => 'WBS-UPDATE',
            'ref' => 'REF-UPDATE',
            'worksheet_no' => 'WS-UPDATE',
            'revision' => '1',
            'task_id' => $task->id,
        ]);

        $mwsPart = MwsPart::query()->where('part_number', 'PN-UPDATE')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('mws.update', $mwsPart), [
            'title' => 'Wing Inspection Sheet Updated',
            'status' => 'In Progress',
            'shopArea' => 'BO',
            'revision' => '2',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('mws_parts', [
            'id' => $mwsPart->id,
            'title' => 'Wing Inspection Sheet Updated',
            'status' => 'In Progress',
            'revision' => '2',
        ]);
    }

    public function test_admin_can_sign_prepared_mws_part(): void
    {
        $admin = User::factory()->create([
            'name' => 'MWS Admin',
            'role' => 'admin',
        ]);
        $task = $this->createTaskFixture('Wing Inspection Task');

        $this->actingAs($admin)->post(route('mws.store'), [
            'title' => 'Wing Inspection Sheet',
            'job_type' => 'Repair',
            'customer_name' => 'PT Dirgantara Indonesia',
            'part_number' => 'PN-SIGN',
            'serial_number' => 'SN-SIGN',
            'shop_area' => 'FO',
            'wbs_no' => 'WBS-SIGN',
            'ref' => 'REF-SIGN',
            'worksheet_no' => 'WS-SIGN',
            'revision' => '1',
            'task_id' => $task->id,
        ]);

        $mwsPart = MwsPart::query()->where('part_number', 'PN-SIGN')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('mws.sign', $mwsPart), [
            'type' => 'prepared',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Prepared berhasil di-sign!',
            ]);

        $this->assertDatabaseHas('mws_parts', [
            'id' => $mwsPart->id,
            'prepared_by' => 'MWS Admin',
        ]);

        $this->assertNotNull($mwsPart->fresh()->prepared_date);
    }

    public function test_admin_can_view_tracking_and_delete_mws_part(): void
    {
        $admin = User::factory()->create([
            'name' => 'MWS Admin',
            'role' => 'admin',
        ]);
        $task = $this->createTaskFixture('Wing Inspection Task');

        $this->actingAs($admin)->post(route('mws.store'), [
            'title' => 'Wing Inspection Sheet',
            'job_type' => 'Repair',
            'customer_name' => 'PT Dirgantara Indonesia',
            'part_number' => 'PN-DELETE',
            'serial_number' => 'SN-DELETE',
            'shop_area' => 'FO',
            'wbs_no' => 'WBS-DELETE',
            'ref' => 'REF-DELETE',
            'worksheet_no' => 'WS-DELETE',
            'revision' => '1',
            'task_id' => $task->id,
        ]);

        $mwsPart = MwsPart::query()->where('part_number', 'PN-DELETE')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('mws.tracking'))
            ->assertOk()
            ->assertViewIs('mws.tracking');

        $response = $this->actingAs($admin)->delete(route('mws.destroy', $mwsPart));

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'MWS berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('mws_parts', [
            'id' => $mwsPart->id,
        ]);
    }

    public function test_admin_can_manage_mws_step_workflow(): void
    {
        $admin = User::factory()->create([
            'name' => 'MWS Admin',
            'nik' => 'ADM-001',
            'role' => 'admin',
        ]);

        $mechanic = User::factory()->create([
            'name' => 'MWS Mechanic',
            'nik' => 'MECH-001',
            'role' => 'mechanic',
        ]);
        $task = $this->createTaskFixture('Workflow Task');

        $this->actingAs($admin)->post(route('mws.store'), [
            'title' => 'Workflow Sheet',
            'job_type' => 'Repair',
            'customer_name' => 'PT Dirgantara Indonesia',
            'part_number' => 'PN-WF',
            'serial_number' => 'SN-WF',
            'shop_area' => 'FO',
            'wbs_no' => 'WBS-WF',
            'ref' => 'REF-WF',
            'worksheet_no' => 'WS-WF',
            'revision' => '1',
            'task_id' => $task->id,
        ]);

        $mwsPart = MwsPart::query()->where('part_number', 'PN-WF')->firstOrFail();
        $step = MwsStep::query()
            ->where('mws_part_id', $mwsPart->id)
            ->where('no', 1)
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('mws.details.store', [$mwsPart->id, 1]), [
                'detail' => 'Inspect panel',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame(['Inspect panel'], $step->details);

        $this->actingAs($admin)
            ->put(route('mws.details.update', [$mwsPart->id, 1, 0]), [
                'detail' => 'Inspect panel carefully',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame(['Inspect panel carefully'], $step->details);

        $this->actingAs($admin)
            ->delete(route('mws.details.destroy', [$mwsPart->id, 1, 0]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame([], $step->details);

        $this->actingAs($admin)
            ->post(route('mws.mechanics.assign', [$mwsPart->id, 1]), [
                'nik' => $mechanic->nik,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame([$mechanic->nik], $step->fresh()->man);

        $this->actingAs($mechanic)
            ->post(route('mws.mechanics.signOn', [$mwsPart->id, 1]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame([$mechanic->nik], $step->fresh()->man);

        $startAt = Carbon::create(2026, 6, 2, 8, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($startAt);

        $this->actingAs($mechanic)
            ->post(route('mws.timer.start', [$mwsPart->id, 1]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame('in_progress', $step->status);
        $this->assertNotNull($step->timer_start_time);

        Carbon::setTestNow($startAt->copy()->addMinutes(125));

        $timerResponse = $this->actingAs($mechanic)
            ->post(route('mws.timer.stop', [$mwsPart->id, 1]))
            ->assertOk()
            ->assertJson(['success' => true]);

        Carbon::setTestNow();

        $step->refresh();
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $step->hours);
        $this->assertSame($step->hours, $timerResponse->json('hours'));
        $this->assertNull($step->timer_start_time);

        $this->actingAs($admin)
            ->post(route('mws.steps.approve', [$mwsPart->id, 1]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame('Approved', $step->tech);

        $this->actingAs($admin)
            ->post(route('mws.steps.finish', [$mwsPart->id, 1]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame('completed', $step->status);
        $this->assertSame('Approved', $step->insp);
    }

    public function test_admin_can_manage_mws_substeps(): void
    {
        $admin = User::factory()->create([
            'name' => 'MWS Admin',
            'role' => 'admin',
        ]);
        $task = $this->createTaskFixture('Substep Task');

        $this->actingAs($admin)->post(route('mws.store'), [
            'title' => 'Substep Sheet',
            'job_type' => 'Repair',
            'customer_name' => 'PT Dirgantara Indonesia',
            'part_number' => 'PN-SUB',
            'serial_number' => 'SN-SUB',
            'shop_area' => 'FO',
            'wbs_no' => 'WBS-SUB',
            'ref' => 'REF-SUB',
            'worksheet_no' => 'WS-SUB',
            'revision' => '1',
            'task_id' => $task->id,
        ]);

        $mwsPart = MwsPart::query()->where('part_number', 'PN-SUB')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('mws.substeps.store', [$mwsPart->id, 1]), [
                'description' => 'Remove cover panel',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $step = MwsStep::query()
            ->where('mws_part_id', $mwsPart->id)
            ->where('no', 1)
            ->firstOrFail();

        $this->assertSame(1, $step->subSteps()->count());
        $subStep = $step->subSteps()->firstOrFail();
        $this->assertSame('a', $subStep->label);

        $this->actingAs($admin)
            ->put(route('mws.substeps.update', [$mwsPart->id, 1, $subStep->id]), [
                'description' => 'Remove cover panel carefully',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('Remove cover panel carefully', $subStep->fresh()->description);

        $this->actingAs($admin)
            ->delete(route('mws.substeps.destroy', [$mwsPart->id, 1, $subStep->id]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('mws_sub_steps', [
            'id' => $subStep->id,
        ]);
    }

    public function test_admin_can_caution_unapprove_and_unfinish_step(): void
    {
        $admin = User::factory()->create([
            'name' => 'MWS Admin',
            'role' => 'admin',
        ]);
        $task = $this->createTaskFixture('Caution Task');

        $this->actingAs($admin)->post(route('mws.store'), [
            'title' => 'Caution Sheet',
            'job_type' => 'Repair',
            'customer_name' => 'PT Dirgantara Indonesia',
            'part_number' => 'PN-CAU',
            'serial_number' => 'SN-CAU',
            'shop_area' => 'FO',
            'wbs_no' => 'WBS-CAU',
            'ref' => 'REF-CAU',
            'worksheet_no' => 'WS-CAU',
            'revision' => '1',
            'task_id' => $task->id,
        ]);

        $mwsPart = MwsPart::query()->where('part_number', 'PN-CAU')->firstOrFail();
        $step = MwsStep::query()->where('mws_part_id', $mwsPart->id)->where('no', 1)->firstOrFail();

        // Approve first so we can unapprove later
        $this->actingAs($admin)
            ->post(route('mws.steps.approve', [$mwsPart->id, 1]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame('Approved', $step->tech);

        // Add caution + note
        $this->actingAs($admin)
            ->put(route('mws.steps.caution', [$mwsPart->id, 1]), [
                'caution' => 'High voltage',
                'note' => 'Isolate power before work',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame('High voltage', $step->caution);
        $this->assertSame('Isolate power before work', $step->note);

        // Unapprove should clear tech
        $this->actingAs($admin)
            ->post(route('mws.steps.unapprove', [$mwsPart->id, 1]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertNull($step->tech);

        // Finish then unfinish
        $this->actingAs($admin)
            ->post(route('mws.steps.finish', [$mwsPart->id, 1]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame('completed', $step->status);
        $this->assertSame('Approved', $step->insp);

        $this->actingAs($admin)
            ->post(route('mws.steps.unfinish', [$mwsPart->id, 1]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $step->refresh();
        $this->assertSame('in_progress', $step->status);
        $this->assertNull($step->insp);
    }

    public function test_admin_can_insert_mws_step_after_existing_step(): void
    {
        $admin = User::factory()->create([
            'name' => 'MWS Admin',
            'role' => 'admin',
        ]);
        $task = $this->createTaskFixture('Insert Step Task');

        $this->actingAs($admin)->post(route('mws.store'), [
            'title' => 'Insert Step Sheet',
            'job_type' => 'Repair',
            'customer_name' => 'PT Dirgantara Indonesia',
            'part_number' => 'PN-INS',
            'serial_number' => 'SN-INS',
            'shop_area' => 'FO',
            'wbs_no' => 'WBS-INS',
            'ref' => 'REF-INS',
            'worksheet_no' => 'WS-INS',
            'revision' => '1',
            'task_id' => $task->id,
        ]);

        $mwsPart = MwsPart::query()->where('part_number', 'PN-INS')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('mws.steps.insertAfter', [$mwsPart->id, 1]), [
                'description' => 'Inserted check',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $steps = MwsStep::query()
            ->where('mws_part_id', $mwsPart->id)
            ->orderBy('no')
            ->get();

        $this->assertCount(11, $steps);
        $this->assertSame('Incoming Record', $steps[0]->description);
        $this->assertSame('Inserted check', $steps[1]->description);
        $this->assertSame(2, $steps[1]->no);
        $this->assertSame(3, $steps[2]->no);
        $this->assertNotSame('Inserted check', $steps[2]->description);
    }

    public function test_admin_can_manage_mws_consumables(): void
    {
        $admin = User::factory()->create([
            'name' => 'MWS Admin',
            'role' => 'admin',
        ]);
        $task = $this->createTaskFixture('Consumable Task');

        $this->actingAs($admin)->post(route('mws.store'), [
            'title' => 'Consumable Sheet',
            'job_type' => 'Repair',
            'customer_name' => 'PT Dirgantara Indonesia',
            'part_number' => 'PN-CONS',
            'serial_number' => 'SN-CONS',
            'shop_area' => 'FO',
            'wbs_no' => 'WBS-CONS',
            'ref' => 'REF-CONS',
            'worksheet_no' => 'WS-CONS',
            'revision' => '1',
            'task_id' => $task->id,
        ]);

        $mwsPart = MwsPart::query()->where('part_number', 'PN-CONS')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('mws.consumables.store', $mwsPart->id), [
                'name' => 'Sealant',
                'identification' => 'SEAL-001',
                'quantity' => '2',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $consumable = MwsConsumable::query()
            ->where('mws_part_id', $mwsPart->id)
            ->where('name', 'Sealant')
            ->firstOrFail();

        $this->assertSame(1, $consumable->order);
        $this->assertDatabaseHas('mws_consumables', [
            'id' => $consumable->id,
            'quantity' => '2',
        ]);

        $this->actingAs($admin)
            ->put(route('mws.consumables.update', [$mwsPart->id, $consumable->id]), [
                'name' => 'Sealant XL',
                'quantity' => '3',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('mws_consumables', [
            'id' => $consumable->id,
            'name' => 'Sealant XL',
            'quantity' => '3',
        ]);

        $this->actingAs($admin)
            ->delete(route('mws.consumables.destroy', [$mwsPart->id, $consumable->id]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('mws_consumables', [
            'id' => $consumable->id,
        ]);
    }
}