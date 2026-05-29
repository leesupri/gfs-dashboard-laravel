@extends('layouts.app')

@section('content')
<div
  x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Page Activity Log</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        Every page visit and error from all dashboard users
      </p>
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
    <form method="GET" action="{{ route('settings.pageLog') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div>
          <label for="pl-start" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From</label>
          <input id="pl-start" type="date" name="start" value="{{ $start }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="pl-end" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To</label>
          <input id="pl-end" type="date" name="end" value="{{ $end }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="pl-user" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Staff</label>
          <select id="pl-user" name="staff_user_id"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value="">All Staff</option>
            @foreach($staffList as $s)
              <option value="{{ $s->id }}" {{ $userId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label for="pl-status" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Status</label>
          <select id="pl-status" name="status"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value=""    {{ $status === ''      ? 'selected' : '' }}>All</option>
            <option value="ok"  {{ $status === 'ok'    ? 'selected' : '' }}>Success (2xx)</option>
            <option value="error" {{ $status === 'error' ? 'selected' : '' }}>Errors (4xx / 5xx)</option>
          </select>
        </div>
        <div>
          <label for="pl-route" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Route</label>
          <input id="pl-route" type="text" name="route" value="{{ $route }}" placeholder="e.g. reports.sales…"
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
            <a href="{{ route('settings.pageLog', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('settings.pageLog') }}"
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
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Period</p>
        <p class="mt-0.5 text-xs font-bold" style="color:var(--text-primary)">
          {{ \Carbon\Carbon::parse($start)->format('d M') }} – {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
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
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Total Visits</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($totalRows) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4 {{ $errorCount > 0 ? 'border-red-200' : '' }}">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $errorCount > 0 ? 'bg-red-100' : 'bg-gray-100' }}">
        <svg class="h-5 w-5 {{ $errorCount > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Errors</p>
        <p class="mt-0.5 text-lg font-bold leading-none {{ $errorCount > 0 ? 'text-red-500' : '' }}"
          @if(!$errorCount) style="color:var(--text-primary)" @endif>
          {{ number_format($errorCount) }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100">
        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
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
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--text-muted)">No activity recorded for the selected period.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
              <th class="px-4 py-3 w-36">Date / Time</th>
              <th class="px-4 py-3 w-36">Staff</th>
              <th class="px-4 py-3 w-28">Status</th>
              <th class="px-4 py-3 w-28">Method</th>
              <th class="px-4 py-3">Page / Route</th>
              <th class="px-4 py-3 w-28">IP</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color:var(--card-border)">
            @foreach($rows as $log)
              @php
                $is5xx = $log->status_code >= 500;
                $is4xx = $log->status_code >= 400 && $log->status_code < 500;
                $isErr = $log->status_code >= 400;
              @endphp
              <tr class="transition-colors {{ $is5xx ? 'bg-red-50/60' : ($is4xx ? 'bg-amber-50/40' : 'hover:bg-gray-50/60') }}">

                {{-- Date / Time --}}
                <td class="px-4 py-3 whitespace-nowrap">
                  @php $ts = $log->created_at->timezone(config('app.timezone')); @endphp
                  <p class="text-xs tabular-nums font-medium" style="color:var(--text-primary)">
                    {{ $ts->format('d/m/Y') }}
                  </p>
                  <p class="text-[10px] tabular-nums" style="color:var(--text-muted)">
                    {{ $ts->format('H:i:s') }} WIB
                  </p>
                </td>

                {{-- Staff --}}
                <td class="px-4 py-3">
                  <div class="flex items-center gap-1.5">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[9px] font-bold text-white"
                      style="background:var(--sidebar-bg)">
                      {{ strtoupper(substr($log->staff_name, 0, 1)) }}
                    </div>
                    <span class="text-xs truncate max-w-[80px]" style="color:var(--text-primary)" title="{{ $log->staff_name }}">
                      {{ $log->staff_name }}
                    </span>
                  </div>
                </td>

                {{-- Status badge --}}
                <td class="px-4 py-3">
                  <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold
                    {{ $is5xx ? 'bg-red-100 text-red-700'
                      : ($is4xx ? 'bg-amber-100 text-amber-700'
                      : 'bg-green-100 text-green-700') }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $is5xx ? 'bg-red-500' : ($is4xx ? 'bg-amber-500' : 'bg-green-500') }}"></span>
                    {{ $log->status_code }}
                  </span>
                </td>

                {{-- Method --}}
                <td class="px-4 py-3">
                  <span class="rounded-md px-2 py-0.5 font-mono text-[10px] font-semibold
                    {{ $log->method === 'GET'    ? 'bg-blue-50 text-blue-600'
                      : ($log->method === 'POST'  ? 'bg-amber-50 text-amber-700'
                      : ($log->method === 'DELETE' ? 'bg-red-50 text-red-600'
                      : 'bg-gray-100 text-gray-600')) }}">
                    {{ $log->method }}
                  </span>
                </td>

                {{-- Route / URL + optional error --}}
                <td class="px-4 py-3">
                  @if($log->route_name)
                    <p class="text-xs font-semibold leading-snug" style="color:var(--text-primary)">
                      {{ $log->route_name }}
                    </p>
                  @endif
                  <p class="truncate text-[10px] max-w-xs" style="color:var(--text-muted)" title="{{ $log->url }}">
                    {{ $log->url }}
                  </p>
                  @if($log->error_message)
                    <p class="mt-1 truncate text-[10px] text-red-600 max-w-xs" title="{{ $log->error_message }}">
                      ⚠ {{ $log->error_message }}
                    </p>
                  @endif
                </td>

                {{-- IP --}}
                <td class="px-4 py-3">
                  <span class="font-mono text-[10px]" style="color:var(--text-muted)">
                    {{ $log->ip_address ?: '—' }}
                  </span>
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
