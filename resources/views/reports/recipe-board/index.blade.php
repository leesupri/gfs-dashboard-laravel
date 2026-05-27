@extends('layouts.app')

@section('content')
<div class="space-y-5">

  {{-- ── Page header ── --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-lg font-semibold">Recipe Board</h1>
      <p class="text-xs text-gray-500 mt-0.5">All recipes with ingredients and unit-of-measure breakdown</p>
    </div>

    <a
      href="{{ route('reports.recipe-board.export', request()->query()) }}"
      class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
      </svg>
      Export CSV
    </a>
  </div>

  {{-- ── Filters ── --}}
  <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">

      <div class="col-span-2 sm:col-span-3 lg:col-span-2">
        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
        <input
          type="text"
          name="q"
          value="{{ $q }}"
          placeholder="Recipe name, ingredient, category…"
          class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
        >
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
        <select name="category" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All categories</option>
          @foreach ($categories as $cat)
            <option value="{{ $cat->id }}" {{ $categoryFilter == $cat->id ? 'selected' : '' }}>
              {{ $cat->name }}{{ $cat->code ? ' ('.$cat->code.')' : '' }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Active</label>
        <select name="active" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="yes" {{ $activeFilter === 'yes' ? 'selected' : '' }}>Active</option>
          <option value="no"  {{ $activeFilter === 'no'  ? 'selected' : '' }}>Inactive</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Sales</label>
        <select name="sales" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="yes" {{ $sales === 'yes' ? 'selected' : '' }}>Yes</option>
          <option value="no"  {{ $sales === 'no'  ? 'selected' : '' }}>No</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Purchased</label>
        <select name="purchased" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="yes" {{ $purchased === 'yes' ? 'selected' : '' }}>Yes</option>
          <option value="no"  {{ $purchased === 'no'  ? 'selected' : '' }}>No</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Stocked</label>
        <select name="stocked" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="yes" {{ $stocked === 'yes' ? 'selected' : '' }}>Yes</option>
          <option value="no"  {{ $stocked === 'no'  ? 'selected' : '' }}>No</option>
        </select>
      </div>

    </div>

    <div class="mt-3 flex flex-wrap items-center gap-3">

      {{-- Has conversi toggle ── --}}
      <label class="inline-flex items-center gap-2 cursor-pointer select-none text-sm text-gray-600">
        <input
          type="checkbox"
          name="has_conversi"
          value="1"
          {{ $hasConversi ? 'checked' : '' }}
          class="rounded border-gray-300 text-gray-900 focus:ring-gray-400"
        >
        <span>Has conversi ingredient</span>
        <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-medium text-violet-700 ring-1 ring-inset ring-violet-200">conversi</span>
      </label>

      <div class="flex gap-2 ml-auto">
        <button
          type="submit"
          class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 transition"
        >
          Apply
        </button>
        <a
          href="{{ route('reports.recipe-board') }}"
          class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
        >
          Clear
        </a>
      </div>
    </div>
  </form>

  {{-- ── Summary counts ── --}}
  <div class="flex flex-wrap gap-3 text-sm">
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 shadow-sm">
      <span class="text-gray-400 text-xs">Showing</span>
      <span class="ml-2 font-semibold">{{ $byRecipe->count() }}</span>
      <span class="text-gray-400 text-xs">of {{ $total }} recipes</span>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 shadow-sm">
      <span class="text-gray-400 text-xs">Active</span>
      <span class="ml-2 font-semibold text-emerald-600">
        {{ $byRecipe->filter(fn($items) => $items->first()->isActive === 'yes')->count() }}
      </span>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 shadow-sm">
      <span class="text-gray-400 text-xs">Inactive</span>
      <span class="ml-2 font-semibold text-gray-400">
        {{ $byRecipe->filter(fn($items) => $items->first()->isActive === 'no')->count() }}
      </span>
    </div>
  </div>

  {{-- ── Cards ── --}}
  @if ($byRecipe->isEmpty())
    <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center shadow-sm">
      <svg class="mx-auto mb-3 w-10 h-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p class="text-sm text-gray-400">No recipes match your filters.</p>
    </div>
  @else

    <div
      x-data="recipeBoard()"
      class="space-y-3"
    >
      {{-- Expand / collapse all ── --}}
      <div class="flex justify-end gap-2 text-xs">
        <button
          @click="expandAll()"
          class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-gray-600 hover:bg-gray-50 transition"
        >
          Expand all
        </button>
        <button
          @click="collapseAll()"
          class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-gray-600 hover:bg-gray-50 transition"
        >
          Collapse all
        </button>
      </div>

      {{-- ── Recipe cards ── --}}
      @foreach ($byRecipe as $recipeId => $items)
        @php
          $first      = $items->first();
          $isInactive = $first->isActive === 'no';
        @endphp

        <div
          x-data="recipeCard({{ $recipeId }})"
          @board-expand.window="open = true"
          @board-collapse.window="open = false"
          class="rounded-2xl border bg-white shadow-sm overflow-hidden transition
            {{ $isInactive ? 'border-gray-200 opacity-60' : 'border-gray-200' }}"
        >

          {{-- Card header ── --}}
          <div
            @click="open = !open"
            class="cursor-pointer px-5 py-4 hover:bg-gray-50 transition select-none
              {{ $isInactive ? 'bg-gray-50' : '' }}"
          >
            <div class="flex items-start justify-between gap-4">

              {{-- Left: name + badges + meta ── --}}
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <svg
                    :class="open ? 'rotate-90' : ''"
                    class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200 shrink-0"
                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                  <span class="font-semibold text-sm text-gray-900">{{ $first->recipeName }}</span>

                  {{-- Active / inactive badge ── --}}
                  @if ($first->isActive === 'yes')
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">active</span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500 ring-1 ring-inset ring-gray-300">inactive</span>
                  @endif
                </div>

                {{-- Flag badges ── --}}
                <div class="mt-1.5 ml-5 flex flex-wrap gap-1.5">
                  @if ($first->sales === 'yes')
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">sales</span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-200">no sales</span>
                  @endif

                  @if ($first->purchased === 'yes')
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-200">purchased</span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-200">no purchase</span>
                  @endif

                  @if ($first->stocked === 'yes')
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700 ring-1 ring-inset ring-amber-200">stocked</span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-200">no stock</span>
                  @endif

                  {{-- Conversi indicator on the recipe itself ── --}}
                  @if ($first->stocked === 'yes' && $first->sales === 'no' && $first->purchased === 'no')
                    <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-medium text-violet-700 ring-1 ring-inset ring-violet-200">conversi</span>
                  @endif
                </div>

                {{-- Category ── --}}
                @if ($first->categoryName)
                  <div class="mt-1.5 ml-5 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span class="text-[11px] text-gray-500">{{ $first->categoryName }}</span>
                    @if ($first->categoryCode)
                      <span class="font-mono text-[10px] text-gray-400">({{ $first->categoryCode }})</span>
                    @endif
                  </div>
                @endif

                <div class="mt-1 ml-5 text-[11px] text-gray-400">
                  Production: {{ number_format((float) $first->production, 2) }} {{ $first->uom }}
                  &nbsp;·&nbsp;
                  {{ $items->count() }} ingredient{{ $items->count() !== 1 ? 's' : '' }}
                </div>
              </div>

              {{-- Right: production qty ── --}}
              <div class="text-right shrink-0">
                <div class="text-xs font-medium text-gray-700">
                  {{ number_format((float) $first->production, 2) }}
                  <span class="text-gray-400 font-normal">{{ $first->uom }}</span>
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">
                  {{ $items->count() }} ingredient{{ $items->count() !== 1 ? 's' : '' }}
                </div>
              </div>

            </div>
          </div>

          {{-- Collapsible ingredient table ── --}}
          <div
            x-show="open"
            x-collapse
            class="border-t border-gray-100"
          >
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500">
                  <tr>
                    <th class="px-5 py-2 text-left font-medium w-8">#</th>
                    <th class="px-4 py-2 text-left font-medium">Code</th>
                    <th class="px-4 py-2 text-left font-medium">Ingredient</th>
                    <th class="px-4 py-2 text-left font-medium">Type</th>
                    <th class="px-4 py-2 text-right font-medium whitespace-nowrap">Recipe qty</th>
                    <th class="px-4 py-2 text-left font-medium">Recipe UOM</th>
                    <th class="px-4 py-2 text-right font-medium whitespace-nowrap">Inv qty</th>
                    <th class="px-4 py-2 text-left font-medium">Inv UOM</th>
                    <th class="px-4 py-2 text-left font-medium">Status</th>
                  </tr>
                </thead>

                {{-- One tbody per ingredient so conversi preview stays scoped ── --}}
                @foreach ($items as $r)
                  @php
                    $isConversi = $r->itemStocked === 'yes'
                               && $r->itemSales   === 'no'
                               && $r->itemPurchased === 'no';

                    $isCombo = $r->itemCombo  === 'yes'
                            && $r->itemSales  === 'yes';

                    $subItems = $isConversi && isset($conversiDetails[$r->itemId])
                        ? $conversiDetails[$r->itemId]
                        : collect();

                    $comboSlots = $isCombo && isset($comboData[$r->itemId])
                        ? $comboData[$r->itemId]
                        : [];
                  @endphp

                  <tbody
                    @if ($isConversi && $subItems->isNotEmpty())
                      x-data="conversiPreview({{ $r->itemId }})"
                    @elseif ($isCombo && !empty($comboSlots))
                      x-data="comboPreview({{ $r->itemId }})"
                    @endif
                    class="divide-y divide-gray-100"
                  >
                    {{-- Main ingredient row ── --}}
                    <tr
                      @if ($isConversi && $subItems->isNotEmpty())
                        @click="toggle()"
                        class="hover:bg-violet-50 transition cursor-pointer"
                      @elseif ($isCombo && !empty($comboSlots))
                        @click="toggle()"
                        class="hover:bg-orange-50 transition cursor-pointer"
                      @else
                        class="hover:bg-gray-50 transition"
                      @endif
                    >
                      <td class="px-5 py-2.5 text-xs text-gray-400">{{ $r->idx }}</td>
                      <td class="px-4 py-2.5 text-xs text-gray-400 font-mono">{{ $r->itemCode }}</td>
                      <td class="px-4 py-2.5 text-gray-800 font-medium">{{ $r->itemName }}</td>

                      {{-- Type column ── --}}
                      <td class="px-4 py-2.5">
                        @if ($isConversi)
                          <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-medium text-violet-700 ring-1 ring-inset ring-violet-200">
                            conversi
                            @if ($subItems->isNotEmpty())
                              <svg
                                :class="open ? 'rotate-90' : ''"
                                class="w-2.5 h-2.5 transition-transform duration-150"
                                fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                              >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                              </svg>
                            @endif
                          </span>
                        @elseif ($isCombo)
                          <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-medium text-orange-700 ring-1 ring-inset ring-orange-200">
                            combo
                            @if (!empty($comboSlots))
                              <svg
                                :class="open ? 'rotate-90' : ''"
                                class="w-2.5 h-2.5 transition-transform duration-150"
                                fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                              >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                              </svg>
                            @endif
                          </span>
                        @else
                          <span class="text-[10px] text-gray-400">—</span>
                        @endif
                      </td>

                      <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) $r->RecQty, 2) }}</td>
                      <td class="px-4 py-2.5 text-xs text-gray-500">{{ $r->recipeUom }}</td>
                      <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format((float) $r->InvQty, 2) }}</td>
                      <td class="px-4 py-2.5 text-xs text-gray-500">{{ $r->InvUom }}</td>

                      {{-- Item active status ── --}}
                      <td class="px-4 py-2.5">
                        @if ($r->itemActive === 'yes')
                          <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">active</span>
                        @else
                          <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-300">inactive</span>
                        @endif
                      </td>
                    </tr>

                    {{-- Conversi sub-recipe preview row ── --}}
                    @if ($isConversi && $subItems->isNotEmpty())
                      <tr x-show="open" x-collapse style="display: none;">
                        <td colspan="9" class="p-0 bg-violet-50/50">

                          <div class="px-10 py-3">
                            <div class="mb-2 flex items-center justify-between gap-3">
                              <div class="flex items-center gap-1.5 text-[10px] font-medium text-violet-600">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                                </svg>
                                Conversi breakdown — {{ $r->itemName }}
                              </div>
                              @if ($subItems->isNotEmpty())
                                <div class="text-[10px] text-violet-500 font-medium">
                                  Produces:
                                  <span class="font-semibold text-violet-700">
                                    {{ number_format((float) $subItems->first()->conversiProduction, 2) }}
                                    {{ $subItems->first()->conversiUom }}
                                  </span>
                                </div>
                              @endif
                            </div>

                            <table class="min-w-full text-xs rounded-lg overflow-hidden ring-1 ring-violet-100">
                              <thead class="bg-violet-100/60 text-[10px] text-violet-700">
                                <tr>
                                  <th class="px-3 py-1.5 text-left font-medium">#</th>
                                  <th class="px-3 py-1.5 text-left font-medium">Code</th>
                                  <th class="px-3 py-1.5 text-left font-medium">Item</th>
                                  <th class="px-3 py-1.5 text-right font-medium whitespace-nowrap">Recipe qty</th>
                                  <th class="px-3 py-1.5 text-left font-medium">Recipe UOM</th>
                                  <th class="px-3 py-1.5 text-right font-medium whitespace-nowrap">Inv qty</th>
                                  <th class="px-3 py-1.5 text-left font-medium">Inv UOM</th>
                                  <th class="px-3 py-1.5 text-left font-medium">Status</th>
                                </tr>
                              </thead>
                              <tbody class="divide-y divide-violet-100 bg-white">
                                @foreach ($subItems as $sub)
                                  <tr class="hover:bg-violet-50/40 transition">
                                    <td class="px-3 py-1.5 text-gray-400">{{ $sub->idx }}</td>
                                    <td class="px-3 py-1.5 font-mono text-gray-400">{{ $sub->itemCode }}</td>
                                    <td class="px-3 py-1.5 text-gray-700">{{ $sub->itemName }}</td>
                                    <td class="px-3 py-1.5 text-right tabular-nums">{{ number_format((float) $sub->RecQty, 2) }}</td>
                                    <td class="px-3 py-1.5 text-gray-400">{{ $sub->recipeUom }}</td>
                                    <td class="px-3 py-1.5 text-right tabular-nums">{{ number_format((float) $sub->InvQty, 2) }}</td>
                                    <td class="px-3 py-1.5 text-gray-400">{{ $sub->InvUom }}</td>
                                    <td class="px-3 py-1.5">
                                      @if ($sub->itemActive === 'yes')
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-1.5 py-0.5 text-[9px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">active</span>
                                      @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-1.5 py-0.5 text-[9px] text-gray-400 ring-1 ring-inset ring-gray-300">inactive</span>
                                      @endif
                                    </td>
                                  </tr>
                                @endforeach
                              </tbody>
                            </table>
                          </div>

                        </td>
                      </tr>
                    @endif

                    {{-- Combo sub-menu preview row ── --}}
                    @if ($isCombo && !empty($comboSlots))
                      <tr x-show="open" x-collapse style="display: none;">
                        <td colspan="9" class="p-0 bg-orange-50/40">
                          <div class="px-10 py-3">

                            {{-- Combo item detail header ── --}}
                            <div class="mb-3 rounded-lg border border-orange-100 bg-white px-4 py-3">
                              <div class="flex flex-wrap items-start justify-between gap-3">

                                {{-- Left: name + code + flags ── --}}
                                <div>
                                  <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 shrink-0 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-800">{{ $r->itemName }}</span>
                                    @if ($r->itemActive === 'yes')
                                      <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">active</span>
                                    @else
                                      <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500 ring-1 ring-inset ring-gray-300">inactive</span>
                                    @endif
                                  </div>

                                  @if ($r->itemCode)
                                    <div class="mt-0.5 ml-5 font-mono text-[11px] text-gray-400">{{ $r->itemCode }}</div>
                                  @endif

                                  {{-- Flag badges ── --}}
                                  <div class="mt-2 ml-5 flex flex-wrap gap-1.5">
                                    <span class="inline-flex items-center rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-semibold text-orange-700 ring-1 ring-inset ring-orange-200">combo</span>

                                    @if ($r->itemSales === 'yes')
                                      <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">sales</span>
                                    @else
                                      <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-200">no sales</span>
                                    @endif

                                    @if ($r->itemPurchased === 'yes')
                                      <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-200">purchased</span>
                                    @else
                                      <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-200">no purchase</span>
                                    @endif

                                    @if ($r->itemStocked === 'yes')
                                      <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700 ring-1 ring-inset ring-amber-200">stocked</span>
                                    @else
                                      <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-200">no stock</span>
                                    @endif
                                  </div>
                                </div>

                                {{-- Right: UOM info ── --}}
                                <div class="flex gap-4 text-right text-xs">
                                  <div>
                                    <div class="text-[10px] text-gray-400">Recipe UOM</div>
                                    <div class="font-medium text-gray-700">{{ $r->recipeUom ?: '—' }}</div>
                                  </div>
                                  <div>
                                    <div class="text-[10px] text-gray-400">Inv UOM</div>
                                    <div class="font-medium text-gray-700">{{ $r->InvUom ?: '—' }}</div>
                                  </div>
                                  <div>
                                    <div class="text-[10px] text-gray-400">Groups</div>
                                    <div class="font-semibold text-orange-600">{{ count($comboSlots) }}</div>
                                  </div>
                                </div>

                              </div>
                            </div>

                            {{-- Combo menu choice groups ── --}}
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-{{ min(count($comboSlots), 4) }}">
                              @foreach ($comboSlots as $comboSlot)
                                <x-modifier-page
                                  :page-id="$comboSlot['pageId']"
                                  :slot="$comboSlot['slot']"
                                  :all-pages="$comboPages"
                                  :all-items="$comboItems"
                                  color="orange"
                                />
                              @endforeach
                            </div>

                          </div>
                        </td>
                      </tr>
                    @endif

                  </tbody>
                @endforeach

                <tfoot class="bg-gray-50 text-xs text-gray-400">
                  <tr>
                    <td colspan="2" class="px-5 py-2">
                      Production:
                      <span class="font-medium text-gray-700">
                        {{ number_format((float) $first->production, 2) }} {{ $first->uom }}
                      </span>
                    </td>
                    <td colspan="7" class="px-4 py-2 text-right">
                      {{ $items->count() }} ingredient{{ $items->count() !== 1 ? 's' : '' }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

            {{-- ── Modifier pages section ── --}}
            @if ($first->sales === 'yes' && isset($modifierData[$recipeId]) && count($modifierData[$recipeId]) > 0)
              <div class="border-t border-gray-100">

                {{-- Modifier section header with toggle ── --}}
                <div
                  @click="openModifiers = !openModifiers"
                  class="flex cursor-pointer items-center justify-between px-5 py-3 hover:bg-gray-50 transition select-none"
                >
                  <div class="flex items-center gap-2">
                    <svg
                      :class="openModifiers ? 'rotate-90' : ''"
                      class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200 shrink-0"
                      fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-xs font-medium text-gray-600">Modifier Pages</span>
                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-600 ring-1 ring-inset ring-indigo-200">
                      {{ count($modifierData[$recipeId]) }} page{{ count($modifierData[$recipeId]) !== 1 ? 's' : '' }}
                    </span>
                  </div>
                </div>

                {{-- Modifier pages grid ── --}}
                <div
                  x-show="openModifiers"
                  x-collapse
                  class="px-5 pb-5"
                >
                  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-{{ min(count($modifierData[$recipeId]), 4) }}">

                    @foreach ($modifierData[$recipeId] as $modSlot)
                      <x-modifier-page
                        :page-id="$modSlot['pageId']"
                        :slot="$modSlot['slot']"
                        :all-pages="$modifierPages"
                        :all-items="$modifierItems"
                      />
                    @endforeach

                  </div>
                </div>

              </div>
            @endif

          </div>

        </div>
      @endforeach

    </div>
  @endif

  {{-- ── Pagination ── --}}
  @if ($paginator->hasPages())
    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm text-sm">

      <span class="text-gray-400 text-xs">
        Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        &nbsp;·&nbsp;
        {{ $total }} recipes total
      </span>

      <div class="flex items-center gap-1">
        {{-- Previous ── --}}
        @if ($paginator->onFirstPage())
          <span class="rounded-lg border border-gray-100 px-3 py-1.5 text-xs text-gray-300 cursor-not-allowed">← Prev</span>
        @else
          <a
            href="{{ $paginator->previousPageUrl() }}"
            class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition"
          >← Prev</a>
        @endif

        {{-- Page numbers ── --}}
        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-medium text-white">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition">{{ $page }}</a>
          @endif
        @endforeach

        {{-- Next ── --}}
        @if ($paginator->hasMorePages())
          <a
            href="{{ $paginator->nextPageUrl() }}"
            class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 transition"
          >Next →</a>
        @else
          <span class="rounded-lg border border-gray-100 px-3 py-1.5 text-xs text-gray-300 cursor-not-allowed">Next →</span>
        @endif
      </div>

    </div>
  @endif

</div>
@endsection