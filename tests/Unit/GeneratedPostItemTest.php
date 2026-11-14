<?php

namespace Tests\Unit;

use App\Features\Ai\DTO\GeneratedPostItem;
use PHPUnit\Framework\TestCase;

class GeneratedPostItemTest extends TestCase
{
    public function test_to_array_returns_all_fields(): void
    {
        $item = new GeneratedPostItem('Judul', 'Umum', 'Ringkasan', 'Isi');

        $this->assertSame(
            ['title' => 'Judul', 'category' => 'Umum', 'excerpt' => 'Ringkasan', 'content' => 'Isi'],
            $item->toArray()
        );
    }
}
