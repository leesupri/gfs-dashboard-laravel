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


  $icons = [
  'dashboard' => 'home',
  'sales.index' => 'cart',
  'itemSales.index' => 'chart',
  'reports.orderBoard' => 'clipboard',
  'noSales.index' => 'x',
  'summarySales.index' => 'trend',
  'reports.void' => 'alert',
  'reports.consumptionDetailInvoice' => 'file',
  'reports.consumptionWarehouse' => 'database',
  'reports.recipe' => 'book',
  'reports.activityLog' => 'activity',
  'reports.marketList' => 'list',
  'reports.productionSummary' => 'layers',
  'reports.productionCard.index' => 'grid',
  'reports.purchaseSummary' => 'bag',
  'reports.purchaseDetail' => 'file',
  'reports.purchaseDetailPartner' => 'users',
  'reports.physicalStockCountSummary' => 'archive',
  'reports.transferDetail' => 'repeat',
  'reports.wasteSummary' => 'trash',
];

function iconPath($name) {
  return [
    'home' => '<path d="M3 9.75 12 4.5l9 5.25v9.75a1.5 1.5 0 0 1-1.5 1.5h-3.75v-6h-6v6H4.5A1.5 1.5 0 0 1 3 19.5V9.75Z"/>',

    'cart' => '<path d="M2.25 3h1.5l2.25 9h11.25l2.25-6H6"/>',

    'chart' => '<path d="M4.5 19.5v-12m6 12v-9m6 9v-6"/>',

    'clipboard' => '<path d="M9 5.25h6M9 9.75h6M9 14.25h6M4.5 4.5h15v15h-15z"/>',

    'x' => '<path d="M6 6l12 12M18 6l-12 12"/>',

    'trend' => '<path d="M3 17l6-6 4 4 7-7"/>',

    'alert' => '<path d="M12 9v3m0 4h.01"/><path d="M10.29 3.86 1.82 18a1.5 1.5 0 0 0 1.3 2.25h17.76a1.5 1.5 0 0 0 1.3-2.25L13.71 3.86a1.5 1.5 0 0 0-2.42 0Z"/>',

    'file' => '<path d="M6 2.25h9l3 3v13.5A1.5 1.5 0 0 1 16.5 21h-9A1.5 1.5 0 0 1 6 19.5V2.25Z"/>',

    'database' => '<ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v6c0 1.66 3.13 3 7 3s7-1.34 7-3V6"/>',

    'book' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M20 22V2H6.5A2.5 2.5 0 0 0 4 4.5v15Z"/>',

    'activity' => '<path d="M22 12h-4l-3 9-6-18-3 9H2"/>',

    'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',

    'layers' => '<path d="M12 2 2 7l10 5 10-5-10-5Zm0 20-10-5"/><path d="M22 12l-10 5-10-5"/>',

    'grid' => '<path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>',

    'bag' => '<path d="M6 2h12l2 7H4l2-7Z"/><path d="M4 9v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"/>',

    'users' => '<path d="M18 21a8 8 0 0 0-12 0"/><circle cx="12" cy="7" r="4"/>',

    'archive' => '<rect x="3" y="3" width="18" height="4"/><path d="M5 7v13h14V7"/><path d="M10 12h4"/>',

    'repeat' => '<path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>',

    'trash' => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/>',
    'settings' => '<path stroke="none" d="M0 0h24v24H0z" fill="none" />
	<path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065" />
	<path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />',

  ][$name] ?? '<circle cx="12" cy="12" r="6"/>';
}
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
  class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white md:hidden ease-in-out"
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
        $icon = $icons[$item['key']] ?? 'circle';
        $iconName = $icons[$item['key']] ?? 'file';
        
      @endphp

      <div class="relative group">
