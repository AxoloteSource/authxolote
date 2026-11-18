<?php

namespace App\Http\Middleware;

use AxoloteSource\Logics\Enums\Http;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EnsureUserIsNotBanned
{
    /**
     * @param  Closure(Request): (SymfonyResponse)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if ($request->user()?->isBanned()) {
            return Response::json([
                'status' => 'error',
                'message' => __('Your account has been banned.'),
                'data' => null,
            ], Http::Forbidden->value);
        }

        return $next($request);
    }
}
