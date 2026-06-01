<!-- resources\views\partials\sidebar.blade.php -->
@php
  $staffUser  = $currentStaffUser ?? null;
  $routeName  = request()->route()?->getName() ?? '';

  // ── Nav groups — filtered by user permissions ──────────────────────────
  $allGroups = [
    [
      'key'   => 'overview',
      'label' => 'Overview',
      'icon'  => 'home',
      'items' => [
        ['label' => 'Dashboard',     'href' => route('dashboard'),          'key' => 'dashboard',          'icon' => 'home'],
        ['label' => 'Sales',         'href' => route('sales.index'),        'key' => 'sales.index',        'icon' => 'cart'],
        ['label' => 'Item Sales',    'href' => route('itemSales.index'),    'key' => 'itemSales.index',    'icon' => 'chart'],
        ['label' => 'Summary Sales', 'href' => route('summarySales.index'), 'key' => 'summarySales.index', 'icon' => 'trend'],
        ['label' => 'Order Board',   'href' => route('reports.orderBoard'), 'key' => 'reports.orderBoard', 'icon' => 'clipboard'],
      ],
    ],
    [
      'key'   => 'sales-ops',
      'label' => 'Sales Operations',
      'icon'  => 'cart',
      'items' => [
        ['label' => 'No Sales',         'href' => route('noSales.index'),                  'key' => 'noSales.index',                  'icon' => 'x'],
        ['label' => 'No Sales Receipt', 'href' => route('reports.noSalesReceiptDetail'),   'key' => 'reports.noSalesReceiptDetail',   'icon' => 'receipt'],
        ['label' => 'Void Report',      'href' => route('reports.void'),                   'key' => 'reports.void',                   'icon' => 'alert'],
        ['label' => 'Cashier Shift',    'href' => route('reports.cashierShift'),            'key' => 'reports.cashierShift',           'icon' => 'card'],
        ['label' => 'Opening Day',      'href' => route('reports.openingDay'),              'key' => 'reports.openingDay',             'icon' => 'activity'],
      ],
    ],
    [
      'key'   => 'daily',
      'label' => 'Daily Reports',
      'icon'  => 'clipboard',
      'items' => [
        ['label' => 'Daily Category',   'href' => route('reports.dailyCategory'),  'key' => 'reports.dailyCategory',  'icon' => 'tag'],
        ['label' => 'Daily Hour Sales', 'href' => route('reports.dailyHour'),      'key' => 'reports.dailyHour',      'icon' => 'activity'],
        ['label' => 'Activity Logs',    'href' => route('reports.activityLog'),    'key' => 'reports.activityLog',    'icon' => 'activity'],
      ],
    ],
    [
      'key'   => 'menu',
      'label' => 'Menu & Recipes',
      'icon'  => 'book',
      'items' => [
        ['label' => 'Market List',    'href' => route('reports.marketList'),   'key' => 'reports.marketList',   'icon' => 'list'],
        ['label' => 'Recipes Report', 'href' => route('reports.recipe'),       'key' => 'reports.recipe',       'icon' => 'book'],
        ['label' => 'Recipes Board',  'href' => route('reports.recipe-board'), 'key' => 'reports.recipe-board', 'icon' => 'book'],
      ],
    ],
    [
      'key'   => 'production',
      'label' => 'Production',
      'icon'  => 'layers',
      'items' => [
        ['label' => 'Production Summary', 'href' => route('reports.productionSummary'),    'key' => 'reports.productionSummary',    'icon' => 'layers'],
        ['label' => 'Production Card',    'href' => route('reports.productionCard.index'), 'key' => 'reports.productionCard.index', 'icon' => 'grid'],
      ],
    ],
    [
      'key'   => 'purchasing',
      'label' => 'Purchasing',
      'icon'  => 'bag',
      'items' => [
        ['label' => 'Purchase Summary',  'href' => route('reports.purchaseSummary'),       'key' => 'reports.purchaseSummary',       'icon' => 'bag'],
        ['label' => 'Purchase Detail',   'href' => route('reports.purchaseDetail'),         'key' => 'reports.purchaseDetail',        'icon' => 'file'],
        ['label' => 'By Partner',        'href' => route('reports.purchaseDetailPartner'), 'key' => 'reports.purchaseDetailPartner', 'icon' => 'users'],
      ],
    ],
    [
      'key'   => 'inventory',
      'label' => 'Inventory',
      'icon'  => 'database',
      'items' => [
        ['label' => 'Consumption DI',   'href' => route('reports.consumptionDetailInvoice'),  'key' => 'reports.consumptionDetailInvoice',  'icon' => 'file'],
        ['label' => 'Consumption WH',   'href' => route('reports.consumptionWarehouse'),       'key' => 'reports.consumptionWarehouse',       'icon' => 'database'],
        ['label' => 'Transfer Detail',  'href' => route('reports.transferDetail'),             'key' => 'reports.transferDetail',             'icon' => 'repeat'],
        ['label' => 'Physical Stock',   'href' => route('reports.physicalStockCountSummary'),  'key' => 'reports.physicalStockCountSummary',  'icon' => 'archive'],
        ['label' => 'Waste Summary',    'href' => route('reports.wasteSummary'),               'key' => 'reports.wasteSummary',               'icon' => 'trash'],
      ],
    ],
    [
      'key'   => 'analytics',
      'label' => 'Analytics',
      'icon'  => 'trend',
      'items' => [
        ['label' => 'Sales Forecast', 'href' => route('reports.salesForecast'), 'key' => 'reports.salesForecast', 'icon' => 'trend'],
      ],
    ],
    [
      'key'   => 'support-nav',
      'label' => 'Support',
      'icon'  => 'ticket',
      'items' => [
        ['label' => 'Support Tickets', 'href' => route('support.tickets.index'), 'key' => 'support.tickets.index', 'icon' => 'ticket'],
      ],
    ],
  ];

  // Filter items by permission, then remove empty groups
  $navGroups = array_values(array_filter(
    array_map(function ($group) use ($staffUser) {
      $group['items'] = array_values(array_filter(
        $group['items'],
        fn ($i) => $staffUser && $staffUser->hasAccess($i['key'])
      ));
      return $group;
    }, $allGroups),
    fn ($g) => !empty($g['items'])
  ));

  // Flat list for collapsed desktop mode (all accessible items)
  $nav = collect($navGroups)->flatMap(fn ($g) => $g['items'])->all();

  $settingsRoutes = [
    'settings.staff', 'settings.security',
    'settings.changePassword', 'settings.pageLog', 'log-viewer.index',
  ];
  $settingsOpen = in_array($routeName, $settingsRoutes, true);
