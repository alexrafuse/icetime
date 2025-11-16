<?php

declare(strict_types=1);

namespace Domain\Shared\Models;

use App\Domain\Shared\Enums\FormCategory;
use Database\Factories\FormFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Form extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return FormFactory::new();
    }

    protected $fillable = [
        'title',
        'description',
        'tally_form_url',
        'category',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => FormCategory::class,
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
