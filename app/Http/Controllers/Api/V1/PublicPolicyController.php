<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubPolicyResource;
use Domain\Board\Models\ClubPolicy;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PublicPolicyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $policies = ClubPolicy::query()
            ->published()
            ->currentVersions()
            ->orderBy('title')
            ->get();

        return ClubPolicyResource::collection($policies);
    }

    public function show(string $slug): ClubPolicyResource
    {
        $policy = ClubPolicy::query()
            ->published()
            ->where('slug', $slug)
            ->latest('version')
            ->firstOrFail();

        return new ClubPolicyResource($policy);
    }
}
