<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\ContentCreatorPermissions;
use App\Support\WebsiteUserPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['super_admin', 'admin', 'moderator'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $websitePermissions = WebsiteUserPermissions::all();
        foreach ($websitePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRole->syncPermissions($websitePermissions);

        $creatorPermissions = ContentCreatorPermissions::all();
        foreach ($creatorPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $creatorRole = Role::firstOrCreate(['name' => 'content_creator', 'guard_name' => 'web']);
        $creatorRole->syncPermissions($creatorPermissions);

        // Ensure there is at least one full-access Filament admin on empty DBs.
        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@sawtgaza.com');
        $adminPassword = env('SEED_ADMIN_PASSWORD', 'Admin@12345');

        $admin = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($adminEmail)])->first();

        if (! $admin) {
            $admin = User::create([
                'name' => 'Super Admin',
                'email' => $adminEmail,
                'password' => $adminPassword,
                'status' => 'active',
                'type' => User::TYPE_ADMIN,
                'country_code' => '+970',
            ]);
        } else {
            $admin->forceFill([
                'status' => 'active',
                'type' => User::TYPE_ADMIN,
            ])->save();

            if ($adminPassword && env('SEED_ADMIN_RESET_PASSWORD', false)) {
                $admin->forceFill(['password' => $adminPassword])->save();
            }
        }

        $admin->syncRoles(['super_admin']);
        $admin->removeRole(User::ROLE_USER);
        $admin->removeRole(User::ROLE_CONTENT_CREATOR);
    }
}
