@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ (request('start') || request('end')) ? 'true' : 'false' }} }"
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
      <a href="{{ route('reports.wasteSummary', array_merge(request()->query(), ['export' => 'csv'])) }}"
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
    <form method="GET" action="{{ route('reports.wasteSummary') }}" class="gfs-card p-5">
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
            ['This Month', ['start' => now()->startOfMonth()->toDateString(),              'end' => now()->endOfMonth()->toDateString()]],
            ['Last Month', ['start' => now()->subMonth()->startOfMonth()->toDateString(),  'end' => now()->subMonth()->endOfMonth()->toDateString()]],
          ] as [$ql, $qp])
            <a href="{{ route('reports.wasteSummary', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.wasteSummary') }}"
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
    $totalCategories = $grouped->count();
    $totalLines      = $grouped->sum(fn($g) => count($g['items']));
  @endphp
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="gfs-card flex items-center gap-3 p-4" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(239,68,68,0.2)">
        <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider text-red-400/70">Total Waste Cost</p>
        <p class="mt-0.5 text-base font-bold leading-none text-red-400 tabular-nums">
          {{ number_format($grandTotal, 0, ',', '.') }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 014-4z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Categories</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ $totalCategories }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Item Lines</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($totalLines) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100">
        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
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
  </div>

  {{-- Category cards --}}
  @forelse($grouped as $group)
    @php
      $catTotal = (float)$group['total'];
      $sharePct = $grandTotal > 0 ? ($catTotal / $grandTotal * 100) : 0;
    @endphp

    <div class="gfs-card overflow-hidden">

      {{-- Category header --}}
      <div class="flex items-center justify-between gap-4 px-5 py-4"
        style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
            style="background:var(--sidebar-bg)">
            <svg class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold" style="color:var(--text-primary)">{{ $group['category'] }}</p>
            <p class="text-xs" style="color:var(--text-muted)">
              {{ count($group['items']) }} line(s)
              <span class="ml-1.5 inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 font-medium text-red-600">
                {{ number_format($sharePct, 1) }}% of total
              </span>
            </p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Waste Cost</p>
          <p class="text-base font-bold tabular-nums text-red-500">{{ number_format($catTotal, 0, ',', '.') }}</p>
        </div>
      </div>

      {{-- Share bar --}}
      <div class="h-1 w-full bg-gray-100">
        <div class="h-full bg-red-400 transition-all" style="width:{{ number_format($sharePct, 1) }}%"></div>
      </div>

      {{-- Items table --}}
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:1px solid var(--card-border); color:var(--text-muted)">
              <th class="px-5 py-2.5 text-left">Item</th>
              <th class="px-4 py-2.5 w-28 text-left">Code</th>
              <th class="px-4 py-2.5 w-24">Qty</th>
              <th class="px-4 py-2.5 w-16 text-left">UOM</th>
              <th class="px-4 py-2.5 w-28">Unit Cost</th>
              <th class="px-4 py-2.5 w-32">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color:var(--card-border)">
            @foreach($group['items'] as $row)
              <tr class="transition-colors hover:bg-gray-50/60">
                <td class="px-5 py-2.5 font-medium" style="color:var(--text-primary)">{{ $row->name }}</td>
                <td class="px-4 py-2.5 font-mono text-xs" style="color:var(--text-muted)">{{ $row->code ?: '—' }}</td>
                <td class="px-4 py-2.5 text-right tabular-nums font-semibold" style="color:var(--text-primary)">
                  {{ number_format($row->quantity, 2, ',', '.') }}
                </td>
                <td class="px-4 py-2.5">
                  <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium" style="color:var(--text-muted)">
                    {{ $row->uom ?: '—' }}
                  </span>
                </td>
                <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">
                  {{ number_format($row->unitCost, 0, ',', '.') }}
                </td>
                <td class="px-4 py-2.5 text-right tabular-nums font-semibold text-red-500">
                  {{ number_format($row->total, 0, ',', '.') }}
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:rgba(239,68,68,0.04); border-top:1px solid rgba(239,68,68,0.15)">
              <td colspan="5" class="px-5 py-2.5 text-xs font-semibold text-right" style="color:var(--text-secondary)">
                Total — {{ $group['category'] }}
              </td>
              <td class="px-4 py-2.5 text-right font-bold tabular-nums text-red-500">
                {{ number_format($catTotal, 0, ',', '.') }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

    </div>
  @empty
    <div class="gfs-card flex flex-col items-center justify-center gap-3 py-20">
      <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
      </svg>
      <p class="text-sm font-medium" style="color:var(--text-muted)">No waste records found for the selected period.</p>
      <a href="{{ route('reports.wasteSummary') }}"
        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
        Clear filters
      </a>
    </div>
  @endforelse

  {{-- Grand total --}}
  @if($grouped->count())
    <div class="flex justify-end">
      <div class="gfs-card flex items-center gap-6 px-6 py-4" style="background:var(--sidebar-bg)">
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-red-400/70">Grand Total Waste</p>
          <p class="mt-0.5 text-xl font-bold tabular-nums text-red-400">{{ number_format($grandTotal, 0, ',', '.') }}</p>
        </div>
        <div class="h-10 w-px" style="background:rgba(255,255,255,0.1)"></div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-red-400/70">Categories</p>
          <p class="mt-0.5 text-xl font-bold text-white">{{ $totalCategories }}</p>
        </div>
        <div class="h-10 w-px" style="background:rgba(255,255,255,0.1)"></div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-red-400/70">Lines</p>
          <p class="mt-0.5 text-xl font-bold text-white">{{ number_format($totalLines) }}</p>
        </div>
      </div>
    </div>
  @endif

</div>
@endsection

