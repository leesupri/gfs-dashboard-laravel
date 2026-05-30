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
      <a href="{{ route('reports.openingDay', array_merge(request()->query(), ['export' => 'csv'])) }}"
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
    <form method="GET" action="{{ route('reports.openingDay') }}" class="gfs-card p-5">
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
            ['This Week',  ['start' => now()->startOfWeek()->toDateString(),               'end' => now()->endOfWeek()->toDateString()]],
            ['This Month', ['start' => now()->startOfMonth()->toDateString(),              'end' => now()->endOfMonth()->toDateString()]],
            ['Last Month', ['start' => now()->subMonth()->startOfMonth()->toDateString(),  'end' => now()->subMonth()->endOfMonth()->toDateString()]],
          ] as [$ql, $qp])
            <a href="{{ route('reports.openingDay', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.openingDay') }}"
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
    <div class="gfs-card flex items-center gap-3 p-4" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Total Days</p>
        <p class="mt-0.5 text-lg font-bold leading-none text-white">{{ number_format($totalDays) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.71.71M18.36 18.36l.71.71M3 12H2m20 0h-1M4.22 19.78l.71-.71M18.36 5.64l.71-.71"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Days Opened</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($totalDays) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4 {{ $closedDays > 0 ? 'border-green-200' : '' }}">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $closedDays > 0 ? 'bg-green-100' : 'bg-gray-100' }}">
        <svg class="h-5 w-5 {{ $closedDays > 0 ? 'text-green-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">EOD Done</p>
        <p class="mt-0.5 text-lg font-bold leading-none {{ $closedDays > 0 ? 'text-green-600' : '' }}"
          @if(!$closedDays) style="color:var(--text-primary)" @endif>
          {{ number_format($closedDays) }}
        </p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4 {{ $openDays > 0 ? 'border-amber-200' : '' }}">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $openDays > 0 ? 'bg-amber-100' : 'bg-gray-100' }}">
        <svg class="h-5 w-5 {{ $openDays > 0 ? 'text-amber-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Not Closed</p>
        <p class="mt-0.5 text-lg font-bold leading-none {{ $openDays > 0 ? 'text-amber-600' : '' }}"
          @if(!$openDays) style="color:var(--text-primary)" @endif>
          {{ number_format($openDays) }}
        </p>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="gfs-card overflow-hidden">
    @if($rows->isEmpty())
      <div class="flex flex-col items-center justify-center gap-3 py-20">
        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-sm font-medium" style="color:var(--text-muted)">No opening day records found for the selected period.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-[10px] font-semibold uppercase tracking-wider"
              style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
              <th class="px-4 py-3 w-32">Date</th>
              <th class="px-4 py-3 w-20">Status</th>
              <th class="px-4 py-3 w-36">Opening Day By</th>
              <th class="px-4 py-3 w-36">Opening Time</th>
              <th class="px-4 py-3 w-36">End of Day By</th>
              <th class="px-4 py-3 w-36">EOD Time</th>
              <th class="px-4 py-3 w-28 text-right">Duration</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color:var(--card-border)">
            @foreach($rows as $row)
              @php
                $isClosed = !is_null($row->closedBy_id);
                $duration = null;
                if ($isClosed && $row->created && $row->closed) {
                    $diffMins = \Carbon\Carbon::parse($row->created)->diffInMinutes(\Carbon\Carbon::parse($row->closed));
                    $hours = intdiv($diffMins, 60);
                    $mins  = $diffMins % 60;
                    $duration = "{$hours}h {$mins}m";
                }
              @endphp
              <tr class="transition-colors hover:bg-gray-50/60 {{ !$isClosed ? 'bg-amber-50/30' : '' }}">

                {{-- Date --}}
                <td class="px-4 py-3">
                  <p class="text-sm font-bold" style="color:var(--text-primary)">
                    {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : '—' }}
                  </p>
                  <p class="text-[10px]" style="color:var(--text-muted)">
                    {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('l') : '' }}
                  </p>
                </td>

                {{-- Status badge --}}
                <td class="px-4 py-3">
                  @if($isClosed)
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-[10px] font-bold text-green-700">
                      <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                      Closed
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                      <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                      Open
                    </span>
                  @endif
                </td>

                {{-- Opening Day By --}}
                <td class="px-4 py-3">
                  @if($row->opening)
                    <div class="flex items-center gap-2">
                      <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[9px] font-bold text-white"
                        style="background:var(--sidebar-bg)">
                        {{ strtoupper(substr($row->opening, 0, 1)) }}
                      </div>
                      <span class="text-xs font-medium" style="color:var(--text-primary)">{{ $row->opening }}</span>
                    </div>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- Opening Time --}}
                <td class="px-4 py-3">
                  @if($row->created)
                    <p class="text-xs tabular-nums font-medium" style="color:var(--text-primary)">
                      {{ \Carbon\Carbon::parse($row->created)->format('d M Y') }}
                    </p>
                    <p class="text-[10px] tabular-nums" style="color:var(--text-muted)">
                      {{ \Carbon\Carbon::parse($row->created)->format('H:i') }}
                    </p>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- EOD By --}}
                <td class="px-4 py-3">
                  @if($row->closing)
                    <div class="flex items-center gap-2">
                      <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[9px] font-bold text-white"
                        style="background:#16a34a">
                        {{ strtoupper(substr($row->closing, 0, 1)) }}
                      </div>
                      <span class="text-xs font-medium" style="color:var(--text-primary)">{{ $row->closing }}</span>
                    </div>
                  @else
                    <span class="text-xs" style="color:var(--text-muted)">Not yet closed</span>
                  @endif
                </td>

                {{-- EOD Time --}}
                <td class="px-4 py-3">
                  @if($row->closed)
                    <p class="text-xs tabular-nums font-medium" style="color:var(--text-primary)">
                      {{ \Carbon\Carbon::parse($row->closed)->format('d M Y') }}
                    </p>
                    <p class="text-[10px] tabular-nums" style="color:var(--text-muted)">
                      {{ \Carbon\Carbon::parse($row->closed)->format('H:i') }}
                    </p>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

                {{-- Duration --}}
                <td class="px-4 py-3 text-right">
                  @if($duration)
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 tabular-nums">
                      {{ $duration }}
                    </span>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>

              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>
@endsection
