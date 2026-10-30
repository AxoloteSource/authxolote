<?php

namespace Tests\Feature\Menu;

use App\Enums\RoleEnum;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuShowTest extends TestCase
{
    public function test_it_can_show_a_menu(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();

        $response = $this->getJson('/api/v1/menus/'.$menu->slug);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $menu->id);
        $response->assertJsonPath('data.slug', $menu->slug);
        $response->assertJsonPath('data.name', $menu->name);
        $response->assertJsonPath('data.size', $menu->size->value);
    }

    public function test_it_returns_the_menu_items_as_a_tree(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->header()->create(['menu_id' => $menu->id]);
        MenuItem::factory()->create(['menu_id' => $menu->id, 'parent_id' => $parent->id]);

        $response = $this->getJson('/api/v1/menus/'.$menu->slug);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $menu->id);
        $response->assertJsonStructure(['data' => ['items' => ['*' => ['id', 'name', 'children']]]]);
    }

    public function test_it_returns_not_found_when_menu_does_not_exist(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->getJson('/api/v1/menus/'.fake()->slug());

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $menu = Menu::factory()->create();

        $this->expectException(AuthenticationException::class);

        $this->getJson('/api/v1/menus/'.$menu->slug);
    }

    public function test_it_hides_menu_items_not_linked_to_the_user_role(): void
    {
        $this->loginAdmin()->attachAction(['auth.menus.show']);

        $menu = Menu::factory()->create();
        $visible = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $hidden = MenuItem::factory()->create(['menu_id' => $menu->id]);

        $visible->roles()->attach(RoleEnum::Admin->value);

        $response = $this->getJson('/api/v1/menus/'.$menu->slug);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.items');
        $response->assertJsonPath('data.items.0.id', $visible->id);
    }

    public function test_it_hides_an_unlinked_parent_together_with_its_children(): void
    {
        $this->loginAdmin()->attachAction(['auth.menus.show']);

        $menu = Menu::factory()->create();
        $header = MenuItem::factory()->header()->create(['menu_id' => $menu->id]);
        MenuItem::factory()->create(['menu_id' => $menu->id, 'parent_id' => $header->id])
            ->roles()
            ->attach(RoleEnum::Admin->value);

        $response = $this->getJson('/api/v1/menus/'.$menu->slug);

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data.items');
    }

    public function test_it_shows_linked_children_within_a_linked_header(): void
    {
        $this->loginAdmin()->attachAction(['auth.menus.show']);

        $menu = Menu::factory()->create();
        $header = MenuItem::factory()->header()->create(['menu_id' => $menu->id]);
        $child = MenuItem::factory()->create(['menu_id' => $menu->id, 'parent_id' => $header->id]);

        $header->roles()->attach(RoleEnum::Admin->value);
        $child->roles()->attach(RoleEnum::Admin->value);

        $response = $this->getJson('/api/v1/menus/'.$menu->slug);

        $response->assertStatus(200);
        $response->assertJsonPath('data.items.0.id', $header->id);
        $response->assertJsonPath('data.items.0.children.0.id', $child->id);
    }
}
