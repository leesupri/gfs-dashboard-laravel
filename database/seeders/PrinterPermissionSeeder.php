<?php

namespace Database\Seeders;

use App\Models\SecurityPermission;
use App\Models\StaffUser;
use Illuminate\Database\Seeder;

/**
 * Seeds printer & print permissions for the admin account (id = 1).
 *
 * Run with:
 *   php artisan db:seed --class=PrinterPermissionSeeder
 */
class PrinterPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = StaffUser::find(1);

        if (!$admin) {
            $this->command->warn('No staff user with id=1 found. Skipping.');
            return;
        }

        $routes = [
            'settings.printer',    // printer settings page
            'print.itemSales',     // thermal item sales
            'print.summarySales',  // thermal summary sales
            'print.receipt',       // thermal receipt
        ];

        foreach ($routes as $route) {
            SecurityPermission::updateOrCreate(
                ['staff_user_id' => $admin->id, 'route_name' => $route],
                ['can_view' => true]
            );
        }

        $this->command->info("Printer permissions seeded for admin (id=1).");
    }
}
