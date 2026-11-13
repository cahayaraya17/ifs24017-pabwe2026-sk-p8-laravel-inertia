<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController
{
    public function __construct(private PostRepositoryInterface $posts)
    {
    }

    public function home(Request $request): Response
    {
        return $this->render($request, true);
    }

    public function index(Request $request): Response
    {
        return $this->render($request, false);
    }

    public function show(string $slug): Response
    {
        return Inertia::render('Public/PostShow', [
            'post' => $this->posts->findPublishedBySlug($slug),
        ]);
    }

    private function render(Request $request, bool $hero): Response
    {
        $q = trim((string) $request->query('q', ''));
        $category = $request->query('category');
        $category = in_array($category, PostCategory::values(), true) ? $category : null;

        return Inertia::render('Public/PostIndex', [
            'posts' => $this->posts->paginatePublished($q !== '' ? $q : null, $category),
            'filters' => ['q' => $q, 'category' => $category],
            'categories' => PostCategory::values(),
            'hero' => $hero,
        ]);
    }
}