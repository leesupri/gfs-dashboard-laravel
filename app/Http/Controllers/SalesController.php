<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesController extends Controller
{
    /* =========================
     *  INDEX )
     * ========================= */
     public function index(Request $request)
{
    // ✅ Default to TODAY
    $start = $request->query('start', now()->toDateString());
    $end   = $request->query('end', now()->toDateString());

    $q = trim((string) $request->query('q', ''));
    $outlet = trim((string) $request->query('outlet', ''));
    $paymentType = strtolower(trim((string) $request->query('payment_type', '')));
    $sort = $request->query('sort', 'datetime_desc');

    $pageSize = 16;

    /* =========================
     * PAYMENT AGG (1 ROW / INVOICE)
     * ========================= */
    $payAgg = DB::connection('reports_mysql')->table('v_payment_index')
        ->selectRaw("
            invoice_id,
            SUM(total) AS paid_total,
            COUNT(*) AS pay_rows,

            CASE
              WHEN COUNT(DISTINCT
                CASE
                  WHEN paymentType='CASH' THEN 'CASH'
                  WHEN paymentType='CARD' AND name LIKE '%QRIS%' THEN 'QRIS'
                  WHEN paymentType='CARD' THEN 'EDC'
                  ELSE 'OTHER'
                END
              ) = 1
              THEN MAX(
                CASE
                  WHEN paymentType='CASH' THEN 'CASH'
                  WHEN paymentType='CARD' AND name LIKE '%QRIS%' THEN 'QRIS'
                  WHEN paymentType='CARD' THEN 'EDC'
                  ELSE 'OTHER'
                END
              )
              ELSE 'MIXED'
            END AS paymentBucket,

            GROUP_CONCAT(
              DISTINCT COALESCE(NULLIF(name,''), paymentType)
              ORDER BY COALESCE(NULLIF(name,''), paymentType)
              SEPARATOR ', '
            ) AS paymentMethod
        ")
        ->groupBy('invoice_id');

    /* =========================
     * BASE QUERY = tbl_sales (JRXML truth)
     * ========================= */
    $base = DB::connection('reports_mysql')->table('tbl_sales as s')
        ->leftJoinSub($payAgg, 'p', function ($join) {
            $join->on('p.invoice_id', '=', 's.invoice_id');
        })
        ->selectRaw("
            s.invoice_id,
            s.date AS created,              
            s.closedTime,
            s.tableName,
            s.subtotal,
            s.discountAmount,
            s.serviceChargeAmount,
            (s.tax1Amount + s.tax2Amount + s.tax3Amount) AS tax1Amount,  -- ✅ blade expects tax1Amount
            s.total,
            s.cashierName,

            COALESCE(p.paymentBucket,'OTHER') AS paymentBucket,
            COALESCE(p.paymentMethod,'-') AS paymentMethod,
            COALESCE(p.paid_total,0) AS paid_total
        ")
        ->where('s.closed', 1)
        ->where('s.voidCheck', 0)
        ->whereBetween('s.date', [
            $start . ' 00:00:00',
            $end   . ' 23:59:59',
        ]);

    /* =========================
     * SEARCH
     * ========================= */
    if ($q) {
        $base->where(function ($w) use ($q) {
            $w->where('s.invoice_id', 'like', "%{$q}%")
              ->orWhere('s.tableName', 'like', "%{$q}%")
              ->orWhere('s.cashierName', 'like', "%{$q}%")
              ->orWhere('p.paymentMethod', 'like', "%{$q}%");
        });
    }

    /* =========================
     * OUTLET FILTER
     * ========================= */
    if ($outlet) {
        $o = strtoupper($outlet);
        if ($o === 'HALL') $base->where('s.tableName', 'like', 'HALL%');
        elseif ($o === 'BALKON') $base->where('s.tableName', 'like', 'Balkon%');
        elseif ($o === 'VIP') $base->where('s.tableName', 'like', 'VIP%');
        elseif ($o === 'OTHER') {
            $base->where(function ($w) {
                $w->whereNull('s.tableName')
                  ->orWhere('s.tableName', '')
                  ->orWhere(function ($w2) {
                      $w2->where('s.tableName', 'not like', 'HALL%')
                         ->where('s.tableName', 'not like', 'Balkon%')
                         ->where('s.tableName', 'not like', 'VIP%');
                  });
            });
        }
    }

    /* =========================
     * PAYMENT FILTER (USE AGG BUCKET)
     * ========================= */
    if ($paymentType) {
        if ($paymentType === 'cash') $base->where('p.paymentBucket', 'CASH');
        elseif ($paymentType === 'qris') $base->where('p.paymentBucket', 'QRIS');
        elseif ($paymentType === 'edc') $base->where('p.paymentBucket', 'EDC');
        elseif ($paymentType === 'other') $base->whereIn('p.paymentBucket', ['OTHER','MIXED']);
    }

    /* =========================
     * KPI (tbl_sales only)
     * ========================= */
    $kpi = DB::connection('reports_mysql')->table('tbl_sales')
        ->whereBetween('date', [
            $start . ' 00:00:00',
            $end   . ' 23:59:59',
        ])
        ->where('closed', 1)
        ->where('voidCheck', 0)
        ->selectRaw("
            COUNT(*) trxCount,
            COALESCE(SUM(total),0) grossSales,
            COALESCE(SUM(subtotal),0) subtotalTotal,
            COALESCE(SUM(discountAmount),0) discountTotal,
            COALESCE(SUM(serviceChargeAmount),0) serviceTotal,
            COALESCE(SUM(tax1Amount + tax2Amount + tax3Amount),0) taxTotal
        ")
        ->first();

    /* =========================
     * PAYMENT BREAKDOWN (FROM AGG)
     * ========================= */
    $paymentBreakdownRows = DB::connection('reports_mysql')->table('tbl_sales as s')
    ->leftJoinSub($payAgg, 'p', function ($j) {
        $j->on('p.invoice_id', '=', 's.invoice_id');
    })
    ->where('s.closed', 1)
    ->where('s.voidCheck', 0)
    ->whereBetween('s.date', [
        $start . ' 00:00:00',
        $end   . ' 23:59:59',
    ])
    ->selectRaw("
        COALESCE(p.paymentBucket,'OTHER') as bucket,
        COUNT(*) as trxCount,
        SUM(s.total) as amountTotal
    ")
    ->groupBy('bucket')
    ->get();

   $paymentBreakdown = [
    'CASH' => ['trxCount'=>0,'amountTotal'=>0],
    'EDC'  => ['trxCount'=>0,'amountTotal'=>0],
    'QRIS' => ['trxCount'=>0,'amountTotal'=>0],
    'OTHER'=> ['trxCount'=>0,'amountTotal'=>0],
];

foreach ($paymentBreakdownRows as $r) {
    $b = strtoupper($r->bucket);
    if (!isset($paymentBreakdown[$b])) $b = 'OTHER';

    $paymentBreakdown[$b] = [
        'trxCount' => (int)$r->trxCount,
        'amountTotal' => (float)$r->amountTotal,
    ];
}

    /* =========================
     * SORTING
     * ========================= */
    if ($sort === 'datetime_asc') {
        $base->orderBy('s.date', 'asc')->orderBy('s.invoice_id', 'asc');
    } elseif ($sort === 'total_desc') {
        $base->orderBy('s.total', 'desc')->orderBy('s.invoice_id', 'desc');
    } elseif ($sort === 'total_asc') {
        $base->orderBy('s.total', 'asc')->orderBy('s.invoice_id', 'asc');
    } else {
        $base->orderBy('s.date', 'desc')->orderBy('s.invoice_id', 'desc');
    }

    /* =========================
     * PAGINATION
     * ========================= */
    $rows = $base->paginate($pageSize)->withQueryString();

    /* =========================
     * UI FIELDS
     * ========================= */
    $rows->getCollection()->transform(function ($r) {
        $r->cashier = $r->cashierName ?: '-';
        return $r;
    });

    return view('sales.index', compact('rows', 'kpi', 'paymentBreakdown'))->with('filters', [
        'q' => $q,
        'start' => $start,
        'end' => $end,
        'outlet' => $outlet,
        'payment_type' => $paymentType,
        'sort' => $sort,
    ]);
}


    /* =========================
     *  RECEIPT (JRXML-CORRECT)
     * ========================= */
    public function receipt($invoice_id)
{
    // 1️⃣ SALE HEADER (ONE ROW)
    $sale = DB::connection('reports_mysql')->table('tbl_sales as s')
        ->leftJoin('tbl_employees as e', 's.closedBy_id', '=', 'e.id')
        ->leftJoin('tbl_customers as c', 's.customer_id', '=', 'c.id')
        ->where('s.invoice_id', (int)$invoice_id)
        ->where('s.voidCheck', '!=', 1)
        ->selectRaw("
            s.invoice_id,
            s.date,
            s.closedTime,
            s.tableName,
            s.type,
            s.pax,
            s.subtotal,
            s.discountAmount,
            s.serviceChargeAmount,
            (s.tax1Amount + s.tax2Amount + s.tax3Amount) AS tax1Amount,
            s.total,
            s.printCount,
            e.name AS cashier,
            c.name AS member
        ")
        ->first();

    if (!$sale) {
        return response()->json([
            'message' => 'Invoice not found'
        ], 404);
    }

    // 2️⃣ ITEMS (DETAIL)
    $items = DB::connection('reports_mysql')->table('v_order_index')
    ->select([
        'id','invoice_id','created','salesType',
        'description','quantity','unitPrice',
        'discountAmount','department','category',
    ])
    ->where('invoice_id', (int)$invoice_id)
    ->whereRaw("COALESCE(NULLIF(TRIM(description),''),'') <> ''") // must have description
    ->where(function ($w) {
        $w->where('quantity', '!=', 0)
          ->orWhere('unitPrice', '!=', 0)
          ->orWhere('discountAmount', '!=', 0)

          // ✅ keep modifiers even if 0 price/qty
          ->orWhereRaw("UPPER(COALESCE(category,'')) = 'MODIFIER'")
          ->orWhereRaw("UPPER(COALESCE(department,'')) = 'MODIFIER'")

          // ✅ keep “note-style” rows your UI groups as modifiers
          ->orWhereRaw("LEFT(TRIM(description),1) IN ('~','-')");
    })
    ->orderBy('created')
    ->orderBy('id')
    ->get();

    // 3️⃣ PAYMENTS (GROUPED)
    $payments = DB::connection('reports_mysql')->table('v_payment_index')
        ->selectRaw("
            CASE
              WHEN paymentType = 'CASH' THEN 'CASH'
              WHEN paymentType = 'CARD' AND name LIKE '%QRIS%' THEN 'QRIS'
              WHEN paymentType = 'CARD' THEN 'EDC'
              ELSE 'OTHER'
            END AS bucket,
            COALESCE(NULLIF(name,''), paymentType) AS method,
            ROUND(SUM(total), 2) AS amount
        ")
        ->where('invoice_id', (int)$invoice_id)
        ->groupBy('bucket', 'method')
        ->orderByDesc('amount')
        ->get();

    $paidTotal = (float) $payments->sum('amount');
    $billTotal = (float) $sale->total;

    return response()->json([
        'invoice_id' => (int)$invoice_id,
        'sale' => $sale,
        'items' => $items,
        'payments' => $payments,
        'paid_total' => round($paidTotal, 2),
        'bill_total' => round($billTotal, 2),
        'diff' => round($paidTotal - $billTotal, 2),
    ]);
}

    /* =========================
     *  CSV EXPORT (2 DECIMAL)
     * ========================= */
    public function export(Request $request)
{
    $base = $this->buildSalesQuery($request);

    return response()->streamDownload(function () use ($base) {
        $out = fopen('php://output', 'w');

        fputcsv($out, [
            'invoice_id','date','closedTime','table','cashier',
            'payment_bucket','payment_method',
            'subtotal','discount','service','tax','total',
            'paid_total','diff'
        ]);

        foreach ($base->cursor() as $r) {
            fputcsv($out, [
                $r->invoice_id,
                $r->date,
                $r->closedTime,
                $r->tableName,
                $r->cashierName,
                $r->paymentBucket,
                $r->paymentMethod,
                number_format((float)$r->subtotal, 2, '.', ''),
                number_format((float)$r->discountAmount, 2, '.', ''),
                number_format((float)$r->serviceChargeAmount, 2, '.', ''),
                number_format((float)$r->taxAmount, 2, '.', ''),
                number_format((float)$r->total, 2, '.', ''),
                number_format((float)$r->paid_total, 2, '.', ''),
                number_format((float)$r->paid_total - (float)$r->total, 2, '.', ''),
            ]);
        }

        fclose($out);
    }, 'sales_transactions_' . date('Ymd_His') . '.csv');
}

/* =========================
 *  BASE QUERY FOR EXPORT
 *  tbl_sales (1 row / invoice)
 *  + aggregated payments (1 row / invoice)
 * ========================= */
private function buildSalesQuery(Request $request)
{
    $q = trim((string) $request->query('q', ''));
    $start = (string) $request->query('start', now()->toDateString());
    $end   = (string) $request->query('end', now()->toDateString());
    $outlet = trim((string) $request->query('outlet', ''));
    $paymentType = strtolower(trim((string) $request->query('payment_type', '')));
    $sort = (string) $request->query('sort', 'datetime_desc');

    // ✅ Aggregate payments to 1 row per invoice
    $payAgg = DB::connection('reports_mysql')->table('v_payment_index')
        ->selectRaw("
            invoice_id,
            SUM(total) AS paid_total,
            COUNT(*) AS pay_rows,

            CASE
              WHEN COUNT(DISTINCT
                CASE
                  WHEN paymentType='CASH' THEN 'CASH'
                  WHEN paymentType='CARD' AND name LIKE '%QRIS%' THEN 'QRIS'
                  WHEN paymentType='CARD' THEN 'EDC'
                  ELSE 'OTHER'
                END
              ) = 1
              THEN MAX(
                CASE
                  WHEN paymentType='CASH' THEN 'CASH'
                  WHEN paymentType='CARD' AND name LIKE '%QRIS%' THEN 'QRIS'
                  WHEN paymentType='CARD' THEN 'EDC'
                  ELSE 'OTHER'
                END
              )
              ELSE 'MIXED'
            END AS paymentBucket,

            GROUP_CONCAT(
              DISTINCT COALESCE(NULLIF(name,''), paymentType)
              ORDER BY COALESCE(NULLIF(name,''), paymentType)
              SEPARATOR ', '
            ) AS paymentMethod
        ")
        ->groupBy('invoice_id');

    // ✅ Base = tbl_sales (JRXML truth)
    $base = DB::connection('reports_mysql')->table('tbl_sales as s')
        ->leftJoinSub($payAgg, 'p', function ($join) {
            $join->on('p.invoice_id', '=', 's.invoice_id');
        })
        ->selectRaw("
            s.invoice_id,
            s.date,
            s.closedTime,
            s.tableName,
            s.cashierName,
            s.subtotal,
            s.discountAmount,
            s.serviceChargeAmount,
            (s.tax1Amount + s.tax2Amount + s.tax3Amount) AS taxAmount,
            s.total,
            COALESCE(p.paid_total, 0) AS paid_total,
            COALESCE(p.paymentBucket, 'OTHER') AS paymentBucket,
            COALESCE(p.paymentMethod, '-') AS paymentMethod
        ")
        ->where('s.closed', 1)
        ->where('s.voidCheck', 0);

    // Date filter (use s.date like JRXML)
    if ($start) $base->where('s.date', '>=', $start . ' 00:00:00');
    if ($end)   $base->where('s.date', '<=', $end . ' 23:59:59');

    // Outlet filter (match your UI)
    if ($outlet) {
        $o = strtoupper($outlet);
        if ($o === 'HALL') $base->where('s.tableName', 'like', 'HALL%');
        elseif ($o === 'BALKON') $base->where('s.tableName', 'like', 'Balkon%');
        elseif ($o === 'VIP') $base->where('s.tableName', 'like', 'VIP%');
        elseif ($o === 'OTHER') {
            $base->where(function ($w) {
                $w->whereNull('s.tableName')
                  ->orWhere('s.tableName', '')
                  ->orWhere(function ($w2) {
                      $w2->where('s.tableName', 'not like', 'HALL%')
                         ->where('s.tableName', 'not like', 'Balkon%')
                         ->where('s.tableName', 'not like', 'VIP%');
                  });
            });
        }
    }

    // Search (columns that actually exist)
    if ($q) {
        $base->where(function ($w) use ($q) {
            $w->where('s.invoice_id', 'like', "%{$q}%")
              ->orWhere('s.tableName', 'like', "%{$q}%")
              ->orWhere('s.cashierName', 'like', "%{$q}%")
              ->orWhere('p.paymentMethod', 'like', "%{$q}%");
        });
    }

    // Payment filter using aggregated bucket
    if ($paymentType) {
        if ($paymentType === 'cash') $base->where('p.paymentBucket', 'CASH');
        elseif ($paymentType === 'qris') $base->where('p.paymentBucket', 'QRIS');
        elseif ($paymentType === 'edc') $base->where('p.paymentBucket', 'EDC');
        elseif ($paymentType === 'other') $base->whereIn('p.paymentBucket', ['OTHER','MIXED']);
    }

    // Sorting
    if ($sort === 'datetime_asc') {
        $base->orderBy('s.date', 'asc')->orderBy('s.invoice_id', 'asc');
    } elseif ($sort === 'total_desc') {
        $base->orderBy('s.total', 'desc')->orderBy('s.invoice_id', 'desc');
    } elseif ($sort === 'total_asc') {
        $base->orderBy('s.total', 'asc')->orderBy('s.invoice_id', 'asc');
    } else {
        $base->orderBy('s.date', 'desc')->orderBy('s.invoice_id', 'desc');
    }

    return $base;
}

}
