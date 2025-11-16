<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Shared\Enums\RecurrencePeriod;
use Domain\Shared\Models\Survey;
use Illuminate\Database\Seeder;

class SurveysSeeder extends Seeder
{
    public function run(): void
    {
        $surveys = [
            [
                'title' => '2024-2025 Season Satisfaction Survey',
                'description' => 'Help us improve your curling experience. Takes just 3 minutes to complete. Your feedback shapes our future programs and facilities.',
                'tally_form_url' => 'https://tally.so/forms/WO9eyP',
                'is_active' => true,
                'priority' => 1,
                'starts_at' => now()->subDays(7),
                'ends_at' => now()->addDays(60),
                'is_recurring' => false,
                'recurrence_period' => null,
            ],
            [
                'title' => 'Ice Conditions Feedback',
                'description' => 'Rate this month\'s ice quality and share any concerns. We review all feedback with our ice technicians.',
                'tally_form_url' => 'https://tally.so/r/ice-conditions',
                'is_active' => true,
                'priority' => 2,
                'starts_at' => null,
                'ends_at' => null,
                'is_recurring' => true,
                'recurrence_period' => RecurrencePeriod::MONTHLY,
            ],
            [
                'title' => 'New Programs Interest Survey',
                'description' => 'We\'re exploring new programs for next season! Tell us what you\'d like to see: mixed doubles leagues, skills clinics, social events, and more.',
                'tally_form_url' => 'https://tally.so/r/new-programs',
                'is_active' => true,
                'priority' => 3,
                'starts_at' => now(),
                'ends_at' => now()->addMonths(2),
                'is_recurring' => false,
                'recurrence_period' => null,
            ],
            [
                'title' => 'Equipment & Pro Shop Feedback',
                'description' => 'Share your thoughts on our pro shop inventory, pricing, and equipment rental options.',
                'tally_form_url' => 'https://tally.so/r/equipment-feedback',
                'is_active' => false,
                'priority' => 10,
                'starts_at' => null,
                'ends_at' => null,
                'is_recurring' => false,
                'recurrence_period' => null,
            ],
        ];

        foreach ($surveys as $survey) {
            Survey::query()->create($survey);
        }
    }
}
