@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-5">
            <div class="text-center">
                <h1 class="text-lg font-semibold text-gray-900">Quinos Point Of Sale</h1>
                <div class="mt-1 text-base font-bold tracking-wide text-gray-900">PRODUCTION DETAILS</div>
            </div>
            <div class="mb-4">
    <a href="{{ route('reports.productionCard.index') }}"
       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        ← Back to List
    </a>
</div>

            <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">Warehouse:</span>
                    <span class="text-gray-900">{{ $header->warehouse ?: '-' }}</span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Date:</span>
                    <span class="text-gray-900">
                        {{ $header->date ? \Carbon\Carbon::parse($header->date)->format('d-M-Y') : '-' }}
                    </span>
                </div>
                <div>
        <span class="font-semibold text-gray-700">Saved By:</span>
        <span class="text-gray-900">{{ $header->savedBy ?: '-' }}</span>
    </div>
                <div class="md:text-right">
                    <span class="font-semibold text-gray-700">No.:</span>
                    <span class="text-gray-900">{{ $header->id }}</span>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 text-xs text-gray-500 flex justify-end">
            Report time:
            <span class="ml-1 text-gray-700">{{ now()->format('l d F Y g:i A') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-y border-gray-200 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Item Name</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-right">Quantity</th>
                        <th class="px-4 py-3 text-left">UOM</th>
                        <th class="px-4 py-3 text-left">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr class="border-b border-gray-200 bg-gray-100">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $product->product_code ?: '-' }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $product->product_name ?: '-' }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $product->category ?: '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">
                                {{ number_format((float)$product->product_qty, 2, '.', ',') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $product->product_uom ?: '-' }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $product->product_description ?: '-' }}</td>
                        </tr>

                        @foreach($product->recipes as $recipe)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-900">{{ $recipe->recipe_code ?: '-' }}</td>
                                <td class="px-4 py-2 text-gray-900">{{ $recipe->recipe_name ?: '-' }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $recipe->recipe_category ?: '-' }}</td>
                                <td class="px-4 py-2 text-right text-gray-700">
                                    {{ number_format((float)$recipe->recipe_qty, 2, '.', ',') }}
                                </td>
                                <td class="px-4 py-2 text-gray-700">{{ $recipe->recipe_uom ?: '-' }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $recipe->recipe_description ?: '-' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-6 py-4">
            <div class="text-sm font-semibold text-gray-800">Notes</div>
            <div class="mt-2 min-h-[96px] rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700 whitespace-pre-line">
                {{ $header->notes ?: '-' }}
            </div>
        </div>
    </div>
</div>
@endsection