<?php

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\App\Http\Middleware\GraphqlEmptyQueryHandler::class);
        $middleware->prependToGroup('api', \App\Http\Middleware\ForceJsonResponse::class);

        $middleware->alias([
            'iae.key' => \App\Http\Middleware\CheckIaeKey::class,
            'iae.sso' => \App\Http\Middleware\VerifyJwtSso::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return ApiResponse::error('Validasi gagal.', 422, $e->errors());
            }

            if ($e instanceof HttpException) {
                return ApiResponse::error(
                    $e->getMessage() ?: 'Terjadi kesalahan pada permintaan.',
                    $e->getStatusCode()
                );
            }

            $message = config('app.debug')
                ? $e->getMessage()
                : 'Terjadi kesalahan pada server.';

            return ApiResponse::error($message, 500);
        });
    })->create();
