<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostServiceExtraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        $author = User::factory()->create();
        $payload = ['title' => 'Judul Kembar', 'category' => 'Umum', 'content' => 'Isi artikel.'];

        $this->actingAs($author)->post('/dashboard/posts', $payload)->assertRedirect('/dashboard/posts');
        $this->actingAs($author)->post('/dashboard/posts', $payload)->assertRedirect('/dashboard/posts');

        $this->assertSame(
            ['judul-kembar', 'judul-kembar-2'],
            Post::query()->orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_store_validation_errors(): void
    {
        $author = User::factory()->create();

        $this->actingAs($author)
            ->post('/dashboard/posts', ['title' => ''])
            ->assertSessionHasErrors(['title', 'category', 'content']);
    }

    public function test_update_replaces_cover_and_keeps_slug_when_title_unchanged(): void
    {
        Storage::fake('public');
        $author = User::factory()->create();

        $this->actingAs($author)->post('/dashboard/posts', [
            'title' => 'Artikel Bersampul',
            'category' => 'Umum',
            'content' => 'Isi.',
            'cover_image' => UploadedFile::fake()->image('lama.jpg'),
        ]);

        $post = Post::query()->firstOrFail();
        $oldPath = $post->cover_image_path;
        $slug = $post->slug;
        $this->assertNotNull($oldPath);

        $this->actingAs($author)
            ->post("/dashboard/posts/{$post->id}", [
                '_method' => 'PUT',
                'title' => 'Artikel Bersampul',
                'category' => 'Bisnis',
                'content' => 'Isi baru.',
                'cover_image' => UploadedFile::fake()->image('baru.jpg'),
            ])
            ->assertRedirect('/dashboard/posts');

        $post->refresh();
        $this->assertSame($slug, $post->slug);
        $this->assertNotSame($oldPath, $post->cover_image_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($post->cover_image_path);
    }

    public function test_toggle_publish_turns_draft_into_published(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->draft()->create(['user_id' => $author->id]);

        $this->actingAs($author)->patch("/dashboard/posts/{$post->id}/publish")->assertRedirect();

        $post->refresh();
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_deleting_post_removes_its_cover_file(): void
    {
        Storage::fake('public');
        $author = User::factory()->create();

        $this->actingAs($author)->post('/dashboard/posts', [
            'title' => 'Akan Dihapus',
            'category' => 'Umum',
            'content' => 'Isi.',
            'cover_image' => UploadedFile::fake()->image('hapus.jpg'),
        ]);

        $post = Post::query()->firstOrFail();
        $path = $post->cover_image_path;

        $this->actingAs($author)->delete("/dashboard/posts/{$post->id}")->assertRedirect('/dashboard/posts');

        Storage::disk('public')->assertMissing($path);
    }
}
