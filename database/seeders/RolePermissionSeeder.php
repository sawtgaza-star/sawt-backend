<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // الأدوار الأساسية — الصلاحيات التفصيلية (CRUD لكل Resource) بيتولدوا تلقائياً
        // من Filament Shield بأمر: php artisan shield:generate --all
        $roles = ['super_admin', 'admin', 'moderator'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
