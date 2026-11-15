<?php

namespace Tests\Feature\Application;

use App\Models\Application;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class ApplicationDeleteTest extends TestCase
{
    public function test_it_can_delete_an_application(): void
    {
        $this->loginRoot();

        $application = Application::factory()->create();

        $response = $this->deleteJson('/api/v1/applications/'.$application->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('applications', ['id' => $application->id]);
    }

    public function test_it_soft_deletes_its_menus_and_items(): void
    {
        $this->loginRoot();

        $application = Application::factory()->create();
        $otherApplication = Application::factory()->create();

        $menu = Menu::factory()->create(['application_id' => $application->id]);
        $otherMenu = Menu::factory()->create(['application_id' => $otherApplication->id]);

        $header = MenuItem::factory()->header()->create(['menu_id' => $menu->id]);
        $child = MenuItem::factory()->create(['menu_id' => $menu->id, 'parent_id' => $header->id]);
        $otherItem = MenuItem::factory()->create(['menu_id' => $otherMenu->id]);

        $response = $this->deleteJson('/api/v1/applications/'.$application->id);

        $response->assertStatus(204);

        $this->assertSoftDeleted('applications', ['id' => $application->id]);
        $this->assertSoftDeleted('menus', ['id' => $menu->id]);
        $this->assertSoftDeleted('menu_items', ['id' => $header->id]);
        $this->assertSoftDeleted('menu_items', ['id' => $child->id]);

        $this->assertNotSoftDeleted('menus', ['id' => $otherMenu->id]);
        $this->assertNotSoftDeleted('menu_items', ['id' => $otherItem->id]);
    }

    public function test_it_stops_serving_menus_after_application_deletion(): void
    {
        $this->loginRoot();

        $application = Application::factory()->create();
        $menu = Menu::factory()->create(['application_id' => $application->id]);
        MenuItem::factory()->create(['menu_id' => $menu->id]);

        $this->deleteJson('/api/v1/applications/'.$application->id)->assertStatus(204);

        $this->loginAdmin()->attachAction(['auth.menus.show']);

        $this->getJson('/api/v1/menus/'.$menu->slug)->assertStatus(404);
    }

    public function test_it_stops_serving_all_items_after_application_deletion(): void
    {
        $this->loginRoot();

        $application = Application::factory()->create();
        $menu = Menu::factory()->create(['application_id' => $application->id]);
        MenuItem::factory()->create(['menu_id' => $menu->id]);

        $this->deleteJson('/api/v1/applications/'.$application->id)->assertStatus(204);

        $this->loginAdmin()->attachAction(['auth.menus.show_all']);

        $this->getJson('/api/v1/menus/'.$menu->slug.'/all-items')->assertStatus(404);
    }

    public function test_it_hides_menus_of_applications_deleted_outside_the_lifecycle(): void
    {
        $this->loginRoot();

        $application = Application::factory()->create();
        $menu = Menu::factory()->create(['application_id' => $application->id]);

        Application::query()->whereKey($application->id)->update(['deleted_at' => now()]);

        $this->assertNotSoftDeleted('menus', ['id' => $menu->id]);

        $this->getJson('/api/v1/menus/'.$menu->slug)->assertStatus(404);
    }

    public function test_it_returns_not_found_when_application_does_not_exist(): void
    {
        $this->withExceptionHandling();
        $this->loginRoot();

        $response = $this->deleteJson('/api/v1/applications/'.fake()->uuid());

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $application = Application::factory()->create();

        $this->expectException(AuthenticationException::class);

        $this->deleteJson('/api/v1/applications/'.$application->id);
    }
}