<a href="{{ $item['href'] }}"
   @click="loading = true"
   class="flex items-center rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200
{{ $isActive ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
:class="collapsed ? 'justify-center' : 'gap-3'">

  <!-- ACTIVE INDICATOR -->
  <span class="absolute left-0 top-0 h-full w-1 rounded-r bg-green-500 transition-all duration-300
    {{ $isActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-50' }}"></span>

  <!-- ICON -->
  <svg xmlns="http://www.w3.org/2000/svg"
       class="h-5 w-5 shrink-0 transition-all duration-200
       {{ $isActive ? 'text-white scale-110' : 'text-gray-400 group-hover:text-gray-700' }}"
       fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
       {!! iconPath($iconName) !!}
  </svg>

  <!-- TEXT -->
  <span x-show="!collapsed" x-transition>
    {{ $item['label'] }}
  </span>
</a>

<!-- TOOLTIP -->
<div x-show="collapsed"
     class="absolute left-full ml-2 top-1/2 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 text-white text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition">
  {{ $item['label'] }}
</div>
</div>
    @endforeach
  </nav>

  @php
  $routeName = request()->route()?->getName() ?? '';

  $settingsRoutes = [
    'settings.staff',
    'settings.security',
    'settings.changePassword',
  ];

  $settingsOpen = in_array($routeName, $settingsRoutes, true);
@endphp

<div class="border-t border-gray-200 p-3 space-y-2">
  <div x-data="{ open: {{ $settingsOpen ? 'true' : 'false' }} }" class="space-y-2">

    <button
      type="button"
      @click="open = !open"
      class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
    >
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317a1.724 1.724 0 0 1 3.35 0l.18.826a1.724 1.724 0 0 0 2.573 1.066l.726-.42a1.724 1.724 0 0 1 2.35.631l.5.866a1.724 1.724 0 0 1-.631 2.35l-.726.419a1.724 1.724 0 0 0 0 2.985l.726.42a1.724 1.724 0 0 1 .631 2.35l-.5.866a1.724 1.724 0 0 1-2.35.631l-.726-.42a1.724 1.724 0 0 0-2.573 1.066l-.18.826a1.724 1.724 0 0 1-3.35 0l-.18-.826a1.724 1.724 0 0 0-2.573-1.066l-.726.42a1.724 1.724 0 0 1-2.35-.631l-.5-.866a1.724 1.724 0 0 1 .631-2.35l.726-.42a1.724 1.724 0 0 0 0-2.985l-.726-.419a1.724 1.724 0 0 1-.631-2.35l.5-.866a1.724 1.724 0 0 1 2.35-.631l.726.42a1.724 1.724 0 0 0 2.573-1.066l.18-.826Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12a3 3 0 1 0 6 0 3 3 0 0 0-6 0Z" />
        </svg>
        <span>Settings</span>
      </div>

      <svg xmlns="http://www.w3.org/2000/svg"
           class="h-4 w-4 transition-transform duration-200"
           :class="open ? 'rotate-180' : 'rotate-0'"
           fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
      </svg>
    </button>

    <div
      x-show="open"
      x-cloak
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-1"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-1"
      class="space-y-1 pl-2"
    >
      @if(($currentStaffUser ?? null)?->hasAccess('settings.staff'))
        @php $active = $routeName === 'settings.staff'; @endphp
        <a href="{{ route('settings.staff') }}"
           @click="mobileMenuOpen = false; loading = true"
           class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition-all duration-150 hover:translate-x-1 {{ $active ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 21a8 8 0 0 0-12 0" />
            <circle cx="12" cy="7" r="4" />
          </svg>
          
          <span>Staff Settings</span>
        </a>
      @endif

      @if(($currentStaffUser ?? null)?->hasAccess('settings.security'))
        @php $active = $routeName === 'settings.security'; @endphp
        <a href="{{ route('settings.security') }}"
           @click="mobileMenuOpen = false; loading = true"
           class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition-all duration-150 hover:translate-x-1 {{ $active ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-1.5 0h12a1.5 1.5 0 0 1 1.5 1.5v6a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 4.5 18v-6A1.5 1.5 0 0 1 6 10.5Z" />
          </svg>
          
          <span>Security Settings</span>
        </a>
      @endif

      @php $active = $routeName === 'settings.changePassword'; @endphp
      <a href="{{ route('settings.changePassword') }}"
         @click="mobileMenuOpen = false; loading = true"
         class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition-all duration-150 hover:translate-x-1 {{ $active ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 1 1 4.243 4.243L9.75 19.736 4.5 21l1.264-5.25L15.75 5.25Z" />
        </svg>
        
        <span>Change Password</span>
      </a>
    </div>
  </div>

  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit"
            class="w-full flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-all duration-150 hover:translate-x-1">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H9m0 0 3-3m-3 3 3 3" />
      </svg>
      
      <span>Logout</span>
    </button>
  </form>
</div>
</aside>

{{-- Desktop sidebar --}}

<aside :class="collapsed ? 'w-20' : 'w-64'"
       class="hidden md:flex flex-col border-r bg-white transition-all duration-300 ease-in-out">
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
    <div class="leading-tight" x-show="!collapsed" x-transition>
      <div class="text-sm font-semibold">GFS Dashboard</div>
      <div class="text-xs text-gray-500">{{ $currentStaffUser->title ?? 'User' }}</div>
    </div>
  </div>

  <nav class="flex-1 space-y-1 p-3 overflow-y-auto">
    @foreach ($nav as $item)
      @php
        $isActive = $routeName === $item['key'];
        $cls = $isActive ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50';
        $icon = $icons[$item['key']] ?? 'circle';
        $iconName = $icons[$item['key']] ?? 'file';
        
      @endphp

      <div class="relative group">
<a href="{{ $item['href'] }}"
   @click="loading = true"
   class="flex items-center rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200
{{ $isActive ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
:class="collapsed ? 'justify-center' : 'gap-3'">

  <!-- ACTIVE INDICATOR -->
  <span class="absolute left-0 top-0 h-full w-1 rounded-r bg-green-500 transition-all duration-300
    {{ $isActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-50' }}"></span>

  <!-- ICON -->
  <svg xmlns="http://www.w3.org/2000/svg"
       class="h-5 w-5 shrink-0 transition-all duration-200
       {{ $isActive ? 'text-white scale-110' : 'text-gray-400 group-hover:text-gray-700' }}"
       fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
       {!! iconPath($iconName) !!}
  </svg>

  <!-- TEXT -->
  <span x-show="!collapsed" x-transition>
    {{ $item['label'] }}
  </span>
</a>

<!-- TOOLTIP -->
<div x-show="collapsed"
     class="absolute left-full ml-2 top-1/2 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 text-white text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition">
  {{ $item['label'] }}
</div>
</div>
    @endforeach
  </nav>

  @php
  $routeName = request()->route()?->getName() ?? '';

  $settingsRoutes = [
    'settings.staff',
    'settings.security',
    'settings.changePassword',
  ];

  $settingsOpen = in_array($routeName, $settingsRoutes, true);
@endphp

<div class="border-t border-gray-200 p-3 space-y-2">
  <div x-data="{ open: {{ $settingsOpen ? 'true' : 'false' }} }" class="space-y-2">

    <button
  type="button"
  @click="open = !open"
  class="relative group flex items-center rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all duration-200"
  :class="collapsed ? 'justify-center' : 'gap-3'"
>
  <!-- ICON -->
  <svg xmlns="http://www.w3.org/2000/svg"
       class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-gray-700 transition"
       fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
    {!! iconPath('settings') !!}
  </svg>

  <!-- TEXT -->
  <span x-show="!collapsed" x-transition>Settings</span>

  <!-- ARROW -->
  <svg x-show="!collapsed"
       class="ml-auto h-4 w-4 transition-transform duration-200"
       :class="open ? 'rotate-180' : ''"
       fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
  </svg>

  <!-- TOOLTIP -->
  <div x-show="collapsed"
       class="absolute left-full ml-2 top-1/2 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 text-white text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition">
    Settings
  </div>
</button>

    <div
      x-show="open"
      x-cloak
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-1"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-1"
      class="space-y-1 pl-2"
    >
      @if(($currentStaffUser ?? null)?->hasAccess('settings.staff'))
        @php $active = $routeName === 'settings.staff'; @endphp
        <a href="{{ route('settings.staff') }}"
   @click="mobileMenuOpen = false; loading = true"
   class="relative group flex items-center rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200
   {{ $active ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
   :class="collapsed ? 'justify-center' : 'gap-3'">

  <!-- ICON -->
  <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-white' : 'text-gray-400' }}"
       fill="none" viewBox="0 0 24 24" stroke="currentColor">
    {!! iconPath('users') !!}
  </svg>

  <!-- TEXT -->
  <span x-show="!collapsed">Staff Settings</span>

  <!-- TOOLTIP -->
  <div x-show="collapsed"
       class="absolute left-full ml-2 top-1/2 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 text-white text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition">
    Staff Settings
  </div>
</a>
      @endif

      @if(($currentStaffUser ?? null)?->hasAccess('settings.security'))
        @php $active = $routeName === 'settings.security'; @endphp
        <a href="{{ route('settings.security') }}"
            @click="mobileMenuOpen = false; loading = true"
   class="relative group flex items-center rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200
   {{ $active ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
   :class="collapsed ? 'justify-center' : 'gap-3'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-1.5 0h12a1.5 1.5 0 0 1 1.5 1.5v6a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 4.5 18v-6A1.5 1.5 0 0 1 6 10.5Z" />
          </svg>
          <!-- TEXT -->
  <span x-show="!collapsed">Security Settings</span>

  <!-- TOOLTIP -->
  <div x-show="collapsed"
       class="absolute left-full ml-2 top-1/2 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 text-white text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition">
    Security Settings
  </div>
          
          
        </a>
      @endif

      @php $active = $routeName === 'settings.changePassword'; @endphp
      <a href="{{ route('settings.changePassword') }}"
        @click="mobileMenuOpen = false; loading = true"
   class="relative group flex items-center rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200
   {{ $active ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
   :class="collapsed ? 'justify-center' : 'gap-3'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 1 1 4.243 4.243L9.75 19.736 4.5 21l1.264-5.25L15.75 5.25Z" />
        </svg>
        <!-- TEXT -->
  <span x-show="!collapsed">Change Password</span>

  <!-- TOOLTIP -->
  <div x-show="collapsed"
       class="absolute left-full ml-2 top-1/2 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 text-white text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition">
    Change Password
  </div>
        
      </a>
    </div>
  </div>

 <form method="POST" action="{{ route('logout') }}">
  @csrf
  <button type="submit"
    class="relative group flex w-full items-center rounded-xl px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-all duration-200"
    :class="collapsed ? 'justify-center' : 'gap-3'">

    <!-- ICON -->
    <svg xmlns="http://www.w3.org/2000/svg"
         class="h-5 w-5 shrink-0 text-red-500"
         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15" />
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M18 12H9m0 0 3-3m-3 3 3 3" />
    </svg>

    <!-- TEXT -->
    <span x-show="!collapsed">Logout</span>

    <!-- TOOLTIP -->
    <div x-show="collapsed"
         class="absolute left-full ml-2 top-1/2 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 text-white text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition">
      Logout
    </div>
  </button>
</form>
</div>
</aside>
