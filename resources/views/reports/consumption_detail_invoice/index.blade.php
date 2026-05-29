@extends('layouts.app')

@section('content')
<div
  x-data="salesPage()"
  x-init="filtersOpen = {{ request()->except('page') ? 'true' : 'false' }}"
  class="space-y-6"
  @keydown.escape.window="closeReceipt()">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">{{ $title }}</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Consumption breakdown by invoice & recipe</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.consumptionDetailInvoice.export', request()->query()) }}"
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
    <form method="GET" class="gfs-card p-5">
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
          <label for="f-invoice" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Invoice #</label>
          <input id="f-invoice" type="text" name="invoice" placeholder="Invoice number" value="{{ $invoice }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-item" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Item</label>
          <input id="f-item" type="text" name="item" placeholder="Item name" value="{{ $item }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="f-warehouse" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Warehouse</label>
          <input id="f-warehouse" type="text" name="warehouse" placeholder="Warehouse" value="{{ $warehouse }}"
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
            <a href="{{ route('reports.consumptionDetailInvoice', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('reports.consumptionDetailInvoice') }}"
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

  {{-- Invoice cards --}}
  @forelse($byInvoice as $invoiceId => $items)
    @php
      $invoiceTotal = $items->sum('totalCost');
      $date         = optional($items->first())->date;
      $recipeCount  = $items->groupBy('resultDescription')->count();
    @endphp

    <div class="gfs-card overflow-hidden">

      {{-- Invoice header --}}
      <div class="flex items-center justify-between gap-4 px-5 py-4"
        style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
            style="background:var(--sidebar-bg)">
            <svg class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div>
            @if(!empty($invoiceId))
              <button type="button"
                class="text-sm font-bold text-gray-900 transition hover:text-green-600"
                @click="openReceipt({{ (int) $invoiceId }})">
                Invoice #{{ $invoiceId }}
              </button>
            @else
              <span class="text-sm font-semibold" style="color:var(--text-muted)">No Invoice</span>
            @endif
            <p class="text-xs" style="color:var(--text-muted)">
              {{ optional($date)->format('d M Y') }}
              <span class="ml-1.5 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 font-medium" style="color:var(--text-muted)">
                {{ $recipeCount }} recipe(s)
              </span>
            </p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Cost</p>
          <p class="text-base font-bold text-green-600">{{ number_format($invoiceTotal, 0, ',', '.') }}</p>
        </div>
      </div>

      {{-- Recipe groups --}}
      @foreach($items->groupBy('resultDescription') as $desc => $lines)
        @php $sub = $lines->sum('totalCost'); @endphp

        {{-- Recipe sub-header --}}
        <div class="flex items-center justify-between px-5 py-2.5"
          style="background:var(--content-bg); border-left:3px solid #22c55e; border-bottom:1px solid var(--card-border)">
          <div>
            <p class="text-sm font-semibold" style="color:var(--text-primary)">{{ $desc }}</p>
            <p class="text-xs" style="color:var(--text-muted)">
              Qty: {{ number_format($lines->first()->resultQuantity, 0) }}
            </p>
          </div>
          <span class="text-sm font-bold" style="color:var(--text-primary)">
            {{ number_format($sub, 0, ',', '.') }}
          </span>
        </div>

        {{-- Ingredient rows --}}
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
                style="background:var(--content-bg); border-bottom:1px solid var(--card-border); color:var(--text-muted)">
                <th class="px-5 py-2.5 text-left">Item</th>
                <th class="px-4 py-2.5 w-20">Qty</th>
                <th class="px-4 py-2.5 w-16 text-left">UOM</th>
                <th class="px-4 py-2.5 w-32">Total Cost</th>
                <th class="px-4 py-2.5 w-36 text-left">Warehouse</th>
              </tr>
            </thead>
            <tbody class="divide-y" style="border-color:var(--card-border)">
              @foreach($lines as $r)
                <tr class="transition-colors hover:bg-gray-50/60">
                  <td class="px-5 py-2.5 font-medium" style="color:var(--text-primary)">{{ $r->item }}</td>
                  <td class="px-4 py-2.5 text-right" style="color:var(--text-secondary)">{{ number_format($r->quantity, 2) }}</td>
                  <td class="px-4 py-2.5" style="color:var(--text-muted)">{{ $r->uom }}</td>
                  <td class="px-4 py-2.5 text-right font-semibold" style="color:var(--text-primary)">{{ number_format($r->totalCost, 0, ',', '.') }}</td>
                  <td class="px-4 py-2.5 text-xs" style="color:var(--text-secondary)">{{ $r->warehouse }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endforeach

    </div>

  @empty
    <div class="gfs-card flex flex-col items-center justify-center gap-3 py-20">
      <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <p class="text-sm font-medium" style="color:var(--text-muted)">No consumption records for the selected filters.</p>
    </div>
  @endforelse

  {{-- Grand total --}}
  @if($byInvoice->count())
    <div class="flex justify-end">
      <div class="gfs-card flex items-center gap-6 px-6 py-4" style="background:var(--sidebar-bg)">
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Grand Total</p>
          <p class="mt-0.5 text-xl font-bold text-green-400">{{ number_format($grandTotal, 0, ',', '.') }}</p>
        </div>
        <div class="h-10 w-px" style="background:rgba(255,255,255,0.1)"></div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Invoices</p>
          <p class="mt-0.5 text-xl font-bold text-white">{{ $byInvoice->count() }}</p>
        </div>
      </div>
    </div>
  @endif

  {{-- Receipt modal --}}
  <div x-show="receiptOpen" x-cloak
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
    style="background:rgba(15,17,23,0.72); backdrop-filter:blur(10px)">
    <div
      class="relative flex w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl sm:rounded-2xl bg-white shadow-2xl"
      style="max-height:92vh"
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
      x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      @click.stop>

      {{-- Modal header --}}
      <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-5 py-4">
        <div>
          <p class="text-sm font-bold" style="color:var(--text-primary)">Sales Receipt</p>
          <p class="text-xs" style="color:var(--text-muted)"
            x-text="receipt ? `Invoice #${receipt.invoice_id}` : 'Loading…'"></p>
        </div>
        <button @click="closeReceipt()"
          class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm font-medium transition hover:bg-gray-50"
          style="color:var(--text-secondary)">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      {{-- Modal body --}}
      <div class="flex-1 overflow-y-auto p-5 space-y-4">

        <template x-if="loadingReceipt">
          <div class="flex items-center justify-center gap-2 py-10" style="color:var(--text-muted)">
            <svg class="h-4 w-4 animate-spin text-green-500" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span class="text-sm">Loading receipt…</span>
          </div>
        </template>

        <template x-if="receiptError">
          <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-text="receiptError"></div>
        </template>

        <template x-if="receipt && !loadingReceipt">
          <div class="space-y-4">

            {{-- Meta grid --}}
            <div class="grid grid-cols-2 gap-3 rounded-xl border border-gray-100 p-4 sm:grid-cols-4"
              style="background:var(--content-bg)">
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Opened</p>
                <p class="mt-1 text-xs font-medium" style="color:var(--text-primary)"
                  x-text="receipt.date ? new Date(receipt.date).toLocaleString('id-ID') : '-'"></p>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Closed</p>
                <p class="mt-1 text-xs font-medium" style="color:var(--text-primary)"
                  x-text="receipt.closedTime ? new Date(receipt.closedTime).toLocaleString('id-ID') : '-'"></p>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Table</p>
                <p class="mt-1 text-xs font-medium" style="color:var(--text-primary)" x-text="receipt.tableName || '-'"></p>
                <p class="text-[10px]" style="color:var(--text-muted)" x-text="`Pax: ${receipt.pax ?? '-'}`"></p>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Cashier</p>
                <p class="mt-1 text-xs font-medium" style="color:var(--text-primary)" x-text="receipt.cashier || '-'"></p>
                <p class="text-[10px]" style="color:var(--text-muted)" x-text="receipt.type || ''"></p>
              </div>
            </div>

            {{-- Items table --}}
            <div class="overflow-hidden rounded-xl border border-gray-100">
              <div class="border-b border-gray-100 px-4 py-2.5" style="background:var(--content-bg)">
                <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Items</p>
              </div>
              <div class="max-h-56 overflow-y-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="sticky top-0 text-left text-[10px] font-semibold uppercase tracking-wider"
                      style="background:var(--content-bg); border-bottom:1px solid var(--card-border); color:var(--text-muted)">
                      <th class="px-4 py-2 w-12">Qty</th>
                      <th class="px-4 py-2">Item</th>
                      <th class="px-4 py-2 w-32 text-right">Amount</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <template x-for="it in receiptItems" :key="it.id">
                      <tr class="hover:bg-gray-50/60 align-top">
                        <td class="px-4 py-2.5 text-sm tabular-nums" style="color:var(--text-secondary)" x-text="it.quantity"></td>
                        <td class="px-4 py-2.5">
                          <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)" x-text="it.description"></p>
                          <p class="text-xs leading-snug" style="color:var(--text-muted)"
                            x-text="(it.department || '') + (it.category ? ' · ' + it.category : '')"></p>
                          <template x-if="Number(it.discountAmount || 0) > 0">
                            <p class="mt-0.5 text-xs text-red-500">Disc: <span x-text="idr(it.discountAmount)"></span></p>
                          </template>
                          <template x-if="it._modifiers && it._modifiers.length">
                            <div class="mt-1 space-y-0.5">
                              <template x-for="m in it._modifiers" :key="m.id">
                                <div class="flex items-baseline gap-1.5 pl-2">
                                  <span class="text-gray-400 text-[10px]">↳</span>
                                  <span class="text-xs" style="color:var(--text-secondary)" x-text="m.description"></span>
                                  <span class="text-xs" style="color:var(--text-muted)"
                                    x-text="Number(m.unitPrice||0) ? idr(Number(m.unitPrice||0)*Number(m.quantity||0)) : ''"></span>
                                </div>
                              </template>
                            </div>
                          </template>
                        </td>
                        <td class="px-4 py-2.5 text-right text-sm font-semibold tabular-nums" style="color:var(--text-primary)"
                          x-text="idr((Number(it.unitPrice||0)*Number(it.quantity||0))-Number(it.discountAmount||0))"></td>
                      </tr>
                    </template>
                    <template x-if="receiptItems.length === 0 && !loadingReceipt">
                      <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm" style="color:var(--text-muted)">No items.</td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>

            {{-- Totals + Payment side by side --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

              {{-- Totals --}}
              <div class="overflow-hidden rounded-xl border border-gray-100">
                <div class="border-b border-gray-100 px-4 py-2.5" style="background:var(--content-bg)">
                  <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Summary</p>
                </div>
                <div class="divide-y divide-gray-100 px-4">
                  <div class="flex justify-between py-2">
                    <span class="text-sm" style="color:var(--text-secondary)">Subtotal</span>
                    <span class="text-sm font-medium" style="color:var(--text-primary)" x-text="idr(receipt.subtotal)"></span>
                  </div>
                  <div class="flex justify-between py-2">
                    <span class="text-sm" style="color:var(--text-secondary)">Discount</span>
                    <span class="text-sm font-medium text-red-500" x-text="idr(receipt.discountAmount)"></span>
                  </div>
                  <div class="flex justify-between py-2">
                    <span class="text-sm" style="color:var(--text-secondary)">Service</span>
                    <span class="text-sm font-medium" style="color:var(--text-primary)" x-text="idr(receipt.serviceChargeAmount)"></span>
                  </div>
                  <div class="flex justify-between py-2">
                    <span class="text-sm" style="color:var(--text-secondary)">Tax</span>
                    <span class="text-sm font-medium" style="color:var(--text-primary)" x-text="idr(receipt.tax1Amount)"></span>
                  </div>
                </div>
                <div class="flex items-center justify-between px-4 py-3" style="background:var(--content-bg); border-top:2px solid var(--card-border)">
                  <span class="font-semibold" style="color:var(--text-primary)">Total</span>
                  <span class="font-bold text-green-600" x-text="idr(receipt.total)"></span>
                </div>
              </div>

              {{-- Payments --}}
              <div class="overflow-hidden rounded-xl border border-gray-100">
                <div class="border-b border-gray-100 px-4 py-2.5" style="background:var(--content-bg)">
                  <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Payment</p>
                </div>
                <div class="divide-y divide-gray-100 px-4">
                  <template x-for="p in receiptPayments" :key="p.bucket + '-' + p.method">
                    <div class="flex items-center justify-between py-2.5">
                      <div>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-700': p.bucket === 'CASH',
                            'bg-blue-100 text-blue-700':  p.bucket === 'EDC',
                            'bg-amber-100 text-amber-700':p.bucket === 'QRIS',
                            'bg-slate-100 text-slate-600':!['CASH','EDC','QRIS'].includes(p.bucket)
                          }"
                          x-text="p.bucket"></span>
                        <span class="ml-1 text-xs" style="color:var(--text-muted)" x-text="p.method || ''"></span>
                      </div>
                      <span class="text-sm font-semibold" style="color:var(--text-primary)" x-text="idr(p.amount)"></span>
                    </div>
                  </template>
                  <template x-if="receiptPayments.length === 0">
                    <p class="py-6 text-center text-sm" style="color:var(--text-muted)">No payments.</p>
                  </template>
                </div>
                <template x-if="receipt && typeof receipt.diff !== 'undefined'">
                  <div class="flex items-center justify-between px-4 py-3"
                    style="border-top:1px solid var(--card-border); background:var(--content-bg)">
                    <span class="text-sm font-semibold" style="color:var(--text-secondary)">
                      <span x-text="Number(receipt.diff) === 0 ? '✓ Balanced' : 'Difference'"></span>
                    </span>
                    <span class="text-sm font-bold"
                      :class="Number(receipt.diff) === 0 ? 'text-green-600' : 'text-red-500'"
                      x-text="idr(receipt.diff)"></span>
                  </div>
                </template>
              </div>
            </div>

          </div>
        </template>

      </div>

      {{-- Modal footer --}}
      <div class="shrink-0 flex items-center justify-between border-t border-gray-100 px-5 py-4">
        <button @click="closeReceipt()"
          class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
          style="color:var(--text-secondary)">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close
        </button>
        <template x-if="receipt">
          <a :href="`/sales/${receipt.invoice_id}/receipt`" target="_blank"
            class="inline-flex items-center gap-1.5 rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Receipt
          </a>
        </template>
      </div>

    </div>
  </div>

</div>
@endsection
