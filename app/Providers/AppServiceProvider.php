<?php

namespace App\Providers;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Repositories\PostRepository;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Controller meminta PostRepositoryInterface, Laravel memberi PostRepository.
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
    }

    public function boot(): void
    {
        if (config('app.force_https')) {
            URL::forceHttps();
        }
    }
}
