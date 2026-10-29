<?php

namespace Tests\Feature;

use Tests\TestCase;

class RootEndpointTest extends TestCase
{
    public function test_root_returns_api_status(): void
    {
        $this->getJson('/')
            ->assertOk()
            ->assertExactJson([
                'name' => config('app.name'),
                'status' => 'ok',
            ]);
    }

    public function test_health_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
