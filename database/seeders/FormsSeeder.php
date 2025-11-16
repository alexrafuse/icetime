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
            // Registration Forms
            [
                'title' => 'New Member Registration',
                'description' => 'Register as a new member of our curling club. Complete this form to get started with your curling journey!',
                'tally_form_url' => 'https://tally.so/r/new-member-registration',
                'category' => FormCategory::REGISTRATION,
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Guest Registration',
                'description' => 'Planning to visit as a guest? Register here to join us for a game or event.',
                'tally_form_url' => 'https://tally.so/r/guest-registration',
                'category' => FormCategory::REGISTRATION,
                'priority' => 2,
                'is_active' => true,
            ],

            // Membership Forms
            [
                'title' => 'Membership Renewal',
                'description' => 'Renew your membership for the upcoming season. Early renewal ensures you don\'t miss out on your favorite ice times!',
                'tally_form_url' => 'https://tally.so/r/membership-renewal',
                'category' => FormCategory::MEMBERSHIP,
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Change Membership Type',
                'description' => 'Upgrade or change your membership type. Switch between Full, Social, or other membership levels.',
                'tally_form_url' => 'https://tally.so/r/change-membership',
                'category' => FormCategory::MEMBERSHIP,
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Family Membership Application',
                'description' => 'Apply for a family membership package. Great value for families who curl together!',
                'tally_form_url' => 'https://tally.so/r/family-membership',
                'category' => FormCategory::MEMBERSHIP,
                'priority' => 3,
                'is_active' => true,
            ],

            // Volunteer Forms
            [
                'title' => 'Volunteer Sign-up',
                'description' => 'Help make our club amazing! Sign up to volunteer for events, ice maintenance, or general club support.',
                'tally_form_url' => 'https://tally.so/r/volunteer-signup',
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
                'title' => 'Ice Time Request',
                'description' => 'Request additional ice time for practice, private events, or special occasions.',
                'tally_form_url' => 'https://tally.so/r/ice-time-request',
                'category' => FormCategory::GENERAL,
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Equipment Rental Request',
                'description' => 'Reserve brooms, sliders, or other curling equipment for your visit.',
                'tally_form_url' => 'https://tally.so/r/equipment-rental',
                'category' => FormCategory::GENERAL,
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Locker Rental Application',
                'description' => 'Apply for a seasonal locker to store your curling gear at the club.',
                'tally_form_url' => 'https://tally.so/r/locker-rental',
                'category' => FormCategory::GENERAL,
                'priority' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($forms as $form) {
            Form::query()->create($form);
        }
    }
}
