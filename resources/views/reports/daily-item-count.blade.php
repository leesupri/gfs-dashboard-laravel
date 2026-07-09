@extends('layouts.app')

@push('styles')
<style>
@media print {
  body * { visibility: hidden !important; }
  #thermal-print, #thermal-print * { visibility: visible !important; }
  #thermal-print { position: fixed; inset: 0; padding: 4mm 3mm; }
  @page { size: 80mm auto; margin: 0; }

  #thermal-print {
    width: 72mm;
    font-family: 'Courier New', Courier, monospace;
    font-size: 8pt;
    color: #000;
    background: #fff;
  }
  #thermal-print .t-center  { text-align: center; }
  #thermal-print .t-title   { font-size: 10pt; font-weight: bold; text-align: center; }
  #thermal-print .t-sub     { font-size: 7pt; text-align: center; color: #333; }
  #thermal-print .t-dash    { border-top: 1px dashed #000; margin: 1.5mm 0; }
  #thermal-print .t-solid   { border-top: 1px solid #000; margin: 1.5mm 0; }
  #thermal-print .t-kpi     { display: flex; justify-content: space-between; font-size: 7.5pt; padding: 0.4mm 0; }
  #thermal-print .t-cat     { font-weight: bold; text-transform: uppercase; font-size: 7pt;
                               letter-spacing: 0.03em; padding: 1.5mm 0 0.5mm; border-top: 1px dashed #000; }
  #thermal-print table      { width: 100%; border-collapse: collapse; }
  #thermal-print td         { font-size: 7.5pt; padding: 0.5mm 0; vertical-align: top; }
  #thermal-print .td-name   { width: 78%; word-break: break-word; padding-right: 1mm; }
  #thermal-print .td-qty    { width: 22%; text-align: right; font-weight: bold; white-space: nowrap; }
  #thermal-print .t-footer  { display: flex; justify-content: space-between;
                               font-weight: bold; font-size: 8.5pt; padding-top: 1.5mm; margin-top: 1mm;
                               border-top: 1px solid #000; }
  #thermal-print .t-printed { font-size: 6.5pt; text-align: center; color: #666; margin-top: 3mm; }
}
</style>
@endpush

