<?php

namespace Database\Seeders;

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

        // Same as user + creator profile extras (bio / photo / socials / followers)
        $creatorPermissions = ContentCreatorPermissions::all();
        foreach ($creatorPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $creatorRole = Role::firstOrCreate(['name' => 'content_creator', 'guard_name' => 'web']);
        $creatorRole->syncPermissions($creatorPermissions);
    }
}
