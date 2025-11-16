<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Shared\Enums\FormCategory;
use Domain\Shared\Models\Form;
use Illuminate\Database\Seeder;

class FormsSeeder extends Seeder
{
    public function run(): void
    {
        $forms = [
            // // Registration Forms
            // [
            //     'title' => 'New Member Registration',
            //     'description' => 'Register as a new member of our curling club. Complete this form to get started with your curling journey!',
            //     'tally_form_url' => 'https://tally.so/r/new-member-registration',
            //     'category' => FormCategory::REGISTRATION,
            //     'priority' => 1,
            //     'is_active' => true,
            // ],

            // // Membership Forms
            // [
            //     'title' => 'Membership Renewal',
            //     'description' => 'Renew your membership for the upcoming season. Early renewal ensures you don\'t miss out on your favorite ice times!',
            //     'tally_form_url' => 'https://tally.so/r/membership-renewal',
            //     'category' => FormCategory::MEMBERSHIP,
            //     'priority' => 1,
            //     'is_active' => true,
            // ],
            // [
            //     'title' => 'Change Membership Type',
            //     'description' => 'Upgrade or change your membership type. Switch between half season and full season, and one leage and full membership.',
            //     'tally_form_url' => 'https://tally.so/r/change-membership',
            //     'category' => FormCategory::MEMBERSHIP,
            //     'priority' => 2,
            //     'is_active' => true,
            // ],

            // [
            //     'title' => 'Family Membership Application',
            //     'description' => 'Apply for a family membership package. Great value for families who curl together!',
            //     'tally_form_url' => 'https://tally.so/r/family-membership',
            //     'category' => FormCategory::MEMBERSHIP,
            //     'priority' => 3,
            //     'is_active' => true,
            // ],

            // Volunteer Forms
            [
                'title' => 'Volunteer Sign-up',
                'description' => 'Help make our club amazing! Sign up to volunteer for events, ice maintenance, or general club support.',
                'tally_form_url' => 'https://tally.so/r/mJQLYd',
                'category' => FormCategory::VOLUNTEER,
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Committee Interest Form',
                'description' => 'Interested in joining a committee? Let us know which areas interest you - from events to facilities management.',
                'tally_form_url' => 'https://tally.so/r/committee-interest',
                'category' => FormCategory::VOLUNTEER,
                'priority' => 2,
                'is_active' => true,
            ],

            // General Forms
            [
                'title' => 'Event Idea',
                'description' => 'Submit an idea for an on-ice or off-ice event.',
                'tally_form_url' => 'https://tally.so/r/ice-time-request',
                'category' => FormCategory::GENERAL,
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Sponsorship Request',
                'description' => 'Thinking of becomming a sponsor of the club? Complete this form and we will get in touch.',
                'tally_form_url' => 'https://tally.so/r/equipment-rental',
                'category' => FormCategory::GENERAL,
                'priority' => 2,
                'is_active' => true,
            ],

        ];

        foreach ($forms as $form) {
            Form::query()->create($form);
        }
    }
}
