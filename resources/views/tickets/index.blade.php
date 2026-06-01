@extends('layouts.app')

@section('content')
<div class="space-y-5">

  {{-- ── Page header ──────────────────────────────────────────────────────── --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-xl font-bold" style="color:var(--text-primary)">Support Tickets</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-muted)">Manage and respond to helpdesk tickets</p>
    </div>
  </div>

  {{-- ── Stats strip ──────────────────────────────────────────────────────── --}}
  <div class="grid grid-cols-3 gap-4">
    @php
      $statCards = [
        ['label' => 'Open',            'value' => $stats['open'],          'color' => 'text-blue-600',  'bg' => 'bg-blue-50'],
        ['label' => 'In Progress',     'value' => $stats['in_progress'],   'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
        ['label' => 'Resolved Today',  'value' => $stats['resolved_today'],'color' => 'text-green-600', 'bg' => 'bg-green-50'],
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
    <form method="GET" action="{{ route('support.tickets.index') }}" class="flex flex-wrap gap-3 items-end">

      {{-- Status tabs --}}
      <div class="flex flex-wrap gap-1.5">
        @php
          $allStatuses = ['all' => 'All'] + $statusLabels;
          $activeStatus = $filters['status'] ?? 'all';
        @endphp
        @foreach($allStatuses as $val => $lbl)
          <a href="{{ route('support.tickets.index', array_merge($filters, ['status' => $val])) }}"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150
              {{ $activeStatus === $val
                ? 'bg-gray-900 text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $lbl }}
          </a>
        @endforeach
      </div>

      <div class="flex flex-wrap gap-2 ml-auto items-end">
        {{-- Priority --}}
        <select name="priority" onchange="this.form.submit()"
          class="rounded-lg border px-3 py-1.5 text-xs font-medium focus:outline-none"
          style="border-color:var(--card-border);color:var(--text-secondary);background:var(--card-bg);">
          <option value="all" {{ ($filters['priority'] ?? 'all') === 'all' ? 'selected' : '' }}>All Priorities</option>
          @foreach($priorityLabels as $v => $l)
            <option value="{{ $v }}" {{ ($filters['priority'] ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
          @endforeach
        </select>

        {{-- Category --}}
        <select name="category" onchange="this.form.submit()"
          class="rounded-lg border px-3 py-1.5 text-xs font-medium focus:outline-none"
          style="border-color:var(--card-border);color:var(--text-secondary);background:var(--card-bg);">
          <option value="all" {{ ($filters['category'] ?? 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
          @foreach($categoryLabels as $v => $l)
            <option value="{{ $v }}" {{ ($filters['category'] ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
          @endforeach
        </select>

        {{-- Assigned To --}}
        <select name="assigned_to" onchange="this.form.submit()"
          class="rounded-lg border px-3 py-1.5 text-xs font-medium focus:outline-none"
          style="border-color:var(--card-border);color:var(--text-secondary);background:var(--card-bg);">
          <option value="">All Staff</option>
          <option value="unassigned" {{ ($filters['assigned_to'] ?? '') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
          @foreach($staffList as $s)
            <option value="{{ $s->id }}" {{ ($filters['assigned_to'] ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
          @endforeach
        </select>

        {{-- Search --}}
        <div class="relative">
          <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
            placeholder="Search tickets..."
            class="rounded-lg border pl-8 pr-3 py-1.5 text-xs focus:outline-none w-44"
            style="border-color:var(--card-border);color:var(--text-primary);background:var(--card-bg);">
          <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5" style="color:var(--text-muted);"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>

        <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white transition"
          style="background:var(--sidebar-bg);">Search</button>

        @if(array_filter($filters))
          <a href="{{ route('support.tickets.index') }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
            style="color:var(--text-muted);background:var(--content-bg);">Clear</a>
        @endif
      </div>

    </form>
  </div>

  {{-- ── Table ────────────────────────────────────────────────────────────── --}}
  <div class="gfs-card overflow-hidden">
    @if($tickets->isEmpty())
      <div class="flex flex-col items-center gap-3 py-16 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50">
          <svg class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold" style="color:var(--text-primary)">No tickets found</p>
          <p class="mt-1 text-xs" style="color:var(--text-muted)">Try adjusting your filters or search query.</p>
        </div>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr style="border-bottom:1px solid var(--card-border);background:var(--content-bg);">
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Ticket #</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Subject</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Submitter</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Category</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Priority</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Assigned To</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Created</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($tickets as $ticket)
              <tr class="transition-colors hover:bg-gray-50" style="border-bottom:1px solid var(--card-border);">

                {{-- Ticket number --}}
                <td class="px-4 py-3">
                  <span class="font-mono text-xs font-semibold" style="color:var(--text-primary);">{{ $ticket->ticket_number }}</span>
                </td>

                {{-- Subject --}}
                <td class="px-4 py-3 max-w-[220px]">
                  <p class="truncate text-sm font-medium" style="color:var(--text-primary);">{{ $ticket->subject }}</p>
                </td>

                {{-- Submitter --}}
                <td class="px-4 py-3">
                  <p class="text-xs font-medium" style="color:var(--text-primary);">{{ $ticket->submitter_name }}</p>
                  <p class="text-[11px]" style="color:var(--text-muted);">{{ $ticket->submitter_email }}</p>
                </td>

                {{-- Category --}}
                <td class="px-4 py-3">
                  <span class="text-xs" style="color:var(--text-secondary);">{{ $categoryLabels[$ticket->category] ?? $ticket->category }}</span>
                </td>

                {{-- Priority badge --}}
                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $ticket->priorityBadge() }}">
                    {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
                  </span>
                </td>

                {{-- Status badge --}}
                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $ticket->statusBadge() }}">
                    {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                  </span>
                </td>

                {{-- Assigned --}}
                <td class="px-4 py-3">
                  @if($ticket->assignedTo)
                    <span class="text-xs" style="color:var(--text-secondary);">{{ $ticket->assignedTo->name }}</span>
                  @else
                    <span class="text-xs italic" style="color:var(--text-muted);">Unassigned</span>
                  @endif
                </td>

                {{-- Created at --}}
                <td class="px-4 py-3">
                  <span class="text-xs" style="color:var(--text-muted);">{{ $ticket->created_at->format('d M Y') }}</span>
                </td>

                {{-- Action --}}
                <td class="px-4 py-3">
                  <a href="{{ route('support.tickets.show', $ticket->id) }}"
                    @click="loading = true"
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

      {{-- Pagination --}}
      @if($tickets->hasPages())
        <div class="border-t px-4 py-3" style="border-color:var(--card-border);">
          {{ $tickets->links() }}
        </div>
      @endif
    @endif
  </div>

</div>
@endsection
