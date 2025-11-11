<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Shared\Models\DrawDocument;
use Illuminate\Database\Seeder;

class DrawDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $drawDocuments = [
            [
                'title' => 'Tuesday League Draw 2025-2026',
                'day_of_week' => 2,
                'file_path' => 'draws/2025/tuesday.pdf',
                'valid_from' => '2025-10-01',
                'valid_until' => '2026-03-31',
            ],
            [
                'title' => 'Wednesday League Draw 2025-2026',
                'day_of_week' => 3,
                'file_path' => 'draws/2025/wednesday.pdf',
                'valid_from' => '2025-10-01',
                'valid_until' => '2026-03-31',
            ],
            [
                'title' => 'Thursday League Draw 2025-2026',
                'day_of_week' => 4,
                'file_path' => 'draws/2025/thursday.pdf',
                'valid_from' => '2025-10-01',
                'valid_until' => '2026-03-31',
            ],
            [
                'title' => 'Friday League Draw 2025-2026',
                'day_of_week' => 5,
                'file_path' => 'draws/2025/friday.pdf',
                'valid_from' => '2025-10-01',
                'valid_until' => '2026-03-31',
            ],
        ];

        foreach ($drawDocuments as $document) {
            DrawDocument::query()->create($document);
        }
    }
}
