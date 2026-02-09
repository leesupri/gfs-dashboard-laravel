<!-- resources\views\sales\index.blade.php -->
@extends('layouts.app')

@section('content')
<div x-data="salesPage()" x-init="init()" class="space-y-7">

  <!-- Header -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-xl font-semibold text-gray-900">Sales</h1>
      <p class="mt-1 text-sm text-gray-600">Filter transactions and review sales performance.</p>
    </div>

    <div class="flex items-center gap-2">
      <a
  href="{{ route('sales.export', request()->query()) }}"
  class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
>
  Export CSV
</a>

      <button type="button" @click="filtersOpen = !filtersOpen"
        class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
        Filter
      </button>
    </div>
  </div>

  <!-- Filters -->
  <form method="GET" action="{{ route('sales.index') }}"
    x-show="filtersOpen" x-cloak
    class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
      <div class="lg:col-span-2">
        <label class="block text-xs font-medium text-gray-600">Search</label>
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Invoice, table, cashier..."
          class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-gray-400" />
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Quick Date</label>
        <select id="quickDate"
  class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
  <!-- @change="applyQuickDate($event.target.value)"> -->
  <option value="">Custom</option>
  <option value="today">Today</option>
  <option value="this_month">This month</option>
  <option value="last_month">Last month</option>
