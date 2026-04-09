<!-- resources\views\reports\consumption_detail_invoice\index.blade.php -->
@extends('layouts.app')

@section('content')
<div x-data="salesPage()" x-init="init()" class="space-y-6">

  {{-- Header row --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-lg font-semibold">{{ $title }}</h1>
      <p class="text-xs text-gray-500">Filter by date / invoice / item / warehouse</p>
    </div>

    <div class="flex flex-wrap items-end gap-2">
      <a
        href="{{ route('reports.consumptionDetailInvoice.export', request()->query()) }}"
        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Export CSV
      </a>

      <form method="GET" class="flex flex-wrap gap-2 items-end">
        <input type="date" name="start" value="{{ $start }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
        <input type="date" name="end" value="{{ $end }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">

        <input type="text" name="invoice" placeholder="Invoice #" value="{{ $invoice }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
        <input type="text" name="item" placeholder="Item" value="{{ $item }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
        <input type="text" name="warehouse" placeholder="Warehouse" value="{{ $warehouse }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">

        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
          Apply
        </button>

        <a
          href="{{ route('reports.consumptionDetailInvoice') }}"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          Clear
        </a>
      </form>
    </div>
  </div>

  {{-- Content --}}
  @forelse($byInvoice as $invoiceId => $items)
    @php
      $invoiceTotal = $items->sum('totalCost');
      $date = optional($items->first())->date;
    @endphp

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div class="border-b px-4 py-3 flex items-center justify-between bg-gray-50">
       @if(!empty($invoiceId))
  <button
    type="button"
    class="font-medium text-gray-900 hover:underline"
    @click="openReceipt({{ (int) $invoiceId }})"
  >
    Invoice #{{ $invoiceId }}
  </button>
@else
  <span class="text-gray-400">Invoice #N/A</span>
@endif

    <span class="ml-2 text-xs text-gray-500">
      {{ optional($date)->format('d M Y') }}
    </span>
  

        <div class="font-semibold text-gray-900">
          Total: {{ number_format($invoiceTotal, 2) }}
        </div>
      </div>

      @foreach($items->groupBy('resultDescription') as $desc => $lines)
        @php $sub = $lines->sum('totalCost'); @endphp

        <div class="px-4 py-2 flex justify-between items-center bg-gray-100 border-t text-sm font-semibold">
          <div>
            {{ $desc }}
            <span class="text-xs text-gray-500">
              ({{ $lines->first()->resultQuantity }})
            </span>
          </div>
          <div>{{ number_format($sub, 2) }}</div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-600">
              <tr>
                <th class="px-4 py-2 text-left">Item</th>
                <th class="px-4 py-2 text-right">Qty</th>
                <th class="px-4 py-2 text-left">UOM</th>
                <th class="px-4 py-2 text-right">Total Cost</th>
                <th class="px-4 py-2 text-left">Warehouse</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($lines as $r)
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-2">{{ $r->item }}</td>
                  <td class="px-4 py-2 text-right">{{ number_format($r->quantity, 2) }}</td>
                  <td class="px-4 py-2">{{ $r->uom }}</td>
                  <td class="px-4 py-2 text-right">{{ number_format($r->totalCost, 2) }}</td>
                  <td class="px-4 py-2">{{ $r->warehouse }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endforeach
    </div>

  @empty
    <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600">
      No data for selected filters.
    </div>
  @endforelse

  {{-- Grand total --}}
  <div class="flex justify-end">
    <div class="rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold shadow-sm">
      Grand Total: {{ number_format($grandTotal, 2) }}
    </div>
  </div>

  <!-- receipt modal -->
  <div x-show="receiptOpen" x-cloak class="fixed inset-0 z-50">
  <div class="absolute inset-0 bg-black/40" @click="closeReceipt()"></div>

  <div class="absolute left-1/2 top-1/2 w-[95vw] max-w-2xl -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white shadow-xl">
    <div class="flex items-center justify-between border-b px-4 py-3">
      <div>
        <p class="text-sm font-semibold text-gray-900">Receipt</p>
        <p class="text-xs text-gray-500" x-text="receipt ? `Invoice #${receipt.invoice_id}` : ''"></p>
      </div>
      <button class="rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50" @click="closeReceipt()">Close</button>
    </div>

    <div class="p-4 space-y-4 text-sm">

      <template x-if="loadingReceipt">
        <div class="rounded-xl border bg-gray-50 p-4 text-gray-600">Loading receipt...</div>
      </template>

      <template x-if="receiptError">
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700" x-text="receiptError"></div>
      </template>

      <template x-if="receipt && !loadingReceipt">
        <div class="space-y-4">

          <!-- Header info -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <p class="text-xs text-gray-500">Date</p>
              <p class="font-medium" x-text="receipt.date ? new Date(receipt.date).toLocaleString('id-ID') : '-'"></p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Closed at</p>
              <p class="font-medium" x-text="receipt.closedTime ? new Date(receipt.closedTime).toLocaleString('id-ID') : '-'"></p>
            </div>

            <div>
              <p class="text-xs text-gray-500">Table</p>
              <p class="font-medium" x-text="receipt.tableName || '-'"></p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Pax</p>
              <p class="font-medium" x-text="receipt.pax ?? '-'"></p>
            </div>

            <div>
              <p class="text-xs text-gray-500">Type</p>
              <p class="font-medium" x-text="receipt.type || '-'"></p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Cashier</p>
              <p class="font-medium" x-text="receipt.cashier || '-'"></p>
            </div>
          </div>

          <!-- Items -->
          <div class="rounded-xl border">
            <div class="border-b px-3 py-2 text-xs font-semibold text-gray-700">Items</div>

            <div class="max-h-[45vh] overflow-auto">
              <table class="w-full">
                <thead class="sticky top-0 bg-white">
                  <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-3 py-2 w-[70px]">Qty</th>
                    <th class="px-3 py-2">Item</th>
                    <th class="px-3 py-2 w-[140px] text-right">Amount</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
  <template x-for="it in receiptItems" :key="it.id">

    <!-- Parent row -->
    <tr class="text-sm">
      <td class="px-3 py-2 whitespace-nowrap" x-text="it.quantity"></td>
      <td class="px-3 py-2">
        <div class="text-gray-900 font-medium" x-text="it.description"></div>

        <div class="text-xs text-gray-500"
             x-text="(it.department || '') + (it.category ? ' • ' + it.category : '')"></div>

        <template x-if="Number(it.discountAmount || 0) > 0">
          <div class="text-xs text-red-600">
            Disc: <span x-text="idr(it.discountAmount)"></span>
          </div>
        </template>
      </td>

      <td class="px-3 py-2 text-right">
        <span x-text="idr((Number(it.unitPrice || 0) * Number(it.quantity || 0)) - Number(it.discountAmount || 0))"></span>
      </td>
    </tr>

    <!-- Modifier rows -->
    <template x-if="it._modifiers && it._modifiers.length">
      <template x-for="m in it._modifiers" :key="m.id">
        <tr class="text-xs text-gray-600 bg-gray-50/50">
          <td class="px-3 py-2 whitespace-nowrap" x-text="''"></td>
          <td class="px-3 py-2">
            <div class="pl-4 flex items-start gap-2">
              <span class="text-gray-400">↳</span>
              <div class="text-gray-700" x-text="m.description"></div>
            </div>
          </td>
          <td class="px-3 py-2 text-right text-gray-500">
            <span x-text="Number(m.unitPrice || 0) ? idr(Number(m.unitPrice||0) * Number(m.quantity||0)) : ''"></span>
          </td>
        </tr>
      </template>
    </template>

  </template>

  <template x-if="receiptItems.length === 0">
    <tr>
      <td colspan="3" class="px-3 py-4 text-gray-500">No items.</td>
    </tr>
  </template>
</tbody>


                
              </table>
            </div>
          </div>

          <!-- Totals -->
          <div class="rounded-xl border bg-gray-50 p-3">
            <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="font-medium" x-text="idr(receipt.subtotal)"></span></div>
            <div class="flex justify-between"><span class="text-gray-600">Discount</span><span class="font-medium" x-text="idr(receipt.discountAmount)"></span></div>
            <div class="flex justify-between"><span class="text-gray-600">Service</span><span class="font-medium" x-text="idr(receipt.serviceChargeAmount)"></span></div>
            <div class="flex justify-between"><span class="text-gray-600">Tax</span><span class="font-medium" x-text="idr(receipt.tax1Amount)"></span></div>
            <div class="mt-2 flex justify-between border-t pt-2">
              <span class="text-gray-900 font-semibold">Total</span>
              <span class="font-semibold" x-text="idr(receipt.total)"></span>
            </div>
          </div>

          <!-- Payments -->
          <div class="rounded-xl border p-3">
            <div class="text-xs font-semibold text-gray-700 mb-2">Payment</div>

            <template x-for="p in receiptPayments" :key="p.bucket + '-' + p.method">
              <div class="flex justify-between py-1">
                <div class="text-gray-700">
                  <span class="font-medium" x-text="p.bucket"></span>
                  <span class="text-gray-500" x-text="p.method ? ' — ' + p.method : ''"></span>
                </div>
                <div class="font-medium" x-text="idr(p.amount)"></div>
              </div>
            </template>

            <template x-if="receiptPayments.length === 0">
              <div class="text-gray-500">No payment rows.</div>
            </template>

            <template x-if="receipt && typeof receipt.diff !== 'undefined'">
              <div class="mt-2 border-t pt-2 flex justify-between">
                <span class="text-gray-600">Diff (paid - total)</span>
                <span class="font-semibold" :class="Number(receipt.diff) === 0 ? 'text-green-700' : 'text-red-700'"
                      x-text="idr(receipt.diff)"></span>
              </div>
            </template>
          </div>

        </div>
      </template>

    </div>
  </div>
</div>

</div>
@endsection

