@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">{{ $title }}</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Active items with purchase price, average cost, and UOM conversions</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.marketList', array_merge(request()->query(), ['export' => 'csv'])) }}"
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
    <form method="GET" action="{{ route('reports.marketList') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <label for="f-start" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Saved / Updated From</label>
          <input id="f-start" type="date" name="start" value="{{ $start }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-end" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To</label>
          <input id="f-end" type="date" name="end" value="{{ $end }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-category" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Category</label>
          <input id="f-category" type="text" name="category" value="{{ $category }}" placeholder="Category name…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-q" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Search</label>
          <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Item / code / barcode / UOM…"
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
            <a href="{{ route('reports.marketList', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.marketList') }}"
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
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Items</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($summary->total_items ?? 0) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-100">
        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Avg Cost</p>
        <p class="mt-0.5 text-sm font-bold leading-none" style="color:var(--text-primary)">
          {{ number_format((float)($summary->average_cost_mean ?? 0), 0, ',', '.') }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Date Filter</p>
        <p class="mt-0.5 text-xs font-bold leading-snug" style="color:var(--text-primary)">
          @if($start && $end)
            {{ \Carbon\Carbon::parse($start)->format('d M') }} – {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
          @else
            All dates
          @endif
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100">
        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">This Page</p>
        <p class="mt-0.5 text-sm font-bold leading-none" style="color:var(--text-primary)">
          {{ number_format($rows->count()) }}
          <span class="text-xs font-normal" style="color:var(--text-muted)">/ {{ number_format($rows->perPage()) }}</span>
        </p>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="gfs-card overflow-hidden">
    @if($rows->isEmpty())
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--text-muted)">No market list data found for the selected filters.</p>
        <a href="{{ route('reports.marketList') }}"
          class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
          Clear filters
        </a>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
              <th class="px-4 py-3 w-40">Category</th>
              <th class="px-4 py-3 w-24">Code</th>
              <th class="px-4 py-3">Item</th>
              <th class="px-4 py-3 w-44">Units of Measure</th>
              <th class="px-4 py-3 w-32 text-right">Conversions</th>
              <th class="px-4 py-3 w-32 text-right">Purchase Price</th>
              <th class="px-4 py-3 w-32 text-right">Avg Cost</th>
              <th class="px-4 py-3 w-40">Last Modified</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color:var(--card-border)">
            @foreach($rows as $row)
              <tr class="transition-colors hover:bg-gray-50/60 align-top">

                {{-- Category --}}
                <td class="px-4 py-3">
                  <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $row->category ?: '—' }}</p>
                  @if($row->category_code)
                    <span class="mt-0.5 inline-block rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px]" style="color:var(--text-muted)">
                      {{ $row->category_code }}
                    </span>
                  @endif
                </td>

                {{-- Item code --}}
                <td class="px-4 py-3">
                  <span class="font-mono text-xs" style="color:var(--text-secondary)">{{ $row->item_code ?: '—' }}</span>
                </td>

                {{-- Item name + identifiers --}}
                <td class="px-4 py-3">
                  <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $row->item_name ?: '—' }}</p>
                  <div class="mt-1 flex flex-wrap gap-1">
                    @if($row->barcode)
                      <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-mono" style="color:var(--text-muted)">
                        BC: {{ $row->barcode }}
                      </span>
                    @endif
                    @if($row->plu)
                      <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-mono" style="color:var(--text-muted)">
                        PLU: {{ $row->plu }}
                      </span>
                    @endif
                  </div>
                </td>

                {{-- UOMs --}}
                <td class="px-4 py-3">
                  <div class="space-y-1">
                    <div class="flex items-center gap-1.5">
                      <span class="w-10 text-[10px] font-semibold uppercase" style="color:var(--text-muted)">Pur</span>
                      <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ $row->purchase_uom ?: '—' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <span class="w-10 text-[10px] font-semibold uppercase" style="color:var(--text-muted)">Inv</span>
                      <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">{{ $row->inventory_uom ?: '—' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <span class="w-10 text-[10px] font-semibold uppercase" style="color:var(--text-muted)">Rec</span>
                      <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">{{ $row->recipe_uom ?: '—' }}</span>
                    </div>
                  </div>
                </td>

                {{-- Conversions --}}
                <td class="px-4 py-3 text-right">
                  <div class="space-y-1">
                    <div>
                      <span class="text-[10px]" style="color:var(--text-muted)">P→I</span>
                      <span class="ml-1 text-xs tabular-nums font-semibold" style="color:var(--text-primary)">
                        {{ number_format((float)$row->purchase_to_inventory_conversion, 2, ',', '.') }}
                      </span>
                    </div>
                    <div>
                      <span class="text-[10px]" style="color:var(--text-muted)">I→R</span>
                      <span class="ml-1 text-xs tabular-nums font-semibold" style="color:var(--text-primary)">
                        {{ number_format((float)$row->inventory_to_recipe_conversion, 2, ',', '.') }}
                      </span>
                    </div>
                  </div>
                </td>

                {{-- Purchase price --}}
                <td class="px-4 py-3 text-right">
                  <p class="text-sm tabular-nums font-semibold" style="color:var(--text-primary)">
                    {{ number_format((float)$row->purchase_price, 0, ',', '.') }}
                  </p>
                  <p class="text-[10px]" style="color:var(--text-muted)">purchase</p>
                </td>

                {{-- Average cost --}}
                <td class="px-4 py-3 text-right">
                  @php
                    $avgCost  = (float)$row->average_cost;
                    $purPrice = (float)$row->purchase_price;
                    $diff     = $purPrice > 0 ? (($avgCost - $purPrice) / $purPrice * 100) : 0;
                  @endphp
                  <p class="text-sm tabular-nums font-semibold {{ $avgCost > $purPrice ? 'text-red-500' : ($avgCost < $purPrice ? 'text-green-600' : '') }}"
                    @if($avgCost == $purPrice) style="color:var(--text-primary)" @endif>
                    {{ number_format($avgCost, 0, ',', '.') }}
                  </p>
                  @if($purPrice > 0 && abs($diff) > 0.01)
                    <p class="text-[10px] tabular-nums {{ $diff > 0 ? 'text-red-400' : 'text-green-500' }}">
                      {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 1) }}%
                    </p>
                  @else
                    <p class="text-[10px]" style="color:var(--text-muted)">avg cost</p>
                  @endif
                </td>

                {{-- Last modified --}}
                <td class="px-4 py-3">
                  @if($row->updated)
                    <div>
                      <p class="text-[10px] font-semibold uppercase" style="color:var(--text-muted)">Updated</p>
                      <p class="text-xs tabular-nums font-medium" style="color:var(--text-primary)">
                        {{ \Carbon\Carbon::parse($row->updated)->format('d/m/Y') }}
                      </p>
                      <p class="text-[10px] tabular-nums" style="color:var(--text-muted)">
                        {{ \Carbon\Carbon::parse($row->updated)->format('H:i') }}
                      </p>
                    </div>
                  @endif
                  @if($row->saved)
                    <div class="mt-1">
                      <p class="text-[10px] font-semibold uppercase" style="color:var(--text-muted)">Saved</p>
                      <p class="text-xs tabular-nums" style="color:var(--text-secondary)">
                        {{ \Carbon\Carbon::parse($row->saved)->format('d/m/Y H:i') }}
                      </p>
                    </div>
                  @endif
                  @if(!$row->updated && !$row->saved)
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($rows->hasPages())
        <div class="border-t px-5 py-4" style="border-color:var(--card-border); background:var(--content-bg)">
          {{ $rows->links() }}
        </div>
      @endif
    @endif
  </div>

</div>
@endsection
