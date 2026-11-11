<?php

namespace Tests\Feature\MenuItem;

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
