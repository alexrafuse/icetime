<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Membership\Actions\CreateUserFromProfileAction;
use App\Domain\Membership\Data\ProfileData;
use App\Enums\Permission;
use App\Enums\RoleEnum;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserFromProfileActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_new_user_is_assigned_member_role(): void
    {
        $action = new CreateUserFromProfileAction;

        $profile = new ProfileData(
            full_name: 'John Doe',
            email: 'john@example.com',
            first_name: 'John',
            last_name: 'Doe'
        );

        $user = $action->execute($profile);

        $this->assertTrue($user->hasRole(RoleEnum::MEMBER->value));
        $this->assertTrue($user->can(Permission::VIEW_SPARES->value));
        $this->assertTrue($user->can(Permission::MANAGE_OWN_SPARE->value));
    }

    public function test_existing_user_without_role_is_assigned_member_role(): void
    {
        // Create a user without any role
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $this->assertFalse($existingUser->hasRole(RoleEnum::MEMBER->value));

        $action = new CreateUserFromProfileAction;

        $profile = new ProfileData(
            full_name: 'Existing User',
            email: 'existing@example.com',
            first_name: 'Existing',
            last_name: 'User'
        );

        $user = $action->execute($profile);

        $this->assertEquals($existingUser->id, $user->id);
        $this->assertTrue($user->hasRole(RoleEnum::MEMBER->value));
        $this->assertTrue($user->can(Permission::VIEW_SPARES->value));
        $this->assertTrue($user->can(Permission::MANAGE_OWN_SPARE->value));
    }

    public function test_existing_user_with_role_keeps_their_role(): void
    {
        // Create a user with admin role
        $existingUser = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        $existingUser->assignRole(RoleEnum::ADMIN->value);

        $action = new CreateUserFromProfileAction;

        $profile = new ProfileData(
            full_name: 'Admin User',
            email: 'admin@example.com',
            first_name: 'Admin',
            last_name: 'User'
        );

        $user = $action->execute($profile);

        $this->assertEquals($existingUser->id, $user->id);
        $this->assertTrue($user->hasRole(RoleEnum::ADMIN->value));
        $this->assertFalse($user->hasRole(RoleEnum::MEMBER->value));
        $this->assertEquals(1, $user->roles()->count());
    }

    public function test_member_role_has_required_spare_permissions(): void
    {
        $action = new CreateUserFromProfileAction;

        $profile = new ProfileData(
            full_name: 'Test Member',
            email: 'member@example.com',
            first_name: 'Test',
            last_name: 'Member'
        );

        $user = $action->execute($profile);

        // Test all member permissions related to spares and bookings
        $this->assertTrue($user->can(Permission::VIEW_SPARES->value), 'Member should be able to view spares list');
        $this->assertTrue($user->can(Permission::MANAGE_OWN_SPARE->value), 'Member should be able to manage their own spare availability');
        $this->assertTrue($user->can(Permission::VIEW_BOOKINGS->value), 'Member should be able to view bookings');
        $this->assertTrue($user->can(Permission::MANAGE_OWN_BOOKINGS->value), 'Member should be able to manage their own bookings');
        $this->assertTrue($user->can(Permission::VIEW_AREAS->value), 'Member should be able to view areas');
        $this->assertTrue($user->can(Permission::VIEW_OWN_MEMBERSHIP->value), 'Member should be able to view their own membership');
        $this->assertTrue($user->can(Permission::VIEW_PRODUCTS->value), 'Member should be able to view products');

        // Test that members don't have admin permissions
        $this->assertFalse($user->can(Permission::MANAGE_SPARES->value), 'Member should not be able to manage all spares');
        $this->assertFalse($user->can(Permission::MANAGE_USERS->value), 'Member should not be able to manage users');
    }
}
