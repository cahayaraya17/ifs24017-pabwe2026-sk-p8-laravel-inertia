<?php

use App\Http\Middleware\EnsurePostOwnership;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\URL;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aplikasi berjalan di belakang reverse proxy (HTTPS dihentikan di proxy).
        // Percayai header X-Forwarded-* agar Laravel tahu permintaan memakai https.
        $middleware->trustProxies(at: '*');

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

// Di server (host selain localhost) semua URL dipaksa https, supaya redirect
// dan permintaan XHR Inertia tidak diblokir browser sebagai mixed content.
// Dilewati di terminal/tes dan saat dijalankan lokal.
$app->booted(function ($app): void {
    if ($app->runningInConsole()) {
        return;
    }

    if (! in_array(request()->getHost(), ['localhost', '127.0.0.1'], true)) {
        URL::forceHttps();
    }
});

return $app;