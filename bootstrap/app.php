<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission'          => PermissionMiddleware::class,
            'role'                => RoleMiddleware::class,
            'role_or_permission'  => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // 🔒 Spatie Permission Unauthorized
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Anda tidak punya akses.'
                ], 403);
            }
        });

        // 🔒 abort(403, '...') atau HttpException
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $e->getMessage() ?: 'Forbidden'
                ], $e->getStatusCode());
            }
        });

        // ⚠️ Validasi (422)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Validasi gagal',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // 🛑 Error lain (500)
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Terjadi kesalahan server',
                    'error'   => env('APP_DEBUG') ? $e->getMessage() : null, // detail hanya kalau debug true
                ], 500);
            }
        });

    })
    ->create();
