<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\Board\Models\ClubBylaw;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBylawApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_only_published_bylaws(): void
    {
        ClubBylaw::factory()->published()->create(['title' => 'Published Bylaw']);
        ClubBylaw::factory()->create(['title' => 'Draft Bylaw']);

        $response = $this->getJson('/api/v1/bylaws');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Published Bylaw']);
        $response->assertJsonMissing(['title' => 'Draft Bylaw']);
    }

    public function test_index_returns_bylaws_ordered_by_article_number(): void
    {
        ClubBylaw::factory()->published()->create(['article_number' => 'Article 03', 'slug' => 'bylaw-3']);
        ClubBylaw::factory()->published()->create(['article_number' => 'Article 01', 'slug' => 'bylaw-1']);
        ClubBylaw::factory()->published()->create(['article_number' => 'Article 02', 'slug' => 'bylaw-2']);

        $response = $this->getJson('/api/v1/bylaws');

        $response->assertOk();
        $articles = collect($response->json('data'))->pluck('article_number')->all();
        $this->assertEquals(['Article 01', 'Article 02', 'Article 03'], $articles);
    }

    public function test_show_returns_published_bylaw_by_slug(): void
    {
        ClubBylaw::factory()->published()->create([
            'title' => 'Membership Bylaw',
            'slug' => 'membership-bylaw',
            'article_number' => 'Article 1',
        ]);

        $response = $this->getJson('/api/v1/bylaws/membership-bylaw');

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => 'Membership Bylaw',
            'article_number' => 'Article 1',
        ]);
    }

    public function test_show_returns_404_for_draft_bylaw(): void
    {
        ClubBylaw::factory()->create(['slug' => 'draft-bylaw']);

        $response = $this->getJson('/api/v1/bylaws/draft-bylaw');

        $response->assertNotFound();
    }

    public function test_response_includes_article_number(): void
    {
        ClubBylaw::factory()->published()->create(['slug' => 'test-bylaw']);

        $response = $this->getJson('/api/v1/bylaws/test-bylaw');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'slug',
                'article_number',
                'content',
                'file_url',
                'version',
                'effective_date',
                'published_at',
            ],
        ]);
    }
}
