<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiPostGeneratorService implements AiClientInterface
{
    public function generatePosts(string $topic, int $count, ?string $category = null): array
    {
        $key = config('openai.api_key');
        if (! $key) {
            throw new RuntimeException('OPENAI_API_KEY belum diatur di file .env.');
        }

        $hint = $category ? " Kategori: {$category}." : '';

        $response = Http::withToken($key)
            ->timeout(120)
            ->post(rtrim(config('openai.base_url'), '/').'/chat/completions', [
                'model' => config('openai.model'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu penulis artikel blog profesional berbahasa Indonesia. Balas HANYA JSON: {"articles":[{"title":"...","category":"Teknologi|Pendidikan|Gaya Hidup|Bisnis|Opini|Umum","excerpt":"1-2 kalimat pemikat","content":"artikel lengkap format Markdown, minimal 4 paragraf dengan subjudul"}]}',
                    ],
                    ['role' => 'user', 'content' => "Buat {$count} artikel tentang: {$topic}.{$hint}"],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI menolak permintaan (HTTP '.$response->status().').');
        }

        $content = data_get($response->json(), 'choices.0.message.content', '{}');
        $articles = (json_decode($content, true) ?: [])['articles'] ?? [];

        $items = [];
        foreach ($articles as $article) {
            $title = trim((string) ($article['title'] ?? ''));
            $body = trim((string) ($article['content'] ?? ''));
            if ($title === '' || $body === '') {
                continue;
            }

            $cat = PostCategory::tryFrom((string) ($article['category'] ?? ''))
                ?? PostCategory::tryFrom((string) $category)
                ?? PostCategory::Umum;

            $items[] = new GeneratedPostItem(
                mb_substr($title, 0, 255),
                $cat->value,
                trim((string) ($article['excerpt'] ?? '')),
                $body,
            );
        }

        return array_slice($items, 0, $count);
    }
}