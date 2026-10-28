<?php

namespace Tests\Feature\Action;

use App\Models\Action;
use Tests\TestCase;

class AttachActionsCommandTest extends TestCase
{
    public function test_command_creates_all_configured_actions(): void
    {
        $this->artisan('authxolote:actions')->assertExitCode(0);

        foreach (config('actions.admin') as $name => $description) {
            $this->assertDatabaseHas('actions', [
                'name' => $name,
                'description' => $description,
            ]);
        }
    }

    public function test_command_updates_existing_action_description_without_changing_its_id(): void
    {
        config(['actions' => ['admin' => ['custom.action.update' => 'New description']]]);

        $action = Action::factory()->create([
            'name' => 'custom.action.update',
            'description' => 'Old description',
        ]);
        $originalId = $action->id;

        $this->artisan('authxolote:actions')->assertExitCode(0);

        $action->refresh();

        $this->assertSame($originalId, $action->id);
        $this->assertSame('New description', $action->description);
    }

    public function test_command_reuses_a_soft_deleted_action(): void
    {
        config(['actions' => ['admin' => ['custom.action.soft_deleted' => 'Soft deleted action']]]);

        Action::factory()->create(['name' => 'custom.action.soft_deleted'])->delete();

        $this->artisan('authxolote:actions')->assertExitCode(0);

        $this->assertSame(
            1,
            Action::withTrashed()->where('name', 'custom.action.soft_deleted')->count(),
        );
    }

    public function test_command_fails_when_configuration_is_empty(): void
    {
        config(['actions' => []]);

        $this->artisan('authxolote:actions')->assertExitCode(1);
    }
}
