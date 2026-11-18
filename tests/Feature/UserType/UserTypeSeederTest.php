<?php

namespace Tests\Feature\UserType;

use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Models\User;
use App\Models\UserType;
use Database\Seeders\UserTypeSeeder;
use Tests\TestCase;

class UserTypeSeederTest extends TestCase
{
    public function test_user_types_are_seeded(): void
    {
        $this->assertDatabaseHas('user_types', [
            'id' => UserTypeEnum::Human->value,
            'key' => 'human',
        ]);

        $this->assertDatabaseHas('user_types', [
            'id' => UserTypeEnum::App->value,
            'key' => 'app',
        ]);
    }

    public function test_root_user_is_human(): void
    {
        $root = User::query()
            ->where('role_id', RoleEnum::Root->value)
            ->firstOrFail();

        $this->assertSame(UserTypeEnum::Human->value, $root->user_type_id);
    }

    public function test_running_the_seeder_twice_does_not_duplicate_user_types(): void
    {
        $count = UserType::query()->count();

        $this->seed(UserTypeSeeder::class);

        $this->assertSame($count, UserType::query()->count());
    }
}
