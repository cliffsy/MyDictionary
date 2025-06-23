<?php

use App\Http\Middleware\ValidToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\TranslatableResponse;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            Route::prefix("api/auth")
                ->group(base_path("app/Http/Routes/AuthRoutes.php"));

            $route = Route::prefix('api')->middleware('auth:sanctum');
            $dir = new DirectoryIterator(base_path("app/Http/Routes"));
            foreach ($dir as $fileInfo) {
                if ($fileInfo == "AuthRoutes.php") continue;
                if (!$fileInfo->isDot()) {
                    $route->group(base_path("app/Http/Routes/$fileInfo"));
                }
            }
        }
    )
    ->withMiddleware(function () {
        return [
            \Illuminate\Http\Middleware\HandleCors::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ];
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'failed',
                    'statusCode' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Please sign in again',
                    'data' => null,
                ], Response::HTTP_UNAUTHORIZED);
            }
        });
    })->create();
