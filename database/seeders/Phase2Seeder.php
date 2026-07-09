<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase2Seeder extends Seeder
{
    public function run(): void
    {
        // Grant all admin staff users (role = admin or first user) access to Phase 2 routes
        $adminIds = DB::connection('mysql')
            ->table('staff_users')
            ->where('is_active', true)
            ->pluck('id');

        $routes = [
            'stock.counts.index',
            'stock.approve',
            'reports.dailyItemCount',
        ];

        foreach ($adminIds as $staffId) {
            foreach ($routes as $route) {
                DB::connection('mysql')->table('security_permissions')->updateOrInsert(
                    ['staff_user_id' => $staffId, 'route_name' => $route],
                    ['can_view' => true, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        $this->command->info('Phase2Seeder: granted stock.counts.index, stock.approve, reports.dailyItemCount to ' . $adminIds->count() . ' staff users.');
    }
}