@endphp

{{-- ── Grouped nav macro (shared between mobile + desktop expanded) ──── --}}
{{-- Rendered inline in each sidebar below to avoid scope issues.          --}}

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
  class="sidebar fixed inset-y-0 left-0 z-50 flex w-64 flex-col md:hidden"
  aria-label="Mobile navigation"
>
  <div x-data="{ collapsed: false }" class="flex h-full flex-col">
    <div class="flex h-14 items-center justify-between gap-2 border-b px-4" style="border-color:var(--sidebar-border)">
      <a href="{{ route('welcome') }}" class="flex items-center gap-2" @click="mobileMenuOpen = false">
        <img src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
          alt="Gundaling Farmstead"
          class="h-8 w-auto opacity-0 -translate-y-2 scale-95 transition-all duration-500 ease-out"
          x-init="$nextTick(() => { $el.classList.remove('opacity-0','-translate-y-2','scale-95') })" />
      </a>
      <button type="button" @click="mobileMenuOpen = false"
        class="rounded-lg border border-white/20 px-2 py-1 text-sm text-white/70 hover:bg-white/10">✕</button>
    </div>

    <div class="border-b px-4 py-2 leading-tight" style="border-color:var(--sidebar-border)">
      <div class="text-sm font-semibold text-white">GFS Dashboard</div>
      <div class="text-xs" style="color:var(--sidebar-text)">{{ $currentStaffUser->title ?? 'User' }}</div>
    </div>

    {{-- Mobile grouped nav (always expanded) --}}
    <nav class="flex-1 space-y-0.5 overflow-y-auto p-3" aria-label="Main navigation">
      @foreach($navGroups as $group)
        @php $groupActive = collect($group['items'])->contains(fn($i) => $routeName === $i['key']); @endphp
        <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }" class="mb-0.5">
          <button @click="open = !open"
            class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold uppercase tracking-wider transition hover:bg-white/8"
            :class="open ? 'text-white' : ''"
            style="color:var(--sidebar-text)">
            <x-nav-icon name="{{ $group['icon'] }}" class="h-4 w-4 shrink-0 text-gray-400" />
            <span class="flex-1 text-left">{{ $group['label'] }}</span>
            <span class="rounded-full px-1.5 py-0.5 text-[10px]"
              style="background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.35)">
              {{ count($group['items']) }}
            </span>
            <svg class="h-3.5 w-3.5 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
              fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
          </button>
          <div x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="mt-0.5 space-y-0.5 pl-2" style="border-left:2px solid rgba(34,197,94,0.25); margin-left:0.875rem">
            @foreach($group['items'] as $item)
              @include('partials.sidebar-nav-item', [
                'href'   => $item['href'],
                'label'  => $item['label'],
                'icon'   => $item['icon'],
                'active' => $routeName === $item['key'],
              ])
            @endforeach
          </div>
        </div>
      @endforeach
    </nav>

    {{-- Settings + Logout --}}
    <div class="border-t p-3 space-y-2" style="border-color:var(--sidebar-border)">
      <div x-data="{ open: {{ $settingsOpen ? 'true' : 'false' }} }" class="space-y-2">
        <button type="button" @click="open = !open"
          class="relative flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-white/8"
          style="color:var(--sidebar-text)">
          <x-nav-icon name="settings" class="h-5 w-5 text-gray-400" />
          <span>Settings</span>
          <svg class="ml-auto h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
          </svg>
        </button>
        <div x-show="open" x-cloak
          x-transition:enter="transition ease-out duration-200"
          x-transition:enter-start="opacity-0 -translate-y-1"
          x-transition:enter-end="opacity-100 translate-y-0"
          x-transition:leave="transition ease-in duration-150"
          x-transition:leave-start="opacity-100 translate-y-0"
          x-transition:leave-end="opacity-0 -translate-y-1"
          class="space-y-1 pl-2">
          @if(($currentStaffUser ?? null)?->hasAccess('settings.staff'))
            @include('partials.sidebar-settings-item', ['href' => route('settings.staff'), 'label' => 'Staff Settings', 'icon' => 'users', 'active' => $routeName === 'settings.staff'])
          @endif
          @if(($currentStaffUser ?? null)?->hasAccess('settings.security'))
            @include('partials.sidebar-settings-item', ['href' => route('settings.security'), 'label' => 'Security Settings', 'icon' => 'shields', 'active' => $routeName === 'settings.security'])
          @endif
          @include('partials.sidebar-settings-item', ['href' => route('settings.changePassword'), 'label' => 'Change Password', 'icon' => 'key', 'active' => $routeName === 'settings.changePassword'])
          @if(($currentStaffUser ?? null)?->hasAccess('settings.pageLog'))
            @include('partials.sidebar-settings-item', ['href' => route('settings.pageLog'), 'label' => 'Activity Log', 'icon' => 'activity', 'active' => $routeName === 'settings.pageLog'])
          @endif
          @if(($currentStaffUser ?? null)?->hasAccess('log-viewer.index'))
            @include('partials.sidebar-settings-item', ['href' => route('log-viewer.index'), 'label' => 'Log Viewer', 'icon' => 'terminal', 'active' => str_starts_with($routeName, 'log-viewer')])
          @endif
        </div>
      </div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
          class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-red-400 transition hover:translate-x-1 hover:bg-red-500/15">
          <x-nav-icon name="logout" class="h-4 w-4 text-red-500" />
          <span>Logout</span>
        </button>
      </form>
    </div>
  </div>
