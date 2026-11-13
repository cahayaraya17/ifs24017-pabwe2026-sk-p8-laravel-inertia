<?php

namespace App\Features\Post\Services;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(private PostRepositoryInterface $posts)
    {
    }

    public function create(User $user, array $data, ?UploadedFile $cover = null): Post
    {
        $payload = $this->fields($data)
            + $this->publication((bool) ($data['is_published'] ?? false), null)
            + ['slug' => $this->uniqueSlug($data['title'])];

        if ($cover) {
            $payload['cover_image_path'] = $cover->store('covers', 'public');
        }

        return $this->posts->create($user, $payload);
    }

    public function update(Post $post, array $data, ?UploadedFile $cover = null): Post
    {
        $payload = $this->fields($data)
            + $this->publication((bool) ($data['is_published'] ?? false), $post);

        if ($data['title'] !== $post->title) {
            $payload['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }

        if ($cover) {
            $this->deleteFile($post->cover_image_path);
            $payload['cover_image_path'] = $cover->store('covers', 'public');
        }

        return $this->posts->update($post, $payload);
    }

    public function togglePublish(Post $post): Post
    {
        return $this->posts->update($post, $this->publication(! $post->is_published, $post));
    }

    public function setCover(Post $post, UploadedFile $file): Post
    {
        $this->deleteFile($post->cover_image_path);

        return $this->posts->update($post, ['cover_image_path' => $file->store('covers', 'public')]);
    }

    public function removeCover(Post $post): Post
    {
        $this->deleteFile($post->cover_image_path);

        return $this->posts->update($post, ['cover_image_path' => null]);
    }

    public function delete(Post $post): void
    {
        $this->deleteFile($post->cover_image_path);
        $this->posts->delete($post);
    }

    /** @param list<array{title: string, category: string, excerpt?: ?string, content: string}> $items */
    public function saveGenerated(User $user, array $items): int
    {
        foreach ($items as $item) {
            $this->create($user, [
                'title' => $item['title'],
                'category' => $item['category'],
                'excerpt' => $item['excerpt'] ?? null,
                'content' => $item['content'],
                'is_published' => false,
            ]);
        }

        return count($items);
    }

    private function fields(array $data): array
    {
        return [
            'title' => $data['title'],
            'category' => $data['category'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
        ];
    }

    private function publication(bool $publish, ?Post $post): array
    {
        return [
            'is_published' => $publish,
            'published_at' => $publish ? ($post?->published_at ?? now()) : null,
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $i = 2;

        while ($this->posts->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function deleteFile(?string $path): void
    {
        $path && Storage::disk('public')->delete($path);
    }
}