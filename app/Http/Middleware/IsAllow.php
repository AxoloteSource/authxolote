<?php

namespace App\Http\Middleware;

use AxoloteSource\Logics\Enums\Http;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAllow
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action): Response
    {
        if (! $request->user() || ! method_exists($request->user(), 'belongsToAction') || ! $request->user()->belongsToAction($action)) {
            if ($request->expectsJson()) {
                return \Illuminate\Support\Facades\Response::json([
                    'message' => __('You do not have permission to access this resource'),
                    'data' => ['action' => $action],
                ], Http::Forbidden->value);
            }

            abort(Http::Forbidden->value, __('You do not have permission to access this resource'));
        }

        return $next($request);
    }
}
