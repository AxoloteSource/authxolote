<?php

namespace Tests;

use App\Enums\RoleEnum;
use App\Models\User;
use AxoloteSource\Logics\ErrorContainer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions, WithFaker;

    public static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (! self::$migrated) {
            self::refreshDatabase();
            self::$migrated = true;
        }
        ErrorContainer::resetErrors();
        Mail::fake();
        $this->withoutExceptionHandling();
    }

    public function refreshDatabase(): void
    {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
        $this->artisan('passport:client --personal --name="Laravel Personal Access Client"');
    }

    public function loginRoot(): User
    {
        return $this->createUser(RoleEnum::Root);
    }

    public function loginAdmin(): User
    {
        return $this->createUser(RoleEnum::Admin);
    }

    public function createUser(RoleEnum $role): User
    {
        $user = User::factory()->create(['role_id' => $role->value]);
        Passport::actingAs($user);

        return $user;
    }
}
