<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt #{{ $invoice_id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 py-8">
    <div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-5 text-center">
            <h1 class="text-xl font-semibold text-gray-900">Gundaling Farmstead</h1>
            <p class="mt-1 text-sm text-gray-500">Sales Receipt</p>
        </div>

        <div class="grid grid-cols-1 gap-4 border-b border-gray-200 px-6 py-5 sm:grid-cols-2">
            <div class="space-y-1 text-sm text-gray-700">
                <div><span class="font-medium text-gray-900">Invoice:</span> {{ $sale->invoice_id }}</div>
                <div><span class="font-medium text-gray-900">Date:</span> {{ $sale->date ? \Carbon\Carbon::parse($sale->date)->format('d/m/Y H:i:s') : '-' }}</div>
                <div><span class="font-medium text-gray-900">Closed Time:</span> {{ $sale->closedTime ? \Carbon\Carbon::parse($sale->closedTime)->format('d/m/Y H:i:s') : '-' }}</div>
                <div><span class="font-medium text-gray-900">Table:</span> {{ $sale->tableName ?: '-' }}</div>
            </div>

            <div class="space-y-1 text-sm text-gray-700">
                <div><span class="font-medium text-gray-900">Cashier:</span> {{ $sale->cashier ?: '-' }}</div>
                <div><span class="font-medium text-gray-900">Member:</span> {{ $sale->member ?: '-' }}</div>
                <div><span class="font-medium text-gray-900">Pax:</span> {{ $sale->pax ?: '-' }}</div>
                <div><span class="font-medium text-gray-900">Print Count:</span> {{ $sale->printCount ?? 0 }}</div>
            </div>
        </div>

        <div class="px-6 py-5">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Items</h2>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Unit Price</th>
                            <th class="px-4 py-3 text-right">Discount</th>
                            <th class="px-4 py-3 text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $item)
                            @php
                                $lineTotal = ((float) $item->quantity * (float) $item->unitPrice) - (float) $item->discountAmount;
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $item->description }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $item->salesType ?: '-' }}
                                        @if($item->category)
                                            • {{ $item->category }}
                                        @endif
                                        @if($item->department)
                                            • {{ $item->department }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((float) $item->unitPrice, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((float) $item->discountAmount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($lineTotal, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 border-t border-gray-200 px-6 py-5 md:grid-cols-2">
            <div>
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Payments</h2>

                <div class="space-y-2">
                    @forelse($payments as $payment)
                        <div class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm">
                            <div>
                                <div class="font-medium text-gray-900">{{ $payment->method }}</div>
                                <div class="text-xs text-gray-500">{{ $payment->bucket }}</div>
                            </div>
                            <div class="font-medium text-gray-900">{{ number_format((float) $payment->amount, 2, ',', '.') }}</div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-500">
                            No payments found.
                        </div>
                    @endforelse
                </div>
            </div>

            <div>
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Summary</h2>

                <div class="rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between px-4 py-3 text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium text-gray-900">{{ number_format((float) $sale->subtotal, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm">
                        <span class="text-gray-600">Discount</span>
                        <span class="font-medium text-gray-900">{{ number_format((float) $sale->discountAmount, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm">
                        <span class="text-gray-600">Service Charge</span>
                        <span class="font-medium text-gray-900">{{ number_format((float) $sale->serviceChargeAmount, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm">
                        <span class="text-gray-600">Tax</span>
                        <span class="font-medium text-gray-900">{{ number_format((float) $sale->tax1Amount, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm font-semibold">
                        <span class="text-gray-900">Bill Total</span>
                        <span class="text-gray-900">{{ number_format((float) $bill_total, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm">
                        <span class="text-gray-600">Paid Total</span>
                        <span class="font-medium text-gray-900">{{ number_format((float) $paid_total, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 text-sm font-semibold {{ $diff == 0 ? 'text-green-700' : 'text-red-700' }}">
                        <span>Difference</span>
                        <span>{{ number_format((float) $diff, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 px-6 py-4">
            <a href="javascript:window.close()" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Close
            </a>

            <button onclick="window.print()"
                    class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                Print
            </button>
        </div>
    </div>
</body>
</html>