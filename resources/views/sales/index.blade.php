@extends('layouts.app')

@section('content')
@php
  $paymentColors = [
    'CASH'  => 'bg-green-100 text-green-700',
    'EDC'   => 'bg-blue-100 text-blue-700',
    'QRIS'  => 'bg-amber-100 text-amber-700',
    'OTHER' => 'bg-slate-100 text-slate-600',
  ];
@endphp

<div x-data="salesPage()" x-init="init()" class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-xl font-semibold" style="color:var(--text-primary)">Sales</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Filter transactions and review sales performance.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('sales.export', request()->query()) }}"
        class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
        style="border-color:var(--card-border); color:var(--text-secondary)">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
      </a>
      <button type="button" @click="filtersOpen = !filtersOpen"
        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-white transition active:scale-95"
        :class="filtersOpen ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span x-text="filtersOpen ? 'Hide Filters' : 'Filters'"></span>
      </button>
    </div>
  </div>

  {{-- Filter panel --}}
  <div
    x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
  >
    <form method="GET" action="{{ route('sales.index') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">

        <div class="sm:col-span-2">
          <label for="filter-q" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Search</label>
          <input id="filter-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Invoice, table, cashier…"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
        </div>

        <div>
          <label for="quickDate" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Quick Date</label>
          <select id="quickDate"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500"
            style="border-color:var(--card-border); color:var(--text-primary)">
            <option value="">Custom</option>
            <option value="today">Today</option>
            <option value="this_month">This month</option>
            <option value="last_month">Last month</option>
          </select>
        </div>

        <div>
          <label for="startDate" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Start date</label>
          <input id="startDate" type="date" name="start" value="{{ $filters['start'] ?? now()->toDateString() }}"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
        </div>

        <div>
          <label for="endDate" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">End date</label>
          <input id="endDate" type="date" name="end" value="{{ $filters['end'] ?? now()->toDateString() }}"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
        </div>

        <div>
          <label for="filter-outlet" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Outlet</label>
          <select id="filter-outlet" name="outlet"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500"
            style="border-color:var(--card-border); color:var(--text-primary)">
            <option value="">All outlets</option>
            @foreach(['BALKON', 'HALL', 'VIP', 'OTHER'] as $opt)
              <option value="{{ $opt }}" {{ ($filters['outlet'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label for="filter-payment" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Payment</label>
          <select id="filter-payment" name="payment_type"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500"
            style="border-color:var(--card-border); color:var(--text-primary)">
            <option value="">All types</option>
            @foreach(['cash' => 'Cash', 'edc' => 'EDC', 'qris' => 'QRIS', 'other' => 'Other'] as $val => $lbl)
              <option value="{{ $val }}" {{ ($filters['payment_type'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label for="filter-sort" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Sort</label>
          <select id="filter-sort" name="sort"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500"
            style="border-color:var(--card-border); color:var(--text-primary)">
            <option value="datetime_desc" {{ ($filters['sort'] ?? 'datetime_desc') === 'datetime_desc' ? 'selected' : '' }}>Date (newest)</option>
            <option value="datetime_asc"  {{ ($filters['sort'] ?? '') === 'datetime_asc'  ? 'selected' : '' }}>Date (oldest)</option>
            <option value="total_desc"    {{ ($filters['sort'] ?? '') === 'total_desc'    ? 'selected' : '' }}>Amount (high)</option>
            <option value="total_asc"     {{ ($filters['sort'] ?? '') === 'total_asc'     ? 'selected' : '' }}>Amount (low)</option>
          </select>
        </div>

      </div>

      <div class="mt-4 flex items-center justify-end gap-2 border-t pt-4" style="border-color:var(--card-border)">
        <a href="{{ route('sales.index') }}"
          class="rounded-lg border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
          style="border-color:var(--card-border); color:var(--text-secondary)">Reset</a>
        <button type="submit"
          class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
          Apply
        </button>
      </div>
    </form>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">

    <div class="gfs-card p-5 lg:col-span-2">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Gross Sales</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-green-50 text-green-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </span>
      </div>
      <p class="mt-3 text-2xl font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi->grossSales ?? 0), 0, ',', '.') }}
      </p>
      <p class="mt-0.5 text-xs" style="color:var(--text-muted)">{{ number_format((int)($kpi->trxCount ?? 0), 0, ',', '.') }} transactions</p>
    </div>

    <div class="gfs-card p-5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Service</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
          </svg>
        </span>
      </div>
      <p class="mt-3 text-xl font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi->serviceTotal ?? 0), 0, ',', '.') }}
      </p>
      <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Service charge</p>
    </div>

    <div class="gfs-card p-5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Tax</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
          </svg>
        </span>
      </div>
      <p class="mt-3 text-xl font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi->taxTotal ?? 0), 0, ',', '.') }}
      </p>
      <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Tax collected</p>
    </div>

    <div class="gfs-card p-5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Discount</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-50 text-red-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
          </svg>
        </span>
      </div>
      <p class="mt-3 text-xl font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($kpi->discountTotal ?? 0), 0, ',', '.') }}
      </p>
      <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Discounts given</p>
    </div>

    <div class="gfs-card p-5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Avg Order</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </span>
      </div>
      @php $avg = ($kpi->trxCount ?? 0) ? (($kpi->grossSales ?? 0) / ($kpi->trxCount ?? 1)) : 0; @endphp
      <p class="mt-3 text-xl font-semibold tracking-tight" style="color:var(--text-primary)">
        Rp {{ number_format((int)round($avg), 0, ',', '.') }}
      </p>
      <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Per transaction</p>
    </div>

  </div>

  {{-- Payment Breakdown --}}
  @php
    $pmtConfig = [
      'CASH'  => ['label' => 'Cash',  'dot' => 'bg-green-500'],
      'EDC'   => ['label' => 'EDC',   'dot' => 'bg-blue-500'],
      'QRIS'  => ['label' => 'QRIS',  'dot' => 'bg-amber-500'],
      'OTHER' => ['label' => 'Other', 'dot' => 'bg-slate-400'],
    ];
  @endphp
  <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach($pmtConfig as $key => $cfg)
      @php
        $pmtAmount = (int)round($paymentBreakdown[$key]['amountTotal'] ?? 0);
        $pmtTrx    = (int)($paymentBreakdown[$key]['trxCount'] ?? 0);
      @endphp
      <div class="gfs-card p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium" style="color:var(--text-muted)">{{ $cfg['label'] }}</span>
          <div class="h-2 w-2 rounded-full {{ $cfg['dot'] }}"></div>
        </div>
        <p class="mt-3 text-xl font-semibold tracking-tight" style="color:var(--text-primary)">
          Rp {{ number_format($pmtAmount, 0, ',', '.') }}
        </p>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">{{ number_format($pmtTrx, 0, ',', '.') }} trx</p>
      </div>
    @endforeach
  </div>

  {{-- Transactions Table --}}
  <div class="gfs-card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--card-border)">
      <div>
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Transactions</h2>
        @php
          $rowCount   = is_object($rows) && method_exists($rows, 'count') ? $rows->count()  : (is_array($rows) ? count($rows) : 0);
          $totalCount = is_object($rows) && method_exists($rows, 'total') ? $rows->total() : null;
        @endphp
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">
          Showing {{ $rowCount }} rows@if($totalCount) of {{ number_format($totalCount, 0, ',', '.') }} total@endif
        </p>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full table-fixed" style="min-width:860px">
        <thead>
          <tr class="text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted); background:var(--content-bg); border-bottom:1px solid var(--card-border)">
            <th class="w-[130px] px-4 py-3">Created</th>
            <th class="w-[130px] px-4 py-3 hidden md:table-cell">Closed</th>
            <th class="w-[90px] px-4 py-3">Invoice</th>
            <th class="w-[120px] px-4 py-3">Table</th>
            <th class="w-[110px] px-4 py-3 hidden lg:table-cell">Cashier</th>
            <th class="w-[140px] px-4 py-3">Payment</th>
            <th class="w-[110px] px-4 py-3 text-right hidden xl:table-cell">Gross</th>
            <th class="w-[90px] px-4 py-3 text-right hidden xl:table-cell">Disc</th>
            <th class="w-[90px] px-4 py-3 text-right hidden xl:table-cell">Svc</th>
            <th class="w-[90px] px-4 py-3 text-right hidden xl:table-cell">Tax</th>
            <th class="w-[120px] px-4 py-3 text-right">Total</th>
            <th class="w-[80px] px-4 py-3 text-right">Receipt</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse ($rows as $r)
            @php
              $created  = $r->created    ? \Carbon\Carbon::parse($r->created)->format('d/m/Y H:i')    : '-';
              $closed   = $r->closedTime ? \Carbon\Carbon::parse($r->closedTime)->format('d/m/Y H:i') : '-';
              $bucket   = strtoupper($r->paymentBucket ?? '');
              $badgeCss = $paymentColors[$bucket] ?? 'bg-gray-100 text-gray-600';
            @endphp
            <tr class="text-sm hover:bg-gray-50/60 transition-colors" style="color:var(--text-primary)">
              <td class="px-4 py-3 whitespace-nowrap text-xs" style="color:var(--text-secondary)">{{ $created }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-xs hidden md:table-cell" style="color:var(--text-secondary)">{{ $closed }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-xs font-semibold" style="color:var(--text-primary)">{{ $r->invoice_id }}</td>
              <td class="px-4 py-3 whitespace-nowrap truncate text-sm">{{ $r->tableName ?: '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm hidden lg:table-cell" style="color:var(--text-secondary)">{{ $r->cashier ?: '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex flex-col gap-0.5">
                  <span class="inline-flex w-fit items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeCss }}">
                    {{ $r->paymentBucket ?? '-' }}
                  </span>
                  @if($r->paymentMethod)
                    <span class="text-[11px] truncate" style="color:var(--text-muted)">{{ $r->paymentMethod }}</span>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-right text-sm hidden xl:table-cell">{{ number_format((int)round($r->subtotal ?? 0), 0, ',', '.') }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-right text-sm hidden xl:table-cell text-red-500">{{ number_format((int)round($r->discountAmount ?? 0), 0, ',', '.') }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-right text-sm hidden xl:table-cell">{{ number_format((int)round($r->serviceChargeAmount ?? 0), 0, ',', '.') }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-right text-sm hidden xl:table-cell">{{ number_format((int)round($r->tax1Amount ?? 0), 0, ',', '.') }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-right font-semibold text-sm">Rp {{ number_format((int)round($r->total ?? 0), 0, ',', '.') }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-right">
                @if(!empty($r->invoice_id))
                  <button type="button"
                    class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1.5 text-xs font-medium transition hover:bg-gray-50 hover:border-gray-300"
                    style="border-color:var(--card-border); color:var(--text-secondary)"
                    @click="openReceipt({{ (int)$r->invoice_id }})">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    View
                  </button>
                @else
                  <span class="text-xs" style="color:var(--text-muted)">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="12" class="px-5 py-12 text-center text-sm" style="color:var(--text-muted)">No transactions found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(is_object($rows) && method_exists($rows, 'links'))
      <div class="flex items-center justify-between px-5 py-3 text-sm" style="border-top:1px solid var(--card-border)">
        <p style="color:var(--text-secondary)">Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }}</p>
        <div class="flex items-center gap-1">{{ $rows->onEachSide(1)->links() }}</div>
      </div>
    @endif
  </div>

  {{-- Receipt Modal --}}
  <div
    x-show="receiptOpen" x-cloak
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
    style="background:rgba(15,17,23,0.72); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="closeReceipt()"
  >
    <div class="absolute inset-0" @click="closeReceipt()"></div>

    <div
      x-show="receiptOpen"
      class="relative flex w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl sm:rounded-2xl bg-white"
      style="max-height:92vh; box-shadow:0 24px 80px rgba(0,0,0,0.35), 0 8px 24px rgba(0,0,0,0.2);"
      x-transition:enter="transition ease-out duration-[250ms]"
      x-transition:enter-start="opacity-0 translate-y-6 scale-[0.98]"
      x-transition:enter-end="opacity-100 translate-y-0 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 translate-y-0 scale-100"
      x-transition:leave-end="opacity-0 translate-y-4"
      @click.stop
    >
      {{-- Modal header --}}
      <div class="flex shrink-0 items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--card-border)">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold" style="color:var(--text-primary)">Receipt</p>
            <p class="text-xs" style="color:var(--text-muted)" x-text="receipt ? `Invoice #${receipt.invoice_id}` : 'Loading…'"></p>
          </div>
        </div>
        <button class="flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-gray-100" style="color:var(--text-muted)" @click="closeReceipt()">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      {{-- Modal body --}}
      <div class="min-h-0 flex-1 overflow-y-auto">

        <template x-if="loadingReceipt">
          <div class="flex flex-col items-center justify-center gap-3 py-16">
            <div class="flex gap-1.5">
              <span class="h-2 w-2 rounded-full bg-green-500" style="animation:dot-pulse 1.2s ease-in-out infinite"></span>
              <span class="h-2 w-2 rounded-full bg-green-500" style="animation:dot-pulse 1.2s ease-in-out 0.2s infinite"></span>
              <span class="h-2 w-2 rounded-full bg-green-500" style="animation:dot-pulse 1.2s ease-in-out 0.4s infinite"></span>
            </div>
            <p class="text-sm" style="color:var(--text-muted)">Loading receipt…</p>
          </div>
        </template>

        <template x-if="receiptError && !loadingReceipt">
          <div class="m-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-sm text-red-700" x-text="receiptError"></p>
          </div>
        </template>

        <template x-if="receipt && !loadingReceipt">
          <div class="p-5 space-y-5">

            {{-- Info grid --}}
            <div class="grid grid-cols-3 gap-x-6 gap-y-3">
              <div>
                <p class="text-xs font-medium" style="color:var(--text-muted)">Opened</p>
                <p class="mt-0.5 text-sm font-medium" style="color:var(--text-primary)" x-text="formatDate(receipt.date)"></p>
              </div>
              <div>
                <p class="text-xs font-medium" style="color:var(--text-muted)">Closed</p>
                <p class="mt-0.5 text-sm font-medium" style="color:var(--text-primary)" x-text="formatDate(receipt.closedTime)"></p>
              </div>
              <div>
                <p class="text-xs font-medium" style="color:var(--text-muted)">Table</p>
                <p class="mt-0.5 text-sm font-medium" style="color:var(--text-primary)" x-text="receipt.tableName || '-'"></p>
              </div>
              <div>
                <p class="text-xs font-medium" style="color:var(--text-muted)">Pax</p>
                <p class="mt-0.5 text-sm font-medium" style="color:var(--text-primary)" x-text="receipt.pax ?? '-'"></p>
              </div>
              <div>
                <p class="text-xs font-medium" style="color:var(--text-muted)">Type</p>
                <p class="mt-0.5 text-sm font-medium" style="color:var(--text-primary)" x-text="receipt.type || '-'"></p>
              </div>
              <div>
                <p class="text-xs font-medium" style="color:var(--text-muted)">Cashier</p>
                <p class="mt-0.5 text-sm font-medium" style="color:var(--text-primary)" x-text="receipt.cashier || '-'"></p>
              </div>
            </div>

            {{-- Items --}}
            <div class="overflow-hidden rounded-xl" style="border:1px solid var(--card-border)">
              <div class="px-4 py-2.5" style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Items</p>
              </div>
              <div class="max-h-[38vh] overflow-y-auto">
                <table class="w-full">
                  <thead class="sticky top-0 bg-white" style="border-bottom:1px solid var(--card-border)">
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wide" style="color:var(--text-muted)">
                      <th class="w-12 px-4 py-2.5">Qty</th>
                      <th class="px-4 py-2.5">Description</th>
                      <th class="w-32 px-4 py-2.5 text-right">Amount</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <template x-for="it in receiptItems" :key="it.id">

                      <tr>
                        <td class="px-4 py-2.5 text-sm whitespace-nowrap font-medium align-top" x-text="it.quantity" style="color:var(--text-primary)"></td>
                        <td class="px-4 py-2.5 align-top">
                          <p class="text-sm font-medium" style="color:var(--text-primary)" x-text="it.description"></p>
                          <p class="mt-0.5 text-xs" style="color:var(--text-muted)"
                            x-text="(it.department || '') + (it.category ? ' • ' + it.category : '')"></p>
                            <span class="text-xs" style="color:var(--text-secondary)" x-text="m.description"></span>
                          <template x-if="Number(it.discountAmount || 0) > 0">
                            <p class="mt-0.5 text-xs text-red-600">Disc: <span x-text="idr(it.discountAmount)"></span></p>
                          </template>
                        </td>
                        <td class="px-4 py-2.5 text-right text-sm font-medium whitespace-nowrap align-top" style="color:var(--text-primary)"
                          x-text="idr((Number(it.unitPrice||0)*Number(it.quantity||0))-Number(it.discountAmount||0))"></td>
                      </tr>

                      <template x-if="it._modifiers && it._modifiers.length">
                        <template x-for="m in it._modifiers" :key="m.id">
                          <tr style="background:var(--content-bg)">
                            <td class="px-4 py-1.5"></td>
                            <td class="px-4 py-1.5">
                              <div class="flex items-center gap-2 pl-3">
                                <span class="text-xs" style="color:var(--text-muted)">↳</span>
                                <span class="text-xs" style="color:var(--text-secondary)" x-text="m.description"></span>
                              </div>
                            </td>
                            <td class="px-4 py-1.5 text-right text-xs whitespace-nowrap" style="color:var(--text-secondary)">
                              <span x-text="Number(m.unitPrice||0) ? idr(Number(m.unitPrice||0)*Number(m.quantity||0)) : ''"></span>
                            </td>
                          </tr>
                        </template>
                      </template>

                    </template>
                    <template x-if="receiptItems.length === 0">
                      <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm" style="color:var(--text-muted)">No items.</td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>

            {{-- Totals + Payments --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

              {{-- Summary --}}
              <div class="overflow-hidden rounded-xl" style="border:1px solid var(--card-border)">
                <div class="px-4 py-2.5" style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">
                  <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Summary</p>
                </div>
                <div class="divide-y divide-gray-100">
                  <div class="flex justify-between px-4 py-2.5">
                    <span class="text-sm" style="color:var(--text-secondary)">Subtotal</span>
                    <span class="text-sm font-medium" style="color:var(--text-primary)" x-text="idr(receipt.subtotal)"></span>
                  </div>
                  <div class="flex justify-between px-4 py-2.5">
                    <span class="text-sm" style="color:var(--text-secondary)">Discount</span>
                    <span class="text-sm font-medium text-red-600" x-text="idr(receipt.discountAmount)"></span>
                  </div>
                  <div class="flex justify-between px-4 py-2.5">
                    <span class="text-sm" style="color:var(--text-secondary)">Service</span>
                    <span class="text-sm font-medium" style="color:var(--text-primary)" x-text="idr(receipt.serviceChargeAmount)"></span>
                  </div>
                  <div class="flex justify-between px-4 py-2.5">
                    <span class="text-sm" style="color:var(--text-secondary)">Tax</span>
                    <span class="text-sm font-medium" style="color:var(--text-primary)" x-text="idr(receipt.tax1Amount)"></span>
                  </div>
                  <div class="flex justify-between px-4 py-3" style="background:var(--content-bg)">
                    <span class="text-sm font-semibold" style="color:var(--text-primary)">Total</span>
                    <span class="text-sm font-semibold" style="color:var(--text-primary)" x-text="idr(receipt.total)"></span>
                  </div>
                </div>
              </div>

              {{-- Payments --}}
              <div class="overflow-hidden rounded-xl" style="border:1px solid var(--card-border)">
                <div class="px-4 py-2.5" style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">
                  <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted)">Payment</p>
                </div>
                <div class="divide-y divide-gray-100">
                  <template x-for="p in receiptPayments" :key="p.bucket + '-' + p.method">
                    <div class="flex items-center justify-between px-4 py-2.5">
                      <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-700': p.bucket === 'CASH',
                            'bg-blue-100 text-blue-700':   p.bucket === 'EDC',
                            'bg-amber-100 text-amber-700': p.bucket === 'QRIS',
                            'bg-slate-100 text-slate-600': !['CASH','EDC','QRIS'].includes(p.bucket),
                          }"
                          x-text="p.bucket">
                        </span>
                        <span class="text-xs" style="color:var(--text-muted)" x-text="p.method || ''"></span>
                      </div>
                      <span class="text-sm font-medium" style="color:var(--text-primary)" x-text="idr(p.amount)"></span>
                    </div>
                  </template>
                  <template x-if="receiptPayments.length === 0">
                    <div class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">No payment rows.</div>
                  </template>
                </div>
                <template x-if="receipt && typeof receipt.diff !== 'undefined'">
                  <div class="flex items-center justify-between px-4 py-3" style="border-top:1px solid var(--card-border); background:var(--content-bg)">
                    <span class="text-xs font-medium" style="color:var(--text-muted)">Diff (paid − total)</span>
                    <span class="text-sm font-semibold"
                      :class="Number(receipt.diff) === 0 ? 'text-green-600' : 'text-red-600'"
                      x-text="idr(receipt.diff)">
                    </span>
                  </div>
                </template>
              </div>

            </div>
          </div>
        </template>
      </div>

      {{-- Modal footer --}}
      <div class="flex shrink-0 items-center justify-between gap-3 px-5 py-4" style="border-top:1px solid var(--card-border)">
        <button
          class="inline-flex items-center gap-1.5 rounded-lg border px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
          style="border-color:var(--card-border); color:var(--text-secondary)"
          @click="closeReceipt()">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close
        </button>
        <a x-show="receipt"
          :href="receipt ? `/sales/${receipt.invoice_id}/receipt` : '#'"
          target="_blank"
          class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          Print Receipt
        </a>
      </div>
    </div>
  </div>

</div>
@endsection

