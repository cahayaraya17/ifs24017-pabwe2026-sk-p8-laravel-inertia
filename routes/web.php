<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// SEMENTARA (Tahap 2): halaman React pertama untuk membuktikan Inertia jalan.
// Akan diganti dengan route publik di Tahap 4.
Route::get('/', fn () => Inertia::render('Welcome', [
    'appName' => config('app.name'),
]))->name('home');
