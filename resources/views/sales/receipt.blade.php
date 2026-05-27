<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Receipt #{{ $invoice_id }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    body { font-family: 'Sora', ui-sans-serif, system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; padding: 0; margin: 0; }
      .print-card { box-shadow: none !important; border: none !important; border-radius: 0 !important; max-width: 100% !important; }
      .print-wrapper { padding: 0 !important; }
    }
  </style>
</head>
<body class="bg-gray-100 print-wrapper" style="padding: 2rem 0;">

  <div class="mx-auto max-w-2xl px-4">
    <div class="print-card overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

      {{-- Brand header --}}
      <div class="flex flex-col items-center gap-3 px-6 py-8 text-center"
        style="background: linear-gradient(135deg, #0f1117 0%, #1a1f2e 100%)">
        <img src="{{ asset('images/brand/62.png') }}" alt="Gundaling Farmstead"
          class="h-14 w-auto" style="filter:brightness(0) invert(1); opacity:0.95">
        <div>
          <h1 class="text-lg font-semibold text-white">Gundaling Farmstead</h1>
          <p class="mt-0.5 text-sm" style="color:rgba(255,255,255,0.50)">Official Sales Receipt</p>
        </div>
        <span class="mt-1 rounded-full px-3 py-1 text-xs font-semibold"
          style="background:rgba(34,197,94,0.2); color:#4ade80">
          Invoice #{{ $invoice_id }}
        </span>
      </div>

      {{-- Meta info --}}
      <div class="grid grid-cols-2 gap-x-6 gap-y-4 border-b border-gray-100 px-6 py-5 sm:grid-cols-4">
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Date opened</p>
          <p class="mt-1 text-sm font-medium text-gray-900">
            {{ $sale->date ? \Carbon\Carbon::parse($sale->date)->format('d/m/Y') : '-' }}
          </p>
          <p class="text-xs text-gray-400">{{ $sale->date ? \Carbon\Carbon::parse($sale->date)->format('H:i:s') : '' }}</p>
        </div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Closed</p>
          <p class="mt-1 text-sm font-medium text-gray-900">
            {{ $sale->closedTime ? \Carbon\Carbon::parse($sale->closedTime)->format('d/m/Y') : '-' }}
          </p>
          <p class="text-xs text-gray-400">{{ $sale->closedTime ? \Carbon\Carbon::parse($sale->closedTime)->format('H:i:s') : '' }}</p>
        </div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Table</p>
          <p class="mt-1 text-sm font-medium text-gray-900">{{ $sale->tableName ?: '-' }}</p>
          <p class="text-xs text-gray-400">Pax: {{ $sale->pax ?: '-' }}</p>
        </div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Cashier</p>
          <p class="mt-1 text-sm font-medium text-gray-900">{{ $sale->cashier ?: '-' }}</p>
          <p class="text-xs text-gray-400">{{ $sale->member ? 'Member: '.$sale->member : 'Print #'.($sale->printCount ?? 0) }}</p>
        </div>
      </div>

      {{-- Items --}}
      <div class="px-6 py-5">
        <p class="mb-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Items</p>
        <div class="overflow-hidden rounded-xl border border-gray-100">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400 bg-gray-50"
                style="border-bottom:1px solid #f3f4f6">
                <th class="px-4 py-3">Description</th>
                <th class="px-4 py-3 text-right w-14">Qty</th>
                <th class="px-4 py-3 text-right w-28">Unit Price</th>
                <th class="px-4 py-3 text-right w-24">Disc</th>
                <th class="px-4 py-3 text-right w-28">Line Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($items as $item)
                @php
                  $lineTotal = ((float)$item->quantity * (float)$item->unitPrice) - (float)$item->discountAmount;
                  $meta = implode(' • ', array_filter([
                    $item->salesType  ?: null,
                    $item->category   ?: null,
                    $item->department ?: null,
                  ]));
                @endphp
                <tr class="hover:bg-gray-50/50">
                  <td class="px-4 py-3">
                    <p class="font-medium text-gray-900">{{ $item->description }}</p>
                    @if($meta)
                      <p class="mt-0.5 text-xs text-gray-400">{{ $meta }}</p>
                    @endif
                  </td>
                  <td class="px-4 py-3 text-right text-gray-700">
                    {{ number_format((float)$item->quantity, 0, ',', '.') }}
                  </td>
                  <td class="px-4 py-3 text-right text-gray-700">
                    {{ number_format((float)$item->unitPrice, 0, ',', '.') }}
                  </td>
                  <td class="px-4 py-3 text-right {{ (float)$item->discountAmount > 0 ? 'text-red-500' : 'text-gray-300' }}">
                    {{ (float)$item->discountAmount > 0 ? number_format((float)$item->discountAmount, 0, ',', '.') : '—' }}
                  </td>
                  <td class="px-4 py-3 text-right font-semibold text-gray-900">
                    {{ number_format($lineTotal, 0, ',', '.') }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">No items found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Payments + Summary --}}
      <div class="grid grid-cols-1 gap-5 border-t border-gray-100 px-6 py-5 md:grid-cols-2">

        {{-- Payments --}}
        <div>
          <p class="mb-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Payment</p>
          @php
            $pBadge = [
              'CASH' => 'bg-green-100 text-green-700',
              'EDC'  => 'bg-blue-100 text-blue-700',
              'QRIS' => 'bg-amber-100 text-amber-700',
            ];
          @endphp
          <div class="space-y-2">
            @forelse($payments as $payment)
              <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/60 px-4 py-3">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                    {{ $pBadge[strtoupper($payment->bucket)] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ $payment->bucket }}
                  </span>
                  <span class="text-xs text-gray-500">{{ $payment->method }}</span>
                </div>
                <span class="text-sm font-semibold text-gray-900">
                  {{ number_format((float)$payment->amount, 0, ',', '.') }}
                </span>
              </div>
            @empty
              <div class="rounded-xl border border-gray-100 px-4 py-3 text-sm text-gray-400">No payments found.</div>
            @endforelse
          </div>
        </div>

        {{-- Summary --}}
        <div>
          <p class="mb-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Summary</p>
          <div class="overflow-hidden rounded-xl border border-gray-100">
            <div class="divide-y divide-gray-100">
              @php
                $summaryRows = [
                  ['label' => 'Subtotal',      'value' => (float)$sale->subtotal,            'red' => false],
                  ['label' => 'Discount',       'value' => (float)$sale->discountAmount,      'red' => true],
                  ['label' => 'Service Charge', 'value' => (float)$sale->serviceChargeAmount, 'red' => false],
                  ['label' => 'Tax',            'value' => (float)$sale->tax1Amount,          'red' => false],
                ];
              @endphp
              @foreach($summaryRows as $sr)
                <div class="flex items-center justify-between px-4 py-2.5">
                  <span class="text-sm text-gray-500">{{ $sr['label'] }}</span>
                  <span class="text-sm font-medium {{ $sr['red'] && $sr['value'] > 0 ? 'text-red-500' : 'text-gray-900' }}">
                    {{ number_format($sr['value'], 0, ',', '.') }}
                  </span>
                </div>
              @endforeach
            </div>

            <div class="flex items-center justify-between px-4 py-3 bg-gray-50"
              style="border-top:2px solid #e5e7eb">
              <span class="font-semibold text-gray-900">Bill Total</span>
              <span class="font-semibold text-gray-900">{{ number_format((float)$bill_total, 0, ',', '.') }}</span>
            </div>

            <div class="flex items-center justify-between border-t border-gray-100 px-4 py-2.5">
              <span class="text-sm text-gray-500">Paid Total</span>
              <span class="text-sm font-medium text-gray-900">{{ number_format((float)$paid_total, 0, ',', '.') }}</span>
            </div>

            <div class="flex items-center justify-between rounded-b-xl px-4 py-3
              {{ $diff == 0 ? 'bg-green-50' : 'bg-red-50' }}"
              style="border-top:1px solid {{ $diff == 0 ? '#dcfce7' : '#fee2e2' }}">
              <span class="text-sm font-semibold {{ $diff == 0 ? 'text-green-700' : 'text-red-700' }}">
                {{ $diff == 0 ? '✓ Balanced' : 'Difference' }}
              </span>
              <span class="text-sm font-semibold {{ $diff == 0 ? 'text-green-700' : 'text-red-700' }}">
                {{ number_format((float)$diff, 0, ',', '.') }}
              </span>
            </div>
          </div>
        </div>

      </div>

      {{-- Footer actions --}}
      <div class="no-print flex items-center justify-between border-t border-gray-100 px-6 py-4">
        <a href="javascript:window.close()"
          class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close
        </a>
        <button onclick="window.print()"
          class="inline-flex items-center gap-1.5 rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          Print
        </button>
      </div>

    </div>

    <p class="no-print mt-4 text-center text-xs text-gray-400">GFS Dashboard — {{ now()->format('d M Y H:i') }}</p>
  </div>

</body>
</html>
