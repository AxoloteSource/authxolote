<?php

namespace Tests\Feature\ActionRole;

use App\Models\Action;
use App\Models\ActionRole;
use App\Models\Role;
use Tests\TestCase;

class ActionRoleIndexTest extends TestCase
{
    public function test_action_role_index(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.actions.index']);
        $action = Action::factory()->create();
        $role = Role::factory()->create();
        $role->actions()->attach($action);
        $response = $this->get("/api/v1/roles/{$role->id}/actions?limit=100");
        $response->assertStatus(206);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'active',
                    'deleted_at',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);

        $data = $response->json('data');
        $match = collect($data)->firstWhere('id', $action->id);
        $this->assertTrue($match['active']);
    }

    public function test_assigned_actions(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.actions.index']);
        $role = Role::factory()->create();
        $assignedActions = Action::factory()->count(2)->create();
        $unassignedActions = Action::factory()->count(2)->create();

        $role->actions()->attach($assignedActions->pluck('id'));
        $response = $this->get("/api/v1/roles/{$role->id}/actions?limit=100");
        $response->assertStatus(206);

        $data = $response->json('data');
        foreach ($assignedActions as $action) {
            $match = collect($data)->firstWhere('id', $action->id);
            $this->assertTrue($match['active']);
        }

        foreach ($unassignedActions as $action) {
            $match = collect($data)->firstWhere('id', $action->id);
            $this->assertFalse($match['active']);
        }
    }

    public function test_unassigned_actions(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.actions.index']);
        $role = Role::factory()->create();
        Action::factory()->count(10)->create();
        $response = $this->get("/api/v1/roles/{$role->id}/actions");
        $response->assertStatus(206);
        $data = $response->json('data');
        foreach ($data as $action) {
            $this->assertFalse($action['active']);
        }
    }

    public function test_search_action_name(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.actions.index']);
        $role = Role::factory()->create();

        $customAction = Action::factory()->create([
            'name' => $this->faker->unique()->name(),
        ]);
        Action::factory()->count(10)->create();
        $response = $this->get("/api/v1/roles/{$role->id}/actions?search={$customAction->name}");
        $response->assertStatus(206);
        $data = $response->json('data');
        foreach ($data as $action) {
            $this->assertEquals($customAction->name, $action['name']);
        }
    }

    public function test_search_action_description(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.actions.index']);
        $role = Role::factory()->create();

        $customAction = Action::factory()->create([
            'name' => $this->faker->unique()->name(),
            'description' => $this->faker->sentence(),
        ]);
        Action::factory()->count(10)->create();
        $response = $this->get("/api/v1/roles/{$role->id}/actions?search={$customAction->description}");
        $response->assertStatus(206);
        $data = $response->json('data');
        foreach ($data as $action) {
            $this->assertEquals($customAction->description, $action['description']);
        }
    }

    public function test_only_active_actions(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.actions.index']);
        $role = Role::factory()->create();
        // unassigned actions
        Action::factory()->count(2)->create();
        // assigned actions
        $assignedActions = Action::factory()->count(5)->create();
        $role->actions()->attach($assignedActions->pluck('id'));
        $response = $this->get("/api/v1/roles/{$role->id}/actions?filters[1][property]=active&filters[1][value]=true");
        $response->assertStatus(206);
        $data = $response->json('data');
        $total = $response->json('total');
        $this->assertEquals(5, ActionRole::where('role_id', $role->id)->count());
        $this->assertEquals(5, count($data));
        $this->assertEquals(5, $total);
        foreach ($data as $action) {
            $this->assertTrue($action['active']);
        }
    }

    public function test_only_inactive_actions(): void
    {
        $this->loginAdmin()->attachAction(['auth.role.actions.index']);
        $role = Role::factory()->create();
        // unassigned actions
        Action::factory()->count(10)->create();
        // assigned actions
        $response = $this->get("/api/v1/roles/{$role->id}/actions?filters[1][property]=active&filters[1][value]=false");
        $response->assertStatus(206);
        $data = $response->json('data');
        $this->assertEquals(0, ActionRole::where('role_id', $role->id)->count());
        foreach ($data as $action) {
            $this->assertFalse($action['active']);
        }
    }
}
