<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Enums\RecurrencePeriod;
use App\Domain\Shared\Enums\SurveyStatus;
use App\Filament\Pages\MySurveys;
use App\Filament\Resources\SurveyResource\Pages\CreateSurvey;
use App\Filament\Resources\SurveyResource\Pages\EditSurvey;
use App\Filament\Resources\SurveyResource\Pages\ListSurveys;
use App\Filament\Widgets\SurveyPromptWidget;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Shared\Models\Survey;
use Domain\Shared\Models\SurveyResponse;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SurveyFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');

        $this->member = User::factory()->create(['email' => 'member@test.com']);
        $this->member->assignRole('member');
    }

    public function test_admin_can_create_survey(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateSurvey::class)
            ->fillForm([
                'title' => 'Season Satisfaction Survey',
                'description' => 'Help us improve your experience',
                'tally_form_url' => 'https://tally.so/r/test123',
                'is_active' => true,
                'priority' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('surveys', [
            'title' => 'Season Satisfaction Survey',
            'tally_form_url' => 'https://tally.so/r/test123',
            'priority' => 1,
        ]);
    }

    public function test_admin_can_edit_survey(): void
    {
        $this->actingAs($this->admin);

        $survey = Survey::factory()->create([
            'title' => 'Original Title',
            'priority' => 10,
        ]);

        Livewire::test(EditSurvey::class, ['record' => $survey->id])
            ->fillForm([
                'title' => 'Updated Title',
                'priority' => 1,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('surveys', [
            'id' => $survey->id,
            'title' => 'Updated Title',
            'priority' => 1,
        ]);
    }

    public function test_admin_can_view_surveys_list(): void
    {
        $this->actingAs($this->admin);

        $survey1 = Survey::factory()->create(['title' => 'Survey One', 'priority' => 1]);
        $survey2 = Survey::factory()->create(['title' => 'Survey Two', 'priority' => 2]);

        Livewire::test(ListSurveys::class)
            ->assertCanSeeTableRecords([$survey1, $survey2]);
    }

    public function test_widget_shows_active_survey_to_member(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create([
            'title' => 'Test Survey',
            'is_active' => true,
            'priority' => 1,
        ]);

        $this->assertTrue(SurveyPromptWidget::canView());

        Livewire::test(SurveyPromptWidget::class)
            ->assertSee('Test Survey');
    }

    public function test_widget_does_not_show_if_user_completed_survey(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create([
            'is_active' => true,
            'priority' => 1,
        ]);

        SurveyResponse::factory()->completed()->create([
            'survey_id' => $survey->id,
            'user_id' => $this->member->id,
        ]);

        $this->assertFalse(SurveyPromptWidget::canView());
    }

    public function test_widget_does_not_show_if_user_not_interested(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create([
            'is_active' => true,
            'priority' => 1,
        ]);

        SurveyResponse::factory()->notInterested()->create([
            'survey_id' => $survey->id,
            'user_id' => $this->member->id,
        ]);

        $this->assertFalse(SurveyPromptWidget::canView());
    }

    public function test_user_can_mark_survey_as_completed(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create([
            'is_active' => true,
            'priority' => 1,
        ]);

        Livewire::test(SurveyPromptWidget::class)
            ->call('takeSurvey')
            ->assertRedirect($survey->tally_form_url);

        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'user_id' => $this->member->id,
            'status' => SurveyStatus::COMPLETED->value,
        ]);
    }

    public function test_user_can_mark_survey_as_not_interested(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create([
            'is_active' => true,
            'priority' => 1,
        ]);

        Livewire::test(SurveyPromptWidget::class)
            ->call('notInterested');

        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'user_id' => $this->member->id,
            'status' => SurveyStatus::NOT_INTERESTED->value,
        ]);
    }

    public function test_user_can_mark_survey_for_later(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create([
            'is_active' => true,
            'priority' => 1,
        ]);

        Livewire::test(SurveyPromptWidget::class)
            ->call('remindLater');

        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'user_id' => $this->member->id,
            'status' => SurveyStatus::LATER->value,
        ]);
    }

    public function test_widget_shows_highest_priority_survey_first(): void
    {
        $this->actingAs($this->member);

        $lowPriority = Survey::factory()->create(['priority' => 10, 'is_active' => true]);
        $highPriority = Survey::factory()->create(['priority' => 1, 'is_active' => true]);

        $nextSurvey = Survey::getNextForUser($this->member);

        $this->assertEquals($highPriority->id, $nextSurvey->id);
    }

    public function test_recurring_survey_shows_again_after_period(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()
            ->recurring(RecurrencePeriod::DAILY)
            ->create(['is_active' => true]);

        // User completed it yesterday
        SurveyResponse::factory()->completed()->create([
            'survey_id' => $survey->id,
            'user_id' => $this->member->id,
            'responded_at' => now()->subDay(),
        ]);

        // Should show again since a day has passed
        $this->assertTrue($survey->shouldShowToUser($this->member));
    }

    public function test_inactive_survey_does_not_show(): void
    {
        $this->actingAs($this->member);

        Survey::factory()->inactive()->create();

        $this->assertFalse(SurveyPromptWidget::canView());
    }

    public function test_survey_with_future_start_date_does_not_show(): void
    {
        $this->actingAs($this->member);

        Survey::factory()->create([
            'is_active' => true,
            'starts_at' => now()->addWeek(),
        ]);

        $this->assertFalse(SurveyPromptWidget::canView());
    }

    public function test_survey_with_past_end_date_does_not_show(): void
    {
        $this->actingAs($this->member);

        Survey::factory()->create([
            'is_active' => true,
            'ends_at' => now()->subWeek(),
        ]);

        $this->assertFalse(SurveyPromptWidget::canView());
    }

    public function test_member_can_view_my_surveys_page(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create(['is_active' => true]);

        Livewire::test(MySurveys::class)
            ->assertSuccessful()
            ->assertSee($survey->title);
    }

    public function test_my_surveys_page_shows_available_surveys(): void
    {
        $this->actingAs($this->member);

        $survey1 = Survey::factory()->create(['is_active' => true, 'priority' => 1]);
        $survey2 = Survey::factory()->create(['is_active' => true, 'priority' => 2]);

        Livewire::test(MySurveys::class)
            ->assertSee($survey1->title)
            ->assertSee($survey2->title)
            ->assertSee('Available Surveys');
    }

    public function test_my_surveys_page_shows_past_surveys(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create(['is_active' => true]);

        SurveyResponse::factory()->completed()->create([
            'survey_id' => $survey->id,
            'user_id' => $this->member->id,
        ]);

        Livewire::test(MySurveys::class)
            ->assertSee('Past Surveys')
            ->assertSee($survey->title)
            ->assertSee('Completed');
    }

    public function test_my_surveys_page_shows_empty_state_when_no_surveys(): void
    {
        $this->actingAs($this->member);

        Livewire::test(MySurveys::class)
            ->assertSee('All caught up!')
            ->assertSee('completed all available surveys');
    }

    public function test_user_can_take_survey_from_my_surveys_page(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create(['is_active' => true]);

        Livewire::test(MySurveys::class)
            ->call('takeSurvey', $survey->id)
            ->assertRedirect($survey->tally_form_url);

        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'user_id' => $this->member->id,
            'status' => SurveyStatus::COMPLETED->value,
        ]);
    }

    public function test_user_can_dismiss_survey_from_my_surveys_page(): void
    {
        $this->actingAs($this->member);

        $survey = Survey::factory()->create(['is_active' => true]);

        Livewire::test(MySurveys::class)
            ->call('dismiss', $survey->id);

        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'user_id' => $this->member->id,
            'status' => SurveyStatus::DISMISSED->value,
        ]);
    }
}
