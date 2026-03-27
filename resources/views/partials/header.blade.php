<!-- resources\views\partials\header.blade.php -->
@php
  $routeTitleMap = [
    'dashboard' => 'Dashboard',
    'sales.index' => 'Sales',
    'itemSales.index' => 'Item Sales',
    'noSales.index' => 'No Sales',
    'summarySales.index' => 'Summary Sales',
    'reports.void' => 'Void Report',
    'reports.consumptionWarehouse' => 'Reports consumption by warehouse',
    'reports.recipe'=> 'Master Recipe Report',
    'reports.orderBoard'=>'Order Board All',
    'reports.activityLog'=>'Activity Log',
    'reports.marketList'=>'Market List',
    'reports.productionSummary'=>'Production Summary',
    'reports.productionCard.index'=>'Production List Card',
    'reports.productionCard.show'=>'Production List Details',

  ];

  $routeName = request()->route()?->getName();
@endphp
<header class="sticky top-0 z-20 border-b border-gray-200 bg-white/90 backdrop-blur">
  <div class="flex items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3">
      <button
        type="button"
        class="md:hidden inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        ☰
      </button>

      <div>
  <div class="text-sm font-semibold leading-5">Gundaling Farmstead</div>
  <div class="text-xs text-gray-500">
    {{ $routeTitleMap[$routeName] ?? 'Dashboard' }}
  </div>
</div>
    </div>

    <div class="flex items-center gap-2">
      <span class="hidden sm:inline text-xs text-gray-500">Offline LAN</span>
      <div class="h-8 w-8 rounded-full bg-gray-200"></div>
    </div>
  </div>
</header>
