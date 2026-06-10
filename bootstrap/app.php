<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'staff.auth'       => \App\Http\Middleware\StaffAuth::class,
            'route.permission' => \App\Http\Middleware\CheckRoutePermission::class,
            'staff.api.auth'   => \App\Http\Middleware\StaffApiAuth::class,
        ]);

        // Terminable — runs after response is sent; logs every page visit + errors.
        $middleware->appendToGroup('web', \App\Http\Middleware\LogPageVisit::class);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        // Bind the current exception so LogPageVisit can capture the message.
        $exceptions->render(function (\Throwable $e) {
            app()->instance('current.page.exception', $e);
            return null; // continue with default Laravel error rendering
        });
    })->create();
