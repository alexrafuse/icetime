<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ClubBylawResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'article_number' => $this->article_number,
            'content' => $this->content,
            'file_url' => $this->getFileUrl(),
            'version' => $this->version,
            'effective_date' => $this->effective_date?->toDateString(),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
