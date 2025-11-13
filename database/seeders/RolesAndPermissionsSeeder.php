<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission;
use Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        foreach (Permission::cases() as $permission) {
            PermissionModel::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $member = Role::create(['name' => 'member']);
        $member->givePermissionTo([
            Permission::VIEW_SPARES,
            Permission::MANAGE_OWN_SPARE,
            Permission::VIEW_BOOKINGS,
            Permission::MANAGE_OWN_BOOKINGS,
            Permission::VIEW_AREAS,
            Permission::VIEW_OWN_MEMBERSHIP,
            Permission::VIEW_PRODUCTS,
        ]);

        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo([
            Permission::VIEW_SPARES,
            Permission::MANAGE_SPARES,
            Permission::VIEW_BOOKINGS,
            Permission::MANAGE_BOOKINGS,
            Permission::VIEW_AREAS,
            Permission::VIEW_USERS,
            Permission::MANAGE_USERS,
            Permission::VIEW_MEMBERSHIPS,
            Permission::VIEW_PRODUCTS,
        ]);

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::values());

        // Assign admin role to your email
        $user = User::where('email', 'hello@stacked.dev')->first();
        if ($user) {
            $user->assignRole('admin');
        }
    }
}
