@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ (request('q') || request('start') || request('end') || request('dept')) ? 'true' : 'false' }} }" class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold">No Sales Report</h1>
            <p class="text-sm text-gray-600">
                Compliment & Duty Meal tracking (audit-ready)
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('noSales.export', request()->query()) }}"
                class="inline-flex items-center rounded bg-emerald-600 px-3 py-1.5 text-sm text-white hover:bg-emerald-700"
                >
                Export CSV
            </a>
            <button type="button" @click="filtersOpen = !filtersOpen"
            class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
            Filter
            </button>

        </div>
  </div>

  {{-- Filters --}}
  <form
  method="GET"
  action="{{ route('noSales.index') }}"
  x-show="filtersOpen"
  x-cloak
  class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"
>
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-8">
  <input type="date" name="start" value="{{ $filters['start'] ?? '' }}"
    class="rounded border px-2 py-1 lg:col-span-2">

  <input type="date" name="end" value="{{ $filters['end'] ?? '' }}"
    class="rounded border px-2 py-1 lg:col-span-2">

    <select name="type" class="rounded border px-2 py-1">
      <option value="">All Types</option>
      <option value="compliment" {{ ($filters['type'] ?? '')==='compliment'?'selected':'' }}>Compliment</option>
      <option value="duty" {{ ($filters['type'] ?? '')==='duty'?'selected':'' }}>Duty Meal</option>
      <option value="other" {{ ($filters['type'] ?? '')==='other'?'selected':'' }}>Other</option>
    </select>

    <input
    name="q"
    value="{{ $filters['q'] ?? '' }}"
    placeholder="Item / reason"
    class="rounded border px-2 py-1 lg:col-span-2">

  <div class="flex gap-2 lg:col-span-8">
    <button class="rounded bg-gray-900 text-white px-4 py-1.5">
      Apply
    </button>

    <a href="{{ route('noSales.index') }}"
      class="rounded border border-gray-300 px-4 py-1.5 text-sm text-gray-700 hover:bg-gray-50">
      Reset
    </a>
</div>
</div>
  </form>

  {{-- Table --}}
  <div class="overflow-auto rounded-xl border bg-white">
    <table class="w-full text-sm">
      

      <tbody class="divide-y">
        {{-- Receipts --}}
@forelse($bySales as $salesId => $items)
  @php $h = $items->first(); @endphp

  <div class="rounded-xl border bg-white overflow-hidden">

    {{-- Receipt Header --}}
    <div class="bg-gray-50 px-4 py-3 border-b">
      <div class="flex justify-between items-start">
        <div>
          <div class="font-semibold text-gray-900">
            Sales ID #{{ $salesId }}
          </div>
          <div class="text-xs text-gray-600">
            Table: {{ $h->tableName ?? '-' }} •
            Approved: {{ $h->approved_by ?? '-' }}
          </div>
          <div class="text-xs text-gray-500 mt-1">
            {{ $h->notes ?? '-' }}
          </div>
        </div>

        <span class="px-2 py-1 rounded text-xs font-semibold
          {{ $h->no_sale_type === 'COMPLIMENT' ? 'bg-green-100 text-green-800' :
             ($h->no_sale_type === 'DUTY MEAL' ? 'bg-yellow-100 text-yellow-800' :
              'bg-gray-100 text-gray-700') }}">
          {{ $h->no_sale_type }}
        </span>
      </div>
    </div>

    {{-- Items --}}
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-xs text-gray-600">
        <tr>
          <th class="px-4 py-2 text-left">Item</th>
          <th class="px-4 py-2 text-center">Qty</th>
          <th class="px-4 py-2 text-right">Cost</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @foreach($items as $r)
          <tr>
            <td class="px-4 py-2">{{ $r->description }}</td>
            <td class="px-4 py-2 text-center">{{ number_format($r->quantity,0) }}</td>
            <td class="px-4 py-2 text-right">
              Rp {{ number_format($r->unitCost * $r->quantity,2,',','.') }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Totals --}}
    <div class="border-t bg-gray-50 px-4 py-3 text-sm">
      <div class="grid grid-cols-5 gap-2 text-right">
  <div>
    Subtotal<br>
    <b>Rp {{ number_format($h->subtotal ?? 0, 2, ',', '.') }}</b>
  </div>

  <div>
    Discount<br>
    <b>Rp {{ number_format($h->discountAmount ?? 0, 2, ',', '.') }}</b>
  </div>

  <div>
    Service<br>
    <b>Rp {{ number_format($h->serviceChargeAmount ?? 0, 2, ',', '.') }}</b>
  </div>

  <div>
    Tax<br>
    <b>Rp {{ number_format($h->taxAmount ?? 0, 2, ',', '.') }}</b>
  </div>

  <div class="font-semibold">
    Total<br>
    <b>Rp {{ number_format($h->total ?? 0, 2, ',', '.') }}</b>
  </div>
</div>
    </div>

  </div>

@empty
  <div class="text-center text-gray-500 py-8">
    No no-sales records found.
  </div>
@endforelse

      </tbody>
    </table>
  </div>
</div>



@endsection
