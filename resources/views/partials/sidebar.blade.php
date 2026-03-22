@php
  $nav = [
    ['label' => 'Dashboard',      'href' => route('dashboard'),           'key' => 'dashboard'],
    ['label' => 'Sales',          'href' => route('sales.index'),         'key' => 'sales.index'],
    ['label' => 'ItemSales',      'href' => route('itemSales.index'),     'key' => 'itemSales.index'],
    ['label' => 'NoSales',        'href' => route('noSales.index'),       'key' => 'noSales.index'],
    ['label' => 'Summary Sales',  'href' => route('summarySales.index'),  'key' => 'summarySales.index'],
    ['label' => 'Void report',    'href' => route('reports.void'),        'key' => 'reports.void'],
    ['label' => 'Consumption DI', 'href' => route('reports.consumptionDetailInvoice'), 'key' => 'reports.consumptionDetailInvoice'],
    ['label' => 'Consumption WH', 'href' => route('reports.consumptionWarehouse'), 'key' => 'reports.consumptionWarehouse'],
    ['label' => 'Inventory',      'href' => '#', 'key' => 'inventory'],
    ['label' => 'Recipes Report', 'href' => route('reports.recipe'), 'key' => 'reports.recipe'],
    ['label' => 'Reports',        'href' => '#', 'key' => 'reports'],
  ];

  $routeName = request()->route()?->getName() ?? '';
@endphp

<aside class="hidden md:flex md:w-64 md:flex-col md:border-r md:border-gray-200 md:bg-white md:h-screen md:shrink-0">
  <div class="flex h-14 items-center gap-2 border-b border-gray-200 px-4">
    <div class="flex items-center gap-2">
  <img
    src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
    alt="Gundaling Farmstead"
    class="h-12 w-auto"
  />
</div>
    <div class="leading-tight">
      <div class="text-sm font-semibold">GFS Dashboard</div>
      <div class="text-xs text-gray-500">Admin</div>
    </div>
  </div>

  <nav class="flex-1 space-y-1 p-3 overflow-y-auto">
    @foreach ($nav as $item)
      @php
  $isActive = $routeName === $item['key']; // we will set key = route name
  $cls = $isActive
    ? 'bg-gray-900 text-white'
    : 'text-gray-700 hover:bg-gray-50';
  @endphp

      <a href="{{ $item['href'] }}"
         class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium {{ $cls }}">
        <span class="inline-block h-2 w-2 rounded-full {{ $isActive ? 'bg-green-400' : 'bg-gray-300' }}"></span>
        <span>{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>

  <div class="border-t border-gray-200 p-3">
    <a href="#" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
      Settings
    </a>
  </div>
</aside>
