<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Http\Middleware\RateLimitExceededHandler;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(RateLimitExceededHandler::class);

        $middleware->alias([
            'admin' => App\Http\Middleware\UserIsAdmin::class,
            'revisor' => App\Http\Middleware\UserIsRevisor::class,
            'writer' => App\Http\Middleware\UserIsWriter::class,
            'admin.local' => App\Http\Middleware\OnlyLocalAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            Log::warning('Attempt not authorized: Method not allowed. Method: ' . $request->method()
                . ', URL: ' . $request->fullUrl()
                . ', IP: ' . $request->ip()
                . ', User Agent: ' . $request->userAgent());

            return redirect(route('homepage'))->with('error', 'Not Authorized');
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            Log::warning(
                'Attempt not authorized: CSRF token mismatch. Method: ' . $request->method()
                . ', URL: ' . $request->fullUrl()
                . ', IP: ' . $request->ip()
                . ', User Agent: ' . $request->userAgent()
            );

            return redirect(route('homepage'))->with('error', 'Not Authorized');
        });
    })
    ->create();
