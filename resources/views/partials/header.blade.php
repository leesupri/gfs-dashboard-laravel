@php
  $routeTitleMap = [
    'welcome' => 'Home',
    'dashboard' => 'Dashboard',
    'sales.index' => 'Sales',
    'itemSales.index' => 'Item Sales',
    'noSales.index' => 'No Sales',
    'summarySales.index' => 'Summary Sales',
    'reports.void' => 'Void Report',
    'reports.consumptionWarehouse' => 'Reports consumption by warehouse',
    'reports.recipe'=> 'Master Recipe Report',
    'reports.recipe-board' => 'Recipes Board',
    'reports.orderBoard'=>'Order Board All',
    'reports.activityLog'=>'Activity Log',
    'reports.marketList'=>'Market List',
    'reports.productionSummary'=>'Production Summary',
    'reports.productionCard.index'=>'Production List Card',
    'reports.productionCard.show'=>'Production List Details',
    'reports.purchaseSummary'=>'Purchase Summary List',
    'reports.purchaseDetail'=>'Purchase detail card',
    'reports.purchaseDetailPartner' => 'Purchase Detail by Partner',
    'reports.physicalStockCountSummary' => 'Physical Stock Count Summary',
    'reports.transferDetail' => 'Transfer Detail Report',
    'reports.wasteSummary' => 'Waste Summary Report',
    'settings.staff' => 'Staff Settings',
    'settings.security' => 'Security Settings',
    'settings.changePassword' => 'Change Password',
  ];

  $routeName = request()->route()?->getName();
@endphp

<div>
  <div
    class="flex items-center justify-between gap-3 px-4 py-3 opacity-0 -translate-y-3 transition-all duration-500 sm:px-6 lg:px-8"
    x-init="$nextTick(() => { $el.classList.remove('opacity-0','-translate-y-3') })"
  >
    <div class="flex items-center gap-3">
      <button
  type="button"
  @click="window.innerWidth < 768 ? mobileMenuOpen = true : collapsed = !collapsed"
  class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 cursor-pointer"
>
  <span class="transition-transform duration-200"
        :class="collapsed ? 'rotate-90' : 'rotate-0'">
    ☰
  </span>
</button>

      <div>
        <div class="text-sm font-semibold leading-5">Gundaling Farmstead</div>
        <div class="text-xs text-gray-500">
          {{ $routeTitleMap[$routeName] ?? 'Dashboard' }}
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <div class="hidden text-right sm:block">
        <div class="text-sm font-medium text-gray-900">{{ $currentStaffUser->name ?? 'Staff' }}</div>
        <div class="text-xs text-gray-500">{{ $currentStaffUser->title ?? 'User' }}</div>
      </div>

      <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-700">
        {{ strtoupper(substr($currentStaffUser->name ?? 'S', 0, 1)) }}
      </div>
    </div>
  </div>
</div>