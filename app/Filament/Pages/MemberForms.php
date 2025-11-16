<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Shared\Enums\FormCategory;
use Domain\Shared\Models\Form;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class MemberForms extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.member-forms';

    protected static ?string $title = 'Forms';

    protected static ?string $navigationLabel = 'Forms';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    #[Url]
    public string $search = '';

    #[Url]
    public string $categoryFilter = '';

    public function getFormsByCategory(): array
    {
        $query = Form::query()
            ->where('is_active', true);

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

        $forms = $query->orderBy('priority')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Form $form) => $form->category->value);

        $categorizedForms = [];

        foreach (FormCategory::cases() as $category) {
            if (isset($forms[$category->value]) && $forms[$category->value]->isNotEmpty()) {
                $categorizedForms[$category->value] = [
                    'category' => $category,
                    'forms' => $forms[$category->value],
                ];
            }
        }

        return $categorizedForms;
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
