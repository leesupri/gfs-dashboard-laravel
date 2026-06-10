<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase1Seeder extends Seeder
{
    public function run(): void
    {
        DB::table('app_settings')->insertOrIgnore([
            ['key' => 'app_name',              'value' => 'GFS Dashboard'],
            ['key' => 'logo_path',             'value' => null],
            ['key' => 'cost_alert_threshold',  'value' => '45'],
            ['key' => 'sales_alert_threshold', 'value' => '80'],
        ]);

        $this->command->info('Phase 1 default app settings seeded.');
    }
}
