<?php

namespace Tests\Feature\User;

use App\Enums\RoleEnum;
use App\Models\User;
use Tests\TestCase;

class UserRootTest extends TestCase
{
    public function test_it_detects_root_and_non_root_users(): void
    {
        $root = User::factory()->create(['role_id' => RoleEnum::Root->value]);
        $admin = User::factory()->create(['role_id' => RoleEnum::Admin->value]);

        $this->assertTrue($root->isRoot());
        $this->assertFalse($admin->isRoot());
    }
}
