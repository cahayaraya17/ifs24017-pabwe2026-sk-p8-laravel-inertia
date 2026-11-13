<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'username', 'role', 'profile_photo_path'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'drafts' => fn () => $request->session()->get('drafts'),
            ],
        ];
    }
}