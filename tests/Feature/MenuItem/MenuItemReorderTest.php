<?php

namespace Tests\Feature\MenuItem;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuItemReorderTest extends TestCase
{
    public function test_it_reorders_sibling_menu_items(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $first = MenuItem::factory()->create(['menu_id' => $menu->id, 'sort_order' => 0]);
        $second = MenuItem::factory()->create(['menu_id' => $menu->id, 'sort_order' => 1]);

        $response = $this->putJson('/api/v1/menu-items/reorder', [
            'menu_id' => $menu->id,
            'items' => [
                ['id' => $second->id, 'parent_id' => null, 'sort_order' => 0],
                ['id' => $first->id, 'parent_id' => null, 'sort_order' => 1],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.reordered', 2);

        $this->assertDatabaseHas('menu_items', [
            'id' => $second->id,
            'parent_id' => null,
            'sort_order' => 0,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $first->id,
            'parent_id' => null,
            'sort_order' => 1,
        ]);
    }

    public function test_it_moves_a_menu_item_under_another_parent(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->header()->create(['menu_id' => $menu->id, 'sort_order' => 0]);
        $child = MenuItem::factory()->create(['menu_id' => $menu->id, 'sort_order' => 1]);

        $response = $this->putJson('/api/v1/menu-items/reorder', [
            'menu_id' => $menu->id,
            'items' => [
                ['id' => $parent->id, 'parent_id' => null, 'sort_order' => 0],
                ['id' => $child->id, 'parent_id' => $parent->id, 'sort_order' => 0],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('menu_items', [
            'id' => $child->id,
            'parent_id' => $parent->id,
            'sort_order' => 0,
        ]);
    }

    public function test_it_normalizes_sort_order_values(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $first = MenuItem::factory()->create(['menu_id' => $menu->id, 'sort_order' => 0]);
        $second = MenuItem::factory()->create(['menu_id' => $menu->id, 'sort_order' => 1]);

        $response = $this->putJson('/api/v1/menu-items/reorder', [
            'menu_id' => $menu->id,
            'items' => [
                ['id' => $first->id, 'parent_id' => null, 'sort_order' => 5],
                ['id' => $second->id, 'parent_id' => null, 'sort_order' => 10],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('menu_items', ['id' => $first->id, 'sort_order' => 0]);
        $this->assertDatabaseHas('menu_items', ['id' => $second->id, 'sort_order' => 1]);
    }

    public function test_it_rejects_a_payload_without_every_menu_item(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $first = MenuItem::factory()->create(['menu_id' => $menu->id]);
        MenuItem::factory()->create(['menu_id' => $menu->id]);

        $response = $this->putJson('/api/v1/menu-items/reorder', [
            'menu_id' => $menu->id,
            'items' => [
                ['id' => $first->id, 'parent_id' => null, 'sort_order' => 0],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('The payload must include every menu item of the menu.'));
    }

    public function test_it_rejects_an_item_from_another_menu(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $foreign = MenuItem::factory()->create();

        $response = $this->putJson('/api/v1/menu-items/reorder', [
            'menu_id' => $menu->id,
            'items' => [
                ['id' => $foreign->id, 'parent_id' => null, 'sort_order' => 0],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('The menu item does not belong to the given menu.'));

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'sort_order' => $item->sort_order,
        ]);
    }

    public function test_it_rejects_duplicate_menu_items(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $first = MenuItem::factory()->create(['menu_id' => $menu->id]);
        MenuItem::factory()->create(['menu_id' => $menu->id]);

        $response = $this->putJson('/api/v1/menu-items/reorder', [
            'menu_id' => $menu->id,
            'items' => [
                ['id' => $first->id, 'parent_id' => null, 'sort_order' => 0],
                ['id' => $first->id, 'parent_id' => null, 'sort_order' => 1],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('The menu items payload contains duplicate identifiers.'));
    }

    public function test_it_rejects_cycles(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $first = MenuItem::factory()->header()->create(['menu_id' => $menu->id]);
        $second = MenuItem::factory()->header()->create(['menu_id' => $menu->id, 'parent_id' => $first->id]);

        $response = $this->putJson('/api/v1/menu-items/reorder', [
            'menu_id' => $menu->id,
            'items' => [
                ['id' => $first->id, 'parent_id' => $second->id, 'sort_order' => 0],
                ['id' => $second->id, 'parent_id' => $first->id, 'sort_order' => 0],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('The menu item hierarchy contains a cycle.'));
    }

    public function test_it_rejects_an_item_as_its_own_parent(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->header()->create(['menu_id' => $menu->id]);

        $response = $this->putJson('/api/v1/menu-items/reorder', [
            'menu_id' => $menu->id,
            'items' => [
                ['id' => $item->id, 'parent_id' => $item->id, 'sort_order' => 0],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', __('A menu item cannot be its own parent.'));
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->putJson('/api/v1/menu-items/reorder', [
            'menu_id' => '0195f51e-4a37-7087-b5f7-57d001537aba',
            'items' => [],
        ]);
    }
}
