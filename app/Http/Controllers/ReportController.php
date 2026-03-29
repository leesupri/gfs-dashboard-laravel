<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller{

public function activityLog(Request $request)
{
    $start = $request->get('start', now()->toDateString());
    $end   = $request->get('end', now()->toDateString());
    $q     = trim((string) $request->get('q', ''));
    $employee = trim((string) $request->get('employee', ''));
    $invoice  = trim((string) $request->get('invoice', ''));

    $from = Carbon::parse($start)->startOfDay();
    $to   = Carbon::parse($end)->endOfDay();

    $rows = DB::connection('reports_mysql')->table('tbl_activity_logs as al')
        ->join('tbl_employees as e', 'al.employee_id', '=', 'e.id')
        ->leftJoin('tbl_sales as s', 'al.salesId', '=', 's.id')
        ->select([
            'al.date',
            'al.description',
            'al.salesId',
            's.invoice_id',
            'e.name as employee_name',
        ])
        ->whereBetween('al.date', [$from, $to])
        ->when($employee !== '', function ($query) use ($employee) {
            $query->where('e.name', 'like', '%' . $employee . '%');
        })
        ->when($invoice !== '', function ($query) use ($invoice) {
            $query->where('s.invoice_id', 'like', '%' . $invoice . '%');
        })
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('al.description', 'like', '%' . $q . '%')
                    ->orWhere('e.name', 'like', '%' . $q . '%')
                    ->orWhere('al.salesId', 'like', '%' . $q . '%')
                    ->orWhere('s.invoice_id', 'like', '%' . $q . '%');
            });
        })
        ->orderBy('al.date')
        ->paginate(100)
        ->withQueryString();

    $summary = DB::connection('reports_mysql')->table('tbl_activity_logs as al')
        ->join('tbl_employees as e', 'al.employee_id', '=', 'e.id')
        ->leftJoin('tbl_sales as s', 'al.salesId', '=', 's.id')
        ->whereBetween('al.date', [$from, $to])
        ->when($employee !== '', function ($query) use ($employee) {
            $query->where('e.name', 'like', '%' . $employee . '%');
        })
        ->when($invoice !== '', function ($query) use ($invoice) {
            $query->where('s.invoice_id', 'like', '%' . $invoice . '%');
        })
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('al.description', 'like', '%' . $q . '%')
                    ->orWhere('e.name', 'like', '%' . $q . '%')
                    ->orWhere('al.salesId', 'like', '%' . $q . '%')
                    ->orWhere('s.invoice_id', 'like', '%' . $q . '%');
            });
        })
        ->selectRaw('COUNT(*) as total_logs')
        ->first();

    return view('reports.activity-log', [
        'title' => 'Activity Log Report',
        'rows' => $rows,
        'start' => $start,
        'end' => $end,
        'q' => $q,
        'employee' => $employee,
        'invoice' => $invoice,
        'summary' => $summary,
    ]);
}
}