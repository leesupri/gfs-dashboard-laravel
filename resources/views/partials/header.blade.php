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
    'reports.salesForecast'                => 'Sales Forecast',
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
  x-data="{
    userOpen: false,
    clock: '',
    initClock() {
      const tick = () => {
        const now = new Date().toLocaleString('id-ID', {
          timeZone: 'Asia/Jakarta',
          weekday: 'short',
          day:     '2-digit',
          month:   'short',
          year:    'numeric',
          hour:    '2-digit',
          minute:  '2-digit',
          second:  '2-digit',
          hour12: false,
        });
        this.clock = now;
      };
      tick();
      setInterval(tick, 1000);
    }
  }"
  x-init="
    $nextTick(() => { $el.classList.remove('opacity-0','-translate-y-2') });
    initClock();
  "
  @click.away="userOpen = false"
  class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6 opacity-0 -translate-y-2 transition-all duration-500"
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

    {{-- Brand + page title --}}
    <div class="leading-tight">
      <p class="text-[10px] font-semibold uppercase tracking-widest" style="color:var(--text-muted)">
        Gundaling Farmstead
      </p>
      <p class="text-sm font-bold" style="color:var(--text-primary)">{{ $pageTitle }}</p>
    </div>
  </div>

  {{-- Right: clock + user menu --}}
  <div class="flex items-center gap-3">

    {{-- Live clock --}}
    <div class="hidden text-right lg:block">
      <p class="text-xs tabular-nums font-semibold leading-tight" style="color:var(--text-primary)" x-text="clock"></p>
      <p class="text-[10px] leading-tight" style="color:var(--text-muted)">WIB · Asia/Jakarta</p>
    </div>

    {{-- Divider --}}
    <div class="hidden h-7 w-px lg:block" style="background:var(--card-border)"></div>

    {{-- User avatar + dropdown --}}
    <div class="relative">
      <button
        type="button"
        @click="userOpen = !userOpen"
        class="flex items-center gap-2.5 rounded-xl px-2 py-1.5 transition hover:bg-gray-100 active:scale-95"
        aria-label="User menu"
      >
        {{-- Name + title (sm+) --}}
        <div class="hidden text-right sm:block">
          <p class="text-xs font-semibold leading-tight" style="color:var(--text-primary)">
            {{ $currentStaffUser->name ?? 'Staff' }}
          </p>
          <p class="text-[10px] leading-tight" style="color:var(--text-muted)">
            {{ $currentStaffUser->title ?? 'User' }}
          </p>
        </div>

        {{-- Avatar --}}
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
          style="background:var(--sidebar-bg)">
          {{ strtoupper(substr($currentStaffUser->name ?? 'S', 0, 1)) }}
        </div>

        {{-- Chevron --}}
        <svg class="h-3.5 w-3.5 transition-transform duration-150"
          :class="userOpen ? 'rotate-180' : ''"
          fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
          style="color:var(--text-muted)">
          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
        </svg>
      </button>

      {{-- Dropdown panel --}}
      <div
        x-show="userOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute right-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-2xl border bg-white shadow-xl"
        style="border-color:var(--card-border); box-shadow:var(--card-shadow), 0 20px 40px rgba(0,0,0,0.12)"
      >
        {{-- User info header --}}
        <div class="border-b px-4 py-3" style="border-color:var(--card-border); background:var(--content-bg)">
          <p class="text-sm font-bold leading-tight" style="color:var(--text-primary)">
            {{ $currentStaffUser->name ?? 'Staff' }}
          </p>
          <p class="mt-0.5 text-xs" style="color:var(--text-muted)">
            {{ $currentStaffUser->title ?? 'User' }}
          </p>
        </div>

        {{-- Menu items --}}
        <div class="p-1.5 space-y-0.5">
          <a href="{{ route('welcome') }}"
            @click="userOpen = false"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition hover:bg-gray-50"
            style="color:var(--text-secondary)">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 4.5l9 5.25v9.75a1.5 1.5 0 01-1.5 1.5h-3.75v-6h-6v6H4.5A1.5 1.5 0 013 19.5V9.75z"/>
            </svg>
            Home
          </a>

          <a href="{{ route('settings.changePassword') }}"
            @click="userOpen = false"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition hover:bg-gray-50"
            style="color:var(--text-secondary)">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            Change Password
          </a>

          @if(($currentStaffUser ?? null)?->hasAccess('settings.pageLog'))
            <a href="{{ route('settings.pageLog') }}"
              @click="userOpen = false"
              class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition hover:bg-gray-50"
              style="color:var(--text-secondary)">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
              </svg>
              Activity Log
            </a>
          @endif
        </div>

        {{-- Logout --}}
        <div class="border-t p-1.5" style="border-color:var(--card-border)">
          <form method="POST" action="{{ route('logout') }}" aria-label="Log out">
            @csrf
            <button type="submit"
              class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50">
              <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-7.5A2.25 2.25 0 003.75 5.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
              </svg>
              Log Out
            </button>
          </form>
        </div>

      </div>
    </div>

  </div>

</header>
