@php
  function activityType(string $text): array {
    $t = strtoupper($text);
    if (str_contains($t, 'LOGIN'))   return ['label' => 'Login',   'css' => 'bg-green-100 text-green-700',  'dot' => '#22c55e'];
    if (str_contains($t, 'LOGOUT'))  return ['label' => 'Logout',  'css' => 'bg-gray-100 text-gray-600',   'dot' => '#9ca3af'];
    if (str_contains($t, 'VOID'))    return ['label' => 'Void',    'css' => 'bg-red-100 text-red-600',     'dot' => '#ef4444'];
    if (str_contains($t, 'PAYMENT')) return ['label' => 'Payment', 'css' => 'bg-blue-100 text-blue-700',   'dot' => '#3b82f6'];
    if (str_contains($t, 'CLOSE'))   return ['label' => 'Close',   'css' => 'bg-purple-100 text-purple-700','dot' => '#a855f7'];
    if (str_contains($t, 'OPEN'))    return ['label' => 'Open',    'css' => 'bg-sky-100 text-sky-700',     'dot' => '#0ea5e9'];
    if (str_contains($t, 'DISCOUNT') || str_contains($t, 'DISC')) return ['label' => 'Discount', 'css' => 'bg-orange-100 text-orange-700', 'dot' => '#f97316'];
    return ['label' => 'Activity', 'css' => 'bg-amber-100 text-amber-700', 'dot' => '#f59e0b'];
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
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Activity Log</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Employee actions tracked by date, invoice, and event type</p>
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
    <form method="GET" action="{{ route('reports.activityLog') }}" class="gfs-card p-5">
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
          <label for="f-employee" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Employee</label>
          <input id="f-employee" type="text" name="employee" value="{{ $employee }}" placeholder="Name…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-invoice" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Invoice</label>
          <input id="f-invoice" type="text" name="invoice" value="{{ $invoice }}" placeholder="Invoice ID…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-q" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Search</label>
          <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Description or Sales ID…"
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
            <a href="{{ route('reports.activityLog', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.activityLog') }}"
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
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From</p>
        <p class="mt-0.5 text-sm font-bold leading-none" style="color:var(--text-primary)">
          {{ \Carbon\Carbon::parse($start)->format('d M Y') }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To</p>
        <p class="mt-0.5 text-sm font-bold leading-none" style="color:var(--text-primary)">
          {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Logs</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">
          {{ number_format($summary->total_logs ?? 0) }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-100">
        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Showing</p>
        <p class="mt-0.5 text-sm font-bold leading-none" style="color:var(--text-primary)">
          {{ number_format($rows->count()) }} / {{ number_format($rows->perPage()) }}
          <span class="text-xs font-normal" style="color:var(--text-muted)">per page</span>
        </p>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="gfs-card overflow-hidden">
    @if($rows->isEmpty())
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--text-muted)">No activity log records found for the selected filters.</p>
        <a href="{{ route('reports.activityLog') }}"
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
              <th class="px-4 py-3 w-36">Date & Time</th>
              <th class="px-4 py-3 w-28">Type</th>
              <th class="px-4 py-3">Description</th>
              <th class="px-4 py-3 w-24">Sales ID</th>
              <th class="px-4 py-3 w-32">Invoice</th>
              <th class="px-4 py-3 w-36">Employee</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color:var(--card-border)">
            @foreach($rows as $row)
              @php $type = activityType($row->description ?? ''); @endphp
              <tr class="transition-colors hover:bg-gray-50/60">

                {{-- Date & Time --}}
                <td class="px-4 py-3 whitespace-nowrap">
                  @if($row->date)
                    <p class="text-sm tabular-nums font-medium" style="color:var(--text-primary)">
                      {{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}
                    </p>
                    <p class="text-xs tabular-nums" style="color:var(--text-muted)">
                      {{ \Carbon\Carbon::parse($row->date)->format('H:i:s') }}
                    </p>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- Activity type badge --}}
                <td class="px-4 py-3">
                  <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $type['css'] }}">
                    <span class="h-1.5 w-1.5 rounded-full" style="background:{{ $type['dot'] }}"></span>
                    {{ $type['label'] }}
                  </span>
                </td>

                {{-- Description --}}
                <td class="px-4 py-3 max-w-xs">
                  <p class="text-sm leading-snug" style="color:var(--text-primary)">
                    {{ $row->description ?: '—' }}
                  </p>
                </td>

                {{-- Sales ID --}}
                <td class="px-4 py-3">
                  @if($row->salesId)
                    <span class="rounded-lg bg-gray-100 px-2 py-0.5 font-mono text-xs" style="color:var(--text-secondary)">
                      #{{ $row->salesId }}
                    </span>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- Invoice ID --}}
                <td class="px-4 py-3">
                  @if($row->invoice_id)
                    <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                      {{ $row->invoice_id }}
                    </span>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- Employee --}}
                <td class="px-4 py-3">
                  @if($row->employee_name)
                    <div class="flex items-center gap-2">
                      <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-bold text-white"
                        style="background:var(--sidebar-bg)">
                        {{ strtoupper(substr($row->employee_name, 0, 1)) }}
                      </div>
                      <span class="text-sm font-medium" style="color:var(--text-primary)">{{ $row->employee_name }}</span>
                    </div>
                  @else
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
