<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Shared\Enums\SurveyStatus;
use Domain\Shared\Models\Survey;
use Domain\Shared\Models\SurveyResponse;
use Filament\Widgets\Widget;

final class SurveyPromptWidget extends Widget
{
    protected static string $view = 'filament.widgets.survey-prompt-widget';

    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    public ?Survey $survey = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->survey = Survey::getNextForUser($user);
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return Survey::getNextForUser($user) !== null;
    }

    protected function getViewData(): array
    {
        return [
            'survey' => $this->survey,
        ];
    }

    public function takeSurvey(): void
    {
        if (! $this->survey) {
            return;
        }

        $user = auth()->user();

        SurveyResponse::query()->updateOrCreate(
            [
                'survey_id' => $this->survey->id,
                'user_id' => $user->id,
            ],
            [
                'status' => SurveyStatus::COMPLETED,
                'responded_at' => now(),
            ]
        );

        $this->redirect($this->survey->tally_form_url, navigate: false);
    }

    public function notInterested(): void
    {
        if (! $this->survey) {
            return;
        }

        $user = auth()->user();

        SurveyResponse::query()->updateOrCreate(
            [
                'survey_id' => $this->survey->id,
                'user_id' => $user->id,
            ],
            [
                'status' => SurveyStatus::NOT_INTERESTED,
                'responded_at' => now(),
            ]
        );

        $this->mount();
    }

    public function remindLater(): void
    {
        if (! $this->survey) {
            return;
        }

        $user = auth()->user();

        SurveyResponse::query()->updateOrCreate(
            [
                'survey_id' => $this->survey->id,
                'user_id' => $user->id,
            ],
            [
                'status' => SurveyStatus::LATER,
                'responded_at' => now(),
            ]
        );

        $this->mount();
    }

    public function dismiss(): void
    {
        if (! $this->survey) {
            return;
        }

        $user = auth()->user();

        SurveyResponse::query()->updateOrCreate(
            [
                'survey_id' => $this->survey->id,
                'user_id' => $user->id,
            ],
            [
                'status' => SurveyStatus::DISMISSED,
                'responded_at' => now(),
            ]
        );

        $this->mount();
    }
}
