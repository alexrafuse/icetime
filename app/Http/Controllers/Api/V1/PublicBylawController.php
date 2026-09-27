<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubBylawResource;
use Domain\Board\Models\ClubBylaw;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PublicBylawController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $bylaws = ClubBylaw::query()
            ->published()
            ->currentVersions()
            ->orderedByArticle()
            ->get();

        return ClubBylawResource::collection($bylaws);
    }

    public function show(string $slug): ClubBylawResource
    {
        $bylaw = ClubBylaw::query()
            ->published()
            ->where('slug', $slug)
            ->latest('version')
            ->firstOrFail();

        return new ClubBylawResource($bylaw);
    }
}