@section('content')
<div x-data="{ filtersOpen: {{ (request()->hasAny(['start','end','category','q']) ? 'true' : 'false') }} }"
     class="space-y-6">

  {{-- ── Header ───────────────────────────────────────────────────────────── --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Daily Item Count</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        {{ \Carbon\Carbon::parse($start)->format('d M Y') }}
        @if($start !== $end)— {{ \Carbon\Carbon::parse($end)->format('d M Y') }}@endif
        @if($category) &middot; <span class="font-medium" style="color:var(--text-primary)">{{ $category }}</span>@endif
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.dailyItemCount', array_merge(request()->query(), ['export' => 'csv'])) }}"
         class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
         style="color:var(--text-primary)">
        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
      </a>
      <button type="button" onclick="window.print()"
        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
        style="color:var(--text-primary)">
        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print
      </button>
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

  {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
  <div x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2">

    <form method="GET" action="{{ route('reports.dailyItemCount') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Date From --}}
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From</label>
          <input type="date" name="start" value="{{ $start }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>

        {{-- Date To --}}
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To</label>
          <input type="date" name="end" value="{{ $end }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>

        {{-- Category --}}
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Category</label>
          <select name="category"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100"
            style="color:var(--text-primary)">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
              <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
          </select>
        </div>

        {{-- Search --}}
        <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Item Name</label>
          <input type="text" name="q" value="{{ $search }}" placeholder="Search item..."
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>

      </div>

      {{-- Quick picks + buttons --}}
      <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
        <div class="flex flex-wrap gap-2">
          <span class="text-xs font-medium" style="color:var(--text-muted)">Quick:</span>
          @foreach([
            ['Today',      ['start' => now()->toDateString(),                            'end' => now()->toDateString()]],
            ['Yesterday',  ['start' => now()->subDay()->toDateString(),                   'end' => now()->subDay()->toDateString()]],
            ['This Week',  ['start' => now()->startOfWeek()->toDateString(),              'end' => now()->endOfWeek()->toDateString()]],
            ['This Month', ['start' => now()->startOfMonth()->toDateString(),             'end' => now()->endOfMonth()->toDateString()]],
            ['Last Month', ['start' => now()->subMonth()->startOfMonth()->toDateString(), 'end' => now()->subMonth()->endOfMonth()->toDateString()]],
          ] as [$ql, $qp])
            <a href="{{ route('reports.dailyItemCount', $qp) }}"
               class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
               style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex gap-2">
          <a href="{{ route('reports.dailyItemCount') }}"
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

  {{-- ── KPI strip ────────────────────────────────────────────────────────── --}}
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

    <div class="gfs-card flex items-center gap-3 p-4" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Total Items</p>
        <p class="mt-0.5 text-base font-bold text-green-400 tabular-nums">{{ number_format($totalAll, 0) }}</p>
      </div>
    </div>

    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Sales Qty</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($totalSales, 0) }}</p>
      </div>
    </div>

    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">No-Sales Qty</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($totalNoSales, 0) }}</p>
      </div>
    </div>

    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100">
        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Unique Items</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($itemCount, 0) }}</p>
      </div>
    </div>

  </div>

  {{-- ── Table ────────────────────────────────────────────────────────────── --}}
  <div class="gfs-card overflow-hidden">
    @if($rows->isEmpty())
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50">
          <svg class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div class="text-center">
          <p class="text-sm font-medium" style="color:var(--text-primary)">No data for the selected period</p>
          <p class="mt-1 text-xs" style="color:var(--text-muted)">Try adjusting the date range or category filter.</p>
        </div>
      </div>
    @else
      @php
        $grouped   = $rows->groupBy('category');
        $grandTotal = $rows->sum('total_qty');
      @endphp
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr style="background:var(--content-bg); border-bottom:2px solid var(--card-border);">
              <th class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted);">Category / Item</th>
              <th class="px-4 py-3 w-28 text-right text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted);">Sales</th>
              <th class="px-4 py-3 w-28 text-right text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted);">No Sales</th>
              <th class="px-4 py-3 w-28 text-right text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted);">Total</th>
              <th class="px-4 py-3 w-24 text-right text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted);">Share</th>
            </tr>
          </thead>
          <tbody>
            @foreach($grouped as $catName => $catRows)
              @php
                $catTotal      = $catRows->sum('total_qty');
                $catSales      = $catRows->sum('sales_qty');
                $catNoSales    = $catRows->sum('nosales_qty');
                $catPct        = $grandTotal > 0 ? ($catTotal / $grandTotal * 100) : 0;
              @endphp

              {{-- Category header row --}}
              <tr style="background:var(--content-bg); border-top:1px solid var(--card-border); border-bottom:1px solid var(--card-border);">
                <td class="px-5 py-2 text-xs font-bold uppercase tracking-wide" style="color:var(--text-secondary);">
                  {{ $catName ?: 'UNCATEGORIZED' }}
                  <span class="ml-1.5 rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold" style="color:var(--text-muted)">{{ $catRows->count() }} items</span>
                </td>
                <td class="px-4 py-2 text-right text-xs font-semibold tabular-nums" style="color:var(--text-secondary);">{{ number_format($catSales, 0) }}</td>
                <td class="px-4 py-2 text-right text-xs font-semibold tabular-nums" style="color:var(--text-secondary);">{{ number_format($catNoSales, 0) }}</td>
                <td class="px-4 py-2 text-right text-xs font-bold tabular-nums" style="color:var(--text-primary);">{{ number_format($catTotal, 0) }}</td>
                <td class="px-4 py-2 text-right text-[10px] tabular-nums" style="color:var(--text-muted);">{{ number_format($catPct, 1) }}%</td>
              </tr>

              {{-- Item rows --}}
              @foreach($catRows as $row)
                @php $pct = $grandTotal > 0 ? ($row->total_qty / $grandTotal * 100) : 0; @endphp
                <tr class="transition-colors hover:bg-gray-50/60" style="border-bottom:1px solid var(--card-border);">
                  <td class="py-2.5 pl-10 pr-5" style="color:var(--text-primary);">{{ $row->item_name }}</td>
                  <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary);">
                    @if($row->sales_qty > 0)
                      {{ number_format($row->sales_qty, 0) }}
                    @else
                      <span style="color:var(--text-muted)">—</span>
                    @endif
                  </td>
                  <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary);">
                    @if($row->nosales_qty > 0)
                      <span class="text-amber-600 font-medium">{{ number_format($row->nosales_qty, 0) }}</span>
                    @else
                      <span style="color:var(--text-muted)">—</span>
                    @endif
                  </td>
                  <td class="px-4 py-2.5 text-right font-semibold tabular-nums" style="color:var(--text-primary);">{{ number_format($row->total_qty, 0) }}</td>
                  <td class="px-4 py-2.5 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-green-500" style="width:{{ number_format($pct * ($grandTotal / max($catTotal, 1)), 1) }}%"></div>
                      </div>
                      <span class="text-[10px] tabular-nums font-medium" style="color:var(--text-muted)">{{ number_format($pct, 1) }}%</span>
                    </div>
                  </td>
                </tr>
              @endforeach
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:var(--sidebar-bg); border-top:2px solid rgba(34,197,94,0.3)">
              <td class="px-5 py-3 text-xs font-bold uppercase tracking-wider text-green-400">Grand Total</td>
              <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-white">{{ number_format($totalSales, 0) }}</td>
              <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-amber-400">{{ number_format($totalNoSales, 0) }}</td>
              <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-green-400">{{ number_format($totalAll, 0) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    @endif
  </div>

