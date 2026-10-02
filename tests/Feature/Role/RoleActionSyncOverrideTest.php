<?php

namespace Tests\Feature\Role;

use App\Models\Action;
use App\Models\Role;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RoleActionSyncOverrideTest extends TestCase
{
    private function syncActions(array $roles, bool $force = false): TestResponse
    {
        return $this->postJson('/api/v1/roles/attach/actions', [
            'roles' => $roles,
            'force' => $force,
        ]);
    }

    private function toggleActionFromFront(Role $role, Action $action, bool $active): TestResponse
    {
        return $this->put("/api/v1/roles/{$role->id}/actions/{$action->id}", [
            'active' => $active,
        ]);
    }

    public function test_sync_does_not_restore_an_action_removed_from_front(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.attach.actions', 'auth.role.actions.update']);
        $role = Role::factory()->create();
        $action = Action::factory()->create();

        $this->syncActions([$role->key => [$action->name]])->assertStatus(201);
        $this->assertDatabaseHas('action_role', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);

        $this->toggleActionFromFront($role, $action, false)->assertStatus(200);

        $this->assertDatabaseMissing('action_role', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);
        $this->assertDatabaseHas('action_role_overrides', [
            'role_id' => $role->id,
            'action_id' => $action->id,
            'active' => false,
        ]);

        $this->syncActions([$role->key => [$action->name]])->assertStatus(201);

        $this->assertDatabaseMissing('action_role', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);
    }

    public function test_force_sync_restores_removed_action_and_clears_overrides(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.attach.actions', 'auth.role.actions.update']);
        $role = Role::factory()->create();
        $action = Action::factory()->create();

        $this->syncActions([$role->key => [$action->name]])->assertStatus(201);
        $this->toggleActionFromFront($role, $action, false)->assertStatus(200);

        $this->syncActions([$role->key => [$action->name]], force: true)->assertStatus(201);

        $this->assertDatabaseHas('action_role', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);
        $this->assertDatabaseMissing('action_role_overrides', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);
    }

    public function test_sync_still_adds_new_config_actions_after_a_front_override(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.attach.actions', 'auth.role.actions.update']);
        $role = Role::factory()->create();
        $removedAction = Action::factory()->create();
        $newAction = Action::factory()->create();

        $this->syncActions([$role->key => [$removedAction->name]])->assertStatus(201);
        $this->toggleActionFromFront($role, $removedAction, false)->assertStatus(200);

        $this->syncActions([$role->key => [$removedAction->name, $newAction->name]])->assertStatus(201);

        $this->assertDatabaseMissing('action_role', [
            'role_id' => $role->id,
            'action_id' => $removedAction->id,
        ]);
        $this->assertDatabaseHas('action_role', [
            'role_id' => $role->id,
            'action_id' => $newAction->id,
        ]);
    }

    public function test_sync_keeps_an_action_added_from_front(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.attach.actions', 'auth.role.actions.update']);
        $role = Role::factory()->create();
        $configAction = Action::factory()->create();
        $manualAction = Action::factory()->create();

        $this->syncActions([$role->key => [$configAction->name]])->assertStatus(201);
        $this->toggleActionFromFront($role, $manualAction, true)->assertStatus(200);

        $this->syncActions([$role->key => [$configAction->name]])->assertStatus(201);

        $this->assertDatabaseHas('action_role', [
            'role_id' => $role->id,
            'action_id' => $manualAction->id,
        ]);
    }

    public function test_command_with_force_option_ignores_front_overrides(): void
    {
        $role = Role::factory()->create();
        $action = Action::factory()->create();
        config(['actions' => [$role->key => [$action->name]]]);

        $this->artisan('authxolote:actions')->assertExitCode(0);
        $this->assertDatabaseHas('action_role', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);

        $this->loginAdmin()->attachAction(['auth.role.actions.update']);
        $this->toggleActionFromFront($role, $action, false)->assertStatus(200);

        $this->artisan('authxolote:actions')->assertExitCode(0);
        $this->assertDatabaseMissing('action_role', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);

        $this->artisan('authxolote:actions', ['--force' => true])->assertExitCode(0);
        $this->assertDatabaseHas('action_role', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);
        $this->assertDatabaseMissing('action_role_overrides', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);
    }
}
