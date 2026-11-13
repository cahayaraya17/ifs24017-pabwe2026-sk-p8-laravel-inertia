<?php

use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\PublicPostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Tahap 4: Halaman publik (tanpa login)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPostController::class, 'home'])->name('home');
Route::get('/articles', [PublicPostController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [PublicPostController::class, 'show'])->name('articles.show');

/*
|--------------------------------------------------------------------------
| Tahap 3: Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
| SEMENTARA (Tahap 3): halaman penampung agar redirect setelah login tidak 404.
| /dashboard/posts diganti PostController di Tahap 5,
| /admin/users diganti AdminUserController di Tahap 8.
*/
Route::get('/dashboard/posts', fn () => Inertia::render('Tahap3/Panel', ['area' => 'Dashboard Penulis']))
    ->middleware(['auth', 'role:member,admin'])
    ->name('dashboard.posts');

Route::get('/admin/users', fn () => Inertia::render('Tahap3/Panel', ['area' => 'Panel Admin']))
    ->middleware(['auth', 'role:admin'])
    ->name('admin.users');
