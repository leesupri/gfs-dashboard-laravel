<!-- resources\views\partials\sidebar.blade.php -->
@php
  $nav = [
    ['label' => 'Dashboard', 'href' => route('dashboard'), 'key' => 'dashboard', 'icon' => 'home'],
    ['label' => 'Sales', 'href' => route('sales.index'), 'key' => 'sales.index', 'icon' => 'cart'],
    ['label' => 'ItemSales', 'href' => route('itemSales.index'), 'key' => 'itemSales.index', 'icon' => 'chart'],
    ['label' => 'Order Board', 'href' => route('reports.orderBoard'), 'key' => 'reports.orderBoard', 'icon' => 'clipboard'],
    ['label' => 'NoSales', 'href' => route('noSales.index'), 'key' => 'noSales.index', 'icon' => 'x'],
    ['label' => 'Summary Sales', 'href' => route('summarySales.index'), 'key' => 'summarySales.index', 'icon' => 'trend'],
    ['label' => 'Void report', 'href' => route('reports.void'), 'key' => 'reports.void', 'icon' => 'alert'],
    ['label' => 'Consumption DI', 'href' => route('reports.consumptionDetailInvoice'), 'key' => 'reports.consumptionDetailInvoice', 'icon' => 'file'],
    ['label' => 'Consumption WH', 'href' => route('reports.consumptionWarehouse'), 'key' => 'reports.consumptionWarehouse', 'icon' => 'database'],
    ['label' => 'Recipes Report', 'href' => route('reports.recipe'), 'key' => 'reports.recipe', 'icon' => 'book'],
    ['label' => 'Recipes Board', 'href' => route('reports.recipe-board'), 'key' => 'reports.recipe-board', 'icon' => 'book'],
    ['label' => 'Activity Logs', 'href' => route('reports.activityLog'), 'key' => 'reports.activityLog', 'icon' => 'activity'],
    ['label' => 'Market List', 'href' => route('reports.marketList'), 'key' => 'reports.marketList', 'icon' => 'list'],
    ['label' => 'Production Summary', 'href' => route('reports.productionSummary'), 'key' => 'reports.productionSummary', 'icon' => 'layers'],
    ['label' => 'Production Card', 'href' => route('reports.productionCard.index'), 'key' => 'reports.productionCard.index', 'icon' => 'grid'],
    ['label' => 'Purchase Summary', 'href' => route('reports.purchaseSummary'), 'key' => 'reports.purchaseSummary', 'icon' => 'bag'],
    ['label' => 'Purchase Detail', 'href' => route('reports.purchaseDetail'), 'key' => 'reports.purchaseDetail', 'icon' => 'file'],
    ['label' => 'Purchase Detail by Partner', 'href' => route('reports.purchaseDetailPartner'), 'key' => 'reports.purchaseDetailPartner', 'icon' => 'users'],
    ['label' => 'Physical Stock Count', 'href' => route('reports.physicalStockCountSummary'), 'key' => 'reports.physicalStockCountSummary', 'icon' => 'archive'],
    ['label' => 'Transfer Detail', 'href' => route('reports.transferDetail'), 'key' => 'reports.transferDetail', 'icon' => 'repeat'],
    ['label' => 'Waste Summary', 'href' => route('reports.wasteSummary'), 'key' => 'reports.wasteSummary', 'icon' => 'trash'],
  ];

  $routeName = request()->route()?->getName() ?? '';
  $staffUser = $currentStaffUser ?? null;

  $nav = collect($nav)
      ->filter(fn ($item) => $staffUser && $staffUser->hasAccess($item['key']))
      ->values()
      ->all();

  $settingsRoutes = [
    'settings.staff',
    'settings.security',
    'settings.changePassword',
    'settings.pageLog',
    'log-viewer.index',
  ];

  $settingsOpen = in_array($routeName, $settingsRoutes, true);
