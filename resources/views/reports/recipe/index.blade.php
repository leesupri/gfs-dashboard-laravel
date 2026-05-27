@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }" class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h1 class="text-lg font-semibold">{{ $title }}</h1>

    <div class="flex items-center gap-2">
      <a
        href="{{ route('reports.recipe.export', request()->query()) }}"
        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Export CSV
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

  <div
    x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
  >
    <form method="GET" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2 lg:col-span-2">
          <label for="f-q" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Search</label>
          <input
            id="f-q"
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="Recipe or ingredient..."
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)"
          >
        </div>

        <div>
          <label for="f-sales" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Sales</label>
          <select id="f-sales" name="sales"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
            <option value="">All</option>
            <option value="yes" {{ $sales === 'yes' ? 'selected' : '' }}>Yes</option>
            <option value="no" {{ $sales === 'no' ? 'selected' : '' }}>No</option>
          </select>
        </div>

        <div>
          <label for="f-purchased" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Purchased</label>
          <select id="f-purchased" name="purchased"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
            <option value="">All</option>
            <option value="yes" {{ $purchased === 'yes' ? 'selected' : '' }}>Yes</option>
            <option value="no" {{ $purchased === 'no' ? 'selected' : '' }}>No</option>
          </select>
        </div>

        <div>
          <label for="f-stocked" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Stocked</label>
          <select id="f-stocked" name="stocked"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
            <option value="">All</option>
            <option value="yes" {{ $stocked === 'yes' ? 'selected' : '' }}>Yes</option>
            <option value="no" {{ $stocked === 'no' ? 'selected' : '' }}>No</option>
          </select>
        </div>

        <div class="flex items-end">
          <label class="inline-flex items-center gap-2 text-sm" style="color:var(--text-secondary)">
            <input type="checkbox" name="hide_zero" value="1" {{ $hideZero ? 'checked' : '' }}
              class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            Hide zero cost
          </label>
        </div>
      </div>

      <div class="mt-4 flex items-center justify-end gap-2 border-t pt-4" style="border-color:var(--card-border)">
        <a
          href="{{ route('reports.recipe') }}"
          class="rounded-lg border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
          style="border-color:var(--card-border); color:var(--text-secondary)"
        >
          Clear
        </a>
        <button type="submit"
          class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
          Apply
        </button>
      </div>
    </form>
  </div>

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
