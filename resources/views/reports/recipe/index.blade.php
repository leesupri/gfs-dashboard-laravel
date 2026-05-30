@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ request()->except('page') ? 'true' : 'false' }} }" class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">{{ $title }}</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Expected vs actual ingredient cost per recipe</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.recipe.export', request()->query()) }}"
         class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
         style="color:var(--text-primary)">
        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
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
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="sm:col-span-2">
          <label for="f-q" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Search</label>
          <input id="f-q" type="text" name="q" value="{{ $q }}" placeholder="Recipe or ingredient name…"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>

        <div>
          <label for="f-sales" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Sales item</label>
          <select id="f-sales" name="sales"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value="">All</option>
            <option value="yes" {{ $sales === 'yes' ? 'selected' : '' }}>Yes</option>
            <option value="no"  {{ $sales === 'no'  ? 'selected' : '' }}>No</option>
          </select>
        </div>

        <div>
          <label for="f-purchased" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Purchased</label>
          <select id="f-purchased" name="purchased"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value="">All</option>
            <option value="yes" {{ $purchased === 'yes' ? 'selected' : '' }}>Yes</option>
            <option value="no"  {{ $purchased === 'no'  ? 'selected' : '' }}>No</option>
          </select>
        </div>

        <div>
          <label for="f-stocked" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Stocked</label>
          <select id="f-stocked" name="stocked"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value="">All</option>
            <option value="yes" {{ $stocked === 'yes' ? 'selected' : '' }}>Yes</option>
            <option value="no"  {{ $stocked === 'no'  ? 'selected' : '' }}>No</option>
          </select>
        </div>

        <div class="flex items-end pb-0.5">
          <label class="inline-flex cursor-pointer items-center gap-2 text-sm" style="color:var(--text-secondary)">
            <input type="checkbox" name="hide_zero" value="1" {{ $hideZero ? 'checked' : '' }}
              class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
            <span>Hide zero cost rows</span>
          </label>
        </div>

      </div>
      <div class="mt-4 flex items-center justify-end gap-2 border-t border-gray-100 pt-4">
        <a href="{{ route('reports.recipe') }}"
          class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
          style="color:var(--text-secondary)">Clear</a>
        <button type="submit"
          class="rounded-xl bg-green-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
          Apply
        </button>
      </div>
    </form>
  </div>

  @if($byRecipe->isEmpty())

    {{-- Empty state --}}
    <div class="gfs-card flex flex-col items-center justify-center gap-3 py-20">
      <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <p class="text-sm font-medium" style="color:var(--text-muted)">No recipe data found for the selected filters.</p>
    </div>

  @else

    {{-- KPI strip --}}
    @php
      $recipeCount   = $byRecipe->count();
      $totalExpected = $rows->sum('expectedTotal');
      $totalActual   = $rows->sum('actualTotal');
      $totalVariance = $totalActual - $totalExpected;
    @endphp
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
      <div class="gfs-card flex items-center gap-3 p-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
          <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Recipes</p>
          <p class="mt-0.5 text-lg font-bold leading-none" style="color:var(--text-primary)">{{ $recipeCount }}</p>
        </div>
      </div>
      <div class="gfs-card flex items-center gap-3 p-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100">
          <svg class="h-5 w-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 13h.01M13 13h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Expected</p>
          <p class="mt-0.5 text-base font-bold leading-none" style="color:var(--text-primary)">{{ number_format($totalExpected, 0, ',', '.') }}</p>
        </div>
      </div>
      <div class="gfs-card flex items-center gap-3 p-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
          <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Actual</p>
          <p class="mt-0.5 text-base font-bold leading-none" style="color:var(--text-primary)">{{ number_format($totalActual, 0, ',', '.') }}</p>
        </div>
      </div>
      <div class="gfs-card flex items-center gap-3 p-4 {{ $totalVariance > 0 ? 'border-red-200' : 'border-green-200' }}">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $totalVariance > 0 ? 'bg-red-100' : 'bg-green-100' }}">
          <svg class="h-5 w-5 {{ $totalVariance > 0 ? 'text-red-500' : 'text-green-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            @if($totalVariance > 0)
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
            @else
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            @endif
          </svg>
        </div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Variance</p>
          <p class="mt-0.5 text-base font-bold leading-none {{ $totalVariance > 0 ? 'text-red-500' : 'text-green-600' }}">
            {{ $totalVariance > 0 ? '+' : '' }}{{ number_format($totalVariance, 0, ',', '.') }}
          </p>
        </div>
      </div>
    </div>

    {{-- Recipe cards --}}
    @foreach($byRecipe as $recipeName => $items)
      @php
        $first          = $items->first();
        $expectedTotal  = $items->sum('expectedTotal');
        $actualTotal    = $items->sum('actualTotal');
        $production     = (float)($first->production ?: 0);
        $expectedPerUnit = $production > 0 ? $expectedTotal / $production : 0;
        $actualPerUnit   = $production > 0 ? $actualTotal  / $production : 0;
        $variance        = $actualPerUnit - $expectedPerUnit;
        $isOver          = $variance > 0;

        $badgeCss = fn($val) => $val === 'yes'
          ? 'bg-green-100 text-green-700'
          : 'bg-gray-100 text-gray-500';
      @endphp

      <div class="gfs-card overflow-hidden">

        {{-- Recipe header --}}
        <div class="flex flex-wrap items-start justify-between gap-4 px-5 py-4"
          style="background:var(--content-bg); border-bottom:1px solid var(--card-border)">

          {{-- Left: name + badges --}}
          <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
              style="background:var(--sidebar-bg)">
              <svg class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-bold" style="color:var(--text-primary)">{{ $recipeName }}</p>
              <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badgeCss($first->sales) }}">
                  Sales: {{ $first->sales }}
                </span>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badgeCss($first->purchased) }}">
                  Purchased: {{ $first->purchased }}
                </span>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badgeCss($first->stocked) }}">
                  Stocked: {{ $first->stocked }}
                </span>
                <span class="text-xs" style="color:var(--text-muted)">
                  Production: <strong style="color:var(--text-secondary)">{{ number_format($production, 2) }} {{ $first->uom }}</strong>
                </span>
              </div>
            </div>
          </div>

          {{-- Right: Expected / Actual / Variance --}}
          <div class="flex items-start gap-5">
            <div class="text-right">
              <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Expected</p>
              <p class="mt-0.5 text-sm font-bold" style="color:var(--text-primary)">{{ number_format($expectedTotal, 0, ',', '.') }}</p>
              <p class="text-[10px]" style="color:var(--text-muted)">/unit: {{ number_format($expectedPerUnit, 2, ',', '.') }}</p>
            </div>
            <div class="text-right">
              <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Actual</p>
              <p class="mt-0.5 text-sm font-bold" style="color:var(--text-primary)">{{ number_format($actualTotal, 0, ',', '.') }}</p>
              <p class="text-[10px]" style="color:var(--text-muted)">/unit: {{ number_format($actualPerUnit, 2, ',', '.') }}</p>
            </div>
            <div class="flex h-full items-center">
              <span class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-bold
                {{ $isOver ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                @if($isOver)
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                  </svg>
                  +{{ number_format($variance, 2, ',', '.') }}
                @else
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                  </svg>
                  {{ number_format($variance, 2, ',', '.') }}
                @endif
              </span>
            </div>
          </div>

        </div>

        {{-- Ingredient table --}}
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
                style="background:var(--content-bg); border-bottom:1px solid var(--card-border); color:var(--text-muted)">
                <th class="px-4 py-2.5 text-left w-24">Code</th>
                <th class="px-4 py-2.5 text-left">Item</th>
                <th class="px-4 py-2.5 w-20">Rec Qty</th>
                <th class="px-4 py-2.5 w-16 text-left">R-UOM</th>
                <th class="px-4 py-2.5 w-20">Inv Qty</th>
                <th class="px-4 py-2.5 w-16 text-left">I-UOM</th>
                <th class="px-4 py-2.5 w-28">Unit Cost</th>
                <th class="px-4 py-2.5 w-28">Avg Cost</th>
                <th class="px-4 py-2.5 w-28">Expected</th>
                <th class="px-4 py-2.5 w-28">Actual</th>
              </tr>
            </thead>
            <tbody class="divide-y" style="border-color:var(--card-border)">
              @foreach($items as $r)
                @php
                  $rowVar = (float)$r->actualTotal - (float)$r->expectedTotal;
                @endphp
                <tr class="transition-colors hover:bg-gray-50/60">
                  <td class="px-4 py-2.5 font-mono text-xs" style="color:var(--text-muted)">{{ $r->itemCode }}</td>
                  <td class="px-4 py-2.5 font-medium" style="color:var(--text-primary)">{{ $r->itemName }}</td>
                  <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">{{ number_format((float)$r->RecQty, 2, ',', '.') }}</td>
                  <td class="px-4 py-2.5 text-xs" style="color:var(--text-muted)">{{ $r->recipeUom }}</td>
                  <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">{{ number_format((float)$r->InvQty, 2, ',', '.') }}</td>
                  <td class="px-4 py-2.5 text-xs" style="color:var(--text-muted)">{{ $r->InvUom }}</td>
                  <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">{{ number_format((float)$r->unitCost, 2, ',', '.') }}</td>
                  <td class="px-4 py-2.5 text-right tabular-nums" style="color:var(--text-secondary)">{{ number_format((float)$r->averageCost, 2, ',', '.') }}</td>
                  <td class="px-4 py-2.5 text-right tabular-nums font-medium" style="color:var(--text-primary)">{{ number_format((float)$r->expectedTotal, 2, ',', '.') }}</td>
                  <td class="px-4 py-2.5 text-right tabular-nums font-semibold {{ $rowVar > 0.01 ? 'text-red-500' : ($rowVar < -0.01 ? 'text-green-600' : '') }}"
                    @if(abs($rowVar) <= 0.01) style="color:var(--text-primary)" @endif>
                    {{ number_format((float)$r->actualTotal, 2, ',', '.') }}
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr style="background:var(--content-bg); border-top:2px solid var(--card-border)">
                <td colspan="8" class="px-4 py-2.5 text-right text-xs font-semibold" style="color:var(--text-secondary)">Recipe Total</td>
                <td class="px-4 py-2.5 text-right font-bold tabular-nums" style="color:var(--text-primary)">{{ number_format($expectedTotal, 2, ',', '.') }}</td>
                <td class="px-4 py-2.5 text-right font-bold tabular-nums {{ $isOver ? 'text-red-500' : 'text-green-600' }}">{{ number_format($actualTotal, 2, ',', '.') }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

      </div>
    @endforeach

    {{-- Grand total --}}
    <div class="flex justify-end">
      <div class="gfs-card flex items-center gap-6 px-6 py-4" style="background:var(--sidebar-bg)">
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Expected Total</p>
          <p class="mt-0.5 text-xl font-bold text-white">{{ number_format($totalExpected, 0, ',', '.') }}</p>
        </div>
        <div class="h-10 w-px" style="background:rgba(255,255,255,0.1)"></div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Actual Total</p>
          <p class="mt-0.5 text-xl font-bold text-white">{{ number_format($totalActual, 0, ',', '.') }}</p>
        </div>
        <div class="h-10 w-px" style="background:rgba(255,255,255,0.1)"></div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Variance</p>
          <p class="mt-0.5 text-xl font-bold {{ $totalVariance > 0 ? 'text-red-400' : 'text-green-400' }}">
            {{ $totalVariance > 0 ? '+' : '' }}{{ number_format($totalVariance, 0, ',', '.') }}
          </p>
        </div>
      </div>
    </div>

  @endif

</div>
@endsection

