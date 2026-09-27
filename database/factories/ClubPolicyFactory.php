<?php

namespace Database\Factories;

use Domain\Board\Enums\DocumentStatus;
use Domain\Board\Models\ClubPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClubPolicyFactory extends Factory
{
    protected $model = ClubPolicy::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraphs(5, true),
            'file_path' => null,
            'version' => 1,
            'status' => DocumentStatus::DRAFT,
            'effective_date' => null,
            'parent_id' => null,
            'approved_by_user_id' => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => DocumentStatus::PUBLISHED,
            'published_at' => now(),
            'effective_date' => now()->toDateString(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => DocumentStatus::ARCHIVED]);
    }
}
