<?php

namespace App\Http\Controllers;

use App\Models\SecurityPermission;
use App\Models\StaffUser;
use Illuminate\Http\Request;

class SecuritySettingController extends Controller
{
    private array $availableRoutes = [
        'welcome' => 'Welcome Page',
        'dashboard' => 'Dashboard',
        'sales.index' => 'Sales',
        'sales.receipt' => 'Sales receipt',
        'itemSales.index' => 'Item Sales',
        'noSales.index' => 'No Sales',
        'summarySales.index' => 'Summary Sales',
        'reports.void' => 'Void Report',
        'reports.consumptionDetailInvoice' => 'Consumption DI',
        'reports.consumptionWarehouse' => 'Consumption WH',
        'reports.recipe' => 'Recipes Report',
        'reports.recipe-board' => 'Recipes Board',
        'reports.orderBoard' => 'Order Board',
        'reports.activityLog' => 'Activity Logs',
        'reports.marketList' => 'Market List',
        'reports.productionSummary' => 'Production Summary',
        'reports.productionCard.index' => 'Production Card',
        'reports.productionCard.show' => 'Production Card Show',
        'reports.purchaseSummary' => 'Purchase Summary',
        'reports.purchaseDetail' => 'Purchase Detail',
        'reports.purchaseDetailPartner' => 'Purchase Detail by Partner',
        'reports.physicalStockCountSummary' => 'Physical Stock Count Summary',
        'reports.transferDetail' => 'Transfer Detail',
        'reports.wasteSummary' => 'Waste Summary Report',
        'settings.staff' => 'Staff Settings',
        'settings.security' => 'Security Settings',
    ];

    public function index(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $status = trim((string) $request->get('status', 'all'));

    $staffUsers = \App\Models\StaffUser::with('permissions')
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%');
            });
        })
        ->when($status === 'active', function ($query) {
            $query->where('is_active', true);
        })
        ->when($status === 'inactive', function ($query) {
            $query->where('is_active', false);
        })
        ->orderBy('name')
        ->get();

    $selectedStaff = null;

    if ($request->filled('staff')) {
        $selectedStaff = $staffUsers->firstWhere('id', (int) $request->get('staff'));

        if (!$selectedStaff) {
            $selectedStaff = \App\Models\StaffUser::with('permissions')->find((int) $request->get('staff'));
        }
    }

    return view('settings.security', [
        'title' => 'Security Settings',
        'staffUsers' => $staffUsers,
        'selectedStaff' => $selectedStaff,
        'availableRoutes' => $this->availableRoutes,
        'search' => $search,
        'status' => $status,
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