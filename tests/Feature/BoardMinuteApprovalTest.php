<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\Board\Enums\MinuteStatus;
use Domain\Board\Models\BoardMinute;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardMinuteApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_sets_status_and_timestamps(): void
    {
        $minute = BoardMinute::factory()->pendingApproval()->create();
        $approver = User::factory()->create();

        $minute->approve($approver);

        $minute->refresh();
        $this->assertEquals(MinuteStatus::APPROVED, $minute->status);
        $this->assertNotNull($minute->approved_at);
        $this->assertEquals($approver->id, $minute->approved_by_user_id);
    }

    public function test_reject_clears_approval(): void
    {
        $minute = BoardMinute::factory()->approved()->create();

        $minute->reject();

        $minute->refresh();
        $this->assertEquals(MinuteStatus::REJECTED, $minute->status);
        $this->assertNull($minute->approved_at);
        $this->assertNull($minute->approved_by_user_id);
    }

    public function test_submit_for_approval(): void
    {
        $minute = BoardMinute::factory()->create();

        $minute->submitForApproval();

        $minute->refresh();
        $this->assertEquals(MinuteStatus::PENDING_APPROVAL, $minute->status);
    }

    public function test_is_approved_returns_correct_state(): void
    {
        $approved = BoardMinute::factory()->approved()->create();
        $draft = BoardMinute::factory()->create();

        $this->assertTrue($approved->isApproved());
        $this->assertFalse($draft->isApproved());
    }

    public function test_is_pending_returns_correct_state(): void
    {
        $pending = BoardMinute::factory()->pendingApproval()->create();
        $draft = BoardMinute::factory()->create();

        $this->assertTrue($pending->isPending());
        $this->assertFalse($draft->isPending());
    }
}
