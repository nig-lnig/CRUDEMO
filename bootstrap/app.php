<?php

use App\Http\Middleware\EnsureLocalEnvironment;
use App\Http\Middleware\InspectDemoRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'local.only' => EnsureLocalEnvironment::class,
            'demo.inspect' => InspectDemoRequest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // L'API répond toujours en JSON
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        // Doublon détecté par la contrainte UNIQUE de la base → 409 Conflict
        $exceptions->render(function (UniqueConstraintViolationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Conflit : cette valeur existe déjà (contrainte UNIQUE).',
                ], 409);
            }
        });

        // 404, 403... : réponse JSON propre, sans trace de débogage
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }
        });
    })->create();
