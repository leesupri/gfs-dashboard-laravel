<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rows = DB::connection('reports_mysql')
            ->table('tbl_warehouses')
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json(['data' => $rows]);
    }
}
