<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $staffUser = app('currentStaffUser');
        $routeName = $request->route()?->getName() ?? '';

       

    $permissionMap = [
        // SALES
    'sales.receipt' => 'sales.index',
    'sales.show' => 'sales.index',
    'sales.export' => 'sales.index',

    // STAFF SETTINGS
    'settings.staff.store' => 'settings.staff',
    'settings.staff.update' => 'settings.staff',
    'settings.staff.destroy' => 'settings.staff',

    // SECURITY SETTINGS
    'settings.security.update' => 'settings.security',

    // REPORTS (optional consistency)
    'reports.consumptionDetailInvoice.export' => 'reports.consumptionDetailInvoice',
    'reports.consumptionWarehouse.export'     => 'reports.consumptionWarehouse',
    'reports.recipe.export'                   => 'reports.recipe',
    'reports.recipe-board.export'             => 'reports.recipe-board',

    // SUPPORT TICKETS — all sub-actions inherit index permission
    'support.tickets.show'         => 'support.tickets.index',
    'support.tickets.reply'        => 'support.tickets.index',
    'support.tickets.updateStatus' => 'support.tickets.index',
    'support.tickets.assign'       => 'support.tickets.index',

    // PRINTER SETTINGS — sub-actions inherit settings.printer
    'settings.printer.store'   => 'settings.printer',
    'settings.printer.update'  => 'settings.printer',
    'settings.printer.destroy' => 'settings.printer',
    'settings.printer.logo'    => 'settings.printer',

    // PRINT VIEWS — inherit the underlying report permission
    'print.receipt'      => 'sales.index',
    'print.itemSales'    => 'itemSales.index',
    'print.summarySales' => 'summarySales.index',
    'print.test'         => 'settings.printer',
    ];

    $routeNameToCheck = $permissionMap[$routeName] ?? $routeName;


        if (!$staffUser || !$staffUser->hasAccess($routeNameToCheck)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
