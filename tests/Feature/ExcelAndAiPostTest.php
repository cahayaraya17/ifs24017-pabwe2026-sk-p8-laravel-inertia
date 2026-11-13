<?php

namespace Tests\Feature;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelAndAiPostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_author_can_export_own_posts_to_excel(): void
    {
        $author = User::factory()->create(['username' => 'penulis_uji', 'role' => 'member']);
        $other = User::factory()->create(['role' => 'member']);

        Post::factory()->create([
            'user_id' => $author->id,
            'title' => 'Artikel Ekspor Pertama',
            'is_published' => true,
        ]);
        Post::factory()->create([
            'user_id' => $other->id,
            'title' => 'Artikel Milik Orang Lain',
        ]);

        $response = $this->actingAs($author)->get('/dashboard/posts/export');

        $response->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $response->headers->get('content-type')
        );

        $tmpFile = tempnam(sys_get_temp_dir(), 'export_').'.xlsx';
        file_put_contents($tmpFile, $response->streamedContent());

        $rows = IOFactory::load($tmpFile)->getActiveSheet()->toArray();
        @unlink($tmpFile);

        $this->assertSame(['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status'], $rows[0]);
        $this->assertCount(2, $rows);
        $this->assertSame('Artikel Ekspor Pertama', $rows[1][0]);
        $this->assertSame('Terbit', $rows[1][4]);
    }

    public function test_author_can_import_posts_from_excel_and_handles_invalid_file(): void
    {
        $author = User::factory()->create(['role' => 'member']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status'],
            ['Artikel Impor 1', 'Teknologi', 'Ringkasan satu', 'Isi lengkap satu', 'Terbit'],
            ['Artikel Impor 2', 'KategoriTidakAda', '', 'Isi lengkap dua', 'Draf'],
            ['', 'Umum', 'Tanpa judul', 'Harus dilewati', 'Draf'],
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'import_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tmpFile);

        $upload = new UploadedFile(
            $tmpFile,
            'artikel.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($author)
            ->post('/dashboard/posts/import', ['file' => $upload])
            ->assertRedirect('/dashboard/posts')
            ->assertSessionHas('success', '2 artikel berhasil diimpor.');

        @unlink($tmpFile);

        $this->assertDatabaseHas('posts', [
            'user_id' => $author->id,
            'title' => 'Artikel Impor 1',
            'category' => 'Teknologi',
            'is_published' => true,
        ]);
        $this->assertDatabaseHas('posts', [
            'user_id' => $author->id,
            'title' => 'Artikel Impor 2',
            'category' => 'Umum',
            'is_published' => false,
        ]);

        // File kosong / hanya header gagal impor
        $emptySheet = new Spreadsheet();
        $emptySheet->getActiveSheet()->fromArray([['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status']]);
        $emptyPath = tempnam(sys_get_temp_dir(), 'empty_').'.xlsx';
        (new Xlsx($emptySheet))->save($emptyPath);

        $emptyUpload = new UploadedFile(
            $emptyPath,
            'kosong.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($author)
            ->post('/dashboard/posts/import', ['file' => $emptyUpload])
            ->assertSessionHasErrors('file');

        @unlink($emptyPath);
    }

    public function test_author_can_generate_and_save_ai_drafts(): void
    {
        $author = User::factory()->create(['role' => 'member']);

        $this->mock(AiClientInterface::class, function ($mock) {
            $mock->shouldReceive('generatePosts')
                ->once()
                ->with('Laravel Inertia', 2, 'Teknologi')
                ->andReturn([
                    new GeneratedPostItem('Judul AI 1', 'Teknologi', 'Ringkasan AI 1', 'Isi panjang AI 1'),
                    new GeneratedPostItem('Judul AI 2', 'Teknologi', 'Ringkasan AI 2', 'Isi panjang AI 2'),
                ]);
        });

        $this->actingAs($author)
            ->post('/dashboard/posts/ai-generate', [
                'topic' => 'Laravel Inertia',
                'count' => 2,
                'category' => 'Teknologi',
            ])
            ->assertRedirect()
            ->assertSessionHas('drafts');

        $this->actingAs($author)
            ->post('/dashboard/posts/ai-save', [
                'items' => [
                    [
                        'title' => 'Judul AI 1',
                        'category' => 'Teknologi',
                        'excerpt' => 'Ringkasan AI 1',
                        'content' => 'Isi panjang AI 1',
                    ],
                    [
                        'title' => 'Judul AI 2',
                        'category' => 'Teknologi',
                        'excerpt' => 'Ringkasan AI 2',
                        'content' => 'Isi panjang AI 2',
                    ],
                ],
            ])
            ->assertRedirect('/dashboard/posts')
            ->assertSessionHas('success', '2 artikel AI disimpan sebagai draf.');

        $this->assertDatabaseHas('posts', [
            'user_id' => $author->id,
            'title' => 'Judul AI 1',
            'is_published' => false,
        ]);
    }

    public function test_openai_service_handles_missing_key_and_http_response(): void
    {
        $author = User::factory()->create(['role' => 'member']);

        config(['openai.api_key' => null]);
        $this->actingAs($author)
            ->post('/dashboard/posts/ai-generate', ['topic' => 'Testing', 'count' => 1])
            ->assertSessionHasErrors(['topic' => 'OPENAI_API_KEY belum diatur di file .env.']);

        config(['openai.api_key' => 'sk-test-key']);
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'articles' => [[
                                'title' => 'Artikel Dari HTTP Fake',
                                'category' => 'Bisnis',
                                'excerpt' => 'Ringkasan fake',
                                'content' => 'Konten lengkap dari OpenAI',
                            ]],
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $this->actingAs($author)
            ->post('/dashboard/posts/ai-generate', ['topic' => 'Bisnis Digital', 'count' => 1])
            ->assertSessionHas('drafts');
    }
}
