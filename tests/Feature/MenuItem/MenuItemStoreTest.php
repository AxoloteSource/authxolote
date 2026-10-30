<?php

namespace Tests\Feature\MenuItem;

use App\Models\Application;
use App\Models\Menu;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuItemStoreTest extends TestCase
{
    public function test_it_can_create_a_menu_item(): void
    {
        $this->loginRoot();

        $menu = $this->createMenu();
        $slug = fake()->unique()->slug();

        $response = $this->postJson('/api/v1/menu-items', [
            'menu_id' => $menu->id,
            'type' => 'link',
            'name' => 'Inicio',
            'slug' => $slug,
            'path' => '/home',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->id,
            'name' => 'Inicio',
            'slug' => $slug,
            'path' => '/home',
        ]);
    }

    public function test_it_can_create_a_header_item(): void
    {
        $this->loginRoot();

        $menu = $this->createMenu();
        $slug = fake()->unique()->slug();

        $response = $this->postJson('/api/v1/menu-items', [
            'menu_id' => $menu->id,
            'type' => 'header',
            'name' => 'Catálogos',
            'slug' => $slug,
            'icon' => 'Database',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->id,
            'name' => 'Catálogos',
            'slug' => $slug,
            'type' => 'header',
        ]);
    }

    public function test_it_can_create_a_section_item(): void
    {
        $this->loginRoot();

        $menu = $this->createMenu();
        $slug = fake()->unique()->slug();

        $response = $this->postJson('/api/v1/menu-items', [
            'menu_id' => $menu->id,
            'type' => 'section',
            'name' => 'Salamandr IA',
            'slug' => $slug,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->id,
            'name' => 'Salamandr IA',
            'slug' => $slug,
            'type' => 'section',
        ]);
    }

    public function test_it_requires_slug(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $menu = $this->createMenu();

        $response = $this->postJson('/api/v1/menu-items', [
            'menu_id' => $menu->id,
            'type' => 'link',
            'name' => 'Inicio',
            'path' => '/home',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_rejects_a_link_item_without_path_or_route(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $menu = $this->createMenu();

        $response = $this->postJson('/api/v1/menu-items', [
            'menu_id' => $menu->id,
            'type' => 'link',
            'name' => 'Inicio',
            'slug' => fake()->unique()->slug(),
        ]);

        $response->assertStatus(422);
    }

    public function test_it_rejects_a_header_item_with_path(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $menu = $this->createMenu();

        $response = $this->postJson('/api/v1/menu-items', [
            'menu_id' => $menu->id,
            'type' => 'header',
            'name' => 'Inicio',
            'slug' => fake()->unique()->slug(),
            'path' => '/home',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_rejects_a_section_item_with_path(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $menu = $this->createMenu();

        $response = $this->postJson('/api/v1/menu-items', [
            'menu_id' => $menu->id,
            'type' => 'section',
            'name' => 'Inicio',
            'slug' => fake()->unique()->slug(),
            'path' => '/home',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $menu = $this->createMenu();

        $this->expectException(AuthenticationException::class);

        $this->postJson('/api/v1/menu-items', [
            'menu_id' => $menu->id,
            'type' => 'link',
            'name' => 'Inicio',
            'slug' => 'inicio',
            'path' => '/home',
        ]);
    }

    private function createMenu(): Menu
    {
        return Menu::query()->create([
            'application_id' => Application::factory()->create()->id,
            'name' => 'Menu '.fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
        ]);
    }
}
