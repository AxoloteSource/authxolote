<?php

namespace Tests\Feature\ActionRole;

use App\Enums\RoleEnum;
use App\Models\Action;
use App\Models\ActionRole;
use Tests\TestCase;

class ActionRoleCommandTest extends TestCase
{
    public function test_attach_actions_permission_is_linked_by_name(): void
    {
        $this->artisan('authxolote:actions')->assertExitCode(0);

        $actionId = Action::query()
            ->where('name', 'auth.role.attach.actions')
            ->value('id');

        $this->assertDatabaseHas('action_role', [
            'action_id' => $actionId,
            'role_id' => RoleEnum::Admin->value,
        ]);
    }

    public function test_all_configured_actions_are_linked_to_admin(): void
    {
        $this->artisan('authxolote:actions')->assertExitCode(0);

        $actionIds = Action::query()
            ->whereIn('name', array_keys(config('actions.admin')))
            ->pluck('id');

        $this->assertSame(
            $actionIds->count(),
            ActionRole::query()
                ->where('role_id', RoleEnum::Admin->value)
                ->whereIn('action_id', $actionIds)
                ->count(),
        );
    }

    public function test_actions_outside_the_configuration_are_not_linked_to_admin(): void
    {
        $action = Action::factory()->create(['name' => 'custom.not.configured.action']);

        $this->artisan('authxolote:actions')->assertExitCode(0);

        $this->assertDatabaseMissing('action_role', [
            'action_id' => $action->id,
            'role_id' => RoleEnum::Admin->value,
        ]);
    }
}
