<?php

namespace Tests\Feature\MenuItem;

use App\Models\MenuItem;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class MenuItemIndexTest extends TestCase
{
    public function test_it_returns_paginated_menu_items(): void
    {
        $this->loginRoot();

        MenuItem::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/menu-items');

        $response->assertStatus(206);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'name',
                    'path',
                    'menu',
                    'active',
                    'created_at',
                ],
            ],
            'current_page',
            'columns',
        ]);
    }

    public function test_it_returns_only_top_level_items_by_default(): void
    {
        $this->loginRoot();

        $parent = MenuItem::factory()->create(['sort_order' => 0]);
        $child = MenuItem::factory()->create(['parent_id' => $parent->id]);

        $response = $this->getJson('/api/v1/menu-items');

        $response->assertStatus(206);
        $topLevelIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($parent->id, $topLevelIds);
        $this->assertNotContains($child->id, $topLevelIds);
    }

    public function test_it_returns_auth_menu_items_by_default(): void
    {
        $this->loginRoot();

        $response = $this->getJson('/api/v1/menu-items');

        $response->assertStatus(206);
        $response->assertJsonFragment(['name' => 'home']);
    }

    public function test_it_rejects_filters_on_hidden_columns(): void
    {
        $this->loginRoot();

        MenuItem::factory()->count(2)->create();

        $query = http_build_query([
            'filters' => [
                ['property' => 'deleted_at', 'value' => now()->toDateTimeString(), 'operator' => '='],
            ],
        ]);

        $response = $this->getJson("/api/v1/menu-items?{$query}");

        $response->assertStatus(422)
            ->assertJsonPath('message', __('The filter property ":property" is not allowed.', ['property' => 'deleted_at']))
            ->assertJsonPath('data', null);
    }

    public function test_it_still_filters_by_exposed_columns(): void
    {
        $this->loginRoot();

        MenuItem::factory()->create(['name' => 'Filtered Item']);

        $query = http_build_query([
            'filters' => [
                ['property' => 'name', 'value' => 'Filtered Item', 'operator' => '='],
            ],
        ]);

        $response = $this->getJson("/api/v1/menu-items?{$query}");

        $response->assertStatus(206);
        $response->assertJsonCount(1, 'data');
        $this->assertSame('Filtered Item', $response->json('data.0.name'));
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->getJson('/api/v1/menu-items');
    }
}
