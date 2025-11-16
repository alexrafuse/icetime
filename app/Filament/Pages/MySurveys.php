<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Shared\Enums\SurveyStatus;
use Domain\Shared\Models\Survey;
use Domain\Shared\Models\SurveyResponse;
use Filament\Pages\Page;

class MySurveys extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string $view = 'filament.pages.my-surveys';

    protected static ?string $title = 'My Surveys';

    protected static ?string $navigationLabel = 'Surveys';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    public function getAvailableSurveys()
    {
        $user = auth()->user();

        return Survey::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('created_at')
            ->get()
            ->filter(fn (Survey $survey) => $survey->shouldShowToUser($user));
    }

    public function getPastSurveys()
    {
        $user = auth()->user();

        return $user->surveyResponses()
            ->with('survey')
            ->latest('responded_at')
            ->get()
            ->map(fn ($response) => [
                'survey' => $response->survey,
                'status' => $response->status,
                'responded_at' => $response->responded_at,
            ]);
    }

    public function takeSurvey(int $surveyId): void
    {
        $survey = Survey::findOrFail($surveyId);
        $user = auth()->user();

        SurveyResponse::query()->updateOrCreate(
            [
                'survey_id' => $survey->id,
                'user_id' => $user->id,
            ],
            [
                'status' => SurveyStatus::COMPLETED,
                'responded_at' => now(),
            ]
        );

        $this->redirect($survey->tally_form_url, navigate: false);
    }

    public function notInterested(int $surveyId): void
    {
        $user = auth()->user();

        SurveyResponse::query()->updateOrCreate(
            [
                'survey_id' => $surveyId,
                'user_id' => $user->id,
            ],
            [
                'status' => SurveyStatus::NOT_INTERESTED,
                'responded_at' => now(),
            ]
        );

        $this->redirect(request()->header('Referer'));
    }

    public function remindLater(int $surveyId): void
    {
        $user = auth()->user();

        SurveyResponse::query()->updateOrCreate(
            [
                'survey_id' => $surveyId,
                'user_id' => $user->id,
            ],
            [
                'status' => SurveyStatus::LATER,
                'responded_at' => now(),
            ]
        );

        $this->redirect(request()->header('Referer'));
    }

    public function dismiss(int $surveyId): void
    {
        $user = auth()->user();

        SurveyResponse::query()->updateOrCreate(
            [
                'survey_id' => $surveyId,
                'user_id' => $user->id,
            ],
            [
                'status' => SurveyStatus::DISMISSED,
                'responded_at' => now(),
            ]
        );

        $this->redirect(request()->header('Referer'));
    }
}
