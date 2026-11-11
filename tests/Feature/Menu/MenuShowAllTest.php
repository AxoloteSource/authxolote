<?php

namespace Tests\Feature\Menu;

use App\Enums\RoleEnum;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuShowAllTest extends TestCase
{
    public function test_it_returns_all_menu_items_regardless_of_role(): void
    {
        $this->loginAdmin()->attachAction(['auth.menus.show_all']);

        $menu = Menu::factory()->create();
        $linked = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $unlinked = MenuItem::factory()->create(['menu_id' => $menu->id]);

        $linked->roles()->attach(RoleEnum::Admin->value);

        $response = $this->getJson('/api/v1/menus/'.$menu->slug.'/all-items');

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $menu->id);
        $response->assertJsonCount(2, 'data.items');
    }

    public function test_it_flags_items_without_the_requested_role(): void
    {
        $this->loginAdmin()->attachAction(['auth.menus.show_all']);

        $menu = Menu::factory()->create();
        $linked = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $unlinked = MenuItem::factory()->create(['menu_id' => $menu->id]);

        $linked->roles()->attach(RoleEnum::Admin->value);

        $response = $this->getJson('/api/v1/menus/'.$menu->slug.'/all-items?role_id='.RoleEnum::Admin->value);

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.items');

        $items = collect($response->json('data.items'));

        $linkedItem = $items->firstWhere('id', $linked->id);
        $unlinkedItem = $items->firstWhere('id', $unlinked->id);

        $this->assertTrue($linkedItem['has_role']);
        $this->assertFalse($unlinkedItem['has_role']);
    }

    public function test_it_flags_all_items_as_visible_without_role(): void
    {
        $this->loginAdmin()->attachAction(['auth.menus.show_all']);

        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $menu->id]);

        $response = $this->getJson('/api/v1/menus/'.$menu->slug.'/all-items');

        $response->assertStatus(200);
        $response->assertJsonPath('data.items.0.has_role', true);
    }

    public function test_it_returns_not_found_when_menu_does_not_exist(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->getJson('/api/v1/menus/'.fake()->slug().'/all-items');

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $menu = Menu::factory()->create();

        $this->expectException(AuthenticationException::class);

        $this->getJson('/api/v1/menus/'.$menu->slug.'/all-items');
    }
}
