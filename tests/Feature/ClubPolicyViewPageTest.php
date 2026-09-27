<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ClubBylawResource\Pages\ListClubBylaws;
use App\Filament\Resources\ClubBylawResource\Pages\ViewClubBylaw;
use App\Filament\Resources\ClubPolicyResource\Pages\ListClubPolicies;
use App\Filament\Resources\ClubPolicyResource\Pages\ViewClubPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Board\Enums\DocumentStatus;
use Domain\Board\Models\ClubBylaw;
use Domain\Board\Models\ClubPolicy;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClubPolicyViewPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_can_view_policy_page(): void
    {
        $policy = ClubPolicy::factory()->create([
            'status' => DocumentStatus::PUBLISHED,
            'content' => '# Test Policy Content',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ViewClubPolicy::class, ['record' => $policy->getRouteKey()])
            ->assertSuccessful()
            ->assertSee('Test Policy Content');
    }

    public function test_can_view_bylaw_page(): void
    {
        $bylaw = ClubBylaw::factory()->create([
            'status' => DocumentStatus::PUBLISHED,
            'content' => '# Test Bylaw Content',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ViewClubBylaw::class, ['record' => $bylaw->getRouteKey()])
            ->assertSuccessful()
            ->assertSee('Test Bylaw Content');
    }

    public function test_policy_list_has_api_instructions_action(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListClubPolicies::class)
            ->assertActionExists('api_instructions');
    }

    public function test_bylaw_list_has_api_instructions_action(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ListClubBylaws::class)
            ->assertActionExists('api_instructions');
    }
}
