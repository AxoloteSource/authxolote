<?php

namespace Tests\Feature\Menu;

use App\Models\Application;
use App\Models\Menu;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuIndexTest extends TestCase
{
    public function test_it_returns_paginated_menus(): void
    {
        $this->loginRoot();

        Menu::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/menus');

        $response->assertStatus(206);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'application',
                    'items_count',
                    'active',
                    'created_at',
                ],
            ],
            'current_page',
            'columns',
        ]);
    }

    public function test_it_can_search_menus_by_name(): void
    {
        $this->loginRoot();

        Menu::factory()->create(['name' => 'Header']);
        Menu::factory()->create(['name' => 'Topbar']);

        $response = $this->getJson('/api/v1/menus?search=topbar');

        $response->assertStatus(206);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Topbar', $response->json('data.0.name'));
    }

    public function test_it_can_order_by_application_name(): void
    {
        $this->loginRoot();

        $firstApplication = Application::factory()->create(['name' => 'Aaa App']);
        $lastApplication = Application::factory()->create(['name' => 'Zzz App']);

        Menu::factory()->create(['application_id' => $firstApplication->id]);
        Menu::factory()->create(['application_id' => $lastApplication->id]);

        $response = $this->getJson('/api/v1/menus?order_by=application.name&order=desc');

        $response->assertStatus(206);
        $this->assertSame('Zzz App', $response->json('data.0.application'));
        $this->assertNotNull($response->json('data.0.items_count'));
    }

    public function test_it_returns_auth_menu_by_default(): void
    {
        $this->loginRoot();

        $response = $this->getJson('/api/v1/menus');

        $response->assertStatus(206);
        $response->assertJsonFragment(['slug' => 'auth']);
    }

    public function test_it_rejects_filters_on_hidden_columns(): void
    {
        $this->loginRoot();

        Menu::factory()->count(2)->create();

        $query = http_build_query([
            'filters' => [
                ['property' => 'deleted_at', 'value' => now()->toDateTimeString(), 'operator' => '='],
            ],
        ]);

        $response = $this->getJson("/api/v1/menus?{$query}");

        $response->assertStatus(422)
            ->assertJsonPath('message', __('The filter property ":property" is not allowed.', ['property' => 'deleted_at']))
            ->assertJsonPath('data', null);
    }

    public function test_it_still_filters_by_exposed_columns(): void
    {
        $this->loginRoot();

        Menu::factory()->create(['name' => 'Filtered Menu']);

        $query = http_build_query([
            'filters' => [
                ['property' => 'name', 'value' => 'Filtered Menu', 'operator' => '='],
            ],
        ]);

        $response = $this->getJson("/api/v1/menus?{$query}");

        $response->assertStatus(206);
        $response->assertJsonCount(1, 'data');
        $this->assertSame('Filtered Menu', $response->json('data.0.name'));
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->getJson('/api/v1/menus');
    }
}
