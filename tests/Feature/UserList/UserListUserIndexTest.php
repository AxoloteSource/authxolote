<?php

namespace Tests\Feature\UserList;

use App\Models\Role;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class UserListUserIndexTest extends TestCase
{
    public function test_it_returns_paginated_users_from_a_list(): void
    {
        $this->loginRoot();

        $users = User::factory()->count(3)->create();
        $userList = UserList::factory()->create();
        $userList->users()->sync($users->pluck('id'));

        $response = $this->getJson("/api/v1/user-lists/{$userList->id}/users");

        $response->assertStatus(206);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
            'current_page',
            'columns',
        ]);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_it_returns_empty_list_when_list_has_no_users(): void
    {
        $this->loginRoot();

        $userList = UserList::factory()->create();

        $response = $this->getJson("/api/v1/user-lists/{$userList->id}/users");

        $response->assertStatus(206);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_it_rejects_filters_on_hidden_columns(): void
    {
        $this->loginRoot();

        $firstRole = Role::factory()->create();
        $secondRole = Role::factory()->create();

        $first = User::factory()->create(['role_id' => $firstRole->id]);
        $second = User::factory()->create(['role_id' => $secondRole->id]);

        $userList = UserList::factory()->create();
        $userList->users()->sync([$first->id, $second->id]);

        $query = http_build_query([
            'filters' => [
                ['property' => 'role_id', 'value' => $firstRole->id, 'operator' => '='],
            ],
        ]);

        $response = $this->getJson("/api/v1/user-lists/{$userList->id}/users?{$query}");

        $response->assertStatus(422)
            ->assertJsonPath('message', __('The filter property ":property" is not allowed.', ['property' => 'role_id']))
            ->assertJsonPath('data', null);
    }

    public function test_it_rejects_disallowed_filter_operators(): void
    {
        $this->loginRoot();

        $first = User::factory()->create();
        $second = User::factory()->create();

        $userList = UserList::factory()->create();
        $userList->users()->sync([$first->id, $second->id]);

        $query = http_build_query([
            'filters' => [
                ['property' => 'name', 'value' => $first->name, 'operator' => 'regexp'],
            ],
        ]);

        $response = $this->getJson("/api/v1/user-lists/{$userList->id}/users?{$query}");

        $response->assertStatus(422)
            ->assertJsonPath('message', __('The filter operator ":operator" is not allowed.', ['operator' => 'regexp']))
            ->assertJsonPath('data', null);
    }

    public function test_it_rejects_more_than_ten_filters(): void
    {
        $this->loginRoot();

        $userList = UserList::factory()->create();

        $filters = [];

        for ($index = 0; $index < 11; $index++) {
            $filters[] = ['property' => 'name', 'value' => "name{$index}", 'operator' => '='];
        }

        $query = http_build_query(['filters' => $filters]);

        $response = $this->getJson("/api/v1/user-lists/{$userList->id}/users?{$query}");

        $response->assertStatus(422)
            ->assertJsonPath('message', __('Too many filters: the maximum is :max.', ['max' => 10]))
            ->assertJsonPath('data', null);
    }

    public function test_it_filters_by_allowed_properties(): void
    {
        $this->loginRoot();

        $first = User::factory()->create(['name' => 'Jane Doe']);
        $second = User::factory()->create(['name' => 'John Smith']);

        $userList = UserList::factory()->create();
        $userList->users()->sync([$first->id, $second->id]);

        $query = http_build_query([
            'filters' => [
                ['property' => 'name', 'value' => 'Jane Doe', 'operator' => '='],
            ],
        ]);

        $response = $this->getJson("/api/v1/user-lists/{$userList->id}/users?{$query}");

        $response->assertStatus(206);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($first->id, $response->json('data.0.id'));
    }

    public function test_it_returns_404_when_list_not_found(): void
    {
        $this->loginRoot();

        $response = $this->getJson('/api/v1/user-lists/99999/users');

        $response->assertStatus(404);
    }

    public function test_it_returns_all_users_when_list_slug_is_all_users(): void
    {
        $this->loginRoot();

        $users = User::factory()->count(3)->create();
        $userList = UserList::firstOrCreate(
            ['slug' => UserList::SLUG_ALL_USERS],
            UserList::factory()->make(['slug' => UserList::SLUG_ALL_USERS])->toArray(),
        );

        $response = $this->getJson("/api/v1/user-lists/{$userList->id}/users");

        $response->assertStatus(206);
        $this->assertCount(User::count(), $response->json('data'));
    }

    public function test_it_requires_authentication(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->getJson('/api/v1/user-lists/1/users');
    }
}
