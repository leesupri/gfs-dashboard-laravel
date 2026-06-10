<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WarehouseApiController extends Controller
{
    public function index()
    {
        $warehouses = DB::connection('reports_mysql')
            ->table('tbl_warehouses')
            ->select('id', 'name', 'code', 'active')
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $warehouses,
        ]);
    }
}
