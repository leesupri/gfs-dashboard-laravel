@extends('layouts.app')

@section('content')
<div
  x-data="{
    showFilter: {{ request()->hasAny(['start','end']) ? 'true' : 'false' }}
  }" class="max-w-7xl mx-auto p-6 space-y-8">

  {{-- Header --}}
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-bold">Summary Sales Report</h1>
      <p class="text-sm text-gray-500">
        {{ $filters['from']->toDateString() }} → {{ $filters['to']->toDateString() }}
      </p>
    </div>

    <div class="flex gap-2">
      <a href="{{ route('summarySales.export', request()->query()) }}"
         class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
         Export CSV
      </a>
      <button
        type="button"
        @click="showFilter = !showFilter"
        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-white transition active:scale-95"
        :class="showFilter ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span x-text="showFilter ? 'Hide Filters' : 'Filters'"></span>
      </button>
    </div>
  </div>

  {{-- Filters --}}
<form
  x-show="showFilter"
  x-cloak
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0 -translate-y-2"
  x-transition:enter-end="opacity-100 translate-y-0"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100 translate-y-0"
  x-transition:leave-end="opacity-0 -translate-y-2"
  method="GET"
  action="{{ route('summarySales.index') }}"
  class="border rounded-xl p-4 bg-white grid grid-cols-1 md:grid-cols-4 gap-4">

  <div>
    <label class="text-xs text-gray-500">From</label>
    <input type="date" name="start"
      value="{{ request('start') ?? $filters['from']->toDateString() }}"
      class="w-full border rounded px-2 py-1">
  </div>

  <div>
    <label class="text-xs text-gray-500">To</label>
    <input type="date" name="end"
      value="{{ request('end') ?? $filters['to']->toDateString() }}"
      class="w-full border rounded px-2 py-1">
  </div>

  <div class="flex items-end gap-2">
    <button class="px-4 py-2 bg-gray-900 text-white rounded">
      Apply
    </button>
    <a href="{{ route('summarySales.index') }}"
       class="px-4 py-2 border rounded">
      Reset
    </a>
  </div>

  {{-- Presets --}}
  <div class="flex items-end gap-2 text-sm">
    <a href="{{ route('summarySales.index', [
        'start'=>now()->toDateString(),
        'end'=>now()->toDateString()
    ]) }}" class="underline">Today</a>

    <a href="{{ route('summarySales.index', [
        'start'=>now()->subDay()->toDateString(),
        'end'=>now()->subDay()->toDateString()
    ]) }}" class="underline">Yesterday</a>

    <a href="{{ route('summarySales.index', [
        'start'=>now()->startOfMonth()->toDateString(),
        'end'=>now()->endOfMonth()->toDateString()
    ]) }}" class="underline">This Month</a>
  </div>
</form>

    {{-- KPI --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4">
  <x-kpi label="Subtotal" :value="$summary->subtotal"/>
  <x-kpi label="Discount" :value="$summary->discount" negative/>
  <x-kpi label="Net Sales" :value="$summary->subtotal - $summary->discount"/>

  @if($summary->service > 0)
    <x-kpi label="Service" :value="$summary->service"/>
  @endif

  @if($summary->tax > 0)
    <x-kpi label="Tax" :value="$summary->tax"/>
  @endif

  <x-kpi label="Total" :value="$summary->total" highlight/>
</div>

    {{-- Payments --}}
   <div class="rounded-xl border p-4 bg-white">
  <h2 class="font-semibold mb-2">Payment</h2>

  @foreach($payments as $p)
    <div class="flex justify-between text-sm">
      <span>{{ $p->qty }} {{ $p->name }}</span>
      <span>
        {{ number_format($p->amount,2) }}
        <span class="text-gray-500">
          ({{ number_format($p->percentage,0) }}%)
        </span>
      </span>
    </div>
  @endforeach

  <div class="border-t mt-2 pt-2 flex justify-between font-semibold">
    <span>Total</span>
    <span>{{ number_format($payments->sum('amount'),2) }}</span>
  </div>
</div>

    {{-- Profit & Loss --}}
    <div class="rounded-xl border p-4 bg-white">
  <h2 class="font-semibold mb-2">Profit & Loss</h2>

  <x-row label="Subtotal" :value="$profit->subtotal"/>
  <x-row label="Discount" :value="$profit->discount" negative/>
  <x-row label="Net Sales" :value="$profit->netSales"/>

  <x-row
    label="Total Cost"
    :value="$profit->totalCost"
    negative
    note="{{ number_format($profit->costPercent,0) }}%"
    class="
      {{ $profit->costPercent > 70 ? 'text-red-600' :
         ($profit->costPercent > 55 ? 'text-amber-600' : 'text-emerald-600') }}
    "
  />

  <x-row
    label="Profit"
    :value="$profit->profit"
    strong
    class="{{ $profit->profit < 0 ? 'text-red-600' : 'text-emerald-600' }}"
  />
</div>

<div class="bg-white border rounded-xl p-4">
  <h3 class="font-semibold mb-2">Void</h3>

  @foreach($voids as $v)
    <div class="flex justify-between text-sm">
      <span>{{ number_format($v->qty) }} {{ $v->description }}</span>
      <span>{{ number_format($v->price,2) }}</span>
    </div>
  @endforeach

  <div class="border-t mt-2 pt-2 font-semibold flex justify-between">
    <span>Total</span>
    <span>{{ number_format($voids->sum('price'),2) }}</span>
  </div>
</div>


    {{-- Department --}}
    @php
  $sections = [
    ['title'=>'Department','rows'=>$department,'label'=>'department'],
    ['title'=>'Category','rows'=>$category,'label'=>'category'],
  ];
@endphp

@foreach($sections as $s)
<div class="rounded-xl border bg-white p-4">
  <h3 class="font-semibold mb-2">{{ $s['title'] }}</h3>

  @foreach($s['rows'] as $r)
    <div class="flex justify-between text-sm">
      <span>{{ number_format($r->qty) }} {{ $r->{$s['label']} }}</span>
      <span>
        {{ number_format($r->price,2) }}
        <span class="text-gray-500">
          ({{ number_format($r->percent,0) }}%)
        </span>
      </span>
    </div>
  @endforeach

  <div class="border-t mt-2 pt-2 flex justify-between font-semibold">
    <span>Total</span>
    <span>{{ number_format($s['rows']->sum('price'),2) }}</span>
  </div>
</div>
@endforeach

  {{-- visitor --}}

  <div class="border rounded-xl p-4 bg-white">
  <h3 class="font-semibold mb-2">Visitor Statistic</h3>

  <x-row label="Total Sales" :value="$stats->ttl"/>
  <x-row label="Total Guest" :value="$stats->guest"/>
  <x-row label="Avg / Guest" :value="$stats->avgGuest"/>
  <x-row label="Transactions" :value="$stats->trans"/>
  <x-row label="Avg / Transaction" :value="$stats->avgTrans"/>
</div>
</div>
@endsection
