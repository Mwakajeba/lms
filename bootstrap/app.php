<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'company.scope' => \App\Http\Middleware\CompanyScopeMiddleware::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'apply.settings' => \App\Http\Middleware\ApplySystemSettings::class,
            'set.locale' => \App\Http\Middleware\SetLocale::class,
            'subscription.check' => \App\Http\Middleware\CheckSubscriptionStatus::class,
        ]);

        // Apply system settings globally
        $middleware->append(\App\Http\Middleware\ApplySystemSettings::class);

        // Set locale globally
        $middleware->append(\App\Http\Middleware\SetLocale::class);

        // Check subscription status globally (except for auth routes)
        $middleware->append(\App\Http\Middleware\CheckSubscriptionStatus::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
