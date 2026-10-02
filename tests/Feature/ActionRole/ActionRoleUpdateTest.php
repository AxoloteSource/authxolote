<?php

namespace Tests\Feature\ActionRole;

use App\Models\Action;
use App\Models\Role;
use Tests\TestCase;

class ActionRoleUpdateTest extends TestCase
{
    public function test_update_action_role(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.actions.update']);

        Action::factory()->count(10)->create();
        $action = Action::factory()->create();
        $role = Role::factory()->create();

        $this->assertDatabaseMissing('action_role', [
            'action_id' => $action->id,
            'role_id' => $role->id,
        ]);

        $response = $this->put("/api/v1/roles/{$role->id}/actions/{$action->id}", [
            'active' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('action_role', [
            'role_id' => $role->id,
            'action_id' => $action->id,
        ]);
        $this->assertDatabaseHas('action_role_overrides', [
            'role_id' => $role->id,
            'action_id' => $action->id,
            'active' => true,
        ]);
    }

    public function test_remove_action_role_if_active_is_false()
    {
        $this->loginAdmin()->attachAction(['auth.role.actions.update']);
        $role = Role::factory()->create();
        $action = Action::factory()->create();
        $action->roles()->attach($role->id);

        $this->assertDatabaseHas('action_role', [
            'action_id' => $action->id,
            'role_id' => $role->id,
        ]);

        $response = $this->put("/api/v1/roles/{$role->id}/actions/{$action->id}", [
            'active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('action_role', [
            'action_id' => $action->id,
            'role_id' => $role->id,
        ]);
        $this->assertDatabaseHas('action_role_overrides', [
            'action_id' => $action->id,
            'role_id' => $role->id,
            'active' => false,
        ]);
    }
}
