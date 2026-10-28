<?php

namespace Tests\Feature\ActionRole;

use App\Models\Action;
use App\Models\Role;
use Tests\TestCase;

class ActionRoleStoreTest extends TestCase
{
    public function test_can_store_action_role_relationship()
    {
        $this->loginAdmin()->attachAction(['auth.role.attach.actions']);

        $payload = [
            'roles' => [
                'Role'.$this->faker->unique()->word => [
                    'action.'.$this->faker->unique()->word.'.index',
                ],
                'Role'.$this->faker->unique()->word => [
                    'action.'.$this->faker->unique()->word.'.view',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/roles/attach/actions', $payload);

        $response->assertStatus(201);

        foreach ($payload['roles'] as $role => $actions) {
            foreach ($actions as $actionName) {
                $role = Role::where('key', strtolower($role))->first();
                $action = Action::where('name', $actionName)->first();

                $this->assertDatabaseHas('action_role', [
                    'role_id' => $role->id,
                    'action_id' => $action->id,
                ]);
            }
        }
    }

    public function test_cannot_store_duplicate_roles()
    {
        $this->loginAdmin()->attachAction(['auth.role.attach.actions']);

        $duplicatedRole = 'Role'.$this->faker->unique()->word;
        $actionName = 'action.'.$this->faker->unique()->word.'.index';

        $payload = [
            'roles' => [
                $duplicatedRole => [
                    $actionName,
                ],
            ],
        ];

        $firstResponse = $this->postJson('/api/v1/roles/attach/actions', $payload);
        $firstResponse->assertStatus(201);

        $role = Role::where('key', strtolower($duplicatedRole))->first();
        $action = Action::where('name', $actionName)->first();

        $this->assertNotNull($role);
        $this->assertNotNull($action);

        $secondResponse = $this->postJson('/api/v1/roles/attach/actions', $payload);

        $secondResponse->assertStatus(201);

        $this->assertEquals(1, \DB::table('action_role')
            ->where('role_id', $role->id)
            ->where('action_id', $action->id)
            ->count()
        );
    }
}
