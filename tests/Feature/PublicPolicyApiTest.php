<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\Board\Models\ClubPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPolicyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_only_published_policies(): void
    {
        ClubPolicy::factory()->published()->create(['title' => 'Published Policy']);
        ClubPolicy::factory()->create(['title' => 'Draft Policy']);
        ClubPolicy::factory()->archived()->create(['title' => 'Archived Policy']);

        $response = $this->getJson('/api/v1/policies');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Published Policy']);
        $response->assertJsonMissing(['title' => 'Draft Policy']);
        $response->assertJsonMissing(['title' => 'Archived Policy']);
    }

    public function test_show_returns_published_policy_by_slug(): void
    {
        $policy = ClubPolicy::factory()->published()->create([
            'title' => 'Safety Policy',
            'slug' => 'safety-policy',
        ]);

        $response = $this->getJson('/api/v1/policies/safety-policy');

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => 'Safety Policy',
            'slug' => 'safety-policy',
        ]);
    }

    public function test_show_returns_404_for_draft_policy(): void
    {
        ClubPolicy::factory()->create(['slug' => 'draft-policy']);

        $response = $this->getJson('/api/v1/policies/draft-policy');

        $response->assertNotFound();
    }

    public function test_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/v1/policies/nonexistent');

        $response->assertNotFound();
    }

    public function test_response_includes_expected_fields(): void
    {
        ClubPolicy::factory()->published()->create([
            'title' => 'Test Policy',
            'slug' => 'test-policy',
            'version' => 2,
        ]);

        $response = $this->getJson('/api/v1/policies/test-policy');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'slug',
                'content',
                'file_url',
                'version',
                'effective_date',
                'published_at',
            ],
        ]);
    }
}
