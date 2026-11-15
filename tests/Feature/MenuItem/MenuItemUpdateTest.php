<?php

namespace Tests\Feature\MenuItem;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuItemUpdateTest extends TestCase
{
    public function test_it_can_update_a_menu_item(): void
    {
        $this->loginRoot();

        $item = MenuItem::factory()->create();

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $item->menu_id,
            'type' => $item->type->value,
            'name' => 'Updated Item',
            'slug' => 'updated-item',
            'path' => '/updated',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'name' => 'Updated Item',
            'slug' => 'updated-item',
            'path' => '/updated',
        ]);
    }

    public function test_it_rejects_a_link_item_without_path_or_route_on_update(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $item = MenuItem::factory()->create();

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $item->menu_id,
            'type' => 'link',
            'name' => 'Updated Item',
            'slug' => $item->slug,
        ]);

        $response->assertStatus(422);
    }

    public function test_it_can_update_an_item_to_a_section(): void
    {
        $this->loginRoot();

        $item = MenuItem::factory()->header()->create();

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $item->menu_id,
            'type' => 'section',
            'name' => 'Salamandr IA',
            'slug' => $item->slug,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'type' => 'section',
        ]);
    }

    public function test_it_rejects_a_section_item_with_route(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $item = MenuItem::factory()->header()->create();

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $item->menu_id,
            'type' => 'section',
            'name' => 'Salamandr IA',
            'slug' => $item->slug,
            'route' => 'ia.index',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_can_set_a_parent_within_the_same_menu(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->header()->create(['menu_id' => $menu->id]);
        $item = MenuItem::factory()->create(['menu_id' => $menu->id]);

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $menu->id,
            'parent_id' => $parent->id,
            'type' => $item->type->value,
            'name' => $item->name,
            'slug' => $item->slug,
            'path' => $item->path,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_it_rejects_a_self_parent(): void
    {
        $this->loginRoot();

        $item = MenuItem::factory()->header()->create();

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $item->menu_id,
            'parent_id' => $item->id,
            'type' => $item->type->value,
            'name' => $item->name,
            'slug' => $item->slug,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('A menu item cannot be its own parent.'));
        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'parent_id' => null]);
    }

    public function test_it_rejects_a_parent_from_another_menu(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $otherMenu = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $foreignParent = MenuItem::factory()->header()->create(['menu_id' => $otherMenu->id]);

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $menu->id,
            'parent_id' => $foreignParent->id,
            'type' => $item->type->value,
            'name' => $item->name,
            'slug' => $item->slug,
            'path' => $item->path,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('The parent menu item does not belong to the given menu.'));
        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'parent_id' => null]);
    }

    public function test_it_rejects_a_cycle(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->header()->create(['menu_id' => $menu->id]);
        $child = MenuItem::factory()->header()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parent->id,
        ]);

        $response = $this->putJson('/api/v1/menu-items/'.$parent->id, [
            'menu_id' => $menu->id,
            'parent_id' => $child->id,
            'type' => $parent->type->value,
            'name' => $parent->name,
            'slug' => $parent->slug,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('The menu item hierarchy contains a cycle.'));
        $this->assertDatabaseHas('menu_items', ['id' => $parent->id, 'parent_id' => null]);
    }

    public function test_it_rejects_a_menu_that_does_not_exist(): void
    {
        $this->loginRoot();

        $item = MenuItem::factory()->create();

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => fake()->uuid(),
            'type' => $item->type->value,
            'name' => $item->name,
            'slug' => $item->slug,
            'path' => $item->path,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('The selected menu is invalid.'));
        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'menu_id' => $item->menu_id]);
    }

    public function test_it_rejects_a_soft_deleted_menu(): void
    {
        $this->loginRoot();

        $item = MenuItem::factory()->create();
        $menu = Menu::factory()->create();
        $menu->delete();

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $menu->id,
            'type' => $item->type->value,
            'name' => $item->name,
            'slug' => $item->slug,
            'path' => $item->path,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('The selected menu is invalid.'));
        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'menu_id' => $item->menu_id]);
    }

    public function test_it_rejects_moving_an_item_with_children_to_another_menu(): void
    {
        $this->loginRoot();

        $source = Menu::factory()->create();
        $destination = Menu::factory()->create();
        $item = MenuItem::factory()->header()->create(['menu_id' => $source->id]);
        MenuItem::factory()->create(['menu_id' => $source->id, 'parent_id' => $item->id]);

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $destination->id,
            'type' => $item->type->value,
            'name' => $item->name,
            'slug' => $item->slug,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('A menu item with children cannot be moved to another menu.'));
        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'menu_id' => $source->id]);
    }

    public function test_it_can_move_an_item_without_children_to_another_menu(): void
    {
        $this->loginRoot();

        $source = Menu::factory()->create();
        $destination = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $source->id]);

        $response = $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $destination->id,
            'type' => $item->type->value,
            'name' => $item->name,
            'slug' => $item->slug,
            'path' => $item->path,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'menu_id' => $destination->id,
        ]);
    }

    public function test_it_returns_not_found_when_menu_item_does_not_exist(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->putJson('/api/v1/menu-items/'.fake()->uuid(), [
            'menu_id' => fake()->uuid(),
            'type' => 'link',
            'name' => 'Updated Item',
            'slug' => 'updated-item',
            'path' => '/updated',
        ]);

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $item = MenuItem::factory()->create();

        $this->expectException(AuthenticationException::class);

        $this->putJson('/api/v1/menu-items/'.$item->id, [
            'menu_id' => $item->menu_id,
            'type' => $item->type->value,
            'name' => 'Updated Item',
            'slug' => $item->slug,
            'path' => '/updated',
        ]);
    }
}
