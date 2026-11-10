<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\PreventBackHistory;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // FITUR TC-SEC-05: Pasang middleware anti-bfcache pada semua web request agar halaman terlindungi setelah logout
        $middleware->appendToGroup('web', [
            PreventBackHistory::class,
        ]);
        $middleware->alias([
            'active.account' => EnsureAccountIsActive::class,
            'password.changed' => EnsurePasswordIsChanged::class,
            'prevent-back-history' => PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
