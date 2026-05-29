@php
  function varianceColor(float $v): string {
    $abs = abs($v);
    if ($abs > 0.15) return 'text-red-500';
    if ($abs > 0.05) return 'text-amber-500';
    if ($abs == 0)   return '';
    return 'text-green-600';
  }
  function diffColor(float $d): string {
    if ($d > 0) return 'text-green-600';
    if ($d < 0) return 'text-red-500';
    return '';
  }
@endphp

@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Physical Stock Count</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Variance analysis grouped by warehouse and category</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.physicalStockCountSummary', array_merge(request()->query(), ['export' => 'csv'])) }}"
         class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
         style="color:var(--text-primary)">
        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export CSV
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
    <form method="GET" action="{{ route('reports.physicalStockCountSummary') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
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
        <div>
          <label for="f-warehouse" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Warehouse</label>
          <input id="f-warehouse" type="text" name="warehouse" value="{{ $warehouse }}" placeholder="Warehouse name…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-category" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Category</label>
          <input id="f-category" type="text" name="category" value="{{ $category }}" placeholder="Category name…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-q" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Search</label>
          <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Item / code…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
      </div>
      <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
        <div class="flex flex-wrap gap-2">
          <span class="text-xs font-medium" style="color:var(--text-muted)">Quick:</span>
          @foreach([
            ['Today',      ['start' => now()->toDateString(),                             'end' => now()->toDateString()]],
            ['Yesterday',  ['start' => now()->subDay()->toDateString(),                    'end' => now()->subDay()->toDateString()]],
            ['This Month', ['start' => now()->startOfMonth()->toDateString(),              'end' => now()->endOfMonth()->toDateString()]],
            ['Last Month', ['start' => now()->subMonth()->startOfMonth()->toDateString(),  'end' => now()->subMonth()->endOfMonth()->toDateString()]],
          ] as [$ql, $qp])
            <a href="{{ route('reports.physicalStockCountSummary', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.physicalStockCountSummary') }}"
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
  @php
    $grandDiffCost  = (float)($summary->grand_diff_cost ?? 0);
    $totalLines     = (int)($summary->raw_lines ?? 0);
    $warehouseCount = count($groupedRows);
  @endphp
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Period</p>
        <p class="mt-0.5 text-xs font-bold leading-snug" style="color:var(--text-primary)">
          {{ \Carbon\Carbon::parse($start)->format('d M') }} – {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Warehouses</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ $warehouseCount }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100">
        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Lines</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($totalLines) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4 {{ $grandDiffCost < 0 ? 'border-red-200' : ($grandDiffCost > 0 ? 'border-green-200' : '') }}">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $grandDiffCost < 0 ? 'bg-red-100' : ($grandDiffCost > 0 ? 'bg-green-100' : 'bg-gray-100') }}">
        <svg class="h-5 w-5 {{ $grandDiffCost < 0 ? 'text-red-500' : ($grandDiffCost > 0 ? 'text-green-600' : 'text-gray-400') }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Grand Diff Cost</p>
        <p class="mt-0.5 text-sm font-bold leading-none tabular-nums {{ $grandDiffCost < 0 ? 'text-red-500' : ($grandDiffCost > 0 ? 'text-green-600' : '') }}"
          @if($grandDiffCost == 0) style="color:var(--text-primary)" @endif>
          {{ $grandDiffCost > 0 ? '+' : '' }}{{ number_format($grandDiffCost, 0, ',', '.') }}
        </p>
      </div>
    </div>
  </div>

  {{-- Truncation warning --}}
  @if(!empty($truncated))
    <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
      <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
      <div>
        <p class="text-sm font-semibold text-amber-800">Result limit reached (2,000 rows)</p>
        <p class="mt-0.5 text-xs text-amber-700">
          The query returned too many rows. Please narrow the date range, warehouse, or category filter to see complete data.
        </p>
      </div>
    </div>
  @endif

  {{-- Warehouse groups --}}
  @forelse($groupedRows as $warehouseGroup)
    @php
      $wDiffCost  = (float)$warehouseGroup['subtotal_diff_cost'];
      $wCatCount  = count($warehouseGroup['categories']);
      $wItemCount = array_sum(array_map(fn($c) => count($c['items']), $warehouseGroup['categories']));
    @endphp

    <div class="gfs-card overflow-hidden">

      {{-- Warehouse header --}}
      <div class="flex items-center justify-between gap-4 px-5 py-4"
        style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
            style="background:var(--sidebar-bg)">
            <svg class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold" style="color:var(--text-primary)">{{ $warehouseGroup['warehouse'] }}</p>
            <p class="text-xs" style="color:var(--text-muted)">
              {{ $wCatCount }} categor{{ $wCatCount === 1 ? 'y' : 'ies' }} ·
              {{ $wItemCount }} item{{ $wItemCount === 1 ? '' : 's' }}
            </p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Diff Cost</p>
          <p class="text-base font-bold tabular-nums {{ $wDiffCost < 0 ? 'text-red-500' : ($wDiffCost > 0 ? 'text-green-600' : '') }}"
            @if($wDiffCost == 0) style="color:var(--text-primary)" @endif>
            {{ $wDiffCost > 0 ? '+' : '' }}{{ number_format($wDiffCost, 0, ',', '.') }}
          </p>
        </div>
      </div>

      {{-- Category groups --}}
      @foreach($warehouseGroup['categories'] as $categoryGroup)
        @php
          $cDiffCost  = (float)$categoryGroup['subtotal_diff_cost'];
          $cItemCount = count($categoryGroup['items']);
        @endphp

        {{-- Category sub-header --}}
        <div class="flex items-center justify-between px-5 py-2"
          style="background:var(--content-bg); border-left:3px solid #22c55e; border-bottom:1px solid var(--card-border)">
          <span class="text-xs font-semibold" style="color:var(--text-secondary)">{{ $categoryGroup['category'] }}</span>
          <span class="text-xs font-semibold tabular-nums {{ $cDiffCost < 0 ? 'text-red-500' : ($cDiffCost > 0 ? 'text-green-600' : '') }}"
            @if($cDiffCost == 0) style="color:var(--text-muted)" @endif>
            {{ $cDiffCost > 0 ? '+' : '' }}{{ number_format($cDiffCost, 0, ',', '.') }}
          </span>
        </div>

        {{-- Items table --}}
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
                style="background:var(--content-bg); border-bottom:1px solid var(--card-border); color:var(--text-muted)">
                <th class="px-5 py-2 text-left">Item</th>
                <th class="px-4 py-2 w-16 text-left">UOM</th>
                <th class="px-4 py-2 w-28">Calculated</th>
                <th class="px-4 py-2 w-28">Actual</th>
                <th class="px-4 py-2 w-24">Diff</th>
                <th class="px-4 py-2 w-24">Variance</th>
                <th class="px-4 py-2 w-32">Diff Cost</th>
              </tr>
            </thead>
            <tbody class="divide-y" style="border-color:var(--card-border)">
              @foreach($categoryGroup['items'] as $row)
                @php
                  $diff     = (float)$row->diff;
                  $diffCost = (float)$row->diff_cost;
                  $variance = (float)$row->variances;
                @endphp
                <tr class="transition-colors hover:bg-gray-50/60">
                  <td class="px-5 py-2.5">
                    <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $row->item_name }}</p>
                    <p class="font-mono text-[10px]" style="color:var(--text-muted)">{{ $row->item_code }}</p>
                  </td>
                  <td class="px-4 py-2.5">
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium" style="color:var(--text-muted)">
                      {{ $row->uom }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">
                    {{ number_format((float)$row->calculated, 2, ',', '.') }}
                  </td>
                  <td class="px-4 py-2.5 text-right tabular-nums font-medium" style="color:var(--text-primary)">
                    {{ number_format((float)$row->actual, 2, ',', '.') }}
                  </td>
                  <td class="px-4 py-2.5 text-right tabular-nums font-semibold {{ diffColor($diff) }}"
                    @if($diff == 0) style="color:var(--text-muted)" @endif>
                    {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2, ',', '.') }}
                  </td>
                  <td class="px-4 py-2.5 text-right text-xs font-semibold tabular-nums {{ varianceColor($variance) }}"
                    @if($variance == 0) style="color:var(--text-muted)" @endif>
                    {{ $variance > 0 ? '+' : '' }}{{ number_format($variance * 100, 1, ',', '.') }}%
                  </td>
                  <td class="px-4 py-2.5 text-right tabular-nums font-semibold {{ diffColor($diffCost) }}"
                    @if($diffCost == 0) style="color:var(--text-muted)" @endif>
                    {{ $diffCost > 0 ? '+' : '' }}{{ number_format($diffCost, 0, ',', '.') }}
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr style="background:rgba(34,197,94,0.04); border-top:1px solid rgba(34,197,94,0.15)">
                <td colspan="6" class="px-5 py-2 text-xs font-semibold text-right" style="color:var(--text-secondary)">
                  Total {{ $categoryGroup['category'] }}
                </td>
                <td class="px-4 py-2 text-right font-bold tabular-nums {{ $cDiffCost < 0 ? 'text-red-500' : ($cDiffCost > 0 ? 'text-green-600' : '') }}"
                  @if($cDiffCost == 0) style="color:var(--text-primary)" @endif>
                  {{ $cDiffCost > 0 ? '+' : '' }}{{ number_format($cDiffCost, 0, ',', '.') }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      @endforeach

      {{-- Warehouse total footer --}}
      <div class="flex items-center justify-between border-t px-5 py-3"
        style="border-color:var(--card-border); background:var(--content-bg)">
        <span class="text-xs font-semibold uppercase tracking-wide" style="color:var(--text-secondary)">
          Total — {{ $warehouseGroup['warehouse'] }}
        </span>
        <span class="text-sm font-bold tabular-nums {{ $wDiffCost < 0 ? 'text-red-500' : ($wDiffCost > 0 ? 'text-green-600' : '') }}"
          @if($wDiffCost == 0) style="color:var(--text-primary)" @endif>
          {{ $wDiffCost > 0 ? '+' : '' }}{{ number_format($wDiffCost, 0, ',', '.') }}
        </span>
      </div>

    </div>
  @empty
    <div class="gfs-card flex flex-col items-center justify-center gap-3 py-20">
      <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
      </svg>
      <p class="text-sm font-medium" style="color:var(--text-muted)">No physical stock count data found for the selected filters.</p>
      <a href="{{ route('reports.physicalStockCountSummary') }}"
        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
        Clear filters
      </a>
    </div>
  @endforelse

</div>
@endsection
