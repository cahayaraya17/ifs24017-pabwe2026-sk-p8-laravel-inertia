<?php

namespace Tests\Feature;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthorPostDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_author_can_view_dashboard_with_own_posts_and_stats(): void
    {
        $author = User::factory()->create(['role' => 'member']);
        $other = User::factory()->create(['role' => 'member']);

        Post::factory()->create(['user_id' => $author->id, 'is_published' => true]);
        Post::factory()->draft()->create(['user_id' => $author->id]);
        Post::factory()->create(['user_id' => $other->id]);

        $this->actingAs($author)
            ->get('/dashboard/posts')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Post/Index')
                ->has('posts', 2)
                ->where('stats.total', 2)
                ->where('stats.done', 1)
                ->where('stats.open', 1)
                ->has('categories', count(PostCategory::values()))
            );
    }

    public function test_author_can_create_update_toggle_publish_and_delete_post(): void
    {
        Storage::fake('public');
        $author = User::factory()->create(['role' => 'member']);

        // Create
        $this->actingAs($author)
            ->post('/dashboard/posts', [
                'title' => 'Artikel Tahap Lima',
                'category' => 'Teknologi',
                'excerpt' => 'Ringkasan singkat artikel.',
                'content' => '# Konten Markdown',
                'is_published' => true,
                'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            ])
            ->assertRedirect('/dashboard/posts')
            ->assertSessionHas('success');

        $post = Post::where('title', 'Artikel Tahap Lima')->firstOrFail();
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->cover_image_path);
        Storage::disk('public')->assertExists($post->cover_image_path);

        // Show detail
        $this->actingAs($author)
            ->get("/dashboard/posts/{$post->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Post/Show')
                ->where('post.id', $post->id)
            );

        // Toggle publish -> draft
        $this->actingAs($author)
            ->patch("/dashboard/posts/{$post->id}/publish")
            ->assertRedirect();
        $this->assertFalse($post->fresh()->is_published);

        // Update
        $this->actingAs($author)
            ->put("/dashboard/posts/{$post->id}", [
                'title' => 'Artikel Tahap Lima Revisi',
                'category' => 'Pendidikan',
                'excerpt' => 'Ringkasan diperbarui.',
                'content' => 'Isi diperbarui.',
                'is_published' => true,
            ])
            ->assertRedirect('/dashboard/posts');
        $this->assertSame('Artikel Tahap Lima Revisi', $post->fresh()->title);

        // Remove cover & re-upload cover
        $oldPath = $post->fresh()->cover_image_path;
        $this->actingAs($author)->delete("/dashboard/posts/{$post->id}/cover")->assertRedirect();
        Storage::disk('public')->assertMissing($oldPath);
        $this->assertNull($post->fresh()->cover_image_path);

        $this->actingAs($author)
            ->post("/dashboard/posts/{$post->id}/cover", [
                'cover_image' => UploadedFile::fake()->image('new-cover.png'),
            ])
            ->assertRedirect();
        $this->assertNotNull($post->fresh()->cover_image_path);

        // Delete
        $this->actingAs($author)
            ->delete("/dashboard/posts/{$post->id}")
            ->assertRedirect('/dashboard/posts');
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_author_cannot_manage_other_users_post(): void
    {
        $owner = User::factory()->create(['role' => 'member']);
        $intruder = User::factory()->create(['role' => 'member']);
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->get("/dashboard/posts/{$post->id}")
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete("/dashboard/posts/{$post->id}")
            ->assertForbidden();
    }
}
