<?php

namespace Tests\Support;

use Illuminate\Http\Request;

class TransactionMiddleware extends \App\Http\Middleware\TransactionMiddleware
{
    /**
     * The base middleware skips transactions during tests. This variant keeps
     * the route bypasses but exercises the real transaction pipeline.
     */
    protected function shouldBypassTransaction(Request $request): bool
    {
        return $request->isMethod('get')
            || $this->isTransactionBypassRoute($request);
    }
}
