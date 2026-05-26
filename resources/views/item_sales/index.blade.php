<!-- resources/views/item_sales/index.blade.php -->
@extends('layouts.app')

@section('content')
@php
if (!function_exists('costColor')) {
    function costColor(float $pct): string {
        if ($pct < 40) return 'text-green-600';
        if ($pct < 55) return 'text-yellow-600';
        return 'text-red-600';
    }
}
@endphp
<div x-data="{ filtersOpen: {{ (request('q') || request('start') || request('end') || request('dept')) ? 'true' : 'false' }} }" class="space-y-6">

  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-xl font-semibold">Item Sales</h1>
      <p class="text-sm text-gray-600">Daily item sales with cost and cost %.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('itemSales.export', request()->query()) }}"
         class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Export CSV
      </a>
      <button type="button" @click="filtersOpen = !filtersOpen"
        class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
        Filter
      </button>
    </div>
  </div>

  <form method="GET" action="{{ route('itemSales.index') }}" x-show="filtersOpen" x-cloak
        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-8">
      <div class="lg:col-span-2">
        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Item code or name"
               class="w-full rounded-lg border px-3 py-2 text-sm"/>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Department</label>
        <select name="dept" class="w-full rounded-lg border px-3 py-2 text-sm">
          <option value="">All</option>
          @foreach($departments as $dept)
            <option value="{{ $dept }}" {{ ($filters['dept'] ?? '') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
        <select name="cat" class="w-full rounded-lg border px-3 py-2 text-sm">
          <option value="">All</option>
          @foreach($categories as $dept => $cats)
            <optgroup label="{{ $dept }}">
              @foreach($cats as $c)
                <option value="{{ $c->name }}" {{ request('cat') === $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
              @endforeach
            </optgroup>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Start</label>
        <input type="date" name="start" value="{{ $filters['start'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm"/>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">End</label>
        <input type="date" name="end" value="{{ $filters['end'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm"/>
      </div>
      <div class="flex items-end">
        {{-- FIXED: value was "0", must be "1" so $request->boolean('hide_zero') returns true --}}
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" name="hide_zero" value="1" {{ !empty($filters['hide_zero']) ? 'checked' : '' }}>
          Hide zero items
        </label>
      </div>
      <div class="flex items-end justify-end gap-2">
        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Apply</button>
        <a href="{{ route('itemSales.index') }}" class="rounded-lg border px-4 py-2 text-sm">Reset</a>
      </div>
    </div>
    <div class="mt-4 flex flex-wrap gap-2 border-t pt-4">
      <span class="text-xs text-gray-500 mr-2">Quick range:</span>
      <a href="{{ route('itemSales.index', array_merge(request()->except(['start','end']), ['start' => now()->toDateString(), 'end' => now()->toDateString()])) }}"
         class="rounded-lg border px-3 py-1.5 text-xs hover:bg-gray-50">Today</a>
      <a href="{{ route('itemSales.index', array_merge(request()->except(['start','end']), ['start' => now()->subDay()->toDateString(), 'end' => now()->subDay()->toDateString()])) }}"
         class="rounded-lg border px-3 py-1.5 text-xs hover:bg-gray-50">Yesterday</a>
      <a href="{{ route('itemSales.index', array_merge(request()->except(['start','end']), ['start' => now()->startOfMonth()->toDateString(), 'end' => now()->endOfMonth()->toDateString()])) }}"
         class="rounded-lg border px-3 py-1.5 text-xs hover:bg-gray-50">This Month</a>
    </div>
  </form>

  {{-- KPI --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
    <div class="rounded-2xl border bg-white p-4">
      <div class="text-xs text-gray-500">Net Revenue</div>
      <div class="text-lg font-semibold">Rp {{ number_format((int)round($kpi['netRevenue']),0,',','.') }}</div>
    </div>
    <div class="rounded-2xl border bg-white p-4">
      <div class="text-xs text-gray-500">Cost</div>
      <div class="text-lg font-semibold">Rp {{ number_format((int)round($kpi['cost']),0,',','.') }}</div>
    </div>
    <div class="rounded-2xl border bg-white p-4">
      <div class="text-xs text-gray-500">Cost %</div>
      <div class="text-lg font-semibold">{{ number_format($kpi['costPct'], 2) }}%</div>
    </div>
    <div class="rounded-2xl border bg-white p-4">
      <div class="text-xs text-gray-500">Profit</div>
      <div class="text-lg font-semibold">Rp {{ number_format((int)round($kpi['profit']),0,',','.') }}</div>
    </div>
    @if($hasService)
    <div class="rounded-2xl border bg-white p-4">
      <div class="text-xs text-gray-500">Service</div>
      <div class="text-lg font-semibold">Rp {{ number_format((int)round($kpi['service']),0,',','.') }}</div>
    </div>
    @endif
    @if($hasTax)
    <div class="rounded-2xl border bg-white p-4">
      <div class="text-xs text-gray-500">Tax</div>
      <div class="text-lg font-semibold">Rp {{ number_format((int)round($kpi['tax']),0,',','.') }}</div>
    </div>
    @endif
    <div class="rounded-2xl border bg-white p-4">
      <div class="text-xs text-gray-500">Total</div>
      <div class="text-lg font-semibold">Rp {{ number_format((int)round($kpi['total']),0,',','.') }}</div>
    </div>
  </div>

  {{-- Table --}}
  <div class="overflow-hidden rounded-2xl border bg-white">
    <div class="p-2 overflow-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500">
          <tr>
            <th class="px-3 py-2 text-left w-[110px]">Item Code</th>
            <th class="px-3 py-2 text-left">Description</th>
            <th class="px-3 py-2 text-right w-[90px]">Quantity</th>
            <th class="px-3 py-2 text-right w-[120px]">Revenue</th>
            <th class="px-3 py-2 text-right w-[120px]">Discount</th>
            <th class="px-3 py-2 text-right w-[120px]">Cost</th>
            <th class="px-3 py-2 text-right w-[120px]">Profit</th>
            @if($hasService)
            <th class="px-3 py-2 text-right w-[120px]">Service</th>
            @endif
            @if($hasTax)
            <th class="px-3 py-2 text-right w-[120px]">Tax</th>
            @endif
            <th class="px-3 py-2 text-right w-[120px]">Total</th>
            <th class="px-3 py-2 text-right w-[80px]">Cost%</th>
          </tr>
        </thead>

        <tbody class="divide-y">
@forelse($byDept as $dept => $cats)
  <tr><td colspan="11" class="h-3 bg-gray-50"></td></tr>
  <tr class="bg-gray-100">
    <td colspan="11" class="px-3 py-2 font-bold text-gray-900">
      {{ $dept ?: 'NO DEPARTMENT' }}
      <span class="ml-2 text-xs font-normal text-gray-600">
        (Cost% {{ number_format($deptKpi[$dept]['costPct'] ?? 0, 2) }}%)
      </span>
    </td>
  </tr>

  @foreach($cats as $cat => $items)
    <tr class="bg-white border-t border-gray-200">
      <td colspan="11" class="px-6 py-2 font-semibold text-gray-800">
        &#9658; {{ $cat ?: 'UNCATEGORIZED' }}
        <span class="ml-2 text-xs font-normal text-gray-500">
          (Cost% {{ number_format($catKpi[$dept][$cat]['costPct'] ?? 0, 2) }}%)
        </span>
      </td>
    </tr>

    @foreach($items as $it)
      @php
        $hasData = (float)$it->quantity != 0 || (float)$it->subtotal != 0 ||
                   (float)$it->discount != 0 || (float)$it->cost != 0 ||
                   (float)$it->serviceCharge != 0 || (float)$it->tax != 0;
        if (!$hasData) continue;
        $revenue  = (float) $it->subtotal;
        $discount = (float) $it->discount;
        $net      = $it->subtotal - $it->discount;
        $cost     = (float) $it->cost;
        $profit   = $revenue - $cost;
        $service  = (float) $it->serviceCharge;
        $tax      = (float) $it->tax;
        $total    = $net + $it->serviceCharge + $it->tax;
        $costPct  = $net > 0 ? ($it->cost / $net) * 100 : 0;
      @endphp
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-2 whitespace-nowrap">{{ $it->code }}</td>
        <td class="px-6 py-2">{{ $it->name }}</td>
        <td class="px-3 py-2 text-right">{{ number_format((float)$it->quantity, 0) }}</td>
        <td class="px-3 py-2 text-right">{{ number_format($revenue, 2, ',', '.') }}</td>
        <td class="px-3 py-2 text-right">{{ number_format($discount, 2, ',', '.') }}</td>
        <td class="px-3 py-2 text-right">{{ number_format($cost, 2, ',', '.') }}</td>
        <td class="px-3 py-2 text-right">{{ number_format($profit, 2, ',', '.') }}</td>
        @if($hasService)<td class="px-3 py-2 text-right">{{ number_format($service, 2, ',', '.') }}</td>@endif
        @if($hasTax)<td class="px-3 py-2 text-right">{{ number_format($tax, 2, ',', '.') }}</td>@endif
        <td class="px-3 py-2 text-right font-medium">{{ number_format($total, 2, ',', '.') }}</td>
        <td class="px-3 py-2 text-right font-semibold {{ costColor($costPct) }}">
          {{ number_format($costPct, 2) }}%
        </td>
      </tr>
    @endforeach

    {{-- Category TOTAL row — FIXED: was <<td, now correct <td --}}
    @php $t = $catKpi[$dept][$cat]; @endphp
    <tr class="bg-gray-50 font-semibold border-t border-gray-300">
      <td class="px-6 py-2" colspan="2">{{ $cat ?: 'UNCATEGORIZED' }} TOTAL</td>
      <td class="px-3 py-2 text-right">{{ number_format((float)$t['qty'],0) }}</td>
      <td class="px-3 py-2 text-right">{{ number_format($t['revenue'],2,',','.') }}</td>
      <td class="px-3 py-2 text-right">{{ number_format($t['discount'],2,',','.') }}</td>
      <td class="px-3 py-2 text-right">{{ number_format($t['cost'],2,',','.') }}</td>
      <td class="px-3 py-2 text-right">{{ number_format($t['profit'],2,',','.') }}</td>
      <td class="px-3 py-2 text-right">{{ number_format($t['service'],2,',','.') }}</td>
      <td class="px-3 py-2 text-right">{{ number_format($t['tax'],2,',','.') }}</td>
      <td class="px-3 py-2 text-right">{{ number_format($t['total'],2,',','.') }}</td>
      <td class="px-3 py-2 text-right font-semibold {{ costColor($t['costPct']) }}">
        {{ number_format($t['costPct'],2) }}%
      </td>
    </tr>
  @endforeach

  {{-- Department TOTAL row --}}
  @php $d = $deptKpi[$dept]; @endphp
  <tr class="bg-gray-200 font-bold border-t-2 border-gray-400">
    <td class="px-4 py-3" colspan="2">{{ $dept ?: 'NO DEPARTMENT' }} TOTAL</td>
    <td class="px-3 py-3 text-right">{{ number_format((float)$d['qty'],0) }}</td>
    <td class="px-3 py-3 text-right">{{ number_format($d['revenue'],2,',','.') }}</td>
    <td class="px-3 py-3 text-right">{{ number_format($d['discount'],2,',','.') }}</td>
    <td class="px-3 py-3 text-right">{{ number_format($d['cost'],2,',','.') }}</td>
    <td class="px-3 py-3 text-right">{{ number_format($d['profit'],2,',','.') }}</td>
    <td class="px-3 py-3 text-right">{{ number_format($d['service'],2,',','.') }}</td>
    <td class="px-3 py-3 text-right">{{ number_format($d['tax'],2,',','.') }}</td>
    <td class="px-3 py-3 text-right">{{ number_format($d['total'],2,',','.') }}</td>
    <td class="px-3 py-3 text-right font-bold {{ costColor($d['costPct']) }}">
      {{ number_format($d['costPct'],2) }}%
    </td>
  </tr>
@empty
  <tr><td class="px-3 py-6 text-gray-500" colspan="11">No results.</td></tr>
@endforelse

{{-- GRAND TOTAL row --}}
<tr class="bg-gray-900 text-white font-bold">
  <td class="px-3 py-3" colspan="2">GRAND TOTAL</td>
  <td class="px-3 py-3 text-right">{{ number_format((float)$kpi['qty'],0) }}</td>
  <td class="px-3 py-3 text-right">{{ number_format((float)$kpi['revenue'],2,',','.') }}</td>
  <td class="px-3 py-3 text-right">{{ number_format((float)$kpi['discount'],2,',','.') }}</td>
  <td class="px-3 py-3 text-right">{{ number_format((float)$kpi['cost'],2,',','.') }}</td>
  <td class="px-3 py-3 text-right">{{ number_format((float)$kpi['profit'],2,',','.') }}</td>
  <td class="px-3 py-3 text-right">{{ number_format((float)$kpi['service'],2,',','.') }}</td>
  <td class="px-3 py-3 text-right">{{ number_format((float)$kpi['tax'],2,',','.') }}</td>
  <td class="px-3 py-3 text-right">{{ number_format((float)$kpi['total'],2,',','.') }}</td>
  <td class="px-3 py-3 text-right font-bold {{ costColor($kpi['costPct']) }}">
    {{ number_format($kpi['costPct'],2) }}%
  </td>
</tr>
</tbody>
      </table>
    </div>
  </div>

</div>
@endsection