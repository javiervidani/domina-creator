<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // No web group: no session, cookies or CSRF. The token is the only gate.
            Route::prefix('ops')->middleware(['throttle:30,1', App\Ops\OpsToken::class])->group(function (): void {
                Route::get('health', App\Ops\HealthController::class);
                Route::get('logs', App\Ops\LogsController::class);
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['tenant' => App\Tenancy\EnterTenancy::class]);
        // Route-model binding queries creator tables, so the schema must be entered before it runs (and after auth).
        $middleware->prependToPriorityList(
            before: Illuminate\Routing\Middleware\SubstituteBindings::class,
            prepend: App\Tenancy\EnterTenancy::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
