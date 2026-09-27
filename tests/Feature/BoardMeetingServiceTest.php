<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\Board\Enums\MeetingStatus;
use Domain\Board\Models\BoardMeeting;
use Domain\Board\Services\BoardMeetingService;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardMeetingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BoardMeetingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BoardMeetingService;
    }

    public function test_create_meeting_with_agenda_items(): void
    {
        $user = User::factory()->create();

        $meeting = $this->service->createWithAgenda([
            'title' => 'Monthly Board Meeting',
            'description' => 'Regular monthly meeting',
            'scheduled_at' => now()->addWeek(),
            'location' => 'Board Room',
            'status' => MeetingStatus::SCHEDULED,
            'called_by_user_id' => $user->id,
        ], [
            ['title' => 'Call to Order', 'duration_minutes' => 5],
            ['title' => 'Financial Report', 'duration_minutes' => 15],
            ['title' => 'New Business', 'duration_minutes' => 30],
        ]);

        $this->assertDatabaseHas('board_meetings', ['title' => 'Monthly Board Meeting']);
        $this->assertCount(3, $meeting->agendas);
        $this->assertEquals(0, $meeting->agendas->first()->sort_order);
        $this->assertEquals(2, $meeting->agendas->last()->sort_order);
    }

    public function test_complete_meeting(): void
    {
        $meeting = BoardMeeting::factory()->create();

        $this->service->completeMeeting($meeting);

        $meeting->refresh();
        $this->assertEquals(MeetingStatus::COMPLETED, $meeting->status);
    }

    public function test_cancel_meeting(): void
    {
        $meeting = BoardMeeting::factory()->create();

        $this->service->cancelMeeting($meeting);

        $meeting->refresh();
        $this->assertEquals(MeetingStatus::CANCELLED, $meeting->status);
    }

    public function test_get_upcoming_returns_only_scheduled_future_meetings(): void
    {
        BoardMeeting::factory()->create([
            'scheduled_at' => now()->addWeek(),
            'status' => MeetingStatus::SCHEDULED,
        ]);
        BoardMeeting::factory()->completed()->create();
        BoardMeeting::factory()->cancelled()->create();

        $upcoming = $this->service->getUpcoming();

        $this->assertCount(1, $upcoming);
    }
}