@endphp

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
        <img
          src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
          alt="Gundaling Farmstead"
          class="h-8 w-auto opacity-0 -translate-y-2 scale-95 transition-all duration-500 ease-out"
          x-init="$nextTick(() => { $el.classList.remove('opacity-0','-translate-y-2','scale-95') })"
        />
      </a>

      <button
        type="button"
        @click="mobileMenuOpen = false"
        class="rounded-lg border border-white/20 px-2 py-1 text-sm text-white/70 hover:bg-white/10"
      >
        ✕
      </button>
    </div>

    <div class="border-b px-4 py-2 leading-tight" style="border-color:var(--sidebar-border)">
      <div class="text-sm font-semibold text-white">GFS Dashboard</div>
      <div class="text-xs" style="color:var(--sidebar-text)">{{ $currentStaffUser->title ?? 'User' }}</div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Main navigation">
      @foreach ($nav as $item)
        @include('partials.sidebar-nav-item', [
          'href' => $item['href'],
          'label' => $item['label'],
          'icon' => $item['icon'],
          'active' => $routeName === $item['key'],
        ])
      @endforeach
    </nav>

    <div class="border-t p-3 space-y-2" style="border-color:var(--sidebar-border)">
      <div x-data="{ open: {{ $settingsOpen ? 'true' : 'false' }} }" class="space-y-2">
        <button
          type="button"
          @click="open = !open"
          class="relative flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200 hover:bg-white/8" style="color:var(--sidebar-text)"
        >
          <x-nav-icon name="settings" class="h-5 w-5 text-gray-400" />
          <span>Settings</span>

          <svg
            class="ml-auto h-4 w-4 transition-transform duration-200"
            :class="open ? 'rotate-180' : ''"
            fill="none" viewBox="0 0 24 24" stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
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
            @include('partials.sidebar-settings-item', [
              'href' => route('settings.staff'),
              'label' => 'Staff Settings',
              'icon' => 'users',
              'active' => $routeName === 'settings.staff',
            ])
          @endif

          @if(($currentStaffUser ?? null)?->hasAccess('settings.security'))
            @include('partials.sidebar-settings-item', [
              'href' => route('settings.security'),
              'label' => 'Security Settings',
              'icon' => 'shields',
              'active' => $routeName === 'settings.security',
            ])
          @endif

          @include('partials.sidebar-settings-item', [
            'href' => route('settings.changePassword'),
            'label' => 'Change Password',
            'icon' => 'key',
            'active' => $routeName === 'settings.changePassword',
          ])

          @if(($currentStaffUser ?? null)?->hasAccess('settings.pageLog'))
            @include('partials.sidebar-settings-item', [
              'href' => route('settings.pageLog'),
              'label' => 'Activity Log',
              'icon' => 'activity',
              'active' => $routeName === 'settings.pageLog',
            ])
          @endif

          @if(($currentStaffUser ?? null)?->hasAccess('log-viewer.index'))
            @include('partials.sidebar-settings-item', [
              'href' => route('log-viewer.index'),
              'label' => 'Log Viewer',
              'icon' => 'terminal',
              'active' => str_starts_with($routeName, 'log-viewer'),
            ])
          @endif
        </div>
      </div>

      <div class="relative group">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit"
                  class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-red-400 transition-all duration-150 hover:translate-x-1 hover:bg-red-500/15">
            <x-nav-icon name="logout" class="h-4 w-4 text-red-500" />
            <span>Logout</span>
          </button>
        </form>
      </div>
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
      <img
        src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
        alt="Gundaling Farmstead"
        class="h-8 w-auto opacity-0 scale-90 transition-all duration-500 ease-out"
        x-init="$nextTick(() => {
          $el.classList.remove('opacity-0','scale-90');
          setTimeout(() => {
            $el.classList.add('scale-105');
            setTimeout(() => $el.classList.remove('scale-105'), 120);
          }, 300);
        })"
      />
    </a>

    <div class="leading-tight text-white" x-show="!collapsed" x-transition>
      <div class="text-sm font-semibold text-white">GFS Dashboard</div>
      <div class="text-xs" style="color:var(--sidebar-text)">{{ $currentStaffUser->title ?? 'User' }}</div>
    </div>
  </div>

  <nav class="relative z-40 flex-1 overflow-visible p-3" aria-label="Main navigation">
    @foreach ($nav as $item)
      @include('partials.sidebar-nav-item', [
        'href' => $item['href'],
        'label' => $item['label'],
        'icon' => $item['icon'],
        'active' => $routeName === $item['key'],
      ])
    @endforeach
  </nav>

  <div class="border-t p-3 space-y-2" style="border-color:var(--sidebar-border)">
    <div x-data="{ open: {{ $settingsOpen ? 'true' : 'false' }} }" class="space-y-2">
      <button
        type="button"
        @click="open = !open"
        :class="collapsed ? 'justify-center' : 'gap-3'"
        class="relative flex w-full items-center rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200 hover:bg-white/8" style="color:var(--sidebar-text)"
      >
        <x-nav-icon name="settings" class="h-5 w-5 text-gray-400" />
        <span x-show="!collapsed" x-transition>Settings</span>

        <svg
          x-show="!collapsed"
          class="ml-auto h-4 w-4 transition-transform duration-200"
          :class="open ? 'rotate-180' : ''"
          fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--sidebar-text)"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
        </svg>

        <div x-show="collapsed"
     x-cloak
     class="pointer-events-none absolute left-full top-1/2 z-9999 ml-3 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 px-3 py-1.5 text-xs text-white shadow-xl opacity-0 transition group-hover:opacity-100">
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
          @include('partials.sidebar-settings-item', [
            'href' => route('settings.staff'),
            'label' => 'Staff Settings',
            'icon' => 'users',
            'active' => $routeName === 'settings.staff',
          ])
        @endif

        @if(($currentStaffUser ?? null)?->hasAccess('settings.security'))
          @include('partials.sidebar-settings-item', [
            'href' => route('settings.security'),
            'label' => 'Security Settings',
            'icon' => 'shields',
            'active' => $routeName === 'settings.security',
          ])
        @endif

        @include('partials.sidebar-settings-item', [
          'href' => route('settings.changePassword'),
          'label' => 'Change Password',
          'icon' => 'key',
          'active' => $routeName === 'settings.changePassword',
        ])

        @if(($currentStaffUser ?? null)?->hasAccess('settings.pageLog'))
          @include('partials.sidebar-settings-item', [
            'href' => route('settings.pageLog'),
            'label' => 'Activity Log',
            'icon' => 'activity',
            'active' => $routeName === 'settings.pageLog',
          ])
        @endif

        @if(($currentStaffUser ?? null)?->hasAccess('log-viewer.index'))
          @include('partials.sidebar-settings-item', [
            'href' => route('log-viewer.index'),
            'label' => 'Log Viewer',
            'icon' => 'terminal',
            'active' => str_starts_with($routeName, 'log-viewer'),
          ])
        @endif
      </div>
    </div>

    <div class="relative group">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
                :class="collapsed ? 'justify-center' : 'gap-3'"
                class="flex w-full items-center rounded-xl px-3 py-2 text-sm font-medium text-red-600 transition-all duration-150 hover:translate-x-1 hover:bg-red-50">
          <x-nav-icon name="logout" class="h-4 w-4 text-red-500" />
          <span x-show="!collapsed" x-transition>Logout</span>
        </button>
      </form>

      <div x-show="collapsed"
     x-cloak
     class="pointer-events-none absolute left-full top-1/2 z-9999 ml-3 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 px-3 py-1.5 text-xs text-white shadow-xl opacity-0 transition group-hover:opacity-100">
        Logout
      </div>
    </div>
  </div>
</aside>
