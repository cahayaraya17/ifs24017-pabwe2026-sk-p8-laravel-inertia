<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsurePostOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        Gate::authorize('manage', $request->route('post'));

        return $next($request);
    }
}