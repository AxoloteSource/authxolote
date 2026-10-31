<?php

namespace Tests\Feature\Menu;

use App\Models\Application;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuStoreTest extends TestCase
{
    public function test_it_can_create_a_menu(): void
    {
        $this->loginRoot();

        $application = Application::factory()->create();

        $response = $this->postJson('/api/v1/menus', [
            'application_id' => $application->id,
            'name' => 'Main Menu',
            'slug' => 'main-menu',
            'icon' => 'Menu',
            'size' => 'sm',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('menus', [
            'application_id' => $application->id,
            'name' => 'Main Menu',
            'slug' => 'main-menu',
            'size' => 'sm',
        ]);
        $response->assertJsonPath('data.size', 'sm');
    }

    public function test_it_defaults_the_menu_size_to_md(): void
    {
        $this->loginRoot();

        $application = Application::factory()->create();

        $response = $this->postJson('/api/v1/menus', [
            'application_id' => $application->id,
            'name' => 'Main Menu',
            'slug' => 'main-menu',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('menus', [
            'slug' => 'main-menu',
            'size' => 'md',
        ]);
    }

    public function test_it_requires_name(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $application = Application::factory()->create();

        $response = $this->postJson('/api/v1/menus', [
            'application_id' => $application->id,
            'slug' => 'sidebar',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_requires_application(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->postJson('/api/v1/menus', [
            'name' => 'Sidebar',
            'slug' => 'sidebar',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->postJson('/api/v1/menus', [
            'application_id' => Application::factory()->create()->id,
            'name' => 'Sidebar',
            'slug' => 'sidebar',
        ]);
    }
}
