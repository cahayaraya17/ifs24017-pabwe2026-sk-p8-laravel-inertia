<?php

namespace App\Features\Post\Contracts;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface
{
    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): Post;

    public function forUser(User $user): Collection;

    /** Semua artikel sistem beserta penulisnya (untuk admin). */
    public function all(): Collection;

    /** @return array{total: int, done: int, open: int} */
    public function statsFor(User $user): array;

    public function create(User $user, array $data): Post;

    public function update(Post $post, array $data): Post;

    public function delete(Post $post): void;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;
}