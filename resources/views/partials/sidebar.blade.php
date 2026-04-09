<!-- resources\views\partials\sidebar.blade.php -->
@php
  $nav = [
    ['label' => 'Dashboard',      'href' => route('dashboard'),           'key' => 'dashboard'],
    ['label' => 'Sales',          'href' => route('sales.index'),         'key' => 'sales.index'],
    ['label' => 'ItemSales',      'href' => route('itemSales.index'),     'key' => 'itemSales.index'],
    ['label' => 'Order Board', 'href' => route('reports.orderBoard'), 'key' => 'reports.orderBoard'],
    ['label' => 'NoSales',        'href' => route('noSales.index'),       'key' => 'noSales.index'],
    ['label' => 'Summary Sales',  'href' => route('summarySales.index'),  'key' => 'summarySales.index'],
    ['label' => 'Void report',    'href' => route('reports.void'),        'key' => 'reports.void'],
    ['label' => 'Consumption DI', 'href' => route('reports.consumptionDetailInvoice'), 'key' => 'reports.consumptionDetailInvoice'],
    ['label' => 'Consumption WH', 'href' => route('reports.consumptionWarehouse'), 'key' => 'reports.consumptionWarehouse'],
    ['label' => 'Inventory',      'href' => '#', 'key' => 'inventory'],
    ['label' => 'Recipes Report', 'href' => route('reports.recipe'), 'key' => 'reports.recipe'],
    ['label' => 'Activity Logs', 'href' => route('reports.activityLog'), 'key' => 'reports.activityLog'],
    ['label' => 'Market List', 'href' => route('reports.marketList'), 'key' => 'reports.marketList'],
    ['label' => 'Production Summary', 'href' => route('reports.productionSummary'), 'key' => 'reports.productionSummary'],
    ['label' => 'Production Card', 'href' => route('reports.productionCard.index'), 'key' => 'reports.productionCard.index'],
    ['label' => 'Purchase Summary', 'href' => route('reports.purchaseSummary'), 'key' => 'reports.purchaseSummary'],
    ['label' => 'Purchase Detail', 'href' => route('reports.purchaseDetail'), 'key' => 'reports.purchaseDetail'],
    ['label' => 'Purchase Detail by Parther', 'href' => route('reports.purchaseDetailPartner'), 'key' => 'reports.purchaseDetailPartner'],
    ['label' => 'Physical Stock Count', 'href' => route('reports.physicalStockCountSummary'), 'key' => 'reports.physicalStockCountSummary'],
    ['label' => 'Transfer Detail', 'href' => route('reports.transferDetail'), 'key' => 'reports.transferDetail'],
    ['label' => 'Waste Summary', 'href' => route('reports.wasteSummary'), 'key' => 'reports.wasteSummary'],
    ['label' => 'Reports',        'href' => '#', 'key' => 'reports'],
  ];

  

  $routeName = request()->route()?->getName() ?? '';
  $staffUser = $currentStaffUser ?? null;

  $nav = collect($nav)->filter(function ($item) use ($staffUser) {
      return $staffUser && $staffUser->hasAccess($item['key']);
  })->values()->all();
@endphp

{{-- Mobile overlay --}}


{{-- Mobile sidebar --}}
<aside
  x-show="mobileMenuOpen"
  x-cloak
  x-transition:enter="transform transition ease-out duration-300"
  x-transition:enter-start="-translate-x-full opacity-0 scale-[0.98]"
  x-transition:enter-end="translate-x-0 opacity-100 scale-100"
  x-transition:leave="transform transition ease-in duration-200"
  x-transition:leave-start="translate-x-0 opacity-100 scale-100"
  x-transition:leave-end="-translate-x-full opacity-0 scale-[0.98]"
  class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white md:hidden"
