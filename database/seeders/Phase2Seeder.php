<?php

namespace Database\Seeders;

use App\Models\SecurityPermission;
use App\Models\StaffUser;
use Illuminate\Database\Seeder;

class Phase2Seeder extends Seeder
{
    public function run(): void
    {
        $admin = StaffUser::where('username', 'admin')->first();

        if (!$admin) {
            $this->command->warn('No staff user with username=admin found. Skipping.');
            return;
        }

        $routes = [
            'stock.counts.index',
            'stock.approve',
        ];

        foreach ($routes as $route) {
            SecurityPermission::updateOrCreate(
                ['staff_user_id' => $admin->id, 'route_name' => $route],
                ['can_view' => true]
            );
        }

        $this->command->info('Phase 2 stock count permissions seeded for admin.');
    }
}
