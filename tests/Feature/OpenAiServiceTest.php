<?php

namespace Tests\Feature;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** Isi respons OpenAI (array), dipakai oleh Http::sequence()->push(). */
    private function openAiBody(string $content): array
    {
        return ['choices' => [['message' => ['content' => $content]]]];
    }

    public function test_generate_requires_valid_input(): void
    {
        $author = User::factory()->create();

        $this->actingAs($author)
            ->post('/dashboard/posts/ai-generate', ['count' => 9])
            ->assertSessionHasErrors(['topic', 'count']);
    }

    public function test_controller_reports_unexpected_errors(): void
    {
        $author = User::factory()->create();

        $this->mock(AiClientInterface::class, function ($mock) {
            $mock->shouldReceive('generatePosts')->once()->andThrow(new Exception('jaringan putus'));
        });

        $this->actingAs($author)
            ->post('/dashboard/posts/ai-generate', ['topic' => 'Apa saja', 'count' => 1])
            ->assertSessionHasErrors(['topic' => 'Tidak dapat terhubung ke OpenAI.']);
    }

    public function test_controller_reports_openai_http_failure(): void
    {
        $author = User::factory()->create();
        config(['openai.api_key' => 'sk-test']);
        Http::fake(['*' => Http::response([], 500)]);

        $this->actingAs($author)
            ->post('/dashboard/posts/ai-generate', ['topic' => 'Apa saja', 'count' => 1])
            ->assertSessionHasErrors(['topic' => 'OpenAI menolak permintaan (HTTP 500).']);
    }

    public function test_service_parses_articles_and_applies_fallbacks(): void
    {
        config(['openai.api_key' => 'sk-test']);

        Http::fake(['*' => Http::sequence()
            ->push($this->openAiBody(json_encode(['articles' => [
                ['title' => '', 'content' => 'Tanpa judul, dilewati'],
                ['title' => 'Artikel A', 'category' => 'Ngawur', 'content' => 'Isi A'],
                ['title' => 'Artikel B', 'category' => 'Bisnis', 'excerpt' => 'Ringkas B', 'content' => 'Isi B'],
            ]])))
            ->push($this->openAiBody(json_encode(['articles' => [
                ['title' => 'Artikel C', 'content' => 'Isi C'],
            ]])))
            ->push($this->openAiBody('bukan json'))
        ]);

        $service = app(OpenAiPostGeneratorService::class);

        $items = $service->generatePosts('Topik', 5, 'Opini');
        $this->assertCount(2, $items);
        $this->assertSame('Opini', $items[0]->category);
        $this->assertSame('Bisnis', $items[1]->category);
        $this->assertSame('Ringkas B', $items[1]->excerpt);

        $items = $service->generatePosts('Topik', 1);
        $this->assertCount(1, $items);
        $this->assertSame('Umum', $items[0]->category);

        $this->assertSame([], $service->generatePosts('Topik', 1));
    }
}
