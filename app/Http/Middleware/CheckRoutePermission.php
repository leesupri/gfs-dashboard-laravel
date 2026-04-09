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
    'reports.consumptionWarehouse.export' => 'reports.consumptionWarehouse',
    'reports.recipe.export' => 'reports.recipe',
    ];

    $routeNameToCheck = $permissionMap[$routeName] ?? $routeName;


        if (!$staffUser || !$staffUser->hasAccess($routeNameToCheck)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}