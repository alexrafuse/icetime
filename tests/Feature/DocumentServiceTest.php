<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\Board\Enums\DocumentStatus;
use Domain\Board\Models\ClubBylaw;
use Domain\Board\Models\ClubPolicy;
use Domain\Board\Services\DocumentService;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    private DocumentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DocumentService;
    }

    public function test_publish_policy_sets_status_and_timestamps(): void
    {
        $user = User::factory()->create();
        $policy = ClubPolicy::factory()->create();

        $this->service->publishPolicy($policy, $user);

        $policy->refresh();
        $this->assertEquals(DocumentStatus::PUBLISHED, $policy->status);
        $this->assertNotNull($policy->published_at);
        $this->assertEquals($user->id, $policy->approved_by_user_id);
    }

    public function test_publish_policy_archives_parent(): void
    {
        $user = User::factory()->create();
        $parent = ClubPolicy::factory()->published()->create();
        $revision = ClubPolicy::factory()->create(['parent_id' => $parent->id]);

        $this->service->publishPolicy($revision, $user);

        $parent->refresh();
        $this->assertEquals(DocumentStatus::ARCHIVED, $parent->status);
    }

    public function test_create_policy_revision_increments_version(): void
    {
        $policy = ClubPolicy::factory()->published()->create(['version' => 1]);

        $revision = $this->service->createPolicyRevision($policy);

        $this->assertEquals(2, $revision->version);
        $this->assertEquals(DocumentStatus::DRAFT, $revision->status);
        $this->assertEquals($policy->id, $revision->parent_id);
        $this->assertEquals($policy->title, $revision->title);
        $this->assertEquals($policy->slug, $revision->slug);
    }

    public function test_archive_policy(): void
    {
        $policy = ClubPolicy::factory()->published()->create();

        $this->service->archivePolicy($policy);

        $policy->refresh();
        $this->assertEquals(DocumentStatus::ARCHIVED, $policy->status);
    }

    public function test_publish_bylaw_sets_status_and_timestamps(): void
    {
        $user = User::factory()->create();
        $bylaw = ClubBylaw::factory()->create();

        $this->service->publishBylaw($bylaw, $user);

        $bylaw->refresh();
        $this->assertEquals(DocumentStatus::PUBLISHED, $bylaw->status);
        $this->assertNotNull($bylaw->published_at);
        $this->assertEquals($user->id, $bylaw->approved_by_user_id);
    }

    public function test_create_bylaw_revision_preserves_article_number(): void
    {
        $bylaw = ClubBylaw::factory()->published()->create([
            'article_number' => 'Article 5',
            'version' => 1,
        ]);

        $revision = $this->service->createBylawRevision($bylaw);

        $this->assertEquals('Article 5', $revision->article_number);
        $this->assertEquals(2, $revision->version);
        $this->assertEquals($bylaw->id, $revision->parent_id);
    }
}
