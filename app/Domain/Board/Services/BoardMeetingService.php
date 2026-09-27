<?php

declare(strict_types=1);

namespace Domain\Board\Services;

use Domain\Board\Models\BoardMeeting;
use Illuminate\Support\Collection;

final class BoardMeetingService
{
    public function createWithAgenda(array $meetingData, array $agendaItems = []): BoardMeeting
    {
        $meeting = BoardMeeting::create($meetingData);

        collect($agendaItems)->each(fn (array $item, int $index) => $meeting->agendas()->create([
            ...$item,
            'sort_order' => $item['sort_order'] ?? $index,
        ]));

        return $meeting;
    }

    public function completeMeeting(BoardMeeting $meeting): void
    {
        $meeting->complete();
    }

    public function cancelMeeting(BoardMeeting $meeting): void
    {
        $meeting->cancel();
    }

    public function getUpcoming(): Collection
    {
        return BoardMeeting::query()
            ->upcoming()
            ->with(['calledBy', 'agendas'])
            ->orderBy('scheduled_at')
            ->get();
    }
}
