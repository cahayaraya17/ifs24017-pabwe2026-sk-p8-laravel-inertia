<?php

use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\PostController;
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
|--------------------------------------------------------------------------
| Tahap 5: Dashboard Penulis (CRUD, Toggle Publikasi, Cover, Statistik)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:member,admin'])
    ->prefix('dashboard/posts')
    ->name('dashboard.posts.')
    ->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('index');
        Route::post('/', [PostController::class, 'store'])->name('store');

        // Rute khusus (Excel & AI - aktif penuh di Tahap 6 & 7)
        Route::get('/export', [PostController::class, 'export'])->name('export');
        Route::post('/import', [PostController::class, 'import'])->name('import');
        Route::post('/ai-generate', [PostController::class, 'aiGenerate'])->name('ai.generate');
        Route::post('/ai-save', [PostController::class, 'aiSave'])->name('ai.save');

        // Rute dengan validasi kepemilikan artikel (post.owner)
        Route::middleware('post.owner')->group(function () {
            Route::get('/{post}', [PostController::class, 'show'])->name('show');
            Route::put('/{post}', [PostController::class, 'update'])->name('update');
            Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');
            Route::patch('/{post}/publish', [PostController::class, 'publish'])->name('publish');
            Route::post('/{post}/cover', [PostController::class, 'storeCover'])->name('cover.store');
            Route::delete('/{post}/cover', [PostController::class, 'destroyCover'])->name('cover.destroy');
        });
    });

/*
| SEMENTARA (Tahap 3): halaman penampung agar redirect admin setelah login tidak 404.
| /admin/users diganti AdminUserController di Tahap 8.
*/
Route::get('/admin/users', fn () => Inertia::render('Tahap3/Panel', ['area' => 'Panel Admin']))
    ->middleware(['auth', 'role:admin'])
    ->name('admin.users');
