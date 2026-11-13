<?php

namespace App\Features\Post\Controllers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PostController
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private PostService $service,
        private PostExcelService $excel,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Post/Index', [
            'posts' => $this->posts->forUser($request->user()),
            'stats' => $this->posts->statsFor($request->user()),
            'categories' => PostCategory::values(),
        ]);
    }

    public function show(Post $post): Response
    {
        return Inertia::render('Post/Show', ['post' => $post]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->service->create($request->user(), $this->validated($request), $request->file('cover_image'));

        return redirect()->route('dashboard.posts.index')->with('success', 'Artikel berhasil dibuat.');
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->service->update($post, $this->validated($request), $request->file('cover_image'));

        return redirect()->route('dashboard.posts.index')->with('success', 'Artikel berhasil diubah.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->service->delete($post);

        return redirect()->route('dashboard.posts.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function publish(Post $post): RedirectResponse
    {
        $post = $this->service->togglePublish($post);

        return back()->with('success', $post->is_published ? 'Artikel dipublikasikan.' : 'Artikel dijadikan draf.');
    }

    public function storeCover(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate(['cover_image' => ['required', 'image', 'max:2048']]);
        $this->service->setCover($post, $data['cover_image']);

        return back()->with('success', 'Foto sampul diperbarui.');
    }

    public function destroyCover(Post $post): RedirectResponse
    {
        $this->service->removeCover($post);

        return back()->with('success', 'Foto sampul dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        return $this->excel->download(
            $this->posts->forUser($user),
            "artikel-{$user->username}-".now()->format('Ymd').'.xlsx'
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:5120', 'extensions:xlsx,xls,csv']]);

        $count = rescue(fn () => $this->excel->import($request->user(), $request->file('file')), 0, false);

        if ($count === 0) {
            return back()->withErrors(['file' => 'Tidak ada artikel valid yang dapat diimpor dari file ini.']);
        }

        return redirect()->route('dashboard.posts.index')->with('success', "{$count} artikel berhasil diimpor.");
    }

    public function aiGenerate(Request $request, AiClientInterface $ai): RedirectResponse
    {
        $data = $request->validate([
            'topic' => ['required', 'string', 'max:200'],
            'count' => ['required', 'integer', 'min:1', 'max:5'],
            'category' => ['nullable', Rule::enum(PostCategory::class)],
        ]);

        try {
            $items = $ai->generatePosts($data['topic'], (int) $data['count'], $data['category'] ?? null);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'topic' => $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Tidak dapat terhubung ke OpenAI.',
            ]);
        }

        return back()->with('drafts', array_map(fn (GeneratedPostItem $i) => $i->toArray(), $items));
    }

    public function aiSave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:5'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.category' => ['required', Rule::enum(PostCategory::class)],
            'items.*.excerpt' => ['nullable', 'string', 'max:500'],
            'items.*.content' => ['required', 'string'],
        ]);

        $count = $this->service->saveGenerated($request->user(), $data['items']);

        return redirect()->route('dashboard.posts.index')
            ->with('success', "{$count} artikel AI disimpan sebagai draf.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(PostCategory::class)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ]);
        unset($data['cover_image']);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}