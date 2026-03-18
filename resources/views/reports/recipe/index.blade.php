@extends('layouts.app')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h1 class="text-lg font-semibold">{{ $title }}</h1>

    <div class="flex flex-wrap items-end gap-2">
      <a
        href="{{ route('reports.recipe.export', request()->query()) }}"
        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Export CSV
      </a>
    </div>
  </div>

  <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
      <div class="lg:col-span-2">
        <label class="block text-xs font-medium text-gray-600">Search</label>
        <input
          type="text"
          name="q"
          value="{{ $q }}"
          placeholder="Recipe or ingredient..."
          class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm"
        >
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Sales</label>
        <select name="sales" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="yes" {{ $sales === 'yes' ? 'selected' : '' }}>Yes</option>
          <option value="no" {{ $sales === 'no' ? 'selected' : '' }}>No</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Purchased</label>
        <select name="purchased" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="yes" {{ $purchased === 'yes' ? 'selected' : '' }}>Yes</option>
          <option value="no" {{ $purchased === 'no' ? 'selected' : '' }}>No</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600">Stocked</label>
        <select name="stocked" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="yes" {{ $stocked === 'yes' ? 'selected' : '' }}>Yes</option>
          <option value="no" {{ $stocked === 'no' ? 'selected' : '' }}>No</option>
        </select>
      </div>

      <div class="flex items-end">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" name="hide_zero" value="1" {{ $hideZero ? 'checked' : '' }}>
          Hide zero cost
        </label>
      </div>
    </div>

    <div class="mt-4 flex items-center gap-2">
      <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
        Apply
      </button>
      <a
        href="{{ route('reports.recipe') }}"
        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Clear
      </a>
    </div>
  </form>

  @if($byRecipe->isEmpty())
    <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-500 shadow-sm">
      No recipe data found.
    </div>
  @else
   @foreach($byRecipe as $recipeName => $items)
    @php
        $first = $items->first();
        $expectedTotal = $items->sum('expectedTotal');
        $actualTotal = $items->sum('actualTotal');
        $production = (float) $first->production ?: 0;

        $expectedPerUnit = $production > 0 ? $expectedTotal / $production : 0;
        $actualPerUnit   = $production > 0 ? $actualTotal / $production : 0;
        $variance = $actualPerUnit - $expectedPerUnit;
        
        
        
    @endphp

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm mb-6">
        <div class="border-b bg-gray-50 px-4 py-3">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">{{ $recipeName }}</h2>
                    <div class="mt-1 text-xs text-gray-500">
                        Sales: {{ $first->sales }} |
                        Purchased: {{ $first->purchased }} |
                        Stocked: {{ $first->stocked }} |
                        Production: {{ number_format((float)$first->production, 2) }} {{ $first->uom }}
                    </div>
                </div>

                <!-- <div class="text-right text-xs">
                    <div><span class="text-gray-500">Expected:</span> <span class="font-semibold">{{ number_format($expectedTotal, 2) }}</span></div>
                    <div><span class="text-gray-500">Actual:</span> <span class="font-semibold">{{ number_format($actualTotal, 2) }}</span></div>
                </div> -->
                <div class="text-xs {{ $variance > 0 ? 'text-red-500' : 'text-green-500' }}">
                    Var: {{ number_format($variance, 2) }}
            </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Expected</div>
                    <div class="font-semibold text-gray-900">
                        {{ number_format((float) $expectedTotal, 2) }}
                    </div>
                    <div class="text-xs text-gray-400">
                    /unit: {{ number_format((float) $expectedPerUnit, 2) }}
                </div>
    </div>

    <div class="text-right">
        <div class="text-xs text-gray-500">Actual</div>
        <div class="font-semibold text-gray-900">
            {{ number_format((float) $actualTotal, 2) }}
        </div>
        <div class="text-xs text-gray-400">
            /unit: {{ number_format((float) $actualPerUnit, 2) }}
        </div>
    </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-600">
                    <tr>
                        <th class="px-4 py-2 text-left">Code</th>
                        <th class="px-4 py-2 text-left">Item</th>
                        <th class="px-4 py-2 text-right">Rec Qty</th>
                        <th class="px-4 py-2 text-left">Recipe UOM</th>
                        <th class="px-4 py-2 text-right">Inv Qty</th>
                        <th class="px-4 py-2 text-left">Inv UOM</th>
                        <th class="px-4 py-2 text-right">Unit Cost</th>
                        <th class="px-4 py-2 text-right">Avg Cost</th>
                        <th class="px-4 py-2 text-right">Expected</th>
                        <th class="px-4 py-2 text-right">Actual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-xs text-gray-500">{{ $r->itemCode }}</td>
                            <td class="px-4 py-2">{{ $r->itemName }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$r->RecQty, 2) }}</td>
                            <td class="px-4 py-2">{{ $r->recipeUom }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$r->InvQty, 2) }}</td>
                            <td class="px-4 py-2">{{ $r->InvUom }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$r->unitCost, 2) }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$r->averageCost, 2) }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format((float)$r->expectedTotal, 2) }}</td>
                            <td class="px-4 py-2 text-right font-semibold">{{ number_format((float)$r->actualTotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="8" class="px-4 py-2 text-right">Recipe Total</td>
                        <td class="px-4 py-2 text-right">{{ number_format($expectedTotal, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($actualTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endforeach

    <div class="flex justify-end">
      <div class="rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold shadow-sm">
        Grand Total: {{ number_format((float) $grandTotal, 2) }}
      </div>
    </div>
  @endif
</div>
@endsection