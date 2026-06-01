<?php

namespace App\Console\Commands;

use App\Mail\DailyCostAlertMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckDailyCost extends Command
{
    protected $signature = 'cost:check-daily
                            {--date= : Date to check (Y-m-d). Defaults to today.}
                            {--force : Send alert even if already sent today.}';

    protected $description = 'Check if EOD is closed, calculate cost %, and send alert if over threshold.';

    public function handle(): int
    {
        $date    = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();
        $dateStr = $date->toDateString();

        // ── 1. Guard: only alert once per day ───────────────────────────────
        $cacheKey = "cost_alert_sent_{$dateStr}";
        if (!$this->option('force') && Cache::has($cacheKey)) {
            $this->info("Alert already sent for {$dateStr} — use --force to resend.");
            return self::SUCCESS;
        }

        // ── 2. Check if EOD was closed ───────────────────────────────────────
        $eod = DB::connection('reports_mysql')
            ->table('tbl_daily_procedures')
            ->whereDate('date', $dateStr)
            ->whereNotNull('closed')
            ->first();

        if (!$eod) {
            $this->info("EOD not yet closed for {$dateStr}. Skipping.");
            return self::SUCCESS;
        }

        $this->info("EOD closed at {$eod->closed}. Calculating cost…");

        // ── 3. Calculate day-level cost vs revenue ───────────────────────────
        $from = $date->copy()->startOfDay();
        $to   = $date->copy()->endOfDay();

        $summary = DB::connection('reports_mysql')
            ->table('v_order_index')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("
                COALESCE(SUM(quantity * unitPrice), 0) AS revenue,
                COALESCE(SUM(quantity * unitCost),  0) AS cost
            ")
            ->first();

        if (!$summary || $summary->revenue <= 0) {
            $this->info("No sales data found for {$dateStr}.");
            Cache::put($cacheKey, true, now()->endOfDay());
            return self::SUCCESS;
        }

        $costPct       = ($summary->cost / $summary->revenue) * 100;
        $threshold     = (float) config('app.cost_threshold',      env('COST_THRESHOLD',      45));
        $itemThreshold = (float) config('app.item_cost_threshold',  env('ITEM_COST_THRESHOLD', 95));

        $this->line(sprintf(
            "Cost: %s / Revenue: %s = %.2f%%  (threshold: %.0f%%)",
            number_format($summary->cost, 0),
            number_format($summary->revenue, 0),
            $costPct,
            $threshold
        ));

        if ($costPct <= $threshold) {
            $this->info("Cost is within threshold. No alert needed.");
            Cache::put($cacheKey, true, now()->endOfDay());
            return self::SUCCESS;
        }

        $this->warn("Cost exceeds threshold! Fetching high-cost items…");

        // ── 4. Find items with cost % > item threshold ───────────────────────
        $highCostItems = DB::connection('reports_mysql')
            ->table('v_order_index')
            ->whereBetween('date', [$from, $to])
            ->selectRaw("
                COALESCE(department, 'N/A')  AS department,
                COALESCE(category,   'N/A')  AS category,
                description,
                SUM(quantity)                              AS qty,
                SUM(quantity * unitPrice)                  AS revenue,
                SUM(quantity * unitCost)                   AS cost,
                SUM(quantity * unitCost)
                  / NULLIF(SUM(quantity * unitPrice), 0)
                  * 100                                    AS costPct
            ")
            ->groupBy('department', 'category', 'description')
            ->havingRaw(
                "SUM(quantity * unitCost) / NULLIF(SUM(quantity * unitPrice), 0) * 100 > ?",
                [$itemThreshold]
            )
            ->orderByDesc('costPct')
            ->get();

        $this->table(
            ['Dept', 'Category', 'Item', 'Qty', 'Revenue', 'Cost', 'Cost%'],
            $highCostItems->map(fn($r) => [
                $r->department, $r->category, $r->description,
                number_format($r->qty, 0),
                number_format($r->revenue, 0),
                number_format($r->cost, 0),
                number_format($r->costPct, 1) . '%',
            ])->toArray()
        );

        // ── 5. Send email alert ───────────────────────────────────────────────
        $recipients = array_values(array_filter(
            array_map('trim', explode(',', env('COST_ALERT_EMAIL', '')))
        ));

        if (empty($recipients)) {
            $this->warn("COST_ALERT_EMAIL not configured — alert not sent.");
            Cache::put($cacheKey, true, now()->endOfDay());
            return self::SUCCESS;
        }

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new DailyCostAlertMail(
                    date:          $date,
                    summary:       $summary,
                    costPct:       $costPct,
                    threshold:     $threshold,
                    items:         $highCostItems,
                    itemThreshold: $itemThreshold,
                    eod:           $eod,
                ));
                $this->info("Alert sent to {$email}");
            } catch (\Throwable $e) {
                $this->error("Failed to send to {$email}: {$e->getMessage()}");
            }
        }

        Cache::put($cacheKey, true, now()->endOfDay());
        return self::SUCCESS;
    }
}
