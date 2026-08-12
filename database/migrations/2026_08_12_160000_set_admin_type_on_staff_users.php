<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $adminIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', User::FILAMENT_ROLES)
            ->where('model_has_roles.model_type', User::class)
            ->pluck('model_has_roles.model_id');

        if ($adminIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $adminIds)->update(['type' => User::TYPE_ADMIN]);
        }

        $creatorIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', User::ROLE_CONTENT_CREATOR)
            ->where('model_has_roles.model_type', User::class)
            ->pluck('model_has_roles.model_id');

        if ($creatorIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $creatorIds)
                ->where('type', '!=', User::TYPE_ADMIN)
                ->update(['type' => User::TYPE_CONTENT_CREATOR]);
        }

        if (DB::getSchemaBuilder()->hasTable('creators')) {
            $linkedIds = DB::table('creators')->whereNull('deleted_at')->pluck('user_id');
            if ($linkedIds->isNotEmpty()) {
                DB::table('users')
                    ->whereIn('id', $linkedIds)
                    ->where('type', '!=', User::TYPE_ADMIN)
                    ->update(['type' => User::TYPE_CONTENT_CREATOR]);
            }
        }

        DB::table('users')
            ->whereNotIn('type', User::TYPES)
            ->update(['type' => User::TYPE_USER]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('type', User::TYPE_ADMIN)
            ->update(['type' => User::TYPE_USER]);
    }
};
