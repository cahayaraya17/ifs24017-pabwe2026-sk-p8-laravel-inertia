<?php

namespace App\Features\Post\Repositories;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository implements PostRepositoryInterface
{
    private const LIST_COLUMNS = [
        'id', 'user_id', 'title', 'slug', 'category',
        'excerpt', 'cover_image_path', 'published_at',
    ];

    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator
    {
        return Post::query()
            ->select(self::LIST_COLUMNS)
            ->published()
            ->with('user:id,name,username')
            ->when($search, fn (Builder $q) => $q->where(function (Builder $w) use ($search) {
                $term = '%'.mb_strtolower($search).'%';
                $w->whereRaw('LOWER(title) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(content) LIKE ?', [$term]);
            }))
            ->when($category, fn (Builder $q) => $q->where('category', $category))
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): Post
    {
        return Post::query()
            ->published()
            ->with('user:id,name,username')
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function forUser(User $user): Collection
    {
        return Post::query()->where('user_id', $user->id)->orderByDesc('id')->get();
    }

    public function all(): Collection
    {
        return Post::query()->with('user:id,name,username')->orderByDesc('id')->get();
    }

    public function statsFor(User $user): array
    {
        $total = Post::query()->where('user_id', $user->id)->count();
        $published = Post::query()->where('user_id', $user->id)->where('is_published', true)->count();

        return ['total' => $total, 'done' => $published, 'open' => $total - $published];
    }

    public function create(User $user, array $data): Post
    {
        return $user->posts()->create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post;
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Post::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }
}