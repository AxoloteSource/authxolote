<?php

namespace Tests\Feature\Menu;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuDeleteTest extends TestCase
{
    public function test_it_can_delete_a_menu(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();

        $response = $this->deleteJson('/api/v1/menus/'.$menu->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('menus', ['id' => $menu->id]);
    }

    public function test_it_soft_deletes_its_items(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $otherItem = MenuItem::factory()->create();

        $response = $this->deleteJson('/api/v1/menus/'.$menu->id);

        $response->assertStatus(204);

        $this->assertSoftDeleted('menus', ['id' => $menu->id]);
        $this->assertSoftDeleted('menu_items', ['id' => $item->id]);
        $this->assertNotSoftDeleted('menu_items', ['id' => $otherItem->id]);
    }

    public function test_it_returns_not_found_when_menu_does_not_exist(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->deleteJson('/api/v1/menus/'.fake()->uuid());

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $menu = Menu::factory()->create();

        $this->expectException(AuthenticationException::class);

        $this->deleteJson('/api/v1/menus/'.$menu->id);
    }
}
