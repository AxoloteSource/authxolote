<?php

namespace Tests\Feature\User;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    public function test_it_returns_paginated_users(): void
    {
        $this->loginRoot();

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(206);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'user_type',
                    'user_type_name',
                    'created_at',
                ],
            ],
            'current_page',
            'columns',
        ]);

        $columns = collect($response->json('columns'));
        $this->assertTrue($columns->contains(fn (array $column) => $column['accessor'] === 'user_type_name'));
    }

    public function test_it_can_search_users_by_email(): void
    {
        $this->loginRoot();

        User::factory()->create(['email' => 'test@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        $response = $this->getJson('/api/v1/users?search=test@example.com');

        $response->assertStatus(206);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('test@example.com', $response->json('data.0.email'));
    }

    public function test_it_filters_users_by_user_type(): void
    {
        $this->loginRoot();

        User::factory()->create([
            'email' => 'app@example.com',
            'user_type_id' => UserTypeEnum::App->value,
        ]);
        User::factory()->create([
            'email' => 'human@example.com',
            'user_type_id' => UserTypeEnum::Human->value,
        ]);

        $response = $this->getJson('/api/v1/users?filters[0][property]=user_type&filters[0][value]=app');

        $response->assertStatus(206);
        $response->assertJsonCount(1, 'data');
        $this->assertSame('app@example.com', $response->json('data.0.email'));
        $this->assertSame(UserTypeEnum::App->value, $response->json('data.0.user_type_id'));
        $this->assertSame('App', $response->json('data.0.user_type_name'));
        $this->assertSame('app', $response->json('data.0.user_type.key'));
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->getJson('/api/v1/users');
    }
}
