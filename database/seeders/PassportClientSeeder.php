<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class PassportClientSeeder extends Seeder
{
    public function run(ClientRepository $clients): void
    {
        if (! Client::query()->where('password_client', true)->where('revoked', false)->exists()) {
            $clients->createPasswordGrantClient(
                null,
                'Laravel Password Grant Client',
                'http://localhost',
                'users',
            );
        }

        if (! Client::query()->where('personal_access_client', true)->where('revoked', false)->exists()) {
            $clients->createPersonalAccessClient(
                null,
                'Laravel Personal Access Client',
                'http://localhost',
            );
        }
    }
}
