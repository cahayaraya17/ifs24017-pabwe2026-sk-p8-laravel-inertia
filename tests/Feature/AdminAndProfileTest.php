<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AdminAndProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** Membaca isi file xlsx dari respons unduhan. */
    private function sheetRows($response): array
    {
        $base = tempnam(sys_get_temp_dir(), 'adm_');
        $file = $base.'.xlsx';
        file_put_contents($file, $response->streamedContent());
        $rows = IOFactory::load($file)->getActiveSheet()->toArray();
        @unlink($file);
        @unlink($base);

        return $rows;
    }

    public function test_admin_pages_are_forbidden_for_member_and_redirect_guest(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->get('/admin/users')->assertRedirect('/login');
        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->get('/admin/posts')->assertForbidden();
    }

    public function test_admin_can_list_users_with_post_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        Post::factory(2)->create(['user_id' => $member->id]);
        Post::factory()->draft()->create(['user_id' => $member->id]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users')
                ->has('users', 2)
                ->where('users.1.posts_count', 3)
                ->where('users.1.published_posts_count', 2)
            );
    }

    public function test_admin_can_view_and_export_posts_of_one_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member', 'username' => 'penulis_a']);
        Post::factory(2)->create(['user_id' => $member->id]);
        Post::factory()->draft()->create(['user_id' => $member->id]);
        Post::factory()->create();

        $this->actingAs($admin)
            ->get("/admin/users/{$member->id}/posts")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/UserPosts')
                ->where('owner.username', 'penulis_a')
                ->has('posts', 3)
                ->missing('posts.0.content')
                ->where('stats.total', 3)
                ->where('stats.done', 2)
                ->where('stats.open', 1)
            );

        $rows = $this->sheetRows(
            $this->actingAs($admin)->get("/admin/users/{$member->id}/posts/export")->assertOk()
        );

        $this->assertCount(4, $rows);
        $this->assertSame(['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status'], $rows[0]);
    }

    public function test_admin_can_view_and_export_all_posts_with_author(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'member', 'name' => 'Penulis Satu']);
        Post::factory(2)->create(['user_id' => $author->id]);
        Post::factory()->draft()->create(['user_id' => $author->id]);

        $this->actingAs($admin)
            ->get('/admin/posts')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Posts')
                ->has('posts', 3)
                ->where('stats.total', 3)
                ->where('stats.done', 2)
                ->where('stats.open', 1)
            );

        $rows = $this->sheetRows($this->actingAs($admin)->get('/admin/posts/export')->assertOk());

        $this->assertCount(4, $rows);
        $this->assertSame('Penulis', $rows[0][5]);
        $this->assertSame('Penulis Satu', $rows[1][5]);
    }

    public function test_profile_edit_page_shows_current_user(): void
    {
        $user = User::factory()->create(['username' => 'profil_uji']);

        $this->get('/profile')->assertRedirect('/login');

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Edit')
                ->where('profile.username', 'profil_uji')
            );
    }

    public function test_profile_can_be_updated_with_password_and_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'Nama Lama', 'email' => 'lama@example.com']);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Nama Baru',
                'email' => 'baru@example.com',
                'password' => 'passwordbaru1',
                'password_confirmation' => 'passwordbaru1',
                'profile_photo' => UploadedFile::fake()->image('foto1.jpg'),
            ])
            ->assertRedirect('/profile')
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@example.com', $user->email);
        $this->assertTrue(Hash::check('passwordbaru1', $user->password));
        $firstPhoto = $user->profile_photo_path;
        $this->assertNotNull($firstPhoto);
        Storage::disk('public')->assertExists($firstPhoto);

        // Unggah foto kedua: foto lama dihapus, password tidak berubah.
        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Nama Baru',
                'email' => 'baru@example.com',
                'profile_photo' => UploadedFile::fake()->image('foto2.png'),
            ])
            ->assertRedirect('/profile');

        $user->refresh();
        Storage::disk('public')->assertMissing($firstPhoto);
        Storage::disk('public')->assertExists($user->profile_photo_path);
        $this->assertTrue(Hash::check('passwordbaru1', $user->password));
    }

    public function test_profile_update_validation(): void
    {
        User::factory()->create(['email' => 'dipakai@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', ['name' => '', 'email' => 'dipakai@example.com'])
            ->assertSessionHasErrors(['name', 'email']);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Valid',
                'email' => 'valid@example.com',
                'password' => 'abcdefgh',
                'password_confirmation' => 'berbeda',
            ])
            ->assertSessionHasErrors('password');
    }
}
