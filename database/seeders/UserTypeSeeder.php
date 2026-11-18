<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    public function run(): void
    {
        UserType::upsert([
            [
                'id' => UserTypeEnum::Human->value,
                'name' => 'Human',
                'key' => 'human',
            ],
            [
                'id' => UserTypeEnum::App->value,
                'name' => 'App',
                'key' => 'app',
            ],
        ], ['id'], ['name', 'key']);
    }
}
