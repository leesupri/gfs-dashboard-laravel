@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ request()->hasAny(['start','end']) ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Daily Hour Sales</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        Quantity sold per hour, grouped by date and sales type
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.dailyHour', array_merge(request()->query(), ['export' => 'csv'])) }}"
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

  {{-- Filters --}}
  <div x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2">
    <form method="GET" action="{{ route('reports.dailyHour') }}" class="gfs-card p-5">
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
          ] as [$ql, $qp])
            <a href="{{ route('reports.dailyHour', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex gap-2">
          <a href="{{ route('reports.dailyHour') }}"
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

  {{-- Pivot table --}}
  <div class="gfs-card overflow-hidden">
    @if($rows->isEmpty())
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <p class="text-sm font-medium" style="color:var(--text-muted)">No data for the selected period.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            {{-- Column header row --}}
            <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
              <th class="px-4 py-2.5 text-left w-32" style="min-width:9rem">Date</th>
              <th class="px-3 py-2.5 text-left w-28" style="min-width:7rem">Type</th>
              @foreach($hours as $h)
                <th class="px-2 py-2.5 w-10" style="min-width:2.5rem">
                  {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                </th>
              @endforeach
              <th class="px-3 py-2.5 w-20">Total Qty</th>
              <th class="px-3 py-2.5 w-32">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color:var(--card-border)">
            @foreach($rows as $row)
              @php
                $isDelivery = $row->salesType === 'Delivery';
              @endphp
              <tr class="transition-colors hover:bg-gray-50/60 align-top">
                <td class="px-4 py-2.5">
                  @if($row->salesDate)
                    <p class="text-xs font-semibold" style="color:var(--text-primary)">
                      {{ \Carbon\Carbon::parse($row->salesDate)->format('d M Y') }}
                    </p>
                    <p class="text-[10px]" style="color:var(--text-muted)">
                      {{ \Carbon\Carbon::parse($row->salesDate)->format('l') }}
                    </p>
                  @endif
                </td>
                <td class="px-3 py-2.5">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold
                    {{ $isDelivery ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                    {{ $row->salesType }}
                  </span>
                </td>
                @foreach($hours as $h)
                  @php $val = (float)$row->{"h{$h}"}; @endphp
                  <td class="px-2 py-2.5 text-right tabular-nums text-xs
                    {{ $val > 0 ? 'font-semibold' : '' }}"
                    style="{{ $val > 0 ? 'color:var(--text-primary)' : 'color:var(--text-muted)' }}">
                    {{ $val > 0 ? number_format($val, 0) : '—' }}
                  </td>
                @endforeach
                <td class="px-3 py-2.5 text-right tabular-nums font-bold" style="color:var(--text-primary)">
                  {{ number_format($row->qty, 0) }}
                </td>
                <td class="px-3 py-2.5 text-right tabular-nums font-semibold text-green-600">
                  {{ number_format($row->ttlPrice, 0, ',', '.') }}
                </td>
              </tr>
            @endforeach
          </tbody>
          {{-- Grand total row --}}
          <tfoot>
            <tr style="background:var(--sidebar-bg); border-top:2px solid rgba(34,197,94,0.3)">
              <td class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-green-400" colspan="2">Grand Total</td>
              @foreach($hours as $h)
                <td class="px-2 py-2.5 text-right tabular-nums text-xs font-bold"
                  style="{{ $totals['h'.$h] > 0 ? 'color:rgba(255,255,255,0.9)' : 'color:rgba(255,255,255,0.25)' }}">
                  {{ $totals["h{$h}"] > 0 ? number_format($totals["h{$h}"], 0) : '—' }}
                </td>
              @endforeach
              <td class="px-3 py-2.5 text-right tabular-nums text-sm font-bold text-white">
                {{ number_format($totals['qty'], 0) }}
              </td>
              <td class="px-3 py-2.5 text-right tabular-nums text-sm font-bold text-green-400">
                {{ number_format($totals['ttlPrice'], 0, ',', '.') }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- Legend --}}
      <div class="flex items-center gap-4 border-t px-5 py-3 text-[10px]"
        style="border-color:var(--card-border); background:var(--content-bg); color:var(--text-muted)">
        <span><strong>Columns</strong> = operating hours (09:00 – 00:00 midnight)</span>
        <span>·</span>
        <span><strong>Values</strong> = quantity sold</span>
        <span>·</span>
        <span><strong>—</strong> = no sales that hour</span>
      </div>
    @endif
  </div>

</div>
@endsection