>
  <div class="flex h-14 items-center justify-between gap-2 border-b border-gray-200 px-4">
    <a href="{{ route('welcome') }}" class="flex items-center gap-2" @click="mobileMenuOpen = false">
      <img
        src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
        alt="Gundaling Farmstead"
        class="h-8 w-auto opacity-0 -translate-y-2 scale-95 transition-all duration-500 ease-out"
        x-init="$nextTick(() => {$el.classList.remove('opacity-0','-translate-y-2','scale-95')
        })"
      />
    </a>

    <button
      type="button"
      @click="mobileMenuOpen = false"
      class="rounded-lg border border-gray-200 px-2 py-1 text-sm text-gray-700 hover:bg-gray-50"
    >
      ✕
    </button>
  </div>

  <div class="px-4 py-2 leading-tight border-b border-gray-200">
    <div class="text-sm font-semibold">GFS Dashboard</div>
    <div class="text-xs text-gray-500">{{ $currentStaffUser->title ?? 'User' }}</div>
  </div>

  <nav
  :class="mobileMenuOpen ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'"
  class="flex-1 space-y-1 overflow-y-auto p-3 transition-all duration-300"
>
    @foreach ($nav as $item)
      @php
        $isActive = $routeName === $item['key'];
        $cls = $isActive ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50';
      @endphp

      <a href="{{ $item['href'] }}"
         @click="mobileMenuOpen = false, loading = true"
         class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium {{ $cls }}">
        <span class="inline-block h-2 w-2 rounded-full {{ $isActive ? 'bg-green-400' : 'bg-gray-300' }}"></span>
        <span>{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>

  <div class="border-t border-gray-200 p-3 space-y-1">
    @if(($currentStaffUser ?? null)?->hasAccess('settings.staff'))
      <a href="{{ route('settings.staff') }}" @click="mobileMenuOpen = false" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Staff Settings
      </a>
    @endif

    @if(($currentStaffUser ?? null)?->hasAccess('settings.security'))
      <a href="{{ route('settings.security') }}" @click="mobileMenuOpen = false" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Security Settings
      </a>
    @endif

    <a href="{{ route('settings.changePassword') }}" @click="mobileMenuOpen = false" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
      Change Password
    </a>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="w-full text-left rounded-xl px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
        Logout
      </button>
    </form>
  </div>
</aside>

{{-- Desktop sidebar --}}
<aside class="hidden md:flex md:w-64 md:flex-col md:border-r md:border-gray-200 md:bg-white md:shrink-0">
  <div class="flex h-14 items-center gap-2 border-b border-gray-200 px-4">
    <a href="{{ route('welcome') }}" class="flex items-center gap-2">
      <img
        src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
        alt="Gundaling Farmstead"
        class="h-8 w-auto opacity-0  scale-90 transition-all duration-500 ease-out"
  x-init="$nextTick(() => {
    $el.classList.remove('opacity-0','scale-90')
    setTimeout(() => {
      $el.classList.add('scale-105')
      setTimeout(() => {
        $el.classList.remove('scale-105')
      }, 120)
    }, 300)
  })"
      />
    </a>
    <div class="leading-tight">
      <div class="text-sm font-semibold">GFS Dashboard</div>
      <div class="text-xs text-gray-500">{{ $currentStaffUser->title ?? 'User' }}</div>
    </div>
  </div>

  <nav class="flex-1 space-y-1 p-3 overflow-y-auto">
    @foreach ($nav as $item)
      @php
        $isActive = $routeName === $item['key'];
        $cls = $isActive ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50';
      @endphp

      <a href="{{ $item['href'] }}"
        @click="loading = true"
        class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium {{ $cls }}">
        <span class="inline-block h-2 w-2 rounded-full {{ $isActive ? 'bg-green-400' : 'bg-gray-300' }}"></span>
        <span>{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>

  <div class="border-t border-gray-200 p-3 space-y-1">
    @if(($currentStaffUser ?? null)?->hasAccess('settings.staff'))
      <a href="{{ route('settings.staff') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Staff Settings
      </a>
    @endif

    @if(($currentStaffUser ?? null)?->hasAccess('settings.security'))
      <a href="{{ route('settings.security') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Security Settings
      </a>
    @endif

    <a href="{{ route('settings.changePassword') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
      Change Password
    </a>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="w-full text-left rounded-xl px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
        Logout
      </button>
    </form>
  </div>
</aside>