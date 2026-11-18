<?php

namespace Tests\Feature\MenuItem;

use App\Models\MenuItem;
use App\Models\Role;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuItemRoleUpdateTest extends TestCase
{
    public function test_it_attaches_a_role_to_a_menu_item_when_active_is_true(): void
    {
        $this->loginAdmin()->attachAction(['auth.menu_items.roles.update']);

        $item = MenuItem::factory()->create();
        $role = Role::factory()->create();

        $this->assertDatabaseMissing('menu_item_role', [
            'menu_item_id' => $item->id,
            'role_id' => $role->id,
        ]);

        $response = $this->putJson("/api/v1/menu-items/{$item->id}/roles/{$role->id}", [
            'active' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('menu_item_role', [
            'menu_item_id' => $item->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_it_detaches_a_role_from_a_menu_item_when_active_is_false(): void
    {
        $this->loginAdmin()->attachAction(['auth.menu_items.roles.update']);

        $item = MenuItem::factory()->create();
        $role = Role::factory()->create();
        $item->roles()->attach($role->id);

        $this->assertDatabaseHas('menu_item_role', [
            'menu_item_id' => $item->id,
            'role_id' => $role->id,
        ]);

        $response = $this->putJson("/api/v1/menu-items/{$item->id}/roles/{$role->id}", [
            'active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('menu_item_role', [
            'menu_item_id' => $item->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_it_does_not_change_the_menu_item_active_flag_when_detaching_a_role(): void
    {
        $this->loginAdmin()->attachAction(['auth.menu_items.roles.update']);

        $item = MenuItem::factory()->create(['active' => true]);
        $role = Role::factory()->create();
        $item->roles()->attach($role->id);

        $this->putJson("/api/v1/menu-items/{$item->id}/roles/{$role->id}", [
            'active' => false,
        ])->assertStatus(200);

        $this->assertDatabaseMissing('menu_item_role', [
            'menu_item_id' => $item->id,
            'role_id' => $role->id,
        ]);

        $this->assertTrue($item->fresh()->active);
        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'active' => true,
        ]);
    }

    public function test_it_does_not_change_the_menu_item_active_flag_when_attaching_a_role(): void
    {
        $this->loginAdmin()->attachAction(['auth.menu_items.roles.update']);

        $item = MenuItem::factory()->create(['active' => false]);
        $role = Role::factory()->create();

        $this->putJson("/api/v1/menu-items/{$item->id}/roles/{$role->id}", [
            'active' => true,
        ])->assertStatus(200);

        $this->assertDatabaseHas('menu_item_role', [
            'menu_item_id' => $item->id,
            'role_id' => $role->id,
        ]);

        $this->assertFalse($item->fresh()->active);
        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'active' => false,
        ]);
    }

    public function test_it_returns_not_found_when_menu_item_does_not_exist(): void
    {
        $this->loginRoot();

        $role = Role::factory()->create();

        $response = $this->putJson('/api/v1/menu-items/'.fake()->uuid().'/roles/'.$role->id, [
            'active' => true,
        ]);

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $item = MenuItem::factory()->create();
        $role = Role::factory()->create();

        $this->expectException(AuthenticationException::class);

        $this->putJson("/api/v1/menu-items/{$item->id}/roles/{$role->id}", [
            'active' => true,
        ]);
    }
}
