<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if (auth()->check()) {
                return back()->withInput($request->except('_token', 'password', 'password_confirmation'))->withErrors([
                    'error' => 'Sesi formulir sempat kedaluwarsa demi keamanan. Silakan simpan kembali.',
                ]);
            }

            return redirect()->route('login')->withErrors([
                'login' => 'Sesi formulir telah kedaluwarsa demi keamanan. Silakan login kembali.',
            ]);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if (auth()->check()) {
                    return back()->withInput($request->except('_token', 'password', 'password_confirmation'))->withErrors([
                        'error' => 'Sesi formulir sempat kedaluwarsa demi keamanan. Silakan simpan kembali.',
                    ]);
                }

                return redirect()->route('login')->withErrors([
                    'login' => 'Sesi formulir telah kedaluwarsa demi keamanan. Silakan login kembali.',
                ]);
            }
        });
    })->create();
