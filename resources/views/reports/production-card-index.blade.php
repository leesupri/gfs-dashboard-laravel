@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Production Card List</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Browse and open individual production cards</p>
    </div>
    <button type="button" @click="filtersOpen = !filtersOpen"
      class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-white transition active:scale-95"
      :class="filtersOpen ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
      </svg>
      <span x-text="filtersOpen ? 'Hide Filters' : 'Filters'"></span>
    </button>
  </div>

  {{-- Filter panel --}}
  <div x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2">
    <form method="GET" action="{{ route('reports.productionCard.index') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
          <label for="f-q" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Search</label>
          <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Production ID / item / notes…"
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
            <a href="{{ route('reports.productionCard.index', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.productionCard.index') }}"
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
  <div class="grid grid-cols-3 gap-4">
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Period</p>
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
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Cards</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($summary->total_cards ?? 0) }}</p>
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
          {{ number_format($rows->count()) }}<span class="text-xs font-normal" style="color:var(--text-muted)"> / {{ $rows->perPage() }}</span>
        </p>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="gfs-card overflow-hidden">
    @if($rows->isEmpty())
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--text-muted)">No production cards found for the selected filters.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
              <th class="px-4 py-3 w-20">ID</th>
              <th class="px-4 py-3 w-36">Date</th>
              <th class="px-4 py-3 w-36">Warehouse</th>
              <th class="px-4 py-3 w-36">Saved By</th>
              <th class="px-4 py-3 w-24 text-center">Products</th>
              <th class="px-4 py-3 w-20 text-center">Lines</th>
              <th class="px-4 py-3">Notes</th>
              <th class="px-4 py-3 w-28 text-center">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color:var(--card-border)">
            @foreach($rows as $row)
              <tr class="transition-colors hover:bg-gray-50/60">

                {{-- ID --}}
                <td class="px-4 py-3">
                  <span class="font-mono text-xs font-semibold" style="color:var(--text-primary)">#{{ $row->id }}</span>
                </td>

                {{-- Date --}}
                <td class="px-4 py-3 whitespace-nowrap">
                  @if($row->date)
                    <p class="text-sm tabular-nums font-medium" style="color:var(--text-primary)">
                      {{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}
                    </p>
                    <p class="text-xs tabular-nums" style="color:var(--text-muted)">
                      {{ \Carbon\Carbon::parse($row->date)->format('H:i') }}
                    </p>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- Warehouse --}}
                <td class="px-4 py-3">
                  @if($row->warehouse)
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                      <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                      </svg>
                      {{ $row->warehouse }}
                    </span>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- Saved By --}}
                <td class="px-4 py-3">
                  @if($row->savedBy)
                    <div class="flex items-center gap-2">
                      <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-bold text-white"
                        style="background:var(--sidebar-bg)">
                        {{ strtoupper(substr($row->savedBy, 0, 1)) }}
                      </div>
                      <span class="text-sm" style="color:var(--text-primary)">{{ $row->savedBy }}</span>
                    </div>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- Products --}}
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center justify-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                    {{ number_format($row->total_products ?? 0) }}
                  </span>
                </td>

                {{-- Lines --}}
                <td class="px-4 py-3 text-center">
                  <span class="text-sm tabular-nums font-medium" style="color:var(--text-secondary)">
                    {{ number_format($row->total_lines ?? 0) }}
                  </span>
                </td>

                {{-- Notes --}}
                <td class="px-4 py-3 max-w-xs">
                  @if($row->notes)
                    <p class="truncate text-xs" style="color:var(--text-secondary)" title="{{ $row->notes }}">
                      {{ $row->notes }}
                    </p>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- Action --}}
                <td class="px-4 py-3 text-center">
                  <a href="{{ route('reports.productionCard.show', $row->id) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-green-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-green-700 active:scale-95">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Open
                  </a>
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