</aside>

{{-- Desktop sidebar --}}
<aside
  :class="collapsed ? 'w-20' : 'w-64'"
  class="sidebar relative z-40 hidden md:flex md:flex-col overflow-visible"
  aria-label="Desktop navigation"
>
  <div class="flex h-14 items-center gap-2 border-b px-4" style="border-color:var(--sidebar-border)">
    <a href="{{ route('welcome') }}" class="flex items-center gap-2">
      <img src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
        alt="Gundaling Farmstead"
        class="h-8 w-auto opacity-0 scale-90 transition-all duration-500 ease-out"
        x-init="$nextTick(() => {
          $el.classList.remove('opacity-0','scale-90');
          setTimeout(() => { $el.classList.add('scale-105'); setTimeout(() => $el.classList.remove('scale-105'), 120); }, 300);
        })" />
    </a>
    <div class="leading-tight text-white" x-show="!collapsed" x-transition>
      <div class="text-sm font-semibold text-white">GFS Dashboard</div>
      <div class="text-xs" style="color:var(--sidebar-text)">{{ $currentStaffUser->title ?? 'User' }}</div>
    </div>
  </div>

  <nav class="relative z-40 flex-1 overflow-y-auto overflow-x-visible p-3" aria-label="Main navigation">

    {{-- ── COLLAPSED: flat icon list ────────────────────────────────── --}}
    <div x-show="collapsed" class="space-y-0.5">
      @foreach($nav as $item)
        @include('partials.sidebar-nav-item', [
          'href'   => $item['href'],
          'label'  => $item['label'],
          'icon'   => $item['icon'],
          'active' => $routeName === $item['key'],
        ])
      @endforeach
    </div>

    {{-- ── EXPANDED: grouped accordion ────────────────────────────────── --}}
    <div x-show="!collapsed" class="space-y-0.5">
      @foreach($navGroups as $group)
        @php $groupActive = collect($group['items'])->contains(fn($i) => $routeName === $i['key']); @endphp
        <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }" class="mb-0.5">
          <button @click="open = !open"
            class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold uppercase tracking-wider transition hover:bg-white/8"
            :class="open ? 'text-white' : ''"
            style="color:var(--sidebar-text)">
            <x-nav-icon name="{{ $group['icon'] }}" class="h-4 w-4 shrink-0 text-gray-400" />
            <span class="flex-1 text-left">{{ $group['label'] }}</span>
            <span class="rounded-full px-1.5 py-0.5 text-[10px]"
              style="background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.35)">
              {{ count($group['items']) }}
            </span>
            <svg class="h-3.5 w-3.5 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
              fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
          </button>
          <div x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="mt-0.5 space-y-0.5 pl-2" style="border-left:2px solid rgba(34,197,94,0.25); margin-left:0.875rem">
            @foreach($group['items'] as $item)
              @include('partials.sidebar-nav-item', [
                'href'   => $item['href'],
                'label'  => $item['label'],
                'icon'   => $item['icon'],
                'active' => $routeName === $item['key'],
              ])
            @endforeach
          </div>
        </div>
      @endforeach
    </div>

  </nav>

  {{-- Settings + Logout --}}
  <div class="border-t p-3 space-y-2" style="border-color:var(--sidebar-border)">
    <div x-data="{ open: {{ $settingsOpen ? 'true' : 'false' }} }" class="space-y-2">
      <button type="button" @click="open = !open"
        :class="collapsed ? 'justify-center' : 'gap-3'"
        class="relative flex w-full items-center rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-white/8"
        style="color:var(--sidebar-text)">
        <x-nav-icon name="settings" class="h-5 w-5 text-gray-400" />
        <span x-show="!collapsed" x-transition>Settings</span>
        <svg x-show="!collapsed" class="ml-auto h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
          fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--sidebar-text)">
          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
        </svg>
        <div x-show="collapsed" x-cloak
          class="pointer-events-none absolute left-full top-1/2 z-9999 ml-3 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 px-3 py-1.5 text-xs text-white shadow-xl opacity-0 transition group-hover:opacity-100">
          Settings
        </div>
      </button>
      <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="space-y-1 pl-2">
        @if(($currentStaffUser ?? null)?->hasAccess('settings.staff'))
          @include('partials.sidebar-settings-item', ['href' => route('settings.staff'), 'label' => 'Staff Settings', 'icon' => 'users', 'active' => $routeName === 'settings.staff'])
        @endif
        @if(($currentStaffUser ?? null)?->hasAccess('settings.security'))
          @include('partials.sidebar-settings-item', ['href' => route('settings.security'), 'label' => 'Security Settings', 'icon' => 'shields', 'active' => $routeName === 'settings.security'])
        @endif
        @include('partials.sidebar-settings-item', ['href' => route('settings.changePassword'), 'label' => 'Change Password', 'icon' => 'key', 'active' => $routeName === 'settings.changePassword'])
        @if(($currentStaffUser ?? null)?->hasAccess('settings.pageLog'))
          @include('partials.sidebar-settings-item', ['href' => route('settings.pageLog'), 'label' => 'Activity Log', 'icon' => 'activity', 'active' => $routeName === 'settings.pageLog'])
        @endif
        @if(($currentStaffUser ?? null)?->hasAccess('log-viewer.index'))
          @include('partials.sidebar-settings-item', ['href' => route('log-viewer.index'), 'label' => 'Log Viewer', 'icon' => 'terminal', 'active' => str_starts_with($routeName, 'log-viewer')])
        @endif
      </div>
    </div>

    <div class="relative group">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" :class="collapsed ? 'justify-center' : 'gap-3'"
          class="flex w-full items-center rounded-xl px-3 py-2 text-sm font-medium text-red-600 transition hover:translate-x-1 hover:bg-red-50">
          <x-nav-icon name="logout" class="h-4 w-4 text-red-500" />
          <span x-show="!collapsed" x-transition>Logout</span>
        </button>
      </form>
      <div x-show="collapsed" x-cloak
        class="pointer-events-none absolute left-full top-1/2 z-9999 ml-3 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 px-3 py-1.5 text-xs text-white shadow-xl opacity-0 transition group-hover:opacity-100">
        Logout
      </div>
    </div>
  </div>
</aside>
