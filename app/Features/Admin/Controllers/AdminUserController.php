<?php

namespace App\Features\Admin\Controllers;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Services\PostExcelService;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private PostExcelService $excel,
    ) {
    }

    public function index(): Response
    {
        $users = User::query()
            ->withCount([
                'posts',
                'posts as published_posts_count' => fn ($q) => $q->where('is_published', true),
            ])
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/Users', [
            'users' => $users->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'email' => $u->email,
                'role' => $u->role,
                'profile_photo_path' => $u->profile_photo_path,
                'posts_count' => $u->posts_count,
                'published_posts_count' => $u->published_posts_count,
            ])->values(),
        ]);
    }

    public function posts(User $user): Response
    {
        return Inertia::render('Admin/UserPosts', [
            'owner' => $user->only('id', 'name', 'username', 'email', 'role'),
            'posts' => $this->posts->forUser($user)->makeHidden('content')->values(),
            'stats' => $this->posts->statsFor($user),
        ]);
    }

    public function export(User $user): StreamedResponse
    {
        return $this->excel->download(
            $this->posts->forUser($user),
            "artikel-{$user->username}-".now()->format('Ymd').'.xlsx'
        );
    }
}
