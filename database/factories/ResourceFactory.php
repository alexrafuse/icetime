<?php

namespace Database\Factories;

use App\Domain\Shared\Enums\ResourceCategory;
use Domain\Shared\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['url', 'file']);

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(ResourceCategory::cases()),
            'type' => $type,
            'url' => $type === 'url' ? $this->faker->url() : null,
            'file_path' => $type === 'file' ? 'resources/'.$this->faker->regexify('[a-z0-9]{10}').'.pdf' : null,
            'visibility' => $this->faker->randomElement(['all', 'admin_staff_only']),
            'priority' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function url(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'url',
            'url' => $this->faker->url(),
            'file_path' => null,
        ]);
    }

    public function file(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'file',
            'url' => null,
            'file_path' => 'resources/'.$this->faker->regexify('[a-z0-9]{10}').'.pdf',
        ]);
    }

    public function category(ResourceCategory $category): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }

    public function visibleToAll(): self
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'all',
        ]);
    }

    public function adminStaffOnly(): self
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'admin_staff_only',
        ]);
    }

    public function withValidity(): self
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('now', '+3 months'),
        ]);
    }
}
