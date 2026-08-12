<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'type')) {
                $after = Schema::hasColumn('users', 'status') ? 'status' : 'email';
                $table->string('type', 32)->default('user')->after($after)->index();
            }
        });

        $creatorIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'content_creator')
            ->where('model_has_roles.model_type', User::class)
            ->pluck('model_has_roles.model_id');

        if ($creatorIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $creatorIds)->update(['type' => 'content_creator']);
        }

        $linkedIds = DB::table('creators')->whereNull('deleted_at')->pluck('user_id');
        if ($linkedIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $linkedIds)->update(['type' => 'content_creator']);
        }

        if (Schema::hasTable('creator_join_requests')) {
            $approvedEmails = DB::table('creator_join_requests')
                ->where('status', 'approved')
                ->pluck('email')
                ->map(fn ($email) => mb_strtolower((string) $email))
                ->unique()
                ->all();

            $pendingIds = DB::table('creator_join_requests')
                ->where('status', 'pending')
                ->get(['id', 'email'])
                ->filter(fn ($row) => in_array(mb_strtolower((string) $row->email), $approvedEmails, true))
                ->pluck('id');

            if ($pendingIds->isNotEmpty()) {
                DB::table('creator_join_requests')
                    ->whereIn('id', $pendingIds)
                    ->update([
                        'status' => 'rejected',
                        'admin_note' => 'Closed automatically: this email is already an approved content creator.',
                        'reviewed_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
