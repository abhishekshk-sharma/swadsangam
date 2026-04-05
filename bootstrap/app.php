<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\IdentifyTenant::class,
            \App\Http\Middleware\IdentifyBranch::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\IdentifyTenant::class,
            \App\Http\Middleware\IdentifyBranch::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/telegram/webhook',
        ]);
        $middleware->alias([
            'role'       => \App\Http\Middleware\CheckRole::class,
            'multi.auth' => \App\Http\Middleware\MultiGuardAuth::class,
            'api.role'   => \App\Http\Middleware\ApiRoleMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(fn() => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($request->is('api/*') && $e->getStatusCode() === 403) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        });
    })->create();