</div>

{{-- ── Thermal Print Block (hidden on screen, visible on print) ────────── --}}
<div id="thermal-print" style="display:none">

  {{-- Header --}}
  <div class="t-title">Daily Item Count</div>
  <div class="t-sub">
    {{ \Carbon\Carbon::parse($start)->format('d M Y') }}
    @if($start !== $end) &ndash; {{ \Carbon\Carbon::parse($end)->format('d M Y') }}@endif
  </div>
  @if($category)
  <div class="t-sub">Category: {{ $category }}</div>
  @else
  <div class="t-sub">All Categories</div>
  @endif
  <div class="t-solid"></div>

  @if(!$rows->isEmpty())
    @php
      $tGrouped    = $rows->groupBy('category');
      $tGrandTotal = $rows->sum('total_qty');
    @endphp

    {{-- Summary --}}
    <div class="t-kpi"><span>Total Qty</span><span>{{ number_format($tGrandTotal, 0) }}</span></div>
    <div class="t-kpi"><span>Unique Items</span><span>{{ $itemCount }}</span></div>
    <div class="t-dash"></div>

    {{-- Items grouped by category --}}
    @foreach($tGrouped as $catName => $catRows)
      <div class="t-cat">{{ strtoupper($catName ?: 'UNCATEGORIZED') }}</div>
      <table>
        <thead style="display:none"><tr><th>Item</th><th>Qty</th></tr></thead>
        <tbody>
        @foreach($catRows as $row)
          <tr>
            <td class="td-name">{{ $row->item_name }}</td>
            <td class="td-qty">{{ number_format($row->total_qty, 0) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endforeach

    {{-- Grand total --}}
    <div class="t-footer">
      <span>GRAND TOTAL</span>
      <span>{{ number_format($tGrandTotal, 0) }}</span>
    </div>
  @else
    <div class="t-center" style="padding:4mm 0">No data for this period.</div>
  @endif

  <div class="t-printed">Printed: {{ now()->format('d M Y H:i') }}</div>
</div>

@endsection
