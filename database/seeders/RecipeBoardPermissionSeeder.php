<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeBoardPermissionSeeder extends Seeder
{
    /**
     * Only one route name needed in security_permissions.
     * The export route is aliased to this in CheckRoutePermission,
     * so granting 'reports.recipe-board' covers both view and export.
     */
    private string $routeName = 'reports.recipe-board';

    public function run(): void
    {
        // ── Grant to specific users by username ────────────────────────────
        // Add or remove usernames as needed.
        $userIds = DB::table('staff_users')
            ->whereIn('username', [
                'admin',
                // 'manager1',
            ])
            ->where('is_active', true)
            ->pluck('id');

        // ── Or grant to ALL active staff (uncomment to use) ────────────────
        // $userIds = DB::table('staff_users')
        //     ->where('is_active', true)
        //     ->pluck('id');

        if ($userIds->isEmpty()) {
            $this->command->warn('No matching staff users found. Check the usernames.');
            return;
        }

        $now  = now();
        $rows = $userIds->map(fn ($id) => [
            'staff_user_id' => $id,
            'route_name'    => $this->routeName,
            'can_view'      => true,
            'created_at'    => $now,
            'updated_at'    => $now,
        ])->all();

        // Safe to re-run — unique(staff_user_id, route_name) prevents duplicates
        DB::table('security_permissions')->insertOrIgnore($rows);

        $this->command->info(
            "'{$this->routeName}' permission granted to {$userIds->count()} user(s)."
        );
    }
}