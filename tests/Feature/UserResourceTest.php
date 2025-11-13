<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->admin->assignRole('admin');

        $this->member = User::factory()->create(['email' => 'member@example.com']);
        $this->member->assignRole('member');

        $this->staff = User::factory()->create(['email' => 'staff@example.com']);
        $this->staff->assignRole('staff');
    }

    public function test_member_cannot_access_user_resource(): void
    {
        $this->actingAs($this->member);

        $response = $this->get(route('filament.admin.resources.users.index'));

        $response->assertForbidden();
    }

    public function test_staff_can_access_user_resource(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get(route('filament.admin.resources.users.index'));

        $response->assertSuccessful();
    }

    public function test_admin_can_access_user_resource(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('filament.admin.resources.users.index'));

        $response->assertSuccessful();
    }

    public function test_member_cannot_view_individual_user(): void
    {
        $this->actingAs($this->member);

        $otherUser = User::factory()->create();

        Livewire::test(UserResource\Pages\ViewUser::class, [
            'record' => $otherUser->id,
        ])
            ->assertForbidden();
    }

    public function test_staff_can_manage_users(): void
    {
        $this->actingAs($this->staff);

        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $user->id,
        ])
            ->assertSuccessful();
    }
}
