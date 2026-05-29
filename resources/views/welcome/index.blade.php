@extends('layouts.app')

@section('content')
@php
    $staffUser = $currentStaffUser ?? null;

    $allShortcuts = [
        // Overview
        ['group' => 'Overview',  'icon' => 'chart',       'label' => 'Dashboard',             'route' => 'dashboard',                        'desc' => 'Sales performance overview'],
        ['group' => 'Overview',  'icon' => 'receipt',     'label' => 'Sales',                  'route' => 'sales.index',                      'desc' => 'Transactions and payment breakdown'],
        ['group' => 'Overview',  'icon' => 'tag',         'label' => 'Item Sales',             'route' => 'itemSales.index',                   'desc' => 'Per-item sales with cost analysis'],
        ['group' => 'Overview',  'icon' => 'summary',     'label' => 'Summary Sales',          'route' => 'summarySales.index',               'desc' => 'Revenue, payments, and P&L summary'],
        ['group' => 'Overview',  'icon' => 'nosales',     'label' => 'No Sales',               'route' => 'noSales.index',                    'desc' => 'Compliment and duty meal items'],
        ['group' => 'Overview',  'icon' => 'board',       'label' => 'Order Board',            'route' => 'reports.orderBoard',               'desc' => 'Per-bill order board view'],
        ['group' => 'Overview',  'icon' => 'void',        'label' => 'Void Report',            'route' => 'reports.void',                     'desc' => 'Voided transactions and reasons'],
        // Reports
        ['group' => 'Reports',   'icon' => 'activity',    'label' => 'Activity Logs',          'route' => 'reports.activityLog',              'desc' => 'Staff login, payment, and system events'],
        ['group' => 'Reports',   'icon' => 'market',      'label' => 'Market List',            'route' => 'reports.marketList',               'desc' => 'Active items with pricing and UOM'],
        ['group' => 'Reports',   'icon' => 'recipe',      'label' => 'Recipes Report',         'route' => 'reports.recipe',                   'desc' => 'Expected vs actual ingredient cost'],
        ['group' => 'Reports',   'icon' => 'recipe',      'label' => 'Recipe Board',           'route' => 'reports.recipe-board',             'desc' => 'Recipes with ingredient breakdown'],
        ['group' => 'Reports',   'icon' => 'factory',     'label' => 'Production Summary',     'route' => 'reports.productionSummary',        'desc' => 'Production output by category and UOM'],
        ['group' => 'Reports',   'icon' => 'card',        'label' => 'Production Card',        'route' => 'reports.productionCard.index',     'desc' => 'Browse individual production cards'],
        ['group' => 'Reports',   'icon' => 'purchase',    'label' => 'Purchase Summary',       'route' => 'reports.purchaseSummary',          'desc' => 'Purchase totals by category and item'],
        ['group' => 'Reports',   'icon' => 'detail',      'label' => 'Purchase Detail',        'route' => 'reports.purchaseDetail',           'desc' => 'Line-by-line purchase invoice detail'],
        ['group' => 'Reports',   'icon' => 'partner',     'label' => 'Purchase By Partner',    'route' => 'reports.purchaseDetailPartner',    'desc' => 'Purchases grouped by supplier'],
        ['group' => 'Reports',   'icon' => 'consumptionI','label' => 'Consumption by Invoice', 'route' => 'reports.consumptionDetailInvoice', 'desc' => 'Recipe ingredient usage per invoice'],
        ['group' => 'Reports',   'icon' => 'consumptionW','label' => 'Consumption by WH',      'route' => 'reports.consumptionWarehouse',     'desc' => 'Ingredient consumption per warehouse'],
        ['group' => 'Reports',   'icon' => 'stock',       'label' => 'Physical Stock Count',   'route' => 'reports.physicalStockCountSummary','desc' => 'Variance between calculated and actual stock'],
        ['group' => 'Reports',   'icon' => 'transfer',    'label' => 'Transfer Detail',        'route' => 'reports.transferDetail',           'desc' => 'Stock movements between warehouses'],
        ['group' => 'Reports',   'icon' => 'waste',       'label' => 'Waste Summary',          'route' => 'reports.wasteSummary',             'desc' => 'Waste cost by category and item'],
        ['group' => 'Reports',   'icon' => 'forecast',    'label' => 'Sales Forecast',         'route' => 'reports.salesForecast',            'desc' => 'Projected sales by item, category & dept'],
        // Settings
        ['group' => 'Settings',  'icon' => 'staff',       'label' => 'Staff Settings',         'route' => 'settings.staff',                   'desc' => 'Create and manage staff accounts'],
        ['group' => 'Settings',  'icon' => 'security',    'label' => 'Security Settings',      'route' => 'settings.security',                'desc' => 'Control page access per staff'],
        ['group' => 'Settings',  'icon' => 'pagelog',     'label' => 'Activity Log',           'route' => 'settings.pageLog',                 'desc' => 'Who visited which page and any errors'],
        ['group' => 'Settings',  'icon' => 'password',    'label' => 'Change Password',        'route' => 'settings.changePassword',          'desc' => 'Update your account password'],
        ['group' => 'Settings',  'icon' => 'terminal',    'label' => 'Log Viewer',             'route' => 'log-viewer.index',                 'desc' => 'Browse and search application logs'],
    ];

    $shortcuts = collect($allShortcuts)->filter(function ($item) use ($staffUser) {
        return $staffUser && $staffUser->hasAccess($item['route']);
    })->values()->groupBy('group');

    // Icon class per group for card icons
    $groupIconClass = [
        'Overview' => 'bg-green-50 text-green-600 group-hover:bg-green-100',
        'Reports'  => 'bg-blue-50 text-blue-600 group-hover:bg-blue-100',
        'Settings' => 'bg-slate-100 text-slate-600 group-hover:bg-slate-200',
    ];

    $groupOrder = ['Overview', 'Reports', 'Settings'];

    $groupMeta = [
        'Overview' => [
            'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            'color' => 'bg-green-50 text-green-600',
        ],
        'Reports' => [
            'icon'  => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'color' => 'bg-blue-50 text-blue-600',
        ],
        'Settings' => [
            'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'color' => 'bg-slate-100 text-slate-600',
        ],
    ];

    $icons = [
        // Overview
        'chart'        => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'receipt'      => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'tag'          => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z',
        'summary'      => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
        'nosales'      => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
        'board'        => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h.01M9 16h.01M15 12h.01M15 16h.01',
        'void'         => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        // Reports
        'activity'     => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'market'       => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        'recipe'       => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        'factory'      => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
        'card'         => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'purchase'     => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        'detail'       => 'M4 6h16M4 10h16M4 14h16M4 18h16',
        'partner'      => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'consumptionI' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'consumptionW' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
        'stock'        => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'transfer'     => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
        'waste'        => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        // Settings
        'staff'        => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'security'     => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'pagelog'      => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'password'     => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
        'forecast'     => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h13M3 4v13',
        'terminal'     => 'M4 17l6-6-6-6M12 19h8',
    ];

    $now = now('Asia/Jakarta')->locale('id');
    $hour = (int) $now->format('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

<div class="space-y-6">

  {{-- ── Hero greeting ── --}}
  <div class="relative overflow-hidden rounded-2xl p-6 sm:p-8"
       style="background: linear-gradient(135deg, #0f1117 0%, #1a2035 60%, #0f2818 100%);">

    {{-- Subtle dot grid overlay --}}
    <div class="absolute inset-0 opacity-[0.03]"
         style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 24px 24px;"></div>

    {{-- Green glow accent --}}
    <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full opacity-10"
         style="background: radial-gradient(circle, #22c55e, transparent 70%);"></div>
    <div class="absolute -bottom-8 left-1/3 h-32 w-32 rounded-full opacity-5"
         style="background: radial-gradient(circle, #22c55e, transparent 70%);"></div>

    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-medium uppercase tracking-widest text-green-400 opacity-80">
          {{ $now->format('l, d F Y') }}
        </p>
        <h1 class="mt-1.5 text-2xl font-semibold text-white sm:text-3xl">
          {{ $greeting }}, <span class="text-green-400">{{ $staffUser->name ?? 'Staff' }}</span>
        </h1>
        <p class="mt-1.5 text-sm text-white/50">
          Gundaling Farmstead System &mdash; select a module below to get started.
        </p>
      </div>

      <div class="flex shrink-0 items-center gap-3">
        <div class="text-right">
          <p class="text-xs text-white/40">Logged in as</p>
          <p class="text-sm font-semibold text-white/90">{{ $staffUser->name ?? '—' }}</p>
          @if($staffUser && $staffUser->role ?? null)
            <p class="mt-0.5 text-[11px] text-white/40">{{ $staffUser->role }}</p>
          @endif
        </div>
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-500/20 text-green-400 ring-1 ring-green-500/20">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- ── No access state ── --}}
  @if($shortcuts->flatten(1)->isEmpty())
    <div class="flex flex-col items-center gap-3 rounded-2xl border bg-white p-12 text-center shadow-sm"
         style="border-color:var(--card-border)">
      <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50">
        <svg class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-semibold" style="color:var(--text-primary)">No modules available</p>
        <p class="mt-1 text-xs" style="color:var(--text-muted)">Contact your administrator to request access to modules.</p>
      </div>
    </div>

  @else
    {{-- ── Shortcut groups ── --}}
    @foreach ($groupOrder as $groupName)
      @if($shortcuts->has($groupName))
        @php $groupItems = $shortcuts[$groupName]; $meta = $groupMeta[$groupName]; @endphp

        <div class="space-y-3">

          {{-- Group heading --}}
          <div class="flex items-center gap-2.5">
            <div class="flex h-6 w-6 items-center justify-center rounded-lg {{ $meta['color'] }}">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
              </svg>
            </div>
            <h2 class="text-sm font-semibold" style="color:var(--text-primary)">{{ $groupName }}</h2>
            <span class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                  style="background:var(--content-bg); color:var(--text-muted)">
              {{ $groupItems->count() }} module{{ $groupItems->count() !== 1 ? 's' : '' }}
            </span>
          </div>

          {{-- Cards grid --}}
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($groupItems as $item)
              @php
                $iconPath  = $icons[$item['icon']] ?? $icons['receipt'];
                $iconClass = $groupIconClass[$groupName] ?? 'bg-blue-50 text-blue-600 group-hover:bg-blue-100';
              @endphp
              <a
                href="{{ route($item['route']) }}"
                @click="loading = true"
                class="group flex items-start gap-4 rounded-2xl border bg-white p-4 transition-all duration-150
                       hover:-translate-y-0.5 hover:shadow-md"
                style="border-color:var(--card-border); box-shadow:var(--card-shadow)"
              >
                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors duration-150 {{ $iconClass }}">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold truncate transition-colors duration-150 group-hover:text-green-700"
                       style="color:var(--text-primary)">
                      {{ $item['label'] }}
                    </p>
                    <svg class="h-3.5 w-3.5 shrink-0 opacity-0 transition-all duration-150 group-hover:opacity-100 group-hover:translate-x-0.5"
                         style="color:var(--text-muted)"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                  </div>
                  <p class="mt-0.5 text-xs leading-relaxed" style="color:var(--text-muted)">
                    {{ $item['desc'] }}
                  </p>
                </div>
              </a>
            @endforeach
          </div>

        </div>
      @endif
    @endforeach
  @endif

</div>
@endsection
