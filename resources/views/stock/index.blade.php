@extends('layouts.app')

@section('content')
<div class="space-y-5">

  {{-- ── Page header ──────────────────────────────────────────────────────── --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-xl font-bold" style="color:var(--text-primary)">Stock Counts</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-muted)">Review and approve stock count submissions</p>
    </div>
  </div>

  {{-- ── Summary strip ────────────────────────────────────────────────────── --}}
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    @php
      $statCards = [
        ['label' => 'Total',     'value' => $stats['total'],     'color' => 'text-gray-600',  'bg' => 'bg-gray-50'],
        ['label' => 'Draft',     'value' => $stats['draft'],     'color' => 'text-gray-600',  'bg' => 'bg-gray-50'],
        ['label' => 'Submitted', 'value' => $stats['submitted'], 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
        ['label' => 'Approved',  'value' => $stats['approved'],  'color' => 'text-green-600', 'bg' => 'bg-green-50'],
      ];
    @endphp
    @foreach($statCards as $stat)
      <div class="gfs-card flex items-center gap-4 p-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $stat['bg'] }}">
          <span class="text-lg font-bold {{ $stat['color'] }}">{{ $stat['value'] }}</span>
        </div>
        <p class="text-sm font-medium" style="color:var(--text-secondary)">{{ $stat['label'] }}</p>
      </div>
    @endforeach
  </div>

  {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
  <div class="gfs-card p-4">
    <form method="GET" action="{{ route('stock.counts.index') }}" class="flex flex-wrap gap-2 items-end">

      {{-- Warehouse --}}
      <select name="warehouse_id" onchange="this.form.submit()"
        class="rounded-lg border px-3 py-1.5 text-xs font-medium focus:outline-none"
        style="border-color:var(--card-border);color:var(--text-secondary);background:var(--card-bg);">
        <option value="">All Warehouses</option>
        @foreach($warehouses as $wh)
          <option value="{{ $wh->id }}" {{ ($filters['warehouse_id'] ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
        @endforeach
      </select>

      {{-- Type --}}
      <select name="count_type" onchange="this.form.submit()"
        class="rounded-lg border px-3 py-1.5 text-xs font-medium focus:outline-none"
        style="border-color:var(--card-border);color:var(--text-secondary);background:var(--card-bg);">
        <option value="">All Types</option>
        <option value="daily"   {{ ($filters['count_type'] ?? '') === 'daily'   ? 'selected' : '' }}>Daily</option>
        <option value="monthly" {{ ($filters['count_type'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
      </select>

      {{-- Status --}}
      <select name="status" onchange="this.form.submit()"
        class="rounded-lg border px-3 py-1.5 text-xs font-medium focus:outline-none"
        style="border-color:var(--card-border);color:var(--text-secondary);background:var(--card-bg);">
        <option value="">All Statuses</option>
        <option value="draft"     {{ ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' }}>Draft</option>
        <option value="submitted" {{ ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' }}>Submitted</option>
        <option value="approved"  {{ ($filters['status'] ?? '') === 'approved'  ? 'selected' : '' }}>Approved</option>
        <option value="rejected"  {{ ($filters['status'] ?? '') === 'rejected'  ? 'selected' : '' }}>Rejected</option>
      </select>

      {{-- Date from --}}
      <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
        class="rounded-lg border px-3 py-1.5 text-xs focus:outline-none"
        style="border-color:var(--card-border);color:var(--text-primary);background:var(--card-bg);">

      {{-- Date to --}}
      <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
        class="rounded-lg border px-3 py-1.5 text-xs focus:outline-none"
        style="border-color:var(--card-border);color:var(--text-primary);background:var(--card-bg);">

      {{-- Search --}}
      <div class="relative">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
          placeholder="Search warehouse, notes..."
          class="rounded-lg border pl-8 pr-3 py-1.5 text-xs focus:outline-none w-48"
          style="border-color:var(--card-border);color:var(--text-primary);background:var(--card-bg);">
        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5" style="color:var(--text-muted);"
          fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </div>

      <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white transition"
        style="background:var(--sidebar-bg);">Apply</button>

      @if(array_filter($filters))
        <a href="{{ route('stock.counts.index') }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
          style="color:var(--text-muted);background:var(--content-bg);">Clear</a>
      @endif

    </form>
  </div>

  {{-- ── Table ────────────────────────────────────────────────────────────── --}}
  <div class="gfs-card overflow-hidden">
    @if($counts->isEmpty())
      <div class="flex flex-col items-center gap-3 py-16 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50">
          <svg class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold" style="color:var(--text-primary)">No stock counts found</p>
          <p class="mt-1 text-xs" style="color:var(--text-muted)">Try adjusting your filters or search query.</p>
        </div>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr style="border-bottom:1px solid var(--card-border);background:var(--content-bg);">
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Warehouse</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Items</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Created by</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Submitted at</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($counts as $count)
              <tr class="transition-colors hover:bg-gray-50" style="border-bottom:1px solid var(--card-border);">
                <td class="px-4 py-3">
                  <span class="text-xs font-medium" style="color:var(--text-primary);">{{ $count->count_date->format('d M Y') }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="text-xs" style="color:var(--text-secondary);">{{ $count->warehouse_name }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="text-xs capitalize" style="color:var(--text-secondary);">{{ $count->count_type }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="text-xs" style="color:var(--text-secondary);">{{ $count->lines_count }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $count->statusBadge() }}">
                    {{ ucfirst($count->status) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span class="text-xs" style="color:var(--text-secondary);">{{ $count->creator?->name ?? '—' }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="text-xs" style="color:var(--text-muted);">{{ $count->submitted_at?->format('d M Y, H:i') ?? '—' }}</span>
                </td>
                <td class="px-4 py-3">
                  <a href="{{ route('stock.counts.show', $count->id) }}"
                    class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-xs font-medium transition hover:bg-gray-50"
                    style="border-color:var(--card-border);color:var(--text-secondary);">
                    View
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($counts->hasPages())
        <div class="border-t px-4 py-3" style="border-color:var(--card-border);">
          {{ $counts->links() }}
        </div>
      @endif
    @endif
  </div>

</div>
@endsection

@push('scripts')
@vite('resources/js/pages/stock-counts.js')
@endpush
