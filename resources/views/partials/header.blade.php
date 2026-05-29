@php
  $routeTitleMap = [
    'welcome'                              => 'Home',
    'dashboard'                            => 'Dashboard',
    'sales.index'                          => 'Sales',
    'itemSales.index'                      => 'Item Sales',
    'noSales.index'                        => 'No Sales',
    'summarySales.index'                   => 'Summary Sales',
    'reports.void'                         => 'Void Report',
    'reports.orderBoard'                   => 'Order Board',
    'reports.activityLog'                  => 'Activity Log',
    'reports.consumptionDetailInvoice'     => 'Consumption by Invoice',
    'reports.consumptionWarehouse'         => 'Consumption by Warehouse',
    'reports.recipe'                       => 'Recipe Report',
    'reports.recipe-board'                 => 'Recipes Board',
    'reports.marketList'                   => 'Market List',
    'reports.productionSummary'            => 'Production Summary',
    'reports.productionCard.index'         => 'Production Cards',
    'reports.productionCard.show'          => 'Production Card Detail',
    'reports.purchaseSummary'              => 'Purchase Summary',
    'reports.purchaseDetail'               => 'Purchase Detail',
    'reports.purchaseDetailPartner'        => 'Purchase by Partner',
    'reports.physicalStockCountSummary'    => 'Physical Stock Count',
    'reports.transferDetail'               => 'Transfer Detail',
    'reports.wasteSummary'                 => 'Waste Summary',
    'settings.staff'                       => 'Staff Settings',
    'settings.security'                    => 'Security & Permissions',
    'settings.changePassword'              => 'Change Password',
    'settings.pageLog'                     => 'Page Activity Log',
    'log-viewer.index'                     => 'Log Viewer',
  ];

  $routeName  = request()->route()?->getName() ?? '';
  $pageTitle  = $routeTitleMap[$routeName] ?? 'Dashboard';
@endphp

<header
  class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6"
  x-init="$nextTick(() => { $el.classList.remove('opacity-0','-translate-y-2') })"
  style="opacity:0; transform:translateY(-8px); transition: opacity .4s ease, transform .4s ease;"
  aria-label="App header"
>

  {{-- Left: menu toggle + page title --}}
  <div class="flex items-center gap-3">

    {{-- Hamburger / collapse toggle --}}
    <button
      type="button"
      aria-label="Toggle navigation"
      @click="window.innerWidth < 768 ? mobileMenuOpen = true : collapsed = !collapsed"
      class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white p-2 transition hover:bg-gray-50 active:scale-95"
    >
      <svg class="h-4 w-4 transition-transform duration-200" :class="collapsed ? 'rotate-90' : ''"
        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>

    {{-- Brand + page --}}
    <div class="leading-tight">
      <p class="text-[10px] font-semibold uppercase tracking-widest" style="color:var(--text-muted)">
        Gundaling Farmstead
      </p>
      <p class="text-sm font-bold" style="color:var(--text-primary)">{{ $pageTitle }}</p>
    </div>
  </div>

  {{-- Right: user info --}}
  <div class="flex items-center gap-3">
    <div class="hidden text-right sm:block">
      <p class="text-sm font-semibold leading-tight" style="color:var(--text-primary)">
        {{ $currentStaffUser->name ?? 'Staff' }}
      </p>
      <p class="text-xs leading-tight" style="color:var(--text-muted)">
        {{ $currentStaffUser->title ?? 'User' }}
      </p>
    </div>

    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
      style="background:var(--sidebar-bg)">
      {{ strtoupper(substr($currentStaffUser->name ?? 'S', 0, 1)) }}
    </div>
  </div>

</header>
