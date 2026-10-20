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
        // Short name for our role check: ->middleware('role:admin')
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Not logged in -> login page. Already logged in -> home page.
        // Online customers (/shop) have their own login, separate from staff.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('shop*') ? route('shop.login') : route('login'));
        $middleware->redirectUsersTo(fn (Request $request) => $request->is('shop*') ? route('shop.index') : route('dashboard'));

        // PayHere posts the payment result from its own server, without our CSRF token
        $middleware->validateCsrfTokens(except: ['payhere/notify']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 419 "Page Expired" (old form / session timed out): go back with a friendly message
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            $expired = $e instanceof \Illuminate\Session\TokenMismatchException
                || ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() === 419);

            if (! $expired || $request->expectsJson()) {
                return null;
            }

            $message = 'The page expired. Please try again.';

            $response = redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', 'approver_password', '_token'))
                ->with('error', $message);

            // The login page shows errors under the username box
            return $request->is('login') ? $response->withErrors(['username' => $message]) : $response;
        });
    })->create();
