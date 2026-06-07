@extends('layouts.app')

@section('content')
@php
if (!function_exists('costColor')) {
    function costColor(float $pct): string {
        if ($pct < 40) return 'text-green-600';
        if ($pct < 55) return 'text-yellow-500';
        return 'text-red-500';
    }
}
$totalCols = 9 + ($hasService ? 1 : 0) + ($hasTax ? 1 : 0);
@endphp

<div x-data="{ filtersOpen: {{ (request('q') || request('start') || request('end') || request('dept')) ? 'true' : 'false' }} }"
     class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-xl font-semibold" style="color:var(--text-primary)">Item Sales</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Daily item sales with cost and cost %.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('itemSales.export', request()->query()) }}"
        class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
        style="border-color:var(--card-border); color:var(--text-secondary)">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
      </a>
      <a href="{{ route('print.itemSales', array_filter(['start' => request('start'), 'end' => request('end')])) }}"
         target="_blank"
         class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
         style="border-color:var(--card-border); color:var(--text-secondary)">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-6 0h4v4H8v-4z"/>
        </svg>
        Print
      </a>
      <button type="button" @click="filtersOpen = !filtersOpen"
        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-white transition active:scale-95"
        :class="filtersOpen ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span x-text="filtersOpen ? 'Hide Filters' : 'Filters'"></span>
      </button>
    </div>
  </div>

  {{-- Filter panel --}}
  <div
    x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
  >
    <form method="GET" action="{{ route('itemSales.index') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

        <div class="sm:col-span-2">
          <label for="f-q" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Search</label>
          <input id="f-q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Item code or name"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
        </div>

        <div>
          <label for="f-dept" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Department</label>
          <select id="f-dept" name="dept"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500"
            style="border-color:var(--card-border); color:var(--text-primary)">
            <option value="">All departments</option>
            @foreach($departments as $dept)
              <option value="{{ $dept }}" {{ ($filters['dept'] ?? '') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label for="f-cat" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Category</label>
          <select id="f-cat" name="cat"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500"
            style="border-color:var(--card-border); color:var(--text-primary)">
            <option value="">All categories</option>
            @foreach($categories as $deptLabel => $cats)
              <optgroup label="{{ $deptLabel }}">
                @foreach($cats as $c)
                  <option value="{{ $c->name }}" {{ request('cat') === $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
        </div>

        <div>
          <label for="f-start" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Start</label>
          <input id="f-start" type="date" name="start" value="{{ $filters['start'] ?? '' }}"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
        </div>

        <div>
          <label for="f-end" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">End</label>
          <input id="f-end" type="date" name="end" value="{{ $filters['end'] ?? '' }}"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
        </div>

      </div>

      <div class="mt-4 flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between"
        style="border-color:var(--card-border)">

        {{-- Left: quick range + hide zero --}}
        <div class="flex flex-wrap items-center gap-3">
          <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-medium" style="color:var(--text-muted)">Quick:</span>
            @php
              $qBase = request()->except(['start','end']);
            @endphp
            <a href="{{ route('itemSales.index', array_merge($qBase, ['start' => now()->toDateString(), 'end' => now()->toDateString()])) }}"
              class="rounded-lg border px-2.5 py-1 text-xs font-medium transition hover:border-green-400 hover:bg-green-50 hover:text-green-700"
              style="border-color:var(--card-border); color:var(--text-secondary)">Today</a>
            <a href="{{ route('itemSales.index', array_merge($qBase, ['start' => now()->subDay()->toDateString(), 'end' => now()->subDay()->toDateString()])) }}"
              class="rounded-lg border px-2.5 py-1 text-xs font-medium transition hover:border-green-400 hover:bg-green-50 hover:text-green-700"
              style="border-color:var(--card-border); color:var(--text-secondary)">Yesterday</a>
            <a href="{{ route('itemSales.index', array_merge($qBase, ['start' => now()->startOfMonth()->toDateString(), 'end' => now()->endOfMonth()->toDateString()])) }}"
              class="rounded-lg border px-2.5 py-1 text-xs font-medium transition hover:border-green-400 hover:bg-green-50 hover:text-green-700"
              style="border-color:var(--card-border); color:var(--text-secondary)">This Month</a>
          </div>
          <label class="inline-flex cursor-pointer items-center gap-2 text-xs" style="color:var(--text-secondary)">
            <input type="checkbox" name="hide_zero" value="1"
              {{ !empty($filters['hide_zero']) ? 'checked' : '' }}
              class="h-4 w-4 rounded accent-green-600">
            Hide zero items
          </label>
        </div>

        {{-- Right: Reset + Apply --}}
        <div class="flex items-center gap-2">
          <a href="{{ route('itemSales.index') }}"
            class="rounded-lg border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
            style="border-color:var(--card-border); color:var(--text-secondary)">Reset</a>
          <button type="submit"
            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
            Apply
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">

    <div class="gfs-card p-4">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Net Revenue</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-50 text-green-600">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </span>
      </div>
      <p class="mt-2 text-base font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi['netRevenue']), 0, ',', '.') }}
      </p>
    </div>

    <div class="gfs-card p-4">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Cost</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
          </svg>
        </span>
      </div>
      <p class="mt-2 text-base font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi['cost']), 0, ',', '.') }}
      </p>
    </div>

    <div class="gfs-card p-4">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Cost %</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
          </svg>
        </span>
      </div>
      <p class="mt-2 text-base font-semibold tracking-tight {{ costColor($kpi['costPct']) }}">
        {{ number_format($kpi['costPct'], 1) }}%
      </p>
    </div>

    <div class="gfs-card p-4">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Profit</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </span>
      </div>
      <p class="mt-2 text-base font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi['profit']), 0, ',', '.') }}
      </p>
    </div>

    @if($hasService)
    <div class="gfs-card p-4">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Service</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
          </svg>
        </span>
      </div>
      <p class="mt-2 text-base font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi['service']), 0, ',', '.') }}
      </p>
    </div>
    @endif

    @if($hasTax)
    <div class="gfs-card p-4">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Tax</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </span>
      </div>
      <p class="mt-2 text-base font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi['tax']), 0, ',', '.') }}
      </p>
    </div>
    @endif

    <div class="gfs-card p-4">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Total</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
        </span>
      </div>
      <p class="mt-2 text-base font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi['total']), 0, ',', '.') }}
      </p>
    </div>

  </div>

  {{-- Table --}}
  <div class="gfs-card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm" style="min-width:900px">
        <thead>
          <tr class="text-left text-[11px] font-semibold uppercase tracking-wide"
            style="color:var(--text-muted); background:var(--content-bg); border-bottom:1px solid var(--card-border)">
            <th class="px-4 py-3 w-24">Code</th>
            <th class="px-4 py-3">Description</th>
            <th class="px-4 py-3 text-right w-14">Qty</th>
            <th class="px-4 py-3 text-right w-28">Revenue</th>
            <th class="px-4 py-3 text-right w-24">Discount</th>
            <th class="px-4 py-3 text-right w-28">Cost</th>
            <th class="px-4 py-3 text-right w-28">Profit</th>
            @if($hasService)<th class="px-4 py-3 text-right w-24">Service</th>@endif
            @if($hasTax)<th class="px-4 py-3 text-right w-24">Tax</th>@endif
            <th class="px-4 py-3 text-right w-28">Total</th>
            <th class="px-4 py-3 text-right w-20">Cost%</th>
          </tr>
        </thead>

        <tbody>
          @forelse($byDept as $dept => $cats)

            {{-- Department header --}}
            <tr style="background:var(--sidebar-bg)">
              <td colspan="{{ $totalCols }}" class="px-4 py-2.5">
                <div class="flex items-center gap-2">
                  <span class="h-1.5 w-1.5 rounded-full bg-green-400 shrink-0"></span>
                  <span class="text-sm font-bold text-white">{{ $dept ?: 'NO DEPARTMENT' }}</span>
                  <span class="text-xs" style="color:rgba(255,255,255,0.40)">
                    — Cost {{ number_format($deptKpi[$dept]['costPct'] ?? 0, 1) }}%
                  </span>
                </div>
              </td>
            </tr>

            @foreach($cats as $cat => $items)

              {{-- Category sub-header --}}
              <tr style="background:var(--content-bg); border-top:1px solid var(--card-border); border-left:3px solid #22c55e">
                <td colspan="{{ $totalCols }}" class="py-2 pl-6 pr-4">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold" style="color:var(--text-primary)">{{ $cat ?: 'UNCATEGORIZED' }}</span>
                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-medium"
                      style="border-color:var(--card-border); color:var(--text-muted)">
                      Cost {{ number_format($catKpi[$dept][$cat]['costPct'] ?? 0, 1) }}%
                    </span>
                  </div>
                </td>
              </tr>

              @foreach($items as $it)
                @php
                  $hasData = (float)$it->quantity != 0 || (float)$it->subtotal != 0 ||
                             (float)$it->discount != 0 || (float)$it->cost != 0 ||
                             (float)$it->serviceCharge != 0 || (float)$it->tax != 0;
                  if (!$hasData) continue;
                  $revenue  = (float)$it->subtotal;
                  $discount = (float)$it->discount;
                  $net      = $it->subtotal - $it->discount;
                  $cost     = (float)$it->cost;
                  $profit   = $revenue - $cost;
                  $service  = (float)$it->serviceCharge;
                  $tax      = (float)$it->tax;
                  $total    = $net + $it->serviceCharge + $it->tax;
                  $costPct  = $net > 0 ? ($it->cost / $net) * 100 : 0;
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors" style="border-top:1px solid var(--card-border)">
                  <td class="px-4 py-2 whitespace-nowrap text-xs" style="color:var(--text-muted)">{{ $it->code }}</td>
                  <td class="px-4 py-2 font-medium" style="color:var(--text-primary)">{{ $it->name }}</td>
                  <td class="px-4 py-2 text-right text-xs" style="color:var(--text-secondary)">{{ number_format((float)$it->quantity, 0) }}</td>
                  <td class="px-4 py-2 text-right text-xs" style="color:var(--text-secondary)">{{ number_format($revenue, 0, ',', '.') }}</td>
                  <td class="px-4 py-2 text-right text-xs {{ $discount > 0 ? 'text-red-500' : '' }}"
                    style="{{ $discount <= 0 ? 'color:var(--text-muted)' : '' }}">{{ number_format($discount, 0, ',', '.') }}</td>
                  <td class="px-4 py-2 text-right text-xs" style="color:var(--text-secondary)">{{ number_format($cost, 0, ',', '.') }}</td>
                  <td class="px-4 py-2 text-right text-xs" style="color:var(--text-secondary)">{{ number_format($profit, 0, ',', '.') }}</td>
                  @if($hasService)<td class="px-4 py-2 text-right text-xs" style="color:var(--text-secondary)">{{ number_format($service, 0, ',', '.') }}</td>@endif
                  @if($hasTax)<td class="px-4 py-2 text-right text-xs" style="color:var(--text-secondary)">{{ number_format($tax, 0, ',', '.') }}</td>@endif
                  <td class="px-4 py-2 text-right text-sm font-semibold" style="color:var(--text-primary)">{{ number_format($total, 0, ',', '.') }}</td>
                  <td class="px-4 py-2 text-right text-xs font-semibold {{ costColor($costPct) }}">{{ number_format($costPct, 1) }}%</td>
                </tr>
              @endforeach

              {{-- Category TOTAL --}}
              @php $ct = $catKpi[$dept][$cat]; @endphp
              <tr style="background:rgba(34,197,94,0.05); border-top:1px solid rgba(34,197,94,0.15)">
                <td class="pl-6 pr-4 py-2 text-xs font-semibold" colspan="2" style="color:var(--text-secondary)">
                  {{ $cat ?: 'UNCATEGORIZED' }} total
                </td>
                <td class="px-4 py-2 text-right text-xs font-semibold" style="color:var(--text-primary)">{{ number_format((float)$ct['qty'], 0) }}</td>
                <td class="px-4 py-2 text-right text-xs font-semibold" style="color:var(--text-primary)">{{ number_format($ct['revenue'], 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right text-xs font-semibold {{ ($ct['discount'] ?? 0) > 0 ? 'text-red-500' : '' }}"
                  style="{{ ($ct['discount'] ?? 0) <= 0 ? 'color:var(--text-muted)' : '' }}">{{ number_format($ct['discount'] ?? 0, 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right text-xs font-semibold" style="color:var(--text-primary)">{{ number_format($ct['cost'], 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right text-xs font-semibold" style="color:var(--text-primary)">{{ number_format($ct['profit'], 0, ',', '.') }}</td>
                @if($hasService)<td class="px-4 py-2 text-right text-xs font-semibold" style="color:var(--text-primary)">{{ number_format($ct['service'], 0, ',', '.') }}</td>@endif
                @if($hasTax)<td class="px-4 py-2 text-right text-xs font-semibold" style="color:var(--text-primary)">{{ number_format($ct['tax'], 0, ',', '.') }}</td>@endif
                <td class="px-4 py-2 text-right text-sm font-bold" style="color:var(--text-primary)">{{ number_format($ct['total'], 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right text-xs font-bold {{ costColor($ct['costPct']) }}">{{ number_format($ct['costPct'], 1) }}%</td>
              </tr>

            @endforeach

            {{-- Department TOTAL --}}
            @php $dt = $deptKpi[$dept]; @endphp
            <tr style="background:#1a1f2e; border-top:2px solid rgba(255,255,255,0.06)">
              <td class="px-4 py-3 text-xs font-bold text-white" colspan="2">
                {{ $dept ?: 'NO DEPARTMENT' }} TOTAL
              </td>
              <td class="px-4 py-3 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format((float)$dt['qty'], 0) }}</td>
              <td class="px-4 py-3 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($dt['revenue'], 0, ',', '.') }}</td>
              <td class="px-4 py-3 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($dt['discount'], 0, ',', '.') }}</td>
              <td class="px-4 py-3 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($dt['cost'], 0, ',', '.') }}</td>
              <td class="px-4 py-3 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($dt['profit'], 0, ',', '.') }}</td>
              @if($hasService)<td class="px-4 py-3 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($dt['service'], 0, ',', '.') }}</td>@endif
              @if($hasTax)<td class="px-4 py-3 text-right text-xs font-bold" style="color:rgba(255,255,255,0.65)">{{ number_format($dt['tax'], 0, ',', '.') }}</td>@endif
              <td class="px-4 py-3 text-right text-sm font-bold text-white">{{ number_format($dt['total'], 0, ',', '.') }}</td>
              <td class="px-4 py-3 text-right text-xs font-bold {{ costColor($dt['costPct']) }}">{{ number_format($dt['costPct'], 1) }}%</td>
            </tr>

          @empty
            <tr>
              <td colspan="{{ $totalCols }}" class="px-4 py-14 text-center text-sm" style="color:var(--text-muted)">
                No results found. Try adjusting your filters.
              </td>
            </tr>
          @endforelse

          {{-- Grand Total --}}
          <tr style="background:var(--sidebar-bg); border-top:3px solid rgba(34,197,94,0.4)">
            <td class="px-4 py-3.5 text-sm font-bold text-white" colspan="2">GRAND TOTAL</td>
            <td class="px-4 py-3.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.60)">{{ number_format((float)$kpi['qty'], 0) }}</td>
            <td class="px-4 py-3.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.60)">{{ number_format((float)$kpi['revenue'], 0, ',', '.') }}</td>
            <td class="px-4 py-3.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.60)">{{ number_format((float)$kpi['discount'], 0, ',', '.') }}</td>
            <td class="px-4 py-3.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.60)">{{ number_format((float)$kpi['cost'], 0, ',', '.') }}</td>
            <td class="px-4 py-3.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.60)">{{ number_format((float)$kpi['profit'], 0, ',', '.') }}</td>
            @if($hasService)<td class="px-4 py-3.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.60)">{{ number_format((float)$kpi['service'], 0, ',', '.') }}</td>@endif
            @if($hasTax)<td class="px-4 py-3.5 text-right text-xs font-bold" style="color:rgba(255,255,255,0.60)">{{ number_format((float)$kpi['tax'], 0, ',', '.') }}</td>@endif
            <td class="px-4 py-3.5 text-right text-sm font-bold text-white">{{ number_format((float)$kpi['total'], 0, ',', '.') }}</td>
            <td class="px-4 py-3.5 text-right text-xs font-bold {{ costColor($kpi['costPct']) }}">{{ number_format($kpi['costPct'], 1) }}%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

