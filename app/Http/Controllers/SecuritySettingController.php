<?php

namespace App\Http\Controllers;

use App\Models\SecurityPermission;
use App\Models\StaffUser;
use Illuminate\Http\Request;

class SecuritySettingController extends Controller
{
    private array $availableRoutes = [
        'dashboard' => 'Dashboard',
        'sales.index' => 'Sales',
        'itemSales.index' => 'Item Sales',
        'noSales.index' => 'No Sales',
        'summarySales.index' => 'Summary Sales',
        'reports.void' => 'Void Report',
        'reports.consumptionDetailInvoice' => 'Consumption DI',
        'reports.consumptionWarehouse' => 'Consumption WH',
        'reports.recipe' => 'Recipes Report',
        'reports.orderBoard' => 'Order Board',
        'reports.activityLog' => 'Activity Logs',
        'reports.marketList' => 'Market List',
        'reports.productionSummary' => 'Production Summary',
        'reports.productionCard.index' => 'Production Card',
        'reports.purchaseSummary' => 'Purchase Summary',
        'reports.purchaseDetail' => 'Purchase Detail',
        'reports.purchaseDetailPartner' => 'Purchase Detail by Partner',
        'settings.staff' => 'Staff Settings',
        'settings.security' => 'Security Settings',
    ];

    public function index()
    {
        $staffUsers = StaffUser::with('permissions')->orderBy('name')->get();

        return view('settings.security', [
            'title' => 'Security Settings',
            'staffUsers' => $staffUsers,
            'availableRoutes' => $this->availableRoutes,
        ]);
    }

    public function update(Request $request, StaffUser $staffUser)
    {
        $selectedRoutes = $request->input('routes', []);
        $selectedRoutes = is_array($selectedRoutes) ? $selectedRoutes : [];

        $validRoutes = array_keys($this->availableRoutes);

        $selectedRoutes = array_values(array_intersect($selectedRoutes, $validRoutes));

        SecurityPermission::where('staff_user_id', $staffUser->id)->delete();

        foreach ($selectedRoutes as $routeName) {
            SecurityPermission::create([
                'staff_user_id' => $staffUser->id,
                'route_name' => $routeName,
                'can_view' => true,
            ]);
        }

        return redirect()
            ->route('settings.security')
            ->with('success', 'Security permissions updated successfully.');
    }
}