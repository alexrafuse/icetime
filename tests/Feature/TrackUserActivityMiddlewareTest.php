<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\UserActivity;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackUserActivityMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create(['email' => 'user@test.com']);
    }

    public function test_authenticated_user_activity_is_tracked(): void
    {
        $this->actingAs($this->user);

        $this->assertDatabaseCount('user_activities', 0);

        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertSuccessful();

        $this->assertDatabaseHas('user_activities', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseCount('user_activities', 1);
    }

    public function test_unauthenticated_user_activity_is_not_tracked(): void
    {
        $this->assertDatabaseCount('user_activities', 0);

        $response = $this->get('/');

        $this->assertDatabaseCount('user_activities', 0);
    }

    public function test_activity_is_rounded_to_hour(): void
    {
        $this->actingAs($this->user);

        $this->get(route('filament.admin.pages.dashboard'));

        $activity = UserActivity::where('user_id', $this->user->id)->first();

        $this->assertNotNull($activity);
        $this->assertEquals(0, $activity->active_at->minute);
        $this->assertEquals(0, $activity->active_at->second);
    }

    public function test_duplicate_activity_in_same_hour_is_not_created(): void
    {
        $this->actingAs($this->user);

        $this->assertDatabaseCount('user_activities', 0);

        // First request
        $this->get(route('filament.admin.pages.dashboard'));

        $this->assertDatabaseCount('user_activities', 1);

        // Second request in same hour
        $this->get(route('filament.admin.pages.dashboard'));

        // Should still be 1 record due to unique constraint
        $this->assertDatabaseCount('user_activities', 1);
    }

    public function test_multiple_users_can_have_activity_tracked(): void
    {
        $user2 = User::factory()->create(['email' => 'user2@test.com']);

        $this->assertDatabaseCount('user_activities', 0);

        // User 1 activity
        $this->actingAs($this->user);
        $this->get(route('filament.admin.pages.dashboard'));

        // User 2 activity
        $this->actingAs($user2);
        $this->get(route('filament.admin.pages.dashboard'));

        $this->assertDatabaseCount('user_activities', 2);

        $this->assertDatabaseHas('user_activities', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('user_activities', [
            'user_id' => $user2->id,
        ]);
    }

    public function test_middleware_does_not_break_request(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertSuccessful();
    }

    public function test_activity_tracking_on_different_routes(): void
    {
        $this->actingAs($this->user);

        $this->assertDatabaseCount('user_activities', 0);

        // Dashboard
        $this->get(route('filament.admin.pages.dashboard'));

        $this->assertDatabaseCount('user_activities', 1);

        // Another route (won't create duplicate in same hour)
        $this->get(route('filament.admin.resources.users.index'));

        // Still 1 because it's the same hour
        $this->assertDatabaseCount('user_activities', 1);
    }
}
