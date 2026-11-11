<?php

use App\Console\Commands\AttachRolesCommand;
use App\Http\Middleware\IsAllow;
use AxoloteSource\Logics\Middleware\SetHeaders;
use AxoloteSource\Logics\Middleware\TransactionMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (Application $app) {
            Route::get('/', fn () => Response::json([
                'name' => config('app.name'),
                'status' => 'ok',
            ]));

            Route::get('/docs', function () {
                $documentation = public_path('docs/index.html');

                abort_unless(file_exists($documentation), 404);

                return response()->file($documentation);
            })->name('docs');

            Route::prefix('/api/v1')
                ->middleware(['setHeaders', 'api', 'transaction'])
                ->group(base_path('routes/api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'isAllow' => IsAllow::class,
            'setHeaders' => SetHeaders::class,
            'transaction' => TransactionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e) {
            return Response::error(
                null,
                $e->errors(),
            );
        });
    })->withCommands([
        AttachRolesCommand::class,
    ])->create();
