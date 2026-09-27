<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\RoleEnum;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BoardPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        collect(Permission::cases())->each(
            fn (Permission $permission) => PermissionModel::create(['name' => $permission->value])
        );

        $boardMemberRole = Role::create(['name' => RoleEnum::BOARD_MEMBER->value]);
        $boardMemberRole->givePermissionTo(
            collect(RoleEnum::BOARD_MEMBER->permissions())->all()
        );

        $memberRole = Role::create(['name' => RoleEnum::MEMBER->value]);
        $memberRole->givePermissionTo(
            collect(RoleEnum::MEMBER->permissions())->all()
        );

        $adminRole = Role::create(['name' => RoleEnum::ADMIN->value]);
        $adminRole->givePermissionTo(Permission::values());
    }

    public function test_board_member_has_board_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::BOARD_MEMBER->value);

        $this->assertTrue($user->can(Permission::VIEW_BOARD_MEETINGS->value));
        $this->assertTrue($user->can(Permission::MANAGE_BOARD_MEETINGS->value));
        $this->assertTrue($user->can(Permission::VIEW_SPONSORS->value));
        $this->assertTrue($user->can(Permission::MANAGE_SPONSORS->value));
        $this->assertTrue($user->can(Permission::VIEW_CLUB_POLICIES->value));
        $this->assertTrue($user->can(Permission::MANAGE_CLUB_POLICIES->value));
    }

    public function test_regular_member_cannot_access_board_resources(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::MEMBER->value);

        $this->assertFalse($user->can(Permission::VIEW_BOARD_MEETINGS->value));
        $this->assertFalse($user->can(Permission::MANAGE_BOARD_MEETINGS->value));
        $this->assertFalse($user->can(Permission::VIEW_SPONSORS->value));
        $this->assertFalse($user->can(Permission::MANAGE_CLUB_POLICIES->value));
    }

    public function test_admin_has_all_board_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::ADMIN->value);

        $this->assertTrue($user->can(Permission::VIEW_BOARD_MEETINGS->value));
        $this->assertTrue($user->can(Permission::MANAGE_BOARD_MEETINGS->value));
        $this->assertTrue($user->can(Permission::VIEW_SPONSORS->value));
        $this->assertTrue($user->can(Permission::MANAGE_SPONSORS->value));
        $this->assertTrue($user->can(Permission::VIEW_CLUB_POLICIES->value));
        $this->assertTrue($user->can(Permission::MANAGE_CLUB_POLICIES->value));
        $this->assertTrue($user->can(Permission::VIEW_CLUB_BYLAWS->value));
        $this->assertTrue($user->can(Permission::MANAGE_CLUB_BYLAWS->value));
    }

    public function test_board_member_also_has_view_permissions_for_existing_resources(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::BOARD_MEMBER->value);

        $this->assertTrue($user->can(Permission::VIEW_SPARES->value));
        $this->assertTrue($user->can(Permission::VIEW_BOOKINGS->value));
        $this->assertTrue($user->can(Permission::VIEW_AREAS->value));
        $this->assertTrue($user->can(Permission::VIEW_MEMBERSHIPS->value));
        $this->assertTrue($user->can(Permission::VIEW_PRODUCTS->value));
        $this->assertTrue($user->can(Permission::VIEW_RESOURCES->value));
    }
}
