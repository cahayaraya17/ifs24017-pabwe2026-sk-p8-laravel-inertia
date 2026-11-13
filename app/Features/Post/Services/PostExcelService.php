<?php

namespace App\Features\Post\Services;

use App\Features\Post\Enums\PostCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostExcelService
{
    public const HEADERS = ['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status'];

    public function __construct(private PostService $posts)
    {
    }

    public function download(Collection $posts, string $filename, bool $withAuthor = false): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Artikel');

        $headers = self::HEADERS;
        if ($withAuthor) {
            $headers[] = 'Penulis';
        }
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($posts as $post) {
            $values = [
                $post->title,
                $post->category->value,
                $post->excerpt,
                $post->content,
                $post->is_published ? 'Terbit' : 'Draf',
            ];
            if ($withAuthor) {
                $values[] = $post->user?->name ?? '-';
            }
            $sheet->fromArray($values, null, "A{$row}");
            $row++;
        }

        $sheet->getStyle('A1:'.chr(64 + count($headers)).'1')->getFont()->setBold(true);

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function import(User $user, UploadedFile $file): int
    {
        $rows = IOFactory::load($file->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
        array_shift($rows);

        $count = 0;
        foreach ($rows as $row) {
            $title = trim((string) ($row[0] ?? ''));
            $content = trim((string) ($row[3] ?? ''));
            if ($title === '' || $content === '') {
                continue;
            }

            $category = PostCategory::tryFrom(trim((string) ($row[1] ?? ''))) ?? PostCategory::Umum;

            $this->posts->create($user, [
                'title' => mb_substr($title, 0, 255),
                'category' => $category->value,
                'excerpt' => ($row[2] ?? null) ?: null,
                'content' => $content,
                'is_published' => mb_strtolower(trim((string) ($row[4] ?? ''))) === 'terbit',
            ]);
            $count++;
        }

        return $count;
    }
}