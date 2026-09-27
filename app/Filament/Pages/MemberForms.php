<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Shared\Enums\FormCategory;
use Domain\Shared\Models\Form;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class MemberForms extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.member-forms';

    protected static ?string $title = 'Forms';

    protected static ?string $navigationLabel = 'Forms';

    protected static string|\UnitEnum|null $navigationGroup = 'Members Area';

    protected static ?int $navigationSort = 8;

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

        return collect(FormCategory::cases())
            ->filter(fn (FormCategory $category) => isset($forms[$category->value]) && $forms[$category->value]->isNotEmpty())
            ->mapWithKeys(fn (FormCategory $category) => [
                $category->value => [
                    'category' => $category,
                    'forms' => $forms[$category->value],
                ],
            ])
            ->all();
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
