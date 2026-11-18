<?php

namespace App\Http\Middleware;

use AxoloteSource\Logics\Middleware\TransactionMiddleware as BaseTransactionMiddleware;
use Illuminate\Http\Request;

class TransactionMiddleware extends BaseTransactionMiddleware
{
    /**
     * Routes whose failure responses intentionally persist database changes.
     *
     * @var array<int, string>
     */
    protected array $bypassPaths = [
        'api/v1/otp/challenges/*/verify',
        'api/v1/reset-password',
    ];

    protected function shouldBypassTransaction(Request $request): bool
    {
        return parent::shouldBypassTransaction($request)
            || $this->isTransactionBypassRoute($request);
    }

    protected function isTransactionBypassRoute(Request $request): bool
    {
        return $request->is(...$this->bypassPaths);
    }
}
