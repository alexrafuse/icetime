<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Shared\Enums\ResourceCategory;
use Domain\Shared\Models\Resource;
use Illuminate\Database\Seeder;

class ResourcesSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            // Events Resources
            [
                'title' => 'NS Curl Bonspiels',
                'description' => 'View upcoming and past bonspiels hosted across Nova Scotia. Find competitions to participate in and test your skills!',
                'category' => ResourceCategory::Events,
                'type' => 'url',
                'url' => 'https://nscurl.com/events/category/bonspiels/list/?eventDisplay=past',
                'file_path' => null,
                'visibility' => 'all',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'NS Curl Events Calendar',
                'description' => 'Complete calendar of curling events, courses, and activities happening across Nova Scotia.',
                'category' => ResourceCategory::Events,
                'type' => 'url',
                'url' => 'https://nscurl.com/events/',
                'file_path' => null,
                'visibility' => 'all',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Club Events Calendar',
                'description' => 'Our club\'s complete schedule of events, social gatherings, and special competitions.',
                'category' => ResourceCategory::Events,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/club-events-calendar.pdf',
                'visibility' => 'all',
                'priority' => 3,
                'is_active' => true,
            ],

            // Curriculum Resources
            [
                'title' => 'Learn to Curl Program',
                'description' => 'Complete curriculum for beginners learning the fundamentals of curling. Perfect for new members and instructors.',
                'category' => ResourceCategory::Curriculum,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/learn-to-curl-curriculum.pdf',
                'visibility' => 'all',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Advanced Technique Guide',
                'description' => 'Advanced training materials covering shot strategy, sweeping techniques, and competitive play.',
                'category' => ResourceCategory::Curriculum,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/advanced-technique-guide.pdf',
                'visibility' => 'all',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Youth Curling Development',
                'description' => 'Curriculum designed specifically for young curlers, with age-appropriate drills and activities.',
                'category' => ResourceCategory::Curriculum,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/youth-development-program.pdf',
                'visibility' => 'all',
                'priority' => 3,
                'is_active' => true,
            ],

            // Schedules Resources
            [
                'title' => 'League Schedule - Fall 2025',
                'description' => 'Complete schedule for all fall league games, including times, sheets, and team assignments.',
                'category' => ResourceCategory::Schedules,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/fall-league-schedule.pdf',
                'visibility' => 'all',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Ice Time Availability',
                'description' => 'Current ice time availability for practice sessions and private bookings.',
                'category' => ResourceCategory::Schedules,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/ice-availability.pdf',
                'visibility' => 'all',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Coaching Schedule',
                'description' => 'Schedule of available coaching sessions and instructional programs.',
                'category' => ResourceCategory::Schedules,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/coaching-schedule.pdf',
                'visibility' => 'admin_staff_only',
                'priority' => 3,
                'is_active' => true,
            ],

            // Rules Resources
            [
                'title' => 'Official Curling Rules',
                'description' => 'The complete official rules of curling as established by Curling Canada.',
                'category' => ResourceCategory::Rules,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/official-curling-rules.pdf',
                'visibility' => 'all',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Club House Rules',
                'description' => 'Club-specific rules and etiquette guidelines for all members and guests.',
                'category' => ResourceCategory::Rules,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/club-house-rules.pdf',
                'visibility' => 'all',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'League Regulations',
                'description' => 'Specific regulations and policies for league play, including playoffs and standings.',
                'category' => ResourceCategory::Rules,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/league-regulations.pdf',
                'visibility' => 'all',
                'priority' => 3,
                'is_active' => true,
            ],

            // General Resources
            [
                'title' => 'Club Bylaws',
                'description' => 'Official club bylaws and governance documents.',
                'category' => ResourceCategory::General,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/club-bylaws.pdf',
                'visibility' => 'all',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Membership Benefits Guide',
                'description' => 'Comprehensive guide to all the benefits and perks available to club members.',
                'category' => ResourceCategory::General,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/membership-benefits.pdf',
                'visibility' => 'all',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Facility Map',
                'description' => 'Detailed map of the curling facility, including ice sheets, lounges, and amenities.',
                'category' => ResourceCategory::General,
                'type' => 'file',
                'url' => null,
                'file_path' => 'resources/facility-map.pdf',
                'visibility' => 'all',
                'priority' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($resources as $resource) {
            Resource::query()->create($resource);
        }
    }
}
