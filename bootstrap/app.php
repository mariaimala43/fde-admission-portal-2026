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
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->statefulApi();
        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'api.key'    => \App\Http\Middleware\ApiKeyMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Validation errors → 422 with field-level details
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'data'    => null,
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // Model not found → 404
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found.',
                    'data'    => null,
                    'errors'  => null,
                ], 404);
            }
        });

        // QueryException on the nfemis connection → 503
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') && str_contains($e->getMessage(), 'nfemis')) {
                return response()->json([
                    'success' => false,
                    'message' => 'NFEMIS database temporarily unavailable.',
                    'data'    => null,
                    'errors'  => null,
                ], 503);
            }
        });

        // All other exceptions on API routes → 500
        // Skip exceptions that have their own handlers above (they run in LIFO order).
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (!$request->is('api/*')) {
                return null; // let the default handler deal with non-API routes
            }
            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                || $e instanceof \Illuminate\Database\QueryException
            ) {
                return null; // handled by the more-specific renderers above
            }
            \Illuminate\Support\Facades\Log::error('API unhandled exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'data'    => null,
                'errors'  => null,
            ], 500);
        });
    })->create();
