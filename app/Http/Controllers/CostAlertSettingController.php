<?php

namespace App\Http\Controllers;

use App\Mail\DailyCostAlertMail;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class CostAlertSettingController extends Controller
{
    public function index()
    {
        return view('settings.cost-alert.index', [
            'email'             => AppSetting::get('cost_alert_email', env('COST_ALERT_EMAIL', '')),
            'costThreshold'     => AppSetting::get('cost_threshold', env('COST_THRESHOLD', 45)),
            'itemCostThreshold' => AppSetting::get('item_cost_threshold', env('ITEM_COST_THRESHOLD', 80)),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'cost_alert_email'    => ['nullable', 'string', 'max:500'],
            'cost_threshold'      => ['required', 'numeric', 'min:0', 'max:1000'],
            'item_cost_threshold' => ['required', 'numeric', 'min:0', 'max:1000'],
        ]);

        AppSetting::set('cost_alert_email', $request->string('cost_alert_email')->trim()->toString());
        AppSetting::set('cost_threshold', $request->cost_threshold);
        AppSetting::set('item_cost_threshold', $request->item_cost_threshold);

        return redirect()->route('settings.costAlert')->with('success', 'Cost alert settings updated.');
    }

    public function sendTest()
    {
        $recipients = array_values(array_filter(
            array_map('trim', explode(',', AppSetting::get('cost_alert_email', env('COST_ALERT_EMAIL', ''))))
        ));

        if (empty($recipients)) {
            return back()->with('error', 'No recipient email configured. Save an email address first.');
        }

        $threshold     = (float) AppSetting::get('cost_threshold', env('COST_THRESHOLD', 45));
        $itemThreshold = (float) AppSetting::get('item_cost_threshold', env('ITEM_COST_THRESHOLD', 80));

        $summary = (object) ['revenue' => 1000000, 'cost' => 720000];
        $eod     = (object) ['closed' => now()->format('Y-m-d H:i:s')];
        $items   = new Collection([
            (object) [
                'department' => 'Kitchen',
                'category'   => 'Food',
                'description'=> 'Sample High-Cost Item',
                'qty'        => 10,
                'revenue'    => 100000,
                'cost'       => 90000,
                'costPct'    => 90.0,
            ],
        ]);

        try {
            foreach ($recipients as $email) {
                Mail::to($email)->send(new DailyCostAlertMail(
                    date:          now(),
                    summary:       $summary,
                    costPct:       72.0,
                    threshold:     $threshold,
                    items:         $items,
                    itemThreshold: $itemThreshold,
                    eod:           $eod,
                ));
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }

        return back()->with('success', 'Test email sent to ' . implode(', ', $recipients) . '.');
    }

    public function runCheck(Request $request)
    {
        $request->validate([
            'check_date' => ['required', 'date'],
        ]);

        Artisan::call('cost:check-daily', [
            '--date'  => $request->check_date,
            '--force' => true,
        ]);

        $output = trim(Artisan::output());

        return back()->with('checkOutput', $output);
    }
}
