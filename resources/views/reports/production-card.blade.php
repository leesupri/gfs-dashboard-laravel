@extends('layouts.app')

@section('content')
<div class="space-y-6">

  {{-- Page header --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Production Card</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        Production #{{ $header->id }} · {{ $header->date ? \Carbon\Carbon::parse($header->date)->format('d M Y') : '—' }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.productionCard.index', request()->query()) }}"
        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium transition hover:bg-gray-50"
        style="color:var(--text-secondary)">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to List
      </a>
      <button onclick="window.print()"
        class="inline-flex items-center gap-1.5 rounded-xl bg-green-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print
      </button>
    </div>
  </div>

  {{-- Production header card --}}
  <div class="gfs-card overflow-hidden">
    <div class="grid grid-cols-2 gap-0 sm:grid-cols-4" style="background:var(--sidebar-bg)">
      <div class="border-r px-5 py-4" style="border-color:rgba(255,255,255,0.08)">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Production No.</p>
        <p class="mt-1 font-mono text-lg font-bold text-white">#{{ $header->id }}</p>
      </div>
      <div class="border-r px-5 py-4" style="border-color:rgba(255,255,255,0.08)">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Date</p>
        <p class="mt-1 text-sm font-bold text-white">
          {{ $header->date ? \Carbon\Carbon::parse($header->date)->format('d M Y') : '—' }}
        </p>
        @if($header->date)
          <p class="text-xs tabular-nums" style="color:rgba(255,255,255,0.45)">
            {{ \Carbon\Carbon::parse($header->date)->format('H:i') }}
          </p>
        @endif
      </div>
      <div class="border-r px-5 py-4" style="border-color:rgba(255,255,255,0.08)">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Warehouse</p>
        <p class="mt-1 text-sm font-bold text-white">{{ $header->warehouse ?: '—' }}</p>
      </div>
      <div class="px-5 py-4">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Saved By</p>
        <div class="mt-1 flex items-center gap-2">
          <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
            style="background:rgba(34,197,94,0.25); color:#4ade80">
            {{ strtoupper(substr($header->savedBy ?? '?', 0, 1)) }}
          </div>
          <p class="text-sm font-semibold text-white">{{ $header->savedBy ?: '—' }}</p>
        </div>
      </div>
    </div>

    {{-- Report time strip --}}
    <div class="flex items-center justify-between border-b px-5 py-2.5" style="border-color:var(--card-border); background:var(--content-bg)">
      <div class="flex items-center gap-2">
        <span class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Products</span>
        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">{{ $products->count() }}</span>
      </div>
      <p class="text-xs" style="color:var(--text-muted)">
        Report: {{ now()->format('d M Y · H:i') }}
      </p>
    </div>

    {{-- Hierarchical table --}}
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-right text-[10px] font-semibold uppercase tracking-wider"
            style="background:var(--content-bg); border-bottom:2px solid var(--card-border); color:var(--text-muted)">
            <th class="px-4 py-2.5 text-left w-28">Code</th>
            <th class="px-4 py-2.5 text-left">Name</th>
            <th class="px-4 py-2.5 text-left w-36">Category</th>
            <th class="px-4 py-2.5 w-24">Quantity</th>
            <th class="px-4 py-2.5 w-20 text-left">UOM</th>
            <th class="px-4 py-2.5 text-left">Description</th>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $product)

            {{-- Product row (dark header) --}}
            <tr style="background:var(--sidebar-bg); border-top:2px solid rgba(34,197,94,0.2)">
              <td class="px-4 py-3">
                <span class="font-mono text-xs font-semibold text-green-400">{{ $product->product_code ?: '—' }}</span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <span class="h-2 w-2 rounded-full bg-green-400"></span>
                  <span class="text-sm font-bold text-white">{{ $product->product_name ?: '—' }}</span>
                </div>
              </td>
              <td class="px-4 py-3">
                <span class="rounded-full bg-green-900/50 px-2 py-0.5 text-xs font-medium text-green-300">
                  {{ $product->category ?: '—' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <span class="text-sm font-bold tabular-nums text-white">
                  {{ number_format((float)$product->product_qty, 2, ',', '.') }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span class="text-xs font-medium" style="color:rgba(255,255,255,0.65)">{{ $product->product_uom ?: '—' }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="text-xs" style="color:rgba(255,255,255,0.5)">{{ $product->product_description ?: '' }}</span>
              </td>
            </tr>

            {{-- Recipe rows --}}
            @foreach($product->recipes as $recipe)
              <tr class="transition-colors hover:bg-gray-50/60" style="border-bottom:1px solid var(--card-border)">
                <td class="py-2.5" style="padding-left:2rem">
                  <span class="font-mono text-xs" style="color:var(--text-muted)">{{ $recipe->recipe_code ?: '—' }}</span>
                </td>
                <td class="px-4 py-2.5">
                  <div class="flex items-center gap-2">
                    <span class="text-[10px]" style="color:var(--text-muted)">↳</span>
                    <span class="text-sm font-medium" style="color:var(--text-primary)">{{ $recipe->recipe_name ?: '—' }}</span>
                  </div>
                </td>
                <td class="px-4 py-2.5">
                  <span class="text-xs" style="color:var(--text-muted)">{{ $recipe->recipe_category ?: '—' }}</span>
                </td>
                <td class="px-4 py-2.5 text-right">
                  <span class="text-sm tabular-nums font-semibold" style="color:var(--text-primary)">
                    {{ number_format((float)$recipe->recipe_qty, 2, ',', '.') }}
                  </span>
                </td>
                <td class="px-4 py-2.5">
                  <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                    {{ $recipe->recipe_uom ?: '—' }}
                  </span>
                </td>
                <td class="px-4 py-2.5">
                  <span class="text-xs" style="color:var(--text-muted)">{{ $recipe->recipe_description ?: '' }}</span>
                </td>
              </tr>
            @endforeach

          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Notes --}}
    <div class="border-t px-5 py-4" style="border-color:var(--card-border); background:var(--content-bg)">
      <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Notes</p>
      <div class="min-h-16 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm whitespace-pre-line"
        style="color:{{ $header->notes ? 'var(--text-primary)' : 'var(--text-muted)' }}">
        {{ $header->notes ?: 'No notes.' }}
      </div>
    </div>

  </div>

</div>
@endsection
