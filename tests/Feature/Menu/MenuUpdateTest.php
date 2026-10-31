<?php

namespace Tests\Feature\Menu;

use App\Models\Menu;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuUpdateTest extends TestCase
{
    public function test_it_can_update_a_menu(): void
    {
        $this->loginRoot();

        $menu = Menu::factory()->create();

        $response = $this->putJson('/api/v1/menus/'.$menu->id, [
            'application_id' => $menu->application_id,
            'name' => 'Updated Menu',
            'slug' => $menu->slug,
            'size' => 'lg',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => 'Updated Menu',
            'size' => 'lg',
        ]);
        $response->assertJsonPath('data.size', 'lg');
    }

    public function test_it_returns_not_found_when_menu_does_not_exist(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->putJson('/api/v1/menus/'.fake()->uuid(), [
            'application_id' => fake()->uuid(),
            'name' => 'Updated Menu',
            'slug' => 'updated-menu',
        ]);

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $menu = Menu::factory()->create();

        $this->expectException(AuthenticationException::class);

        $this->putJson('/api/v1/menus/'.$menu->id, [
            'application_id' => $menu->application_id,
            'name' => 'Updated Menu',
            'slug' => $menu->slug,
        ]);
    }
}
