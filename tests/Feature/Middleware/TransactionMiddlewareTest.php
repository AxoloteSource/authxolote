<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\TransactionMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Support\TransactionMiddleware as TestTransactionMiddleware;
use Tests\TestCase;

class TransactionMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(TransactionMiddleware::class, TestTransactionMiddleware::class);
    }

    public function test_it_rolls_back_writes_on_4xx_responses(): void
    {
        Route::post('/_test/transaction-rollback', function () {
            DB::table('password_reset_tokens')->insert([
                'email' => 'rollback@example.com',
                'token' => 'rollback-token',
                'created_at' => now(),
            ]);

            return response()->json(['status' => 'error'], 422);
        })->middleware('transaction');

        $this->postJson('/_test/transaction-rollback')->assertStatus(422);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'rollback@example.com',
        ]);
    }

    public function test_it_commits_writes_on_successful_responses(): void
    {
        Route::post('/_test/transaction-commit', function () {
            DB::table('password_reset_tokens')->insert([
                'email' => 'commit@example.com',
                'token' => 'commit-token',
                'created_at' => now(),
            ]);

            return response()->json(['status' => 'OK'], 201);
        })->middleware('transaction');

        $this->postJson('/_test/transaction-commit')->assertStatus(201);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'commit@example.com',
        ]);
    }
}
