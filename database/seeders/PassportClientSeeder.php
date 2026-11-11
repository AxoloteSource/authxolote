<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class PassportClientSeeder extends Seeder
{
    public function run(ClientRepository $clients): void
    {
        if (Client::query()->where('password_client', true)->where('revoked', false)->exists()) {
            return;
        }

        $clients->createPasswordGrantClient(
            null,
            'Laravel Password Grant Client',
            'http://localhost',
            'users',
        );
    }
}
