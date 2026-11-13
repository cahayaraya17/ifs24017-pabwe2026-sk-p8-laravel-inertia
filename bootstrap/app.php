<?php

use App\Http\Middleware\EnsurePostOwnership;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'role' => EnsureRole::class,
            'post.owner' => EnsurePostOwnership::class,
        ]);

        // Tamu yang membuka halaman terlindungi diarahkan ke /login,
        // pengguna yang sudah login tidak boleh membuka /login lagi.
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(
            fn ($request) => $request->user()?->role === 'admin' ? '/admin/users' : '/dashboard/posts'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
