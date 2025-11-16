<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Shared\Enums\ResourceCategory;
use Domain\Shared\Models\Resource;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class MemberResources extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static string $view = 'filament.pages.member-resources';

    protected static ?string $title = 'Resources';

    protected static ?string $navigationLabel = 'Resources';

    protected static ?string $navigationGroup = 'Members Area';

    protected static ?int $navigationSort = 2;

    #[Url]
    public string $search = '';

    #[Url]
    public string $categoryFilter = '';

    public function getResourcesByCategory(): array
    {
        $query = Resource::query()
            ->where('is_active', true);

        // Apply visibility filter based on user role
        $user = auth()->user();
        if (! $user->hasAnyRole(['admin', 'staff'])) {
            $query->where('visibility', 'all');
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        // Apply category filter
        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        // Filter by validity dates
        $query->where(function ($q) {
            $q->whereNull('valid_from')
                ->orWhere('valid_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('valid_until')
                ->orWhere('valid_until', '>=', now());
        });

        $resources = $query->orderBy('priority')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Resource $resource) => $resource->category->value);

        $categorizedResources = [];

        // Define custom category order: General, Volunteer, Membership, then others
        $categoryOrder = [
            ResourceCategory::General,
            ResourceCategory::Volunteer,
            ResourceCategory::Membership,
            ResourceCategory::Events,
            ResourceCategory::Curriculum,
            ResourceCategory::Schedules,
            ResourceCategory::Rules,
        ];

        foreach ($categoryOrder as $category) {
            if (isset($resources[$category->value]) && $resources[$category->value]->isNotEmpty()) {
                $categorizedResources[$category->value] = [
                    'category' => $category,
                    'resources' => $resources[$category->value],
                ];
            }
        }

        return $categorizedResources;
    }

    public function updatedSearch(): void
    {
        // Automatically refresh when search changes
    }

    public function updatedCategoryFilter(): void
    {
        // Automatically refresh when category filter changes
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
    }
}
