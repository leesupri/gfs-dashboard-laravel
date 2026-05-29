@php
  /**
   * trendClass / trendLabel / trendIcon
   * Returns Tailwind color + label + SVG path for a trend percentage.
   */
  function trendClass(float $pct): string {
    if ($pct >  15) return 'text-green-600';
    if ($pct >   5) return 'text-emerald-500';
    if ($pct >  -5) return 'text-gray-400';
    if ($pct > -15) return 'text-amber-500';
    return 'text-red-500';
  }
  function trendLabel(float $pct): string {
    if ($pct >  15) return 'Strong ↑';
    if ($pct >   5) return 'Growing ↑';
    if ($pct >  -5) return 'Stable';
    if ($pct > -15) return 'Slowing ↓';
    return 'Declining ↓';
  }
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Sales Forecast</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        Based on last <strong>{{ $histDays }}</strong> days →
        projecting next <strong>{{ $forecastDays }}</strong> days
        ({{ now()->format('d M Y') }} – {{ now()->addDays($forecastDays - 1)->format('d M Y') }})
      </p>
    </div>
  </div>

  {{-- Controls --}}
  <div class="gfs-card p-5 space-y-5">

    {{-- Period selectors — plain links so each independently updates its param --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

      {{-- Historical base --}}
      <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">
          Historical base period
          <span class="ml-1 font-normal normal-case" style="color:var(--text-muted)">
            ({{ $histFrom->format('d M') }} – {{ $histTo->format('d M Y') }})
          </span>
        </p>
        <div class="flex flex-wrap gap-2">
          @foreach([7 => '7d', 14 => '14d', 30 => '30d', 60 => '60d', 90 => '90d'] as $days => $label)
            <a href="{{ route('reports.salesForecast', array_merge(request()->query(), ['hist_days' => $days])) }}"
              class="rounded-xl px-4 py-2 text-sm font-semibold transition active:scale-95
                {{ $histDays === $days
                  ? 'bg-green-600 text-white shadow-sm'
                  : 'border border-gray-200 bg-white hover:border-green-400 hover:text-green-600' }}"
              @if($histDays !== $days) style="color:var(--text-secondary)" @endif>
              {{ $label }}
            </a>
          @endforeach
        </div>
      </div>

      {{-- Forecast horizon --}}
      <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">
          Forecast horizon
          <span class="ml-1 font-normal normal-case" style="color:var(--text-muted)">
            (projecting {{ now()->format('d M') }} – {{ now()->addDays($forecastDays - 1)->format('d M Y') }})
          </span>
        </p>
        <div class="flex flex-wrap gap-2">
          @foreach([7 => 'Next 7d', 14 => 'Next 14d', 30 => 'Next 30d'] as $days => $label)
            <a href="{{ route('reports.salesForecast', array_merge(request()->query(), ['forecast_days' => $days])) }}"
              class="rounded-xl px-4 py-2 text-sm font-semibold transition active:scale-95
                {{ $forecastDays === $days
                  ? 'bg-blue-600 text-white shadow-sm'
                  : 'border border-gray-200 bg-white hover:border-blue-400 hover:text-blue-600' }}"
              @if($forecastDays !== $days) style="color:var(--text-secondary)" @endif>
              {{ $label }}
            </a>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Text filters — submitted via the Apply button only --}}
    <form method="GET" action="{{ route('reports.salesForecast') }}">
      {{-- Carry the current period selections into the filter form --}}
      <input type="hidden" name="hist_days"     value="{{ $histDays }}">
      <input type="hidden" name="forecast_days" value="{{ $forecastDays }}">

      <div class="grid grid-cols-1 gap-4 border-t border-gray-100 pt-4 sm:grid-cols-3">
        <div>
          <label for="fc-dept" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Department</label>
          <input id="fc-dept" type="text" name="dept" value="{{ $dept }}" placeholder="Filter department…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="fc-cat" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Category</label>
          <input id="fc-cat" type="text" name="cat" value="{{ $cat }}" placeholder="Filter category…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="fc-q" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Search</label>
          <input id="fc-q" type="text" name="q" value="{{ $q }}" placeholder="Item name…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
      </div>

      <div class="mt-4 flex justify-end gap-2 border-t border-gray-100 pt-4">
        <a href="{{ route('reports.salesForecast', ['hist_days' => $histDays, 'forecast_days' => $forecastDays]) }}"
          class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
          style="color:var(--text-secondary)">Clear filters</a>
        <button type="submit"
          class="rounded-xl bg-green-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
          Apply
        </button>
      </div>
    </form>

  </div>

  {{-- KPI strip --}}
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
    <div class="gfs-card flex items-center gap-3 p-4" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Forecast Revenue</p>
        <p class="mt-0.5 text-base font-bold leading-none text-green-400 tabular-nums">
          {{ number_format($totalForecastRev, 0, ',', '.') }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Items Tracked</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($itemCount) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Departments</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($deptCount) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4 {{ $growingCount > 0 ? 'border-green-200' : '' }}">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $growingCount > 0 ? 'bg-green-100' : 'bg-gray-100' }}">
        <svg class="h-5 w-5 {{ $growingCount > 0 ? 'text-green-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Growing (>5%)</p>
        <p class="mt-0.5 text-lg font-bold leading-none {{ $growingCount > 0 ? 'text-green-600' : '' }}"
          @if(!$growingCount) style="color:var(--text-primary)" @endif>
          {{ number_format($growingCount) }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4 {{ $decliningCount > 0 ? 'border-red-200' : '' }}">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $decliningCount > 0 ? 'bg-red-100' : 'bg-gray-100' }}">
        <svg class="h-5 w-5 {{ $decliningCount > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Declining (&lt;-5%)</p>
        <p class="mt-0.5 text-lg font-bold leading-none {{ $decliningCount > 0 ? 'text-red-500' : '' }}"
          @if(!$decliningCount) style="color:var(--text-primary)" @endif>
          {{ number_format($decliningCount) }}
        </p>
      </div>
    </div>
  </div>

  {{-- Seasonality panel: DOW heatmap + forecast window --}}
  @if(!empty($dowPattern['indices']))
  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

    {{-- Day-of-week pattern --}}
    <div class="gfs-card p-5">
      <p class="mb-3 text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">
        Weekly Pattern — from last {{ $histDays }} days
      </p>
      @php
        // Display order: Mon(2) → Sun(1)
        $dowOrder  = [2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat', 1 => 'Sun'];
        $indices   = $dowPattern['indices'];
        $maxIndex  = max($indices) ?: 1;
      @endphp
      <div class="flex items-end gap-2">
        @foreach($dowOrder as $dow => $dayName)
          @php
            $idx     = $indices[$dow] ?? 1.0;
            $barPct  = ($idx / $maxIndex) * 100;
            $barColor = $idx > 1.2 ? '#22c55e' : ($idx > 0.9 ? '#3b82f6' : '#f59e0b');
            $textColor = $idx > 1.2 ? 'text-green-600' : ($idx > 0.9 ? 'text-blue-600' : 'text-amber-500');
          @endphp
          <div class="flex flex-1 flex-col items-center gap-1">
            <span class="text-[10px] font-bold tabular-nums {{ $textColor }}">
              {{ number_format($idx, 2) }}
            </span>
            <div class="w-full rounded-t" style="height:{{ max(6, (int)($barPct * 0.6)) }}px; background:{{ $barColor }}; opacity:0.8"></div>
            <span class="text-[10px] font-semibold" style="color:var(--text-muted)">{{ $dayName }}</span>
          </div>
        @endforeach
      </div>
      <p class="mt-3 text-[10px]" style="color:var(--text-muted)">
        Index 1.0 = average day. Higher = busier. Used to weight the forecast.
      </p>
    </div>

    {{-- Forecast window day-by-day --}}
    <div class="gfs-card p-5">
      <p class="mb-3 text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">
        Forecast window — next {{ $forecastDays }} days
        <span class="ml-1 font-normal normal-case">(seasonal factor = {{ number_format($seasonalFactor, 2) }} vs flat {{ $forecastDays }}.00)</span>
      </p>
      <div class="space-y-1.5">
        @foreach($forecastWindow as $day)
          @php
            $idx  = $day['index'];
            $color = $idx > 1.2 ? 'bg-green-100 text-green-700' : ($idx > 0.9 ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700');
          @endphp
          <div class="flex items-center gap-3">
            <span class="w-16 text-xs font-semibold" style="color:var(--text-secondary)">{{ $day['date'] }}</span>
            <span class="w-8 text-[10px] font-bold" style="color:var(--text-muted)">{{ $day['day'] }}</span>
            <div class="flex-1 overflow-hidden rounded-full bg-gray-100" style="height:6px">
              <div class="h-full rounded-full transition-all"
                style="width:{{ min(100, $idx * 66.7) }}%; background:{{ $idx > 1.2 ? '#22c55e' : ($idx > 0.9 ? '#3b82f6' : '#f59e0b') }}">
              </div>
            </div>
            <span class="w-12 text-right text-[10px] font-semibold tabular-nums {{ $idx > 1.2 ? 'text-green-600' : ($idx > 0.9 ? 'text-blue-600' : 'text-amber-500') }}">
              ×{{ number_format($idx, 2) }}
            </span>
          </div>
        @endforeach
      </div>
    </div>

  </div>
  @endif

  {{-- Hierarchical table --}}
  <div class="gfs-card overflow-hidden">
    @if(empty($grouped))
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--text-muted)">No sales data available for the selected period.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
              <th class="px-4 py-3 text-left">Item</th>
              <th class="px-4 py-3 w-28">Hist. Qty<br><span class="font-normal normal-case">({{ $histDays }}d)</span></th>
              <th class="px-4 py-3 w-32">Hist. Revenue<br><span class="font-normal normal-case">({{ $histDays }}d)</span></th>
              <th class="px-4 py-3 w-24">Avg / Day</th>
              <th class="px-4 py-3 w-28">Trend<br><span class="font-normal normal-case">vs prev {{ $histDays }}d</span></th>
              <th class="px-4 py-3 w-28">Seasonal Qty<br><span class="font-normal normal-case">({{ $forecastDays }}d adj.)</span></th>
              <th class="px-4 py-3 w-36">Forecast Revenue<br><span class="font-normal normal-case">({{ $forecastDays }}d)</span></th>
            </tr>
          </thead>
          <tbody>
            @php
              $grandHistRev  = 0; $grandHistQty  = 0;
              $grandFcRev    = 0; $grandFcQty    = 0;
            @endphp

            @foreach($grouped as $deptName => $categories)
              @php
                $deptItems   = collect($categories)->flatten(1);
                $deptHistQty = $deptItems->sum('hist_qty');
                $deptHistRev = $deptItems->sum('hist_rev');
                $deptFcQty   = $deptItems->sum('forecast_qty');
                $deptFcRev   = $deptItems->sum('forecast_rev');
                $grandHistQty += $deptHistQty; $grandHistRev += $deptHistRev;
                $grandFcQty   += $deptFcQty;  $grandFcRev   += $deptFcRev;
              @endphp

              {{-- Department header --}}
              <tr style="background:var(--sidebar-bg)">
                <td class="px-4 py-2.5">
                  <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-green-400"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-white">{{ $deptName }}</span>
                  </div>
                </td>
                <td colspan="6"></td>
              </tr>

              @foreach($categories as $catName => $items)
                @php
                  $catItems   = collect($items);
                  $catHistQty = $catItems->sum('hist_qty');
                  $catHistRev = $catItems->sum('hist_rev');
                  $catFcQty   = $catItems->sum('forecast_qty');
                  $catFcRev   = $catItems->sum('forecast_rev');
                @endphp

                {{-- Category sub-header --}}
                <tr style="background:var(--content-bg); border-left:3px solid #22c55e; border-bottom:1px solid var(--card-border)">
                  <td class="py-2 text-xs font-semibold" style="padding-left:1.5rem; color:var(--text-secondary)">
                    {{ $catName }}
                  </td>
                  <td colspan="6"></td>
                </tr>

                {{-- Item rows --}}
                @foreach($items as $row)
                  <tr class="transition-colors hover:bg-gray-50/60 align-top" style="border-bottom:1px solid var(--card-border)">
                    <td class="px-4 py-2.5 font-medium" style="padding-left:2rem; color:var(--text-primary)">
                      {{ $row->item_name }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">
                      {{ number_format($row->hist_qty, 1, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">
                      {{ number_format($row->hist_rev, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums text-xs" style="color:var(--text-muted)">
                      {{ number_format($row->daily_rev, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right">
                      @if($row->trend_pct !== null)
                        <span class="text-xs font-semibold tabular-nums {{ trendClass($row->trend_pct) }}">
                          {{ $row->trend_pct > 0 ? '+' : '' }}{{ number_format($row->trend_pct, 1) }}%
                        </span>
                        <p class="text-[10px] {{ trendClass($row->trend_pct) }}">{{ trendLabel($row->trend_pct) }}</p>
                      @else
                        <span class="text-[10px]" style="color:var(--text-muted)">New</span>
                      @endif
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums font-semibold" style="color:var(--text-primary)">
                      {{ number_format($row->forecast_qty, 1, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums font-bold text-green-600">
                      {{ number_format($row->forecast_rev, 0, ',', '.') }}
                    </td>
                  </tr>
                @endforeach

                {{-- Category TOTAL --}}
                <tr style="background:rgba(34,197,94,0.05); border-top:1px solid rgba(34,197,94,0.15); border-bottom:1px solid rgba(34,197,94,0.15)">
                  <td class="py-2 text-xs font-semibold" style="padding-left:1.5rem; color:var(--text-secondary)">
                    TOTAL {{ $catName }}
                  </td>
                  <td class="px-4 py-2 text-right text-xs font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($catHistQty, 1, ',', '.') }}</td>
                  <td class="px-4 py-2 text-right text-xs font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($catHistRev, 0, ',', '.') }}</td>
                  <td class="px-4 py-2 text-right text-xs tabular-nums" style="color:var(--text-muted)">
                    {{ number_format($catHistRev / $histDays, 0, ',', '.') }}
                  </td>
                  <td></td>
                  <td class="px-4 py-2 text-right text-xs font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($catFcQty, 1, ',', '.') }}</td>
                  <td class="px-4 py-2 text-right text-xs font-bold tabular-nums text-green-600">{{ number_format($catFcRev, 0, ',', '.') }}</td>
                </tr>
              @endforeach

              {{-- Department TOTAL --}}
              <tr style="background:#1a1f2e; border-top:1px solid rgba(255,255,255,0.08)">
                <td class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider" style="color:rgba(255,255,255,0.75)">TOTAL {{ $deptName }}</td>
                <td class="px-4 py-2.5 text-right text-xs font-bold tabular-nums" style="color:rgba(255,255,255,0.9)">{{ number_format($deptHistQty, 1, ',', '.') }}</td>
                <td class="px-4 py-2.5 text-right text-xs font-bold tabular-nums" style="color:rgba(255,255,255,0.9)">{{ number_format($deptHistRev, 0, ',', '.') }}</td>
                <td class="px-4 py-2.5 text-right text-xs tabular-nums" style="color:rgba(255,255,255,0.45)">
                  {{ number_format($deptHistRev / $histDays, 0, ',', '.') }}
                </td>
                <td></td>
                <td class="px-4 py-2.5 text-right text-xs font-bold tabular-nums" style="color:rgba(255,255,255,0.9)">{{ number_format($deptFcQty, 1, ',', '.') }}</td>
                <td class="px-4 py-2.5 text-right text-sm font-bold tabular-nums text-green-400">{{ number_format($deptFcRev, 0, ',', '.') }}</td>
              </tr>
            @endforeach

            {{-- Grand Total --}}
            <tr style="background:var(--sidebar-bg); border-top:3px solid rgba(34,197,94,0.4)">
              <td class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-green-400">Grand Total</td>
              <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-white">{{ number_format($grandHistQty, 1, ',', '.') }}</td>
              <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-white">{{ number_format($grandHistRev, 0, ',', '.') }}</td>
              <td class="px-4 py-3 text-right text-xs tabular-nums" style="color:rgba(255,255,255,0.45)">
                {{ number_format($grandHistRev / $histDays, 0, ',', '.') }}
              </td>
              <td></td>
              <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-white">{{ number_format($grandFcQty, 1, ',', '.') }}</td>
              <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-green-400">{{ number_format($grandFcRev, 0, ',', '.') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    @endif
  </div>

  {{-- Legend --}}
  <div class="gfs-card px-5 py-4" style="background:var(--content-bg)">
    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Trend legend — vs previous {{ $histDays }}-day period</p>
    <div class="flex flex-wrap gap-4 text-xs">
      @foreach([
        ['>+15%',  'text-green-600',  'Strong growth'],
        ['+5–15%', 'text-emerald-500','Growing'],
        ['±5%',    'text-gray-400',   'Stable'],
        ['-5–15%', 'text-amber-500',  'Slowing'],
        ['<-15%',  'text-red-500',    'Declining'],
        ['New',    'text-gray-400',   'No data in comparison period'],
      ] as [$badge, $cls, $desc])
        <div class="flex items-center gap-1.5">
          <span class="font-semibold {{ $cls }}">{{ $badge }}</span>
          <span style="color:var(--text-muted)">{{ $desc }}</span>
        </div>
      @endforeach
    </div>
    <p class="mt-3 text-[10px] leading-relaxed" style="color:var(--text-muted)">
      <strong>Method:</strong> Simple Moving Average + Day-of-Week Seasonal Index.
      Step 1 — daily avg = total sold in last {{ $histDays }} days ÷ {{ $histDays }}.
      Step 2 — for each of the {{ $forecastDays }} projected days, multiply the daily avg by that day's seasonal index (Mon–Sun) instead of assuming all days are equal.
      Seasonal factor for this window = <strong>{{ number_format($seasonalFactor, 2) }}</strong> (vs flat {{ $forecastDays }}.00).
      Does not account for public holidays or one-off events.
    </p>
  </div>

</div>
@endsection
