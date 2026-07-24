<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cloudflare Tunnel terminates HTTPS before forwarding the request to
        // the origin. Trust its forwarded headers so Laravel keeps the public
        // HTTPS scheme when generating absolute URLs and redirects.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX
        );

         $middleware->alias([
            'superadmin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'admin' => \App\Http\Middleware\CheckAdmin::class,
            'subbagian' => \App\Http\Middleware\SubBagianMiddleware::class,
            'nocache' => \App\Http\Middleware\NoCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
