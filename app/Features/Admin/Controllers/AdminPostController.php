<?php

namespace App\Features\Admin\Controllers;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Services\PostExcelService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPostController
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private PostExcelService $excel,
    ) {
    }

    public function index(): Response
    {
        $all = $this->posts->all();
        $published = $all->where('is_published', true)->count();

        return Inertia::render('Admin/Posts', [
            'posts' => $all->makeHidden('content')->values(),
            'stats' => [
                'total' => $all->count(),
                'done' => $published,
                'open' => $all->count() - $published,
            ],
        ]);
    }

    public function export(): StreamedResponse
    {
        return $this->excel->download(
            $this->posts->all(),
            'artikel-semua-'.now()->format('Ymd').'.xlsx',
            true
        );
    }
}
