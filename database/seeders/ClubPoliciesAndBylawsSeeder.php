<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Board\Enums\DocumentStatus;
use Domain\Board\Models\ClubBylaw;
use Domain\Board\Models\ClubPolicy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ClubPoliciesAndBylawsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPolicies();
        $this->seedBylaws();
    }

    private function seedPolicies(): void
    {
        $policiesPath = storage_path('app/seed-content/policies');

        if (! File::isDirectory($policiesPath)) {
            $this->command->warn('Policies content directory not found at: '.$policiesPath);

            return;
        }

        collect(File::files($policiesPath))
            ->filter(fn ($file) => $file->getExtension() === 'md')
            ->each(function ($file) {
                $raw = File::get($file->getPathname());
                $parsed = $this->parseFrontmatter($raw);

                ClubPolicy::create([
                    'title' => $parsed['title'],
                    'slug' => Str::slug($parsed['title']),
                    'content' => $parsed['content'],
                    'version' => 1,
                    'status' => DocumentStatus::PUBLISHED,
                    'effective_date' => now()->toDateString(),
                    'published_at' => now(),
                ]);
            });
    }

    private function seedBylaws(): void
    {
        $bylawsPath = storage_path('app/seed-content/bylaws');

        if (! File::isDirectory($bylawsPath)) {
            $this->command->warn('Bylaws content directory not found at: '.$bylawsPath);

            return;
        }

        $bylawFile = $bylawsPath.'/bylaws.md';

        if (! File::exists($bylawFile)) {
            return;
        }

        $raw = File::get($bylawFile);
        $parsed = $this->parseFrontmatter($raw);
        $sections = $this->splitBylawSections($parsed['content']);

        collect($sections)->each(fn (array $section) => ClubBylaw::create([
            'title' => $section['title'],
            'slug' => Str::slug($section['title']),
            'article_number' => $section['article_number'],
            'content' => $section['content'],
            'version' => 1,
            'status' => DocumentStatus::PUBLISHED,
            'effective_date' => now()->toDateString(),
            'published_at' => now(),
        ]));
    }

    private function parseFrontmatter(string $raw): array
    {
        $title = '';
        $content = $raw;

        if (str_starts_with($raw, '---')) {
            $parts = explode('---', $raw, 3);
            if (count($parts) >= 3) {
                preg_match('/title:\s*["\']?(.+?)["\']?\s*$/m', $parts[1], $matches);
                $title = $matches[1] ?? '';
                $content = trim($parts[2]);
            }
        }

        return ['title' => $title, 'content' => $content];
    }

    private function splitBylawSections(string $content): array
    {
        $sections = [];
        $parts = preg_split('/^## (\d+)\.\s+(.+)$/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

        $i = 1;
        while ($i < count($parts)) {
            $number = $parts[$i] ?? '';
            $title = $parts[$i + 1] ?? '';
            $body = trim($parts[$i + 2] ?? '');

            $sections[] = [
                'article_number' => "Article {$number}",
                'title' => $title,
                'content' => $body,
            ];

            $i += 3;
        }

        return $sections;
    }
}
