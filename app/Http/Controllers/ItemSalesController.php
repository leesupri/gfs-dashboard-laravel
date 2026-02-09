<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemSalesController extends Controller
{
    public function index(Request $request)
    {
        [$filters, $from, $to, $diFrom, $diTo] = $this->parseFilters($request);
        $rows = $this->buildRows($filters, $from, $to, $diFrom, $diTo);

        if ($filters['hide_zero']) {
            $rows = $rows->filter(fn($r) =>
                $r->quantity != 0 ||
                $r->subtotal != 0 ||
                $r->discount != 0 ||
                $r->cost != 0 ||
                $r->serviceCharge != 0 ||
                $r->tax != 0
            )->values();
        }

        $byDept = $rows
            ->groupBy(fn($r) => $r->department ?: 'NO DEPARTMENT')
            ->map(fn($d) => $d->groupBy(fn($r) => $r->category ?: 'UNCATEGORIZED'));

        $deptKpi = $byDept->map(fn($cats) => $this->makeKpi($cats->flatten(1)));
        $catKpi  = $byDept->map(fn($cats) => $cats->map(fn($i) => $this->makeKpi($i)));
        $kpi     = $this->makeKpi($rows);

        $hasService = (float)$kpi['service'] != 0;
        $hasTax     = (float)$kpi['tax'] != 0;

        $departments = DB::table('tbl_departments')->orderBy('name')->pluck('name');

        $categories = DB::table('tbl_categories as c')
            ->leftJoin('tbl_departments as d', 'c.department_id', '=', 'd.id')
            ->select('c.name', 'd.name as department')
            ->orderBy('d.name')->orderBy('c.name')
            ->get()->groupBy('department');

        return view('item_sales.index', compact(
            'filters','byDept','kpi','deptKpi','catKpi',
            'departments','categories','hasService','hasTax'
        ));
    }

    public function exportCsv(Request $request)
{
    [$filters, $from, $to, $diFrom, $diTo] = $this->parseFilters($request);

    $rows = $this->buildRows($filters, $from, $to, $diFrom, $diTo);

    // 🔥 Hide zero items (same as UI)
    if ($filters['hide_zero']) {
        $rows = $rows->filter(fn($r) =>
            (float)$r->quantity != 0 ||
            (float)$r->subtotal != 0 ||
            (float)$r->discount != 0 ||
            (float)$r->cost != 0 ||
            (float)$r->serviceCharge != 0 ||
            (float)$r->tax != 0
        )->values();
    }

    // KPI AFTER filtering
    $kpi = $this->makeKpi($rows);

    $hasService = (float)$kpi['service'] != 0;
    $hasTax     = (float)$kpi['tax'] != 0;

    return response()->streamDownload(function () use ($rows, $hasService, $hasTax) {
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        $header = [
            'Department','Category','Code','Name','Qty',
            'Revenue','Discount','Net Revenue','Cost','Profit'
        ];
        if ($hasService) $header[] = 'Service';
        if ($hasTax)     $header[] = 'Tax';
        $header[] = 'Total';
        $header[] = 'Cost %';

        fputcsv($out, $header);

        foreach ($rows as $r) {

            // Extra safety (Jasper-style)
            if (
                (float)$r->quantity == 0 &&
                (float)$r->subtotal == 0 &&
                (float)$r->discount == 0 &&
                (float)$r->cost == 0 &&
                (float)$r->serviceCharge == 0 &&
                (float)$r->tax == 0
            ) {
                continue;
            }

            $net = $r->subtotal - $r->discount;
            $total = $net + $r->serviceCharge + $r->tax;
            $costPct = $net > 0 ? ($r->cost / $net) * 100 : 0;

            $row = [
                $r->department,
                $r->category,
                $r->code,
                $r->name,
                $r->quantity,
                $r->subtotal,
                $r->discount,
                $net,
                $r->cost,
                $r->subtotal - $r->cost,
            ];

            if ($hasService) $row[] = $r->serviceCharge;
            if ($hasTax)     $row[] = $r->tax;

            $row[] = $total;
            $row[] = round($costPct, 2);

            fputcsv($out, $row);
        }

        fclose($out);
    }, 'item-sales_'.date('Ymd_His').'.csv');
}

    /* ---------------- helpers ---------------- */

    private function parseFilters(Request $request): array
    {
        $from = $request->start ? Carbon::parse($request->start)->startOfDay() : now()->startOfDay();
        $to   = $request->end   ? Carbon::parse($request->end)->endOfDay()   : now()->endOfDay();

        return [
            [
                'q' => $request->q,
                'dept' => $request->dept,
                'cat' => $request->cat,
                'hide_zero' => $request->boolean('hide_zero'),
            ],
            $from,
            $to,
            (int)$from->format('Ym'),
            (int)$to->format('Ym')
        ];
    }

    private function buildRows($filters, $from, $to, $diFrom, $diTo)
    {
        $temp = DB::table('tbl_sales as s')
            ->join('tbl_sales_lines as sl', 's.id', '=', 'sl.sales_id')
            ->whereBetween('s.dateIndex', [$diFrom, $diTo])
            ->whereBetween('s.date', [$from, $to])
            ->where('s.voidCheck', 0)
            ->groupBy('sl.item_id')
            ->selectRaw('
                sl.item_id,
                SUM(sl.quantity - sl.voidQuantity) quantity,
                SUM((sl.quantity - sl.voidQuantity) * sl.unitPrice) subtotal,
                SUM((sl.quantity - sl.voidQuantity) * sl.unitCost) cost,
                SUM(sl.discountAmount) discount,
                SUM(sl.serviceChargeAmount) serviceCharge,
                SUM(sl.tax1Amount + sl.tax2Amount + sl.tax3Amount) tax
            ');

        $q = DB::table('tbl_items as i')
            ->leftJoinSub($temp, 't', 'i.id', '=', 't.item_id')
            ->leftJoin('tbl_categories as c', 'i.category_id', '=', 'c.id')
            ->leftJoin('tbl_departments as d', 'c.department_id', '=', 'd.id')
            ->where('i.sales', 1)
            ->selectRaw('
                d.name department,
                c.name category,
                i.code,
                i.name,
                COALESCE(t.quantity,0) quantity,
                COALESCE(t.subtotal,0) subtotal,
                COALESCE(t.cost,0) cost,
                COALESCE(t.discount,0) discount,
                COALESCE(t.serviceCharge,0) serviceCharge,
                COALESCE(t.tax,0) tax
            ');

        if ($filters['dept']) $q->where('d.name', $filters['dept']);
        if ($filters['cat'])  $q->where('c.name', $filters['cat']);
        if ($filters['q']) {
            $kw = "%{$filters['q']}%";
            $q->where(fn($qq) => $qq->where('i.name','like',$kw)->orWhere('i.code','like',$kw));
        }

        return $q->orderBy('d.name')->orderBy('c.name')->orderBy('i.name')->get();
    }

    private function makeKpi($rows): array
    {
        $rev = $rows->sum('subtotal');
        $disc = $rows->sum('discount');
        $net = $rev - $disc;
        $cost = $rows->sum('cost');

        return [
            'qty' => $rows->sum('quantity'),
            'revenue' => $rev,
            'discount' => $disc,
            'netRevenue' => $net,
            'cost' => $cost,
            'profit' => $rev - $cost,
            'service' => $rows->sum('serviceCharge'),
            'tax' => $rows->sum('tax'),
            'total' => $net + $rows->sum('serviceCharge') + $rows->sum('tax'),
            'costPct' => $net > 0 ? ($cost / $net) * 100 : 0,
        ];
    }
}
