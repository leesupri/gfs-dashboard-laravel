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
      <a href="{{ route('reports.noSalesReceiptDetail', array_merge(request()->query(), ['export' => 'csv'])) }}"
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
    <form method="GET" action="{{ route('reports.noSalesReceiptDetail') }}" class="gfs-card p-5">
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
            <a href="{{ route('reports.noSalesReceiptDetail', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.noSalesReceiptDetail') }}"
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
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
    <div class="gfs-card flex items-center gap-3 p-4" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Receipts</p>
        <p class="mt-0.5 text-lg font-bold leading-none text-white">{{ number_format($totalReceipts) }}</p>
      </div>
    </div>
    <div class="gfs-card flex items-center gap-3 p-4 sm:col-span-2" style="background:var(--sidebar-bg)">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Grand Total</p>
        <p class="mt-0.5 text-lg font-bold leading-none text-green-400 tabular-nums">{{ number_format($grandTotal, 0, ',', '.') }}</p>
      </div>
    </div>
  </div>

  {{-- Receipt cards --}}
  @if(empty($receipts))
    <div class="gfs-card flex flex-col items-center justify-center gap-3 py-20">
      <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <p class="text-sm font-medium" style="color:var(--text-muted)">No no-sale receipts found for the selected period.</p>
    </div>
  @else
    @foreach($receipts as $receipt)
      <div class="gfs-card overflow-hidden">

        {{-- Receipt header --}}
        <div class="grid grid-cols-2 gap-0 border-b sm:grid-cols-4"
          style="border-color:var(--card-border); background:var(--content-bg)">

          {{-- Order # + Table + Type --}}
          <div class="border-r px-4 py-3" style="border-color:var(--card-border)">
            <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Order #</p>
            <p class="mt-0.5 font-mono text-sm font-bold" style="color:var(--text-primary)">#{{ $receipt['id'] }}</p>
            <div class="mt-1 flex flex-wrap gap-1">
              @if($receipt['tableName'])
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700">
                  {{ $receipt['tableName'] }}
                </span>
              @endif
              @if($receipt['type'])
                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700">
                  {{ $receipt['type'] }}
                </span>
              @endif
            </div>
          </div>

          {{-- Opened / Closed --}}
          <div class="border-r px-4 py-3" style="border-color:var(--card-border)">
            <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Opened</p>
            @if($receipt['created'])
              <p class="mt-0.5 text-xs tabular-nums font-medium" style="color:var(--text-primary)">
                {{ \Carbon\Carbon::parse($receipt['created'])->format('d M Y H:i') }}
              </p>
            @endif
            <p class="mt-1.5 text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Closed</p>
            @if($receipt['closed'])
              <p class="text-xs tabular-nums font-medium" style="color:var(--text-primary)">
                {{ \Carbon\Carbon::parse($receipt['closed'])->format('d M Y H:i') }}
              </p>
            @endif
          </div>

          {{-- Cashier + Pax + Member --}}
          <div class="border-r px-4 py-3" style="border-color:var(--card-border)">
            @if($receipt['fullName'])
              <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Cashier</p>
              <div class="mt-0.5 flex items-center gap-1.5">
                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[8px] font-bold text-white"
                  style="background:var(--sidebar-bg)">
                  {{ strtoupper(substr($receipt['fullName'], 0, 1)) }}
                </div>
                <p class="text-xs font-medium" style="color:var(--text-primary)">{{ $receipt['fullName'] }}</p>
              </div>
            @endif
            <div class="mt-1.5 flex items-center gap-3">
              @if($receipt['guest'])
                <div>
                  <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Pax</p>
                  <p class="text-xs font-bold" style="color:var(--text-primary)">{{ $receipt['guest'] }}</p>
                </div>
              @endif
              @if($receipt['member'])
                <div>
                  <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Member</p>
                  <p class="text-xs font-medium text-indigo-600">{{ $receipt['member'] }}</p>
                </div>
              @endif
            </div>
          </div>

          {{-- Notes --}}
          <div class="px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Notes</p>
            <p class="mt-0.5 text-xs leading-snug" style="color:{{ $receipt['notes'] ? 'var(--text-primary)' : 'var(--text-muted)' }}">
              {{ $receipt['notes'] ?: '—' }}
            </p>
          </div>
        </div>

        {{-- Line items --}}
        @if(!empty($receipt['items']))
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
                  style="background:var(--content-bg); border-bottom:1px solid var(--card-border); color:var(--text-muted)">
                  <th class="px-4 py-2 w-12 text-right">Qty</th>
                  <th class="px-4 py-2 text-left">Description</th>
                  <th class="px-4 py-2 w-32">Price</th>
                </tr>
              </thead>
              <tbody class="divide-y" style="border-color:var(--card-border)">
                @foreach($receipt['items'] as $item)
                  <tr class="transition-colors hover:bg-gray-50/60">
                    <td class="px-4 py-2 text-right tabular-nums text-xs" style="color:var(--text-secondary)">
                      {{ number_format($item['quantity'], 0) }}
                    </td>
                    <td class="px-4 py-2">
                      <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $item['description'] }}</p>
                      @if(!empty($item['remark']))
                        <p class="mt-0.5 text-[10px] font-semibold"
                          style="color:#6366f1">{{ $item['remark'] }}</p>
                      @endif
                    </td>
                    <td class="px-4 py-2 text-right tabular-nums font-semibold" style="color:var(--text-primary)">
                      {{ number_format($item['price'], 0, ',', '.') }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif

        {{-- Totals footer --}}
        <div class="flex items-start justify-end border-t" style="border-color:var(--card-border); background:var(--content-bg)">
          <div class="min-w-55 divide-y px-5 py-3" style="border-color:var(--card-border)">
            <div class="flex items-center justify-between py-1.5 text-xs">
              <span style="color:var(--text-secondary)">Subtotal</span>
              <span class="tabular-nums font-medium" style="color:var(--text-primary)">
                {{ number_format($receipt['subtotal'], 0, ',', '.') }}
              </span>
            </div>
            @if($receipt['serviceAmount'] > 0)
              <div class="flex items-center justify-between py-1.5 text-xs">
                <span style="color:var(--text-secondary)">Service</span>
                <span class="tabular-nums" style="color:var(--text-primary)">
                  {{ number_format($receipt['serviceAmount'], 0, ',', '.') }}
                </span>
              </div>
            @endif
            @if($receipt['taxAmount'] > 0)
              <div class="flex items-center justify-between py-1.5 text-xs">
                <span style="color:var(--text-secondary)">Tax</span>
                <span class="tabular-nums" style="color:var(--text-primary)">
                  {{ number_format($receipt['taxAmount'], 0, ',', '.') }}
                </span>
              </div>
            @endif
            @if($receipt['discount'] > 0)
              <div class="flex items-center justify-between py-1.5 text-xs">
                <span style="color:var(--text-secondary)">Discount</span>
                <span class="tabular-nums text-red-500">
                  −{{ number_format($receipt['discount'], 0, ',', '.') }}
                </span>
              </div>
            @endif
            <div class="flex items-center justify-between py-2 text-sm">
              <span class="font-semibold" style="color:var(--text-primary)">Total</span>
              <span class="tabular-nums font-bold text-green-600">
                {{ number_format($receipt['total'], 0, ',', '.') }}
              </span>
            </div>
          </div>
        </div>

      </div>
    @endforeach
  @endif

</div>
@endsection
