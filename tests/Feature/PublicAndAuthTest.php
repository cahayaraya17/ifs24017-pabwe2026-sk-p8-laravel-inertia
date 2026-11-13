<?php

namespace Tests\Feature;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicAndAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_home_and_index_show_only_published(): void
    {
        Post::factory(2)->create();
        Post::factory()->draft()->create();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Public/PostIndex')->where('hero', true)->has('posts.data', 2));
        $this->get('/articles')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->where('hero', false)->has('posts.data', 2));
    }

    public function test_search_and_category_filter(): void
    {
        Post::factory()->create(['title' => 'Belajar Laravel Inertia', 'category' => PostCategory::Teknologi]);
        Post::factory()->create(['title' => 'Memasak Rendang', 'category' => PostCategory::GayaHidup]);

        $this->get('/articles?q=laravel')->assertInertia(fn (Assert $p) => $p
            ->has('posts.data', 1)->where('filters.q', 'laravel'));
        $this->get('/articles?category=Gaya Hidup')->assertInertia(fn (Assert $p) => $p
            ->has('posts.data', 1)->where('filters.category', 'Gaya Hidup'));
        $this->get('/articles?category=Ngawur')->assertInertia(fn (Assert $p) => $p
            ->has('posts.data', 2)->where('filters.category', null));
    }

    public function test_show_article_and_unpublished_is_404(): void
    {
        $post = Post::factory()->create();
        $draft = Post::factory()->draft()->create();

        $this->get("/articles/{$post->slug}")->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Public/PostShow')->where('post.slug', $post->slug));
        $this->get("/articles/{$draft->slug}")->assertNotFound();
    }

    public function test_login_and_register_pages(): void
    {
        $this->get('/login')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Auth/Login'));
        $this->get('/register')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Auth/Register'));
    }

    public function test_register_creates_member_and_logs_in(): void
    {
        $this->post('/register', [
            'name' => 'Budi', 'username' => 'budi_1', 'email' => 'budi@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/dashboard/posts')->assertSessionHas('success');

        $this->assertAuthenticated();
        $this->assertSame('member', User::where('username', 'budi_1')->value('role'));
    }

    public function test_register_validation(): void
    {
        User::factory()->create(['username' => 'budi_1']);

        $this->post('/register', [
            'name' => '', 'username' => 'budi_1', 'email' => 'x', 'password' => '123',
        ])->assertSessionHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_login_redirects_by_role(): void
    {
        User::factory()->create(['username' => 'adm', 'password' => 'rahasia123', 'role' => 'admin']);
        User::factory()->create(['username' => 'mem', 'password' => 'rahasia123', 'role' => 'member']);

        $this->post('/login', ['username' => 'adm', 'password' => 'rahasia123'])
            ->assertRedirect('/admin/users');
        $this->post('/logout');
        $this->post('/login', ['username' => 'mem', 'password' => 'rahasia123'])
            ->assertRedirect('/dashboard/posts');
    }

    public function test_login_failure_and_logout(): void
    {
        User::factory()->create(['username' => 'mem', 'password' => 'rahasia123']);

        $this->post('/login', ['username' => 'mem', 'password' => 'salah'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();

        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_shared_props_for_logged_in_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['success' => 'ok'])->get('/')
            ->assertInertia(fn (Assert $p) => $p
                ->where('auth.user.id', $user->id)->where('flash.success', 'ok'));
    }
}