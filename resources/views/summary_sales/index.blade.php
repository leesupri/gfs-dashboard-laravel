@extends('layouts.app')

@section('content')
<div
  x-data="{ showFilter: {{ request()->hasAny(['start','end']) ? 'true' : 'false' }} }"
  class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Summary Sales</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        {{ $filters['from']->format('d M Y') }} — {{ $filters['to']->format('d M Y') }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('summarySales.export', request()->query()) }}"
         class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
         style="color:var(--text-primary)">
        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export CSV
      </a>
      <button type="button" @click="showFilter = !showFilter"
        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-white transition active:scale-95"
        :class="showFilter ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span x-text="showFilter ? 'Hide Filters' : 'Filters'"></span>
      </button>
    </div>
  </div>

  {{-- Filter panel --}}
  <div x-show="showFilter" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2">
    <form method="GET" action="{{ route('summarySales.index') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
          <label for="ss-start" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From</label>
          <input id="ss-start" type="date" name="start"
            value="{{ request('start') ?? $filters['from']->toDateString() }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="ss-end" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To</label>
          <input id="ss-end" type="date" name="end"
            value="{{ request('end') ?? $filters['to']->toDateString() }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div class="flex items-end gap-2">
          <button type="submit"
            class="rounded-xl bg-green-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
            Apply
          </button>
          <a href="{{ route('summarySales.index') }}"
            class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
            style="color:var(--text-secondary)">
            Reset
          </a>
        </div>
      </div>
      <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Quick:</span>
        @foreach([
          ['Today',      ['start' => now()->toDateString(),                         'end' => now()->toDateString()]],
          ['Yesterday',  ['start' => now()->subDay()->toDateString(),                'end' => now()->subDay()->toDateString()]],
          ['This Month', ['start' => now()->startOfMonth()->toDateString(),          'end' => now()->endOfMonth()->toDateString()]],
          ['Last Month', ['start' => now()->subMonth()->startOfMonth()->toDateString(), 'end' => now()->subMonth()->endOfMonth()->toDateString()]],
        ] as [$ql, $qp])
          <a href="{{ route('summarySales.index', $qp) }}"
            class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
            style="color:var(--text-secondary)">
            {{ $ql }}
          </a>
        @endforeach
      </div>
    </form>
  </div>

  {{-- KPI cards --}}
  @php
    $netSales   = (float)$summary->subtotal - (float)$summary->discount;
    $hasService = (float)$summary->service > 0;
    $hasTax     = (float)$summary->tax > 0;
    $lgGrid = $hasService && $hasTax ? 'lg:grid-cols-6' : ($hasService || $hasTax ? 'lg:grid-cols-5' : 'lg:grid-cols-4');
  @endphp
  {{-- tw-safelist: lg:grid-cols-4 lg:grid-cols-5 lg:grid-cols-6 --}}

  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 {{ $lgGrid }}">
    {{-- Subtotal --}}
    <div class="gfs-card flex items-center gap-4 p-5">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-100">
        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 13h.01M13 13h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
      </div>
      <div class="min-w-0">
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Subtotal</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format((float)$summary->subtotal, 0, ',', '.') }}</p>
      </div>
    </div>

    {{-- Discount --}}
    <div class="gfs-card flex items-center gap-4 p-5">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-100">
        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 014-4z"/>
        </svg>
      </div>
      <div class="min-w-0">
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Discount</p>
        <p class="mt-0.5 text-lg font-bold leading-none text-red-500">{{ number_format((float)$summary->discount, 0, ',', '.') }}</p>
      </div>
    </div>

    {{-- Net Sales --}}
    <div class="gfs-card flex items-center gap-4 p-5">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100">
        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
      </div>
      <div class="min-w-0">
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Net Sales</p>
        <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format($netSales, 0, ',', '.') }}</p>
      </div>
    </div>

    {{-- Service (conditional) --}}
    @if($hasService)
      <div class="gfs-card flex items-center gap-4 p-5">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-100">
          <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Service</p>
          <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format((float)$summary->service, 0, ',', '.') }}</p>
        </div>
      </div>
    @endif

    {{-- Tax (conditional) --}}
    @if($hasTax)
      <div class="gfs-card flex items-center gap-4 p-5">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-100">
          <svg class="h-5 w-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Tax</p>
          <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ number_format((float)$summary->tax, 0, ',', '.') }}</p>
        </div>
      </div>
    @endif

    {{-- Total --}}
    <div class="gfs-card flex items-center gap-4 p-5" style="background:var(--sidebar-bg)">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <div class="min-w-0">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Grand Total</p>
        <p class="mt-0.5 text-lg font-bold leading-none text-green-400">{{ number_format((float)$summary->total, 0, ',', '.') }}</p>
      </div>
    </div>
  </div>

  {{-- Payment + P&L row --}}
  @php
    $costPct   = (float)$profit->costPercent;
    $costColor = $costPct > 70 ? '#ef4444' : ($costPct > 55 ? '#f59e0b' : '#22c55e');
    $pColors = [
      'CASH' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'bar' => '#22c55e'],
      'EDC'  => ['bg' => 'bg-blue-100',  'text' => 'text-blue-700',  'bar' => '#3b82f6'],
      'QRIS' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'bar' => '#f59e0b'],
    ];
    $pTotal = $payments->sum('amount');
  @endphp
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

    {{-- Payment breakdown --}}
    <div class="gfs-card overflow-hidden">
      <div class="border-b border-gray-100 px-5 py-4">
        <h2 class="font-semibold" style="color:var(--text-primary)">Payment Breakdown</h2>
      </div>
      <div class="divide-y divide-gray-100 px-5">
        @forelse($payments as $p)
          @php
            $pct = $pTotal > 0 ? ((float)$p->amount / (float)$pTotal * 100) : 0;
            $pc  = $pColors[strtoupper($p->name)] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'bar' => '#94a3b8'];
          @endphp
          <div class="py-3">
            <div class="mb-1.5 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $pc['bg'] }} {{ $pc['text'] }}">
                  {{ $p->name }}
                </span>
                <span class="text-xs" style="color:var(--text-muted)">{{ number_format($p->qty) }} txn</span>
              </div>
              <div class="text-right">
                <span class="text-sm font-semibold" style="color:var(--text-primary)">{{ number_format((float)$p->amount, 0, ',', '.') }}</span>
                <span class="ml-1 text-xs" style="color:var(--text-muted)">({{ number_format($pct, 0) }}%)</span>
              </div>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
              <div class="h-full rounded-full transition-all" style="width:{{ number_format($pct, 1) }}%; background:{{ $pc['bar'] }}"></div>
            </div>
          </div>
        @empty
          <p class="py-8 text-center text-sm" style="color:var(--text-muted)">No payment data.</p>
        @endforelse
      </div>
      @if($payments->count())
        <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3" style="background:var(--content-bg)">
          <span class="text-sm font-semibold" style="color:var(--text-secondary)">Total</span>
          <span class="font-bold" style="color:var(--text-primary)">{{ number_format((float)$pTotal, 0, ',', '.') }}</span>
        </div>
      @endif
    </div>

    {{-- Profit & Loss --}}
    <div class="gfs-card overflow-hidden">
      <div class="border-b border-gray-100 px-5 py-4">
        <h2 class="font-semibold" style="color:var(--text-primary)">Profit & Loss</h2>
      </div>
      <div class="divide-y divide-gray-100 px-5">
        <div class="flex items-center justify-between py-3">
          <span class="text-sm" style="color:var(--text-secondary)">Subtotal</span>
          <span class="text-sm font-medium" style="color:var(--text-primary)">{{ number_format((float)$profit->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="flex items-center justify-between py-3">
          <span class="text-sm" style="color:var(--text-secondary)">Discount</span>
          <span class="text-sm font-medium text-red-500">−{{ number_format((float)$profit->discount, 0, ',', '.') }}</span>
        </div>
        <div class="flex items-center justify-between py-3" style="background:var(--content-bg); margin-left:-1.25rem; margin-right:-1.25rem; padding-left:1.25rem; padding-right:1.25rem">
          <span class="text-sm font-semibold" style="color:var(--text-primary)">Net Sales</span>
          <span class="text-sm font-semibold" style="color:var(--text-primary)">{{ number_format((float)$profit->netSales, 0, ',', '.') }}</span>
        </div>
        <div class="flex items-center justify-between py-3">
          <span class="text-sm" style="color:var(--text-secondary)">Total Cost</span>
          <div class="flex items-center gap-2">
            <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
              style="background:{{ $costColor }}22; color:{{ $costColor }}">
              {{ number_format($costPct, 0) }}%
            </span>
            <span class="text-sm font-medium text-red-500">−{{ number_format((float)$profit->totalCost, 0, ',', '.') }}</span>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between px-5 py-4" style="border-top:2px solid var(--card-border)">
        <span class="font-semibold" style="color:var(--text-primary)">Gross Profit</span>
        <span class="text-xl font-bold {{ (float)$profit->profit >= 0 ? 'text-green-600' : 'text-red-500' }}">
          {{ number_format((float)$profit->profit, 0, ',', '.') }}
        </span>
      </div>
    </div>

  </div>

  {{-- Void + No Sales + Visitor row --}}
  <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

    {{-- Void --}}
    @php $voidTotal = (float)$voids->sum('price'); @endphp
    <div class="gfs-card overflow-hidden">
      <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-red-100">
          <svg class="h-3.5 w-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </div>
        <h2 class="font-semibold" style="color:var(--text-primary)">Void Items</h2>
        @if($voids->count())
          <span class="ml-auto rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-600">
            {{ number_format($voids->sum('qty')) }} voided
          </span>
        @endif
      </div>
      <div class="divide-y divide-gray-100 px-5">
        @forelse($voids as $v)
          @php $vpct = $voidTotal > 0 ? ((float)$v->price / $voidTotal * 100) : 0; @endphp
          <div class="py-3">
            <div class="mb-1.5 flex items-center justify-between">
              <div>
                <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $v->description }}</p>
                <p class="text-xs" style="color:var(--text-muted)">{{ number_format($v->qty) }} item(s)</p>
              </div>
              <div class="text-right">
                <span class="text-sm font-semibold text-red-500">{{ number_format((float)$v->price, 0, ',', '.') }}</span>
                <span class="ml-1 text-xs" style="color:var(--text-muted)">({{ number_format($vpct, 0) }}%)</span>
              </div>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
              <div class="h-full rounded-full bg-red-400 transition-all" style="width:{{ number_format($vpct, 1) }}%"></div>
            </div>
          </div>
        @empty
          <p class="py-8 text-center text-sm" style="color:var(--text-muted)">No void items.</p>
        @endforelse
      </div>
      @if($voids->count())
        <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3" style="background:var(--content-bg)">
          <span class="text-sm font-semibold" style="color:var(--text-secondary)">Total Void</span>
          <span class="font-bold text-red-500">{{ number_format($voidTotal, 0, ',', '.') }}</span>
        </div>
      @endif
    </div>

    {{-- No Sales --}}
    @php $nsTotal = (float)$noSales->sum('amount'); @endphp
    <div class="gfs-card overflow-hidden">
      <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-100">
          <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
          </svg>
        </div>
        <h2 class="font-semibold" style="color:var(--text-primary)">No Sales</h2>
        @if($noSales->count())
          <span class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
            {{ number_format($noSales->sum('qty')) }} event(s)
          </span>
        @endif
      </div>
      <div class="divide-y divide-gray-100 px-5">
        @forelse($noSales as $ns)
          @php $nspct = $nsTotal > 0 ? ((float)$ns->amount / $nsTotal * 100) : 0; @endphp
          <div class="py-3">
            <div class="mb-1.5 flex items-center justify-between">
              <div>
                <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $ns->name }}</p>
                <p class="text-xs" style="color:var(--text-muted)">{{ number_format($ns->qty) }} time(s)</p>
              </div>
              <div class="text-right">
                <span class="text-sm font-semibold text-amber-600">{{ number_format((float)$ns->amount, 0, ',', '.') }}</span>
                <span class="ml-1 text-xs" style="color:var(--text-muted)">({{ number_format($nspct, 0) }}%)</span>
              </div>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
              <div class="h-full rounded-full bg-amber-400 transition-all" style="width:{{ number_format($nspct, 1) }}%"></div>
            </div>
          </div>
        @empty
          <p class="py-8 text-center text-sm" style="color:var(--text-muted)">No records.</p>
        @endforelse
      </div>
      @if($noSales->count())
        <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3" style="background:var(--content-bg)">
          <span class="text-sm font-semibold" style="color:var(--text-secondary)">Total Amount</span>
          <span class="font-bold text-amber-600">{{ number_format($nsTotal, 0, ',', '.') }}</span>
        </div>
      @endif
    </div>

    {{-- Visitor Statistics --}}
    <div class="gfs-card overflow-hidden">
      <div class="border-b border-gray-100 px-5 py-4">
        <h2 class="font-semibold" style="color:var(--text-primary)">Visitor Statistics</h2>
      </div>
      <div class="divide-y divide-gray-100 px-5">
        @foreach([
          ['Total Sales',       $stats->ttl,      'money'],
          ['Total Guests',      $stats->guest,     'int'],
          ['Avg / Guest',       $stats->avgGuest,  'money'],
          ['Transactions',      $stats->trans,     'int'],
          ['Avg / Transaction', $stats->avgTrans,  'money'],
        ] as [$vlabel, $vval, $vfmt])
          <div class="flex items-center justify-between py-3">
            <span class="text-sm" style="color:var(--text-secondary)">{{ $vlabel }}</span>
            <span class="text-sm font-semibold" style="color:var(--text-primary)">
              @if($vfmt === 'money')
                {{ number_format((float)$vval, 0, ',', '.') }}
              @else
                {{ number_format((int)$vval) }}
              @endif
            </span>
          </div>
        @endforeach
      </div>
    </div>

  </div>

  {{-- Department + Category breakdown --}}
  @php
    $sections = [
      ['title' => 'Department Breakdown', 'rows' => $department, 'label' => 'department'],
      ['title' => 'Category Breakdown',   'rows' => $category,   'label' => 'category'],
    ];
  @endphp
  <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    @foreach($sections as $s)
      @php $sTotal = $s['rows']->sum('price'); @endphp
      <div class="gfs-card overflow-hidden">
        <div class="border-b border-gray-100 px-5 py-4">
          <h2 class="font-semibold" style="color:var(--text-primary)">{{ $s['title'] }}</h2>
        </div>
        <div class="divide-y divide-gray-100 px-5">
          @forelse($s['rows'] as $r)
            @php $rpct = $sTotal > 0 ? ($r->price / $sTotal * 100) : 0; @endphp
            <div class="py-3">
              <div class="mb-1.5 flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $r->{$s['label']} }}</p>
                  <p class="text-xs" style="color:var(--text-muted)">{{ number_format($r->qty) }} items</p>
                </div>
                <div class="text-right">
                  <span class="text-sm font-semibold" style="color:var(--text-primary)">{{ number_format((float)$r->price, 0, ',', '.') }}</span>
                  <span class="ml-1 text-xs" style="color:var(--text-muted)">({{ number_format($rpct, 0) }}%)</span>
                </div>
              </div>
              <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-green-500 transition-all" style="width:{{ number_format($rpct, 1) }}%"></div>
              </div>
            </div>
          @empty
            <p class="py-8 text-center text-sm" style="color:var(--text-muted)">No data available.</p>
          @endforelse
        </div>
        @if($s['rows']->count())
          <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3" style="background:var(--content-bg)">
            <span class="text-sm font-semibold" style="color:var(--text-secondary)">Total</span>
            <span class="font-bold" style="color:var(--text-primary)">{{ number_format((float)$sTotal, 0, ',', '.') }}</span>
          </div>
        @endif
      </div>
    @endforeach
  </div>

</div>
@endsection
