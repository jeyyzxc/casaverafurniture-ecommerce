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
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom middleware
        $middleware->alias([
            'admin.only' => \App\Http\Middleware\AdminOnly::class,
        ]);

        // Redirect guest users to a JSON response instead of a named route
        $middleware->redirectTo(
            guests: fn () => response()->json(['message' => 'Unauthenticated.'], 401)
        );

        // No stateful API - using pure token-based authentication
        // This avoids CSRF requirements for API routes
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
