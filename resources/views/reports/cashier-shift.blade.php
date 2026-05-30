@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ request()->hasAny(['start','end']) ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">{{ $title }}</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        {{ \Carbon\Carbon::parse($start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.cashierShift', array_merge(request()->query(), ['export' => 'csv'])) }}"
         class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
         style="color:var(--text-primary)">
        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
      </a>
      <button type="button" @click="filtersOpen = !filtersOpen"
        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-white transition active:scale-95"
        :class="filtersOpen ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span x-text="filtersOpen ? 'Hide Filters' : 'Filters'"></span>
      </button>
    </div>
  </div>

  {{-- Filter panel --}}
  <div x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2">
    <form method="GET" action="{{ route('reports.cashierShift') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label for="f-start" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From</label>
          <input id="f-start" type="date" name="start" value="{{ $start }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-end" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To</label>
          <input id="f-end" type="date" name="end" value="{{ $end }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
      </div>
      <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
        <div class="flex flex-wrap gap-2">
          <span class="text-xs font-medium" style="color:var(--text-muted)">Quick:</span>
          @foreach([
            ['Today',      ['start' => now()->toDateString(),                             'end' => now()->toDateString()]],
            ['Yesterday',  ['start' => now()->subDay()->toDateString(),                    'end' => now()->subDay()->toDateString()]],
            ['This Week',  ['start' => now()->startOfWeek()->toDateString(),               'end' => now()->endOfWeek()->toDateString()]],
            ['This Month', ['start' => now()->startOfMonth()->toDateString(),              'end' => now()->endOfMonth()->toDateString()]],
            ['Last Month', ['start' => now()->subMonth()->startOfMonth()->toDateString(),  'end' => now()->subMonth()->endOfMonth()->toDateString()]],
          ] as [$ql, $qp])
            <a href="{{ route('reports.cashierShift', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.cashierShift') }}"
            class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
            style="color:var(--text-secondary)">Clear</a>
          <button type="submit"
            class="rounded-xl bg-green-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
            Apply
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- KPI strip --}}
  @php $varColor = $totalVariance < 0 ? 'text-red-400' : ($totalVariance > 0 ? 'text-green-400' : 'text-white'); @endphp
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
    <div class="gfs-card flex items-center gap-3 p-4" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 13h.01M13 13h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Total Shifts</p>
        <p class="mt-0.5 text-lg font-bold leading-none text-white">{{ number_format($totalShifts) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Stations</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($stationCount) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4 {{ $shortages > 0 ? 'border-red-200' : '' }}">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $shortages > 0 ? 'bg-red-100' : 'bg-gray-100' }}">
        <svg class="h-5 w-5 {{ $shortages > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Shortages</p>
        <p class="mt-0.5 text-lg font-bold leading-none {{ $shortages > 0 ? 'text-red-500' : '' }}"
          @if(!$shortages) style="color:var(--text-primary)" @endif>
          {{ number_format($shortages) }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4 {{ $surpluses > 0 ? 'border-green-200' : '' }}">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $surpluses > 0 ? 'bg-green-100' : 'bg-gray-100' }}">
        <svg class="h-5 w-5 {{ $surpluses > 0 ? 'text-green-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Surpluses</p>
        <p class="mt-0.5 text-lg font-bold leading-none {{ $surpluses > 0 ? 'text-green-600' : '' }}"
          @if(!$surpluses) style="color:var(--text-primary)" @endif>
          {{ number_format($surpluses) }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
        style="background:{{ $totalVariance < 0 ? 'rgba(239,68,68,0.2)' : 'rgba(34,197,94,0.2)' }}">
        <svg class="h-5 w-5 {{ $totalVariance < 0 ? 'text-red-400' : 'text-green-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:rgba(255,255,255,0.5)">Net Variance</p>
        <p class="mt-0.5 text-sm font-bold leading-none tabular-nums {{ $varColor }}">
          {{ $totalVariance > 0 ? '+' : '' }}{{ number_format($totalVariance, 0, ',', '.') }}
        </p>
      </div>
    </div>
  </div>

  {{-- Grouped table --}}
  @if(empty($grouped))
    <div class="gfs-card flex flex-col items-center justify-center gap-3 py-20">
      <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
      <p class="text-sm font-medium" style="color:var(--text-muted)">No shift data found for the selected period.</p>
    </div>
  @else
    @foreach($grouped as $stationName => $cashiers)
      @php
        $stationShifts   = collect(array_merge(...array_values($cashiers)));
        $stationVariance = $stationShifts->sum('bedanyaCuy');
      @endphp

      <div class="gfs-card overflow-hidden">

        {{-- Station header --}}
        <div class="flex items-center justify-between gap-4 px-5 py-3"
          style="background:var(--sidebar-bg)">
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-green-400"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-white">{{ $stationName }}</span>
          </div>
          <span class="text-xs font-bold tabular-nums {{ (float)$stationVariance < 0 ? 'text-red-400' : ((float)$stationVariance > 0 ? 'text-green-400' : 'text-white/50') }}">
            {{ (float)$stationVariance > 0 ? '+' : '' }}{{ number_format((float)$stationVariance, 0, ',', '.') }}
          </span>
        </div>

        {{-- Per cashier --}}
        @foreach($cashiers as $cashierName => $shifts)
          @php
            $cashierVariance = collect($shifts)->sum('bedanyaCuy');
          @endphp

          {{-- Cashier sub-header --}}
          <div class="flex items-center justify-between px-5 py-2"
            style="background:var(--content-bg); border-left:3px solid #22c55e; border-bottom:1px solid var(--card-border)">
            <div class="flex items-center gap-2">
              <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[9px] font-bold text-white"
                style="background:var(--sidebar-bg)">
                {{ strtoupper(substr($cashierName, 0, 1)) }}
              </div>
              <span class="text-xs font-semibold" style="color:var(--text-primary)">{{ $cashierName }}</span>
              <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium" style="color:var(--text-muted)">
                {{ count($shifts) }} shift(s)
              </span>
            </div>
            <span class="text-xs font-semibold tabular-nums {{ (float)$cashierVariance < 0 ? 'text-red-500' : ((float)$cashierVariance > 0 ? 'text-green-600' : '') }}"
              @if((float)$cashierVariance == 0) style="color:var(--text-muted)" @endif>
              {{ (float)$cashierVariance > 0 ? '+' : '' }}{{ number_format((float)$cashierVariance, 0, ',', '.') }}
            </span>
          </div>

          {{-- Shift rows --}}
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
                  style="background:var(--content-bg); border-bottom:1px solid var(--card-border); color:var(--text-muted)">
                  <th class="px-4 py-2 text-left w-36">Opening Time</th>
                  <th class="px-4 py-2 text-left w-36">Closing Time</th>
                  <th class="px-4 py-2 w-32">Opening Bal.</th>
                  <th class="px-4 py-2 w-32">Cash Sales</th>
                  <th class="px-4 py-2 w-24">Pay In</th>
                  <th class="px-4 py-2 w-24">Pay Out</th>
                  <th class="px-4 py-2 w-32">Closing Bal.</th>
                  <th class="px-4 py-2 w-32">Difference</th>
                </tr>
              </thead>
              <tbody class="divide-y" style="border-color:var(--card-border)">
                @foreach($shifts as $shift)
                  @php
                    $diff = (float)$shift->bedanyaCuy;
                    $diffColor = $diff < 0 ? 'text-red-500' : ($diff > 0 ? 'text-green-600' : '');
                  @endphp
                  <tr class="transition-colors hover:bg-gray-50/60 {{ $diff < 0 ? 'bg-red-50/40' : '' }}">
                    <td class="px-4 py-2.5">
                      @if($shift->openingTime)
                        <p class="text-xs tabular-nums font-medium" style="color:var(--text-primary)">
                          {{ \Carbon\Carbon::parse($shift->openingTime)->format('d M Y') }}
                        </p>
                        <p class="text-[10px] tabular-nums" style="color:var(--text-muted)">
                          {{ \Carbon\Carbon::parse($shift->openingTime)->format('H:i') }}
                        </p>
                      @else
                        <span style="color:var(--text-muted)">—</span>
                      @endif
                    </td>
                    <td class="px-4 py-2.5">
                      @if($shift->closingTime)
                        <p class="text-xs tabular-nums font-medium" style="color:var(--text-primary)">
                          {{ \Carbon\Carbon::parse($shift->closingTime)->format('d M Y') }}
                        </p>
                        <p class="text-[10px] tabular-nums" style="color:var(--text-muted)">
                          {{ \Carbon\Carbon::parse($shift->closingTime)->format('H:i') }}
                        </p>
                      @else
                        <span style="color:var(--text-muted)">—</span>
                      @endif
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">
                      {{ number_format((float)$shift->openingBalance, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums font-medium" style="color:var(--text-primary)">
                      {{ number_format((float)$shift->cashSales, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums text-green-600">
                      {{ number_format((float)$shift->payIn, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums text-red-500">
                      {{ number_format((float)$shift->payOut, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums font-semibold" style="color:var(--text-primary)">
                      {{ number_format((float)$shift->closingBalance, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right tabular-nums font-bold {{ $diffColor }}"
                      @if($diff == 0) style="color:var(--text-muted)" @endif>
                      @if($diff != 0) {{ $diff > 0 ? '+' : '' }} @endif
                      {{ number_format($diff, 0, ',', '.') }}
                      @if($diff < 0)
                        <p class="text-[9px] font-semibold text-red-400">SHORT</p>
                      @elseif($diff > 0)
                        <p class="text-[9px] font-semibold text-green-500">OVER</p>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endforeach

      </div>
    @endforeach
  @endif

</div>
@endsection
