<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserTypeSeeder::class,
            UserSeeder::class,
            UserListSeeder::class,
            SettingValueTypeSeeder::class,
            SettingSeeder::class,
            PassportClientSeeder::class,
            ApplicationSeeder::class,
            MenuSeeder::class,
            MenuItemSeeder::class,
        ]);
    }
}
