@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }" class="space-y-5">

  {{-- ── Page header ── --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <div class="flex items-center gap-2.5">
        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-green-600 shadow-sm">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
        </div>
        <h1 class="text-lg font-semibold" style="color:var(--text-primary)">Recipe Board</h1>
      </div>
      <p class="mt-1 ml-10.5 text-xs" style="color:var(--text-muted)">All recipes with ingredients and unit-of-measure breakdown</p>
    </div>

    <div class="flex items-center gap-2">
      <a
        href="{{ route('reports.recipe-board.export', request()->query()) }}"
        class="inline-flex items-center gap-2 rounded-xl border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50 active:scale-95"
        style="border-color:var(--card-border); color:var(--text-secondary); box-shadow:var(--card-shadow)"
      >
        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
        </svg>
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

  {{-- ── Filters ── --}}
  <div
    x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
  >
  <form method="GET" class="rounded-2xl border bg-white p-5 shadow-sm" style="border-color:var(--card-border); box-shadow:var(--card-shadow)">
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">

      <div class="col-span-2 sm:col-span-3 lg:col-span-2">
        <label for="filter-q" class="block text-xs font-medium mb-1.5" style="color:var(--text-muted)">Search</label>
        <div class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 pointer-events-none" style="color:var(--text-muted)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/>
          </svg>
          <input
            id="filter-q"
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="Recipe name, ingredient, category…"
            class="w-full rounded-xl border bg-white pl-9 pr-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)"
          >
        </div>
      </div>

      @foreach ([
        ['label' => 'Category',  'name' => 'category',  'type' => 'category'],
        ['label' => 'Active',    'name' => 'active',    'type' => 'yesno'],
        ['label' => 'Sales',     'name' => 'sales',     'type' => 'yesno'],
        ['label' => 'Purchased', 'name' => 'purchased', 'type' => 'yesno'],
        ['label' => 'Stocked',   'name' => 'stocked',   'type' => 'yesno'],
      ] as $filter)
        <div>
          <label for="filter-{{ $filter['name'] }}" class="block text-xs font-medium mb-1.5" style="color:var(--text-muted)">{{ $filter['label'] }}</label>
          <select id="filter-{{ $filter['name'] }}" name="{{ $filter['name'] }}" class="w-full rounded-xl border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20" style="border-color:var(--card-border); color:var(--text-primary)">
            @if ($filter['type'] === 'category')
              <option value="">All categories</option>
              @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ $categoryFilter == $cat->id ? 'selected' : '' }}>
                  {{ $cat->name }}{{ $cat->code ? ' ('.$cat->code.')' : '' }}
                </option>
              @endforeach
            @else
              <option value="">All</option>
              @php
                $val = match($filter['name']) {
                  'active'    => $activeFilter,
                  'sales'     => $sales,
                  'purchased' => $purchased,
                  'stocked'   => $stocked,
                  default     => ''
                };
              @endphp
              <option value="yes" {{ $val === 'yes' ? 'selected' : '' }}>Yes</option>
              <option value="no"  {{ $val === 'no'  ? 'selected' : '' }}>No</option>
            @endif
          </select>
        </div>
      @endforeach

    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3 border-t pt-4" style="border-color:var(--card-border)">
      <label class="inline-flex items-center gap-2 cursor-pointer select-none text-sm" style="color:var(--text-secondary)">
        <input
          type="checkbox"
          name="has_conversi"
          value="1"
          {{ $hasConversi ? 'checked' : '' }}
          class="rounded border-gray-300 text-green-600 focus:ring-green-500"
        >
        <span>Has conversi ingredient</span>
        <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-medium text-violet-700 ring-1 ring-inset ring-violet-200">conversi</span>
      </label>

      <div class="flex gap-2 ml-auto">
        <a
          href="{{ route('reports.recipe-board') }}"
          class="rounded-xl border px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
          style="border-color:var(--card-border); color:var(--text-secondary)"
        >
          Clear
        </a>
        <button
          type="submit"
          class="rounded-xl bg-green-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95 shadow-sm"
        >
          Apply filters
        </button>
      </div>
    </div>
  </form>
  </div>

  {{-- ── Summary stat pills ── --}}
  <div class="flex flex-wrap gap-2.5">
    @php
      $activeCount   = $byRecipe->filter(fn($items) => $items->first()->isActive === 'yes')->count();
      $inactiveCount = $byRecipe->filter(fn($items) => $items->first()->isActive === 'no')->count();
    @endphp

    <div class="flex items-center gap-3 rounded-xl border bg-white px-4 py-2.5 shadow-sm" style="border-color:var(--card-border); box-shadow:var(--card-shadow)">
      <span class="text-xs" style="color:var(--text-muted)">Showing</span>
      <span class="text-sm font-semibold" style="color:var(--text-primary)">{{ $byRecipe->count() }}</span>
      <span class="text-xs" style="color:var(--text-muted)">of {{ $total }} recipes</span>
    </div>

    <div class="flex items-center gap-2 rounded-xl border bg-white px-4 py-2.5 shadow-sm" style="border-color:var(--card-border); box-shadow:var(--card-shadow)">
      <span class="flex h-2 w-2 rounded-full bg-green-500"></span>
      <span class="text-xs" style="color:var(--text-muted)">Active</span>
      <span class="text-sm font-semibold text-green-600">{{ $activeCount }}</span>
    </div>

    <div class="flex items-center gap-2 rounded-xl border bg-white px-4 py-2.5 shadow-sm" style="border-color:var(--card-border); box-shadow:var(--card-shadow)">
      <span class="flex h-2 w-2 rounded-full bg-gray-300"></span>
      <span class="text-xs" style="color:var(--text-muted)">Inactive</span>
      <span class="text-sm font-semibold" style="color:var(--text-muted)">{{ $inactiveCount }}</span>
    </div>
  </div>

  {{-- ── Empty state ── --}}
  @if ($byRecipe->isEmpty())
    <div class="rounded-2xl border bg-white p-14 text-center shadow-sm" style="border-color:var(--card-border); box-shadow:var(--card-shadow)">
      <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <p class="text-sm font-medium" style="color:var(--text-secondary)">No recipes match your filters.</p>
      <p class="mt-1 text-xs" style="color:var(--text-muted)">Try adjusting or clearing your filter criteria.</p>
    </div>

  @else

    <div x-data="recipeBoard()" class="space-y-3">

      {{-- ── Toolbar ── --}}
      <div class="flex items-center justify-between">
        <p class="text-xs" style="color:var(--text-muted)">{{ $byRecipe->count() }} recipe{{ $byRecipe->count() !== 1 ? 's' : '' }} on this page</p>
        <div class="flex gap-1.5">
          <button
            @click="expandAll()"
            class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-1.5 text-xs font-medium transition hover:bg-gray-50"
            style="border-color:var(--card-border); color:var(--text-secondary)"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5-5-5m5 5v-4m0 4h-4"/></svg>
            Expand all
          </button>
          <button
            @click="collapseAll()"
            class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-1.5 text-xs font-medium transition hover:bg-gray-50"
            style="border-color:var(--card-border); color:var(--text-secondary)"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25"/></svg>
            Collapse all
          </button>
        </div>
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
          class="rounded-2xl border bg-white overflow-hidden transition-all duration-200"
          style="border-color:var(--card-border); box-shadow:var(--card-shadow); {{ $isInactive ? 'opacity:0.6' : '' }}"
        >

          {{-- ── Card header ── --}}
          <div
            @click="open = !open"
            class="cursor-pointer px-5 py-4 transition-colors duration-150 select-none"
            :class="open ? 'bg-gray-50/70' : 'hover:bg-gray-50/50'"
          >
            <div class="flex items-start justify-between gap-4">

              {{-- Left ── --}}
              <div class="flex-1 min-w-0">

                {{-- Name row ── --}}
                <div class="flex items-center gap-2.5 flex-wrap">
                  <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md transition-colors" :class="open ? 'bg-green-100' : 'bg-gray-100'">
                    <svg
                      :class="open ? 'rotate-90 text-green-600' : 'text-gray-400'"
                      class="w-3 h-3 transition-transform duration-200"
                      fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                  </div>

                  <span class="font-semibold text-sm" style="color:var(--text-primary)">{{ $first->recipeName }}</span>

                  @if ($first->isActive === 'yes')
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-inset ring-green-200">
                      <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>active
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500 ring-1 ring-inset ring-gray-300">
                      <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>inactive
                    </span>
                  @endif
                </div>

                {{-- Meta row ── --}}
                <div class="mt-2 ml-7.5 flex flex-wrap items-center gap-1.5">

                  @if ($first->sales === 'yes')
                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-inset ring-green-200">sales</span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-200">no sales</span>
                  @endif

                  @if ($first->purchased === 'yes')
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-200">purchased</span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-200">no purchase</span>
                  @endif

                  @if ($first->stocked === 'yes')
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700 ring-1 ring-inset ring-amber-200">stocked</span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-200">no stock</span>
                  @endif

                  @if ($first->stocked === 'yes' && $first->sales === 'no' && $first->purchased === 'no')
                    <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-medium text-violet-700 ring-1 ring-inset ring-violet-200">conversi</span>
                  @endif

                  @if ($first->categoryName)
                    <span class="mx-1 h-3 w-px bg-gray-200"></span>
                    <span class="inline-flex items-center gap-1 text-[11px]" style="color:var(--text-muted)">
                      <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                      </svg>
                      {{ $first->categoryName }}
                      @if ($first->categoryCode)
                        <span class="font-mono text-[10px]">({{ $first->categoryCode }})</span>
                      @endif
                    </span>
                  @endif
                </div>

              </div>

              {{-- Right: production summary ── --}}
              <div class="text-right shrink-0">
                <div class="text-xs font-semibold" style="color:var(--text-primary)">
                  {{ number_format((float) $first->production, 2) }}
                  <span class="font-normal text-[11px]" style="color:var(--text-muted)">{{ $first->uom }}</span>
                </div>
                <div class="mt-0.5 text-[11px]" style="color:var(--text-muted)">
                  {{ $items->count() }} ingredient{{ $items->count() !== 1 ? 's' : '' }}
                </div>
              </div>

            </div>
          </div>

          {{-- ── Ingredient table ── --}}
          <div x-show="open" x-collapse class="border-t" style="border-color:var(--card-border)">
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="border-b" style="background:#fafafa; border-color:var(--card-border)">
                    <th class="px-5 py-2.5 text-left w-8 text-[11px] font-medium" style="color:var(--text-muted)">#</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-medium" style="color:var(--text-muted)">Code</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-medium" style="color:var(--text-muted)">Ingredient</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-medium" style="color:var(--text-muted)">Type</th>
                    <th class="px-4 py-2.5 text-right text-[11px] font-medium whitespace-nowrap" style="color:var(--text-muted)">Recipe qty</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-medium" style="color:var(--text-muted)">UOM</th>
                    <th class="px-4 py-2.5 text-right text-[11px] font-medium whitespace-nowrap" style="color:var(--text-muted)">Inv qty</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-medium" style="color:var(--text-muted)">Inv UOM</th>
                    <th class="px-4 py-2.5 text-left text-[11px] font-medium" style="color:var(--text-muted)">Status</th>
                  </tr>
                </thead>

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
                    class="divide-y"
                    style="border-color:var(--card-border)"
                  >
                    <tr
                      @if ($isConversi && $subItems->isNotEmpty())
                        @click="toggle()"
                        class="transition-colors cursor-pointer hover:bg-violet-50/60"
                      @elseif ($isCombo && !empty($comboSlots))
                        @click="toggle()"
                        class="transition-colors cursor-pointer hover:bg-orange-50/60"
                      @else
                        class="transition-colors hover:bg-gray-50/60"
                      @endif
                    >
                      <td class="px-5 py-3 text-[11px]" style="color:var(--text-muted)">{{ $r->idx }}</td>
                      <td class="px-4 py-3 text-[11px] font-mono" style="color:var(--text-muted)">{{ $r->itemCode }}</td>
                      <td class="px-4 py-3 text-sm font-medium" style="color:var(--text-primary)">{{ $r->itemName }}</td>

                      <td class="px-4 py-3">
                        @if ($isConversi)
                          <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-medium text-violet-700 ring-1 ring-inset ring-violet-200">
                            conversi
                            @if ($subItems->isNotEmpty())
                              <svg :class="open ? 'rotate-90' : ''" class="w-2.5 h-2.5 transition-transform duration-150" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                              </svg>
                            @endif
                          </span>
                        @elseif ($isCombo)
                          <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-medium text-orange-700 ring-1 ring-inset ring-orange-200">
                            combo
                            @if (!empty($comboSlots))
                              <svg :class="open ? 'rotate-90' : ''" class="w-2.5 h-2.5 transition-transform duration-150" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                              </svg>
                            @endif
                          </span>
                        @else
                          <span class="text-[10px]" style="color:var(--text-muted)">—</span>
                        @endif
                      </td>

                      <td class="px-4 py-3 text-right tabular-nums text-sm" style="color:var(--text-primary)">{{ number_format((float) $r->RecQty, 2) }}</td>
                      <td class="px-4 py-3 text-[11px]" style="color:var(--text-muted)">{{ $r->recipeUom }}</td>
                      <td class="px-4 py-3 text-right tabular-nums text-sm" style="color:var(--text-secondary)">{{ number_format((float) $r->InvQty, 2) }}</td>
                      <td class="px-4 py-3 text-[11px]" style="color:var(--text-muted)">{{ $r->InvUom }}</td>

                      <td class="px-4 py-3">
                        @if ($r->itemActive === 'yes')
                          <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-inset ring-green-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>active
                          </span>
                        @else
                          <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400 ring-1 ring-inset ring-gray-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-300"></span>inactive
                          </span>
                        @endif
                      </td>
                    </tr>

                    {{-- ── Conversi sub-recipe ── --}}
                    @if ($isConversi && $subItems->isNotEmpty())
                      <tr x-show="open" x-collapse style="display:none">
                        <td colspan="9" class="p-0">
                          <div class="bg-violet-50/40 border-l-2 border-violet-300 ml-12 mr-4 my-2 rounded-xl overflow-hidden">

                            <div class="px-4 py-2.5 flex items-center justify-between gap-3 border-b border-violet-100">
                              <div class="flex items-center gap-1.5 text-[11px] font-medium text-violet-600">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Conversi — {{ $r->itemName }}
                              </div>
                              @if ($subItems->isNotEmpty())
                                <div class="text-[10px] text-violet-500">
                                  Produces: <span class="font-semibold text-violet-700">{{ number_format((float) $subItems->first()->conversiProduction, 2) }} {{ $subItems->first()->conversiUom }}</span>
                                </div>
                              @endif
                            </div>

                            <table class="min-w-full text-xs">
                              <thead>
                                <tr class="border-b border-violet-100 bg-violet-100/50">
                                  <th class="px-3 py-2 text-left font-medium text-violet-600">#</th>
                                  <th class="px-3 py-2 text-left font-medium text-violet-600">Code</th>
                                  <th class="px-3 py-2 text-left font-medium text-violet-600">Item</th>
                                  <th class="px-3 py-2 text-right font-medium text-violet-600 whitespace-nowrap">Recipe qty</th>
                                  <th class="px-3 py-2 text-left font-medium text-violet-600">UOM</th>
                                  <th class="px-3 py-2 text-right font-medium text-violet-600 whitespace-nowrap">Inv qty</th>
                                  <th class="px-3 py-2 text-left font-medium text-violet-600">Inv UOM</th>
                                  <th class="px-3 py-2 text-left font-medium text-violet-600">Status</th>
                                </tr>
                              </thead>
                              <tbody class="divide-y divide-violet-100 bg-white/60">
                                @foreach ($subItems as $sub)
                                  <tr class="hover:bg-violet-50/40 transition-colors">
                                    <td class="px-3 py-2 text-gray-400">{{ $sub->idx }}</td>
                                    <td class="px-3 py-2 font-mono text-gray-400">{{ $sub->itemCode }}</td>
                                    <td class="px-3 py-2 text-gray-700 font-medium">{{ $sub->itemName }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums text-gray-700">{{ number_format((float) $sub->RecQty, 2) }}</td>
                                    <td class="px-3 py-2 text-gray-400">{{ $sub->recipeUom }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums text-gray-600">{{ number_format((float) $sub->InvQty, 2) }}</td>
                                    <td class="px-3 py-2 text-gray-400">{{ $sub->InvUom }}</td>
                                    <td class="px-3 py-2">
                                      @if ($sub->itemActive === 'yes')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-1.5 py-0.5 text-[9px] font-medium text-green-700 ring-1 ring-inset ring-green-200">
                                          <span class="h-1 w-1 rounded-full bg-green-500"></span>active
                                        </span>
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

                    {{-- ── Combo sub-menu ── --}}
                    @if ($isCombo && !empty($comboSlots))
                      <tr x-show="open" x-collapse style="display:none">
                        <td colspan="9" class="p-0">
                          <div class="bg-orange-50/30 border-l-2 border-orange-300 ml-12 mr-4 my-2 rounded-xl overflow-hidden">

                            {{-- Combo header ── --}}
                            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-orange-100 px-4 py-3">
                              <div>
                                <div class="flex items-center gap-2">
                                  <svg class="w-3.5 h-3.5 shrink-0 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                  </svg>
                                  <span class="text-sm font-semibold" style="color:var(--text-primary)">{{ $r->itemName }}</span>
                                  @if ($r->itemActive === 'yes')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-inset ring-green-200">
                                      <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>active
                                    </span>
                                  @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500 ring-1 ring-inset ring-gray-300">inactive</span>
                                  @endif
                                </div>
                                @if ($r->itemCode)
                                  <div class="mt-0.5 ml-5 font-mono text-[11px]" style="color:var(--text-muted)">{{ $r->itemCode }}</div>
                                @endif
                                <div class="mt-2 ml-5 flex flex-wrap gap-1.5">
                                  <span class="inline-flex items-center rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-semibold text-orange-700 ring-1 ring-inset ring-orange-200">combo</span>
                                  @if ($r->itemSales === 'yes')
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-inset ring-green-200">sales</span>
                                  @endif
                                  @if ($r->itemPurchased === 'yes')
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-200">purchased</span>
                                  @endif
                                  @if ($r->itemStocked === 'yes')
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-700 ring-1 ring-inset ring-amber-200">stocked</span>
                                  @endif
                                </div>
                              </div>

                              <div class="flex gap-5 text-right text-xs">
                                <div>
                                  <div class="text-[10px]" style="color:var(--text-muted)">Recipe UOM</div>
                                  <div class="font-medium" style="color:var(--text-primary)">{{ $r->recipeUom ?: '—' }}</div>
                                </div>
                                <div>
                                  <div class="text-[10px]" style="color:var(--text-muted)">Inv UOM</div>
                                  <div class="font-medium" style="color:var(--text-primary)">{{ $r->InvUom ?: '—' }}</div>
                                </div>
                                <div>
                                  <div class="text-[10px]" style="color:var(--text-muted)">Groups</div>
                                  <div class="font-semibold text-orange-600">{{ count($comboSlots) }}</div>
                                </div>
                              </div>
                            </div>

                            {{-- Combo choice groups ── --}}
                            <div class="p-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-{{ min(count($comboSlots), 4) }}">
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

                <tfoot>
                  <tr class="border-t" style="background:#fafafa; border-color:var(--card-border)">
                    <td colspan="2" class="px-5 py-2.5 text-[11px]" style="color:var(--text-muted)">
                      Production: <span class="font-semibold" style="color:var(--text-primary)">{{ number_format((float) $first->production, 2) }} {{ $first->uom }}</span>
                    </td>
                    <td colspan="7" class="px-4 py-2.5 text-right text-[11px]" style="color:var(--text-muted)">
                      {{ $items->count() }} ingredient{{ $items->count() !== 1 ? 's' : '' }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

            {{-- ── Modifier pages ── --}}
            @if ($first->sales === 'yes' && isset($modifierData[$recipeId]) && count($modifierData[$recipeId]) > 0)
              <div class="border-t" style="border-color:var(--card-border)">

                <div
                  @click="openModifiers = !openModifiers"
                  class="flex cursor-pointer items-center justify-between px-5 py-3 transition-colors hover:bg-gray-50 select-none"
                >
                  <div class="flex items-center gap-2">
                    <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md transition-colors" :class="openModifiers ? 'bg-indigo-100' : 'bg-gray-100'">
                      <svg
                        :class="openModifiers ? 'rotate-90 text-indigo-600' : 'text-gray-400'"
                        class="w-3 h-3 transition-transform duration-200"
                        fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                      </svg>
                    </div>
                    <span class="text-xs font-medium" style="color:var(--text-secondary)">Modifier Pages</span>
                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-600 ring-1 ring-inset ring-indigo-200">
                      {{ count($modifierData[$recipeId]) }} page{{ count($modifierData[$recipeId]) !== 1 ? 's' : '' }}
                    </span>
                  </div>
                </div>

                <div x-show="openModifiers" x-collapse class="px-5 pb-5">
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
    <div class="flex items-center justify-between gap-3 rounded-xl border bg-white px-4 py-3 shadow-sm" style="border-color:var(--card-border); box-shadow:var(--card-shadow)">

      <span class="text-xs" style="color:var(--text-muted)">
        Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        &nbsp;·&nbsp; {{ $total }} recipes total
      </span>

      <div class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
          <span class="rounded-lg border px-3 py-1.5 text-xs cursor-not-allowed" style="border-color:var(--card-border); color:var(--text-muted)">← Prev</span>
        @else
          <a href="{{ $paginator->previousPageUrl() }}" class="rounded-lg border px-3 py-1.5 text-xs transition hover:bg-gray-50" style="border-color:var(--card-border); color:var(--text-secondary)">← Prev</a>
        @endif

        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-medium text-white">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="rounded-lg border px-3 py-1.5 text-xs transition hover:bg-gray-50" style="border-color:var(--card-border); color:var(--text-secondary)">{{ $page }}</a>
          @endif
        @endforeach

        @if ($paginator->hasMorePages())
          <a href="{{ $paginator->nextPageUrl() }}" class="rounded-lg border px-3 py-1.5 text-xs transition hover:bg-gray-50" style="border-color:var(--card-border); color:var(--text-secondary)">Next →</a>
        @else
          <span class="rounded-lg border px-3 py-1.5 text-xs cursor-not-allowed" style="border-color:var(--card-border); color:var(--text-muted)">Next →</span>
        @endif
      </div>

    </div>
  @endif

</div>
@endsection