</select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Start date</label>
        <input id="startDate" type="date" name="start" value="{{ $filters['start'] ?? now()->toDateString() }}"
          class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-gray-400" />
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">End date</label>
        <input id="endDate" type="date" name="end" value="{{ $filters['end'] ?? now()->toDateString() }}"
          class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-gray-400" />
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Outlet</label>
        <select name="outlet" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="BALKON" {{ ($filters['outlet'] ?? '') === 'BALKON' ? 'selected' : '' }}>BALKON</option>
          <option value="HALL" {{ ($filters['outlet'] ?? '') === 'HALL' ? 'selected' : '' }}>HALL</option>
          <option value="VIP" {{ ($filters['outlet'] ?? '') === 'VIP' ? 'selected' : '' }}>VIP</option>
          <option value="OTHER" {{ ($filters['outlet'] ?? '') === 'OTHER' ? 'selected' : '' }}>OTHER</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Payment</label>
        <select name="payment_type" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="cash" {{ ($filters['payment_type'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
          <option value="edc" {{ ($filters['payment_type'] ?? '') === 'edc' ? 'selected' : '' }}>EDC</option>
          <option value="qris" {{ ($filters['payment_type'] ?? '') === 'qris' ? 'selected' : '' }}>QRIS</option>
          <option value="other" {{ ($filters['payment_type'] ?? '') === 'other' ? 'selected' : '' }}>Other</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Sort</label>
        <select name="sort" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="datetime_desc" {{ ($filters['sort'] ?? '') === 'datetime_desc' ? 'selected' : '' }}>Date (newest)</option>
          <option value="datetime_asc" {{ ($filters['sort'] ?? '') === 'datetime_asc' ? 'selected' : '' }}>Date (oldest)</option>
          <option value="total_desc" {{ ($filters['sort'] ?? '') === 'total_desc' ? 'selected' : '' }}>Amount (high)</option>
          <option value="total_asc" {{ ($filters['sort'] ?? '') === 'total_asc' ? 'selected' : '' }}>Amount (low)</option>
        </select>
      </div>
    </div>

    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-xs text-gray-500">Tip: filters use query params (?start=&end=&q=...)</p>
      <div class="flex items-center gap-2">
        <a href="{{ route('sales.index') }}"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
          Reset
        </a>
        <button type="submit"
          class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
          Search
        </button>
      </div>
    </div>
  </form>

  <!-- KPI -->
  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-2">
      <p class="text-xs font-medium text-gray-500">Gross Sales</p>
      <p class="mt-2 text-2xl font-semibold">Rp {{ number_format((int)round($kpi->grossSales ?? 0), 0, ',', '.') }}</p>
      <p class="mt-1 text-xs text-gray-500">{{ (int)($kpi->trxCount ?? 0) }} transactions</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Service</p>
      <p class="mt-2 text-xl font-semibold">Rp {{ number_format((int)round($kpi->serviceTotal ?? 0), 0, ',', '.') }}</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Tax</p>
      <p class="mt-2 text-xl font-semibold">Rp {{ number_format((int)round($kpi->taxTotal ?? 0), 0, ',', '.') }}</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Discount</p>
      <p class="mt-2 text-xl font-semibold">Rp {{ number_format((int)round($kpi->discountTotal ?? 0), 0, ',', '.') }}</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Avg Order</p>
      @php
        $avg = ($kpi->trxCount ?? 0) ? (($kpi->grossSales ?? 0) / ($kpi->trxCount ?? 1)) : 0;
      @endphp
      <p class="mt-2 text-xl font-semibold">Rp {{ number_format((int)round($avg), 0, ',', '.') }}</p>
    </div>
  </div>

  <!-- Payment breakdown -->
  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @foreach (['CASH' => 'Cash', 'EDC' => 'EDC', 'QRIS' => 'QRIS', 'OTHER' => 'Other'] as $k => $label)
      <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-medium text-gray-500">{{ $label }}</p>
        <p class="mt-2 text-xl font-semibold">
          Rp {{ number_format((int)round($paymentBreakdown[$k]['amountTotal'] ?? 0), 0, ',', '.') }}
        </p>
        <p class="mt-1 text-xs text-gray-500">{{ (int)($paymentBreakdown[$k]['trxCount'] ?? 0) }} trx</p>
      </div>
    @endforeach
  </div>

  <!-- Table -->
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
      <div>
        <h2 class="text-sm font-semibold text-gray-900">Transactions</h2>
        @php
          $rowCount = is_object($rows) && method_exists($rows, 'count')
          ? $rows->count()
          : (is_array($rows) ? count($rows) : 0);

          // if paginator exists, show “x of y”
          $totalCount = is_object($rows) && method_exists($rows, 'total')
          ? $rows->total()
             : null;
          @endphp

        <p class="mt-0.5 text-xs text-gray-500">
          Menampilkan {{ $rowCount }} baris
        @if(!is_null($totalCount))
        dari {{ $totalCount }} transaksi
        @endif
        </p>
      </div>
    </div>

    <div class="p-2">
      <table class="w-full table-fixed">
        <thead>
          <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
            <th class="w-[120px] px-2 py-2">Created</th>
            <th class="w-[120px] px-2 py-2 hidden md:table-cell">Closed</th>
            <th class="w-[90px] px-2 py-2">Invoice</th>
            <th class="w-[120px] px-2 py-2">Table</th>
            <th class="w-[120px] px-2 py-2 hidden lg:table-cell">Cashier</th>
            <th class="w-[120px] px-2 py-2">Payment</th>
            <th class="w-[110px] px-2 py-2 text-right hidden xl:table-cell">Gross</th>
            <th class="w-[110px] px-2 py-2 text-right hidden xl:table-cell">Disc</th>
            <th class="w-[110px] px-2 py-2 text-right hidden xl:table-cell">Svc</th>
            <th class="w-[110px] px-2 py-2 text-right hidden xl:table-cell">Tax</th>
            <th class="w-[120px] px-2 py-2 text-right">Total</th>
            <th class="w-[80px] px-2 py-2 text-right">Action</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          @forelse ($rows as $r)
            @php
              // Format datetime local (no GMT)
              $created = $r->created ? \Carbon\Carbon::parse($r->created)->format('d/m/Y H:i') : '-';
              $closed  = $r->closedTime ? \Carbon\Carbon::parse($r->closedTime)->format('d/m/Y H:i') : '-';
            @endphp
            <tr class="text-sm text-gray-700 hover:bg-gray-50">
              <td class="px-2 py-2 whitespace-nowrap">{{ $created }}</td>
              <td class="px-2 py-2 whitespace-nowrap hidden md:table-cell">{{ $closed }}</td>
              <td class="px-2 py-2 whitespace-nowrap font-medium text-gray-900">{{ $r->invoice_id }}</td>
              <td class="px-2 py-2 whitespace-nowrap truncate">{{ $r->tableName ?: '-' }}</td>

              <td class="px-2 py-2 whitespace-nowrap hidden lg:table-cell">{{ $r->cashier ?: '-' }}</td>

              <td class="px-2 py-2 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ $r->paymentBucket ?? '-' }}</div>
                <div class="text-xs text-gray-500 truncate">{{ $r->paymentMethod ?? '-' }}</div>
              </td>

              <td class="px-2 py-2 whitespace-nowrap text-right hidden xl:table-cell">Rp {{ number_format((int)round($r->subtotal ?? 0), 0, ',', '.') }}</td>
              <td class="px-2 py-2 whitespace-nowrap text-right hidden xl:table-cell">Rp {{ number_format((int)round($r->discountAmount ?? 0), 0, ',', '.') }}</td>
              <td class="px-2 py-2 whitespace-nowrap text-right hidden xl:table-cell">Rp {{ number_format((int)round($r->serviceChargeAmount ?? 0), 0, ',', '.') }}</td>
              <td class="px-2 py-2 whitespace-nowrap text-right hidden xl:table-cell">Rp {{ number_format((int)round($r->tax1Amount ?? 0), 0, ',', '.') }}</td>

              <td class="px-2 py-2 whitespace-nowrap text-right font-medium">Rp {{ number_format((int)round($r->total ?? 0), 0, ',', '.') }}</td>

              <td class="px-2 py-2 whitespace-nowrap text-right">
                  @if(!empty($r->invoice_id))
                  <button
                  type="button"
                  class="text-sm font-medium text-gray-900 hover:underline"
                  @click="openReceipt({{ (int)$r->invoice_id }})"
                  >
                  View
                 </button>
                  @else
                  <span class="text-xs text-gray-400">No invoice</span>
                  @endif
                  </td>
            </tr>
          @empty
            <tr>
              <td colspan="12" class="px-4 py-6 text-sm text-gray-500">No results.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if(is_object($rows) && method_exists($rows, 'links'))
      <div class="flex items-center justify-between border-t border-gray-100 px-4 py-3 text-sm">
      <p class="text-gray-600">
      Halaman {{ $rows->currentPage() }} dari {{ $rows->lastPage() }}
      </p>
      <div class="flex items-center gap-2">
      {{ $rows->onEachSide(1)->links() }}
       </div>
      </div>
     @endif
  </div>

  <!-- Receipt Modal -->
  
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
