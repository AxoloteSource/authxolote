<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::upsert([
            [
                'id' => '0195f520-9337-7101-984b-fcc9e27a6b63',
                'role_id' => RoleEnum::Root->value,
                'user_type_id' => UserTypeEnum::Human->value,
                'name' => env('ROOT_USER_NAME'),
                'email' => env('ROOT_USER_EMAIL'),
                'password' => bcrypt(env('ROOT_USER_PASSWORD')),
            ],
        ], ['id'], ['email', 'name', 'role_id', 'user_type_id', 'password']);
    }
}
