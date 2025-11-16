<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Enums\ResourceCategory;
use App\Filament\Pages\MemberResources;
use App\Filament\Resources\ResourceResource;
use App\Filament\Resources\ResourceResource\Pages\CreateResource;
use App\Filament\Resources\ResourceResource\Pages\EditResource;
use App\Filament\Resources\ResourceResource\Pages\ListResources;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Shared\Models\Resource;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');

        $this->staff = User::factory()->create(['email' => 'staff@test.com']);
        $this->staff->assignRole('staff');

        $this->member = User::factory()->create(['email' => 'member@test.com']);
        $this->member->assignRole('member');
    }

    public function test_admin_can_create_url_resource(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateResource::class)
            ->fillForm([
                'title' => 'NS Curl Events',
                'description' => 'View all upcoming curling events',
                'category' => ResourceCategory::Events,
                'type' => 'url',
                'url' => 'https://nscurl.com/events/',
                'visibility' => 'all',
                'priority' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resources', [
            'title' => 'NS Curl Events',
            'type' => 'url',
            'url' => 'https://nscurl.com/events/',
            'category' => 'events',
        ]);
    }

    public function test_admin_can_edit_resource(): void
    {
        $this->actingAs($this->admin);

        $resource = Resource::factory()->url()->create([
            'title' => 'Original Title',
            'category' => ResourceCategory::General,
        ]);

        Livewire::test(EditResource::class, ['record' => $resource->id])
            ->fillForm([
                'title' => 'Updated Title',
                'category' => ResourceCategory::Events,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resources', [
            'id' => $resource->id,
            'title' => 'Updated Title',
            'category' => 'events',
        ]);
    }

    public function test_staff_can_create_resource(): void
    {
        $this->actingAs($this->staff);

        Livewire::test(CreateResource::class)
            ->fillForm([
                'title' => 'Staff Created Resource',
                'description' => 'Test resource',
                'category' => ResourceCategory::Rules,
                'type' => 'url',
                'url' => 'https://example.com',
                'visibility' => 'all',
                'priority' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resources', [
            'title' => 'Staff Created Resource',
        ]);
    }

    public function test_admin_can_view_resources_list(): void
    {
        $this->actingAs($this->admin);

        $resource1 = Resource::factory()->create(['title' => 'Resource One']);
        $resource2 = Resource::factory()->create(['title' => 'Resource Two']);

        Livewire::test(ListResources::class)
            ->assertCanSeeTableRecords([$resource1, $resource2]);
    }

    public function test_member_can_view_member_resources_page(): void
    {
        $this->actingAs($this->member);

        $resource = Resource::factory()->visibleToAll()->create([
            'title' => 'Test Resource',
            'is_active' => true,
        ]);

        Livewire::test(MemberResources::class)
            ->assertSuccessful()
            ->assertSee($resource->title);
    }

    public function test_member_resources_page_groups_by_category(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->create([
            'title' => 'Events Resource',
            'category' => ResourceCategory::Events,
            'visibility' => 'all',
            'is_active' => true,
        ]);

        Resource::factory()->create([
            'title' => 'Curriculum Resource',
            'category' => ResourceCategory::Curriculum,
            'visibility' => 'all',
            'is_active' => true,
        ]);

        Livewire::test(MemberResources::class)
            ->assertSee('Events')
            ->assertSee('Events Resource')
            ->assertSee('Curriculum')
            ->assertSee('Curriculum Resource');
    }

    public function test_member_resources_page_shows_empty_state(): void
    {
        $this->actingAs($this->member);

        Livewire::test(MemberResources::class)
            ->assertSee('No resources available');
    }

    public function test_inactive_resources_not_shown_to_members(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->inactive()->create([
            'title' => 'Inactive Resource',
            'visibility' => 'all',
        ]);

        Livewire::test(MemberResources::class)
            ->assertDontSee('Inactive Resource');
    }

    public function test_admin_staff_only_resources_not_shown_to_members(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->adminStaffOnly()->create([
            'title' => 'Admin Only Resource',
            'is_active' => true,
        ]);

        Livewire::test(MemberResources::class)
            ->assertDontSee('Admin Only Resource');
    }

    public function test_admin_staff_only_resources_shown_to_staff(): void
    {
        $this->actingAs($this->staff);

        Resource::factory()->adminStaffOnly()->create([
            'title' => 'Admin Only Resource',
            'is_active' => true,
        ]);

        Livewire::test(MemberResources::class)
            ->assertSee('Admin Only Resource');
    }

    public function test_admin_staff_only_resources_shown_to_admin(): void
    {
        $this->actingAs($this->admin);

        Resource::factory()->adminStaffOnly()->create([
            'title' => 'Admin Only Resource',
            'is_active' => true,
        ]);

        Livewire::test(MemberResources::class)
            ->assertSee('Admin Only Resource');
    }

    public function test_resources_sorted_by_priority_within_category(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->create([
            'title' => 'Low Priority Resource',
            'category' => ResourceCategory::Events,
            'visibility' => 'all',
            'priority' => 10,
            'is_active' => true,
        ]);

        Resource::factory()->create([
            'title' => 'High Priority Resource',
            'category' => ResourceCategory::Events,
            'visibility' => 'all',
            'priority' => 1,
            'is_active' => true,
        ]);

        $resources = Resource::query()
            ->where('is_active', true)
            ->where('category', ResourceCategory::Events)
            ->orderBy('priority')
            ->get();

        $this->assertEquals('High Priority Resource', $resources->first()->title);
        $this->assertEquals('Low Priority Resource', $resources->last()->title);
    }

    public function test_member_can_search_resources_by_title(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->create([
            'title' => 'Learn to Curl Curriculum',
            'visibility' => 'all',
            'is_active' => true,
        ]);

        Resource::factory()->create([
            'title' => 'League Schedule',
            'visibility' => 'all',
            'is_active' => true,
        ]);

        Livewire::test(MemberResources::class)
            ->set('search', 'Curriculum')
            ->assertSee('Learn to Curl Curriculum')
            ->assertDontSee('League Schedule');
    }

    public function test_member_can_search_resources_by_description(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->create([
            'title' => 'Resource One',
            'description' => 'This resource is for beginners',
            'visibility' => 'all',
            'is_active' => true,
        ]);

        Resource::factory()->create([
            'title' => 'Resource Two',
            'description' => 'This resource is for advanced players',
            'visibility' => 'all',
            'is_active' => true,
        ]);

        Livewire::test(MemberResources::class)
            ->set('search', 'advanced')
            ->assertSee('Resource Two')
            ->assertDontSee('Resource One');
    }

    public function test_member_can_filter_resources_by_category(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->create([
            'title' => 'Events Resource',
            'category' => ResourceCategory::Events,
            'visibility' => 'all',
            'is_active' => true,
        ]);

        Resource::factory()->create([
            'title' => 'Rules Resource',
            'category' => ResourceCategory::Rules,
            'visibility' => 'all',
            'is_active' => true,
        ]);

        Livewire::test(MemberResources::class)
            ->set('categoryFilter', ResourceCategory::Events->value)
            ->assertSee('Events Resource')
            ->assertDontSee('Rules Resource');
    }

    public function test_member_can_clear_filters(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->create([
            'title' => 'Test Resource',
            'visibility' => 'all',
            'is_active' => true,
        ]);

        Livewire::test(MemberResources::class)
            ->set('search', 'Something')
            ->set('categoryFilter', ResourceCategory::Events->value)
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('categoryFilter', '');
    }

    public function test_member_cannot_access_resource_resource(): void
    {
        $this->actingAs($this->member);

        $this->assertFalse(ResourceResource::canViewAny());
    }

    public function test_admin_can_access_resource_resource(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue(ResourceResource::canViewAny());
    }

    public function test_staff_can_access_resource_resource(): void
    {
        $this->actingAs($this->staff);

        $this->assertTrue(ResourceResource::canViewAny());
    }

    public function test_url_resource_has_correct_url(): void
    {
        $resource = Resource::factory()->url()->create([
            'url' => 'https://example.com/page',
        ]);

        $this->assertTrue($resource->isUrl());
        $this->assertFalse($resource->isFile());
        $this->assertEquals('https://example.com/page', $resource->url);
    }

    public function test_file_resource_has_correct_file_path(): void
    {
        $resource = Resource::factory()->file()->create();

        $this->assertTrue($resource->isFile());
        $this->assertFalse($resource->isUrl());
        $this->assertNotNull($resource->file_path);
    }

    public function test_resource_validity_check_works(): void
    {
        $validResource = Resource::factory()->create([
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        $expiredResource = Resource::factory()->create([
            'valid_from' => now()->subWeek(),
            'valid_until' => now()->subDay(),
        ]);

        $futureResource = Resource::factory()->create([
            'valid_from' => now()->addDay(),
            'valid_until' => now()->addWeek(),
        ]);

        $this->assertTrue($validResource->isCurrentlyValid());
        $this->assertFalse($expiredResource->isCurrentlyValid());
        $this->assertFalse($futureResource->isCurrentlyValid());
    }

    public function test_expired_resources_not_shown_to_members(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->create([
            'title' => 'Expired Resource',
            'visibility' => 'all',
            'is_active' => true,
            'valid_from' => now()->subWeek(),
            'valid_until' => now()->subDay(),
        ]);

        Livewire::test(MemberResources::class)
            ->assertDontSee('Expired Resource');
    }

    public function test_future_resources_not_shown_to_members(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->create([
            'title' => 'Future Resource',
            'visibility' => 'all',
            'is_active' => true,
            'valid_from' => now()->addDay(),
            'valid_until' => now()->addWeek(),
        ]);

        Livewire::test(MemberResources::class)
            ->assertDontSee('Future Resource');
    }

    public function test_currently_valid_resources_shown_to_members(): void
    {
        $this->actingAs($this->member);

        Resource::factory()->create([
            'title' => 'Valid Resource',
            'visibility' => 'all',
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        Livewire::test(MemberResources::class)
            ->assertSee('Valid Resource');
    }
}
