@extends('layouts.app')

@section('content')
<div class="space-y-5">

  {{-- ── Back link ────────────────────────────────────────────────────────── --}}
  <div>
    <a href="{{ route('support.tickets.index') }}"
      class="inline-flex items-center gap-1.5 text-sm transition hover:-translate-x-0.5"
      style="color:var(--text-muted);">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Back to Tickets
    </a>
  </div>

  <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

    {{-- ── LEFT COLUMN: ticket body + replies + reply form ─────────────────── --}}
    <div class="space-y-5 lg:col-span-2">

      {{-- Ticket header card --}}
      <div class="rounded-2xl p-5 text-white" style="background:var(--sidebar-bg);">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <span class="font-mono text-xs font-semibold" style="color:rgba(255,255,255,0.45);">{{ $ticket->ticket_number }}</span>
            <h1 class="mt-1 text-lg font-bold text-white leading-snug">{{ $ticket->subject }}</h1>
          </div>
          <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $ticket->priorityBadge() }}">
              {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
            </span>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $ticket->statusBadge() }}">
              {{ $statusLabels[$ticket->status] ?? $ticket->status }}
            </span>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2 text-xs sm:grid-cols-3" style="color:rgba(255,255,255,0.5);">
          <div>
            <span class="block uppercase tracking-wide text-[10px]" style="color:rgba(255,255,255,0.3);">Submitter</span>
            <span class="font-medium text-white/80">{{ $ticket->submitter_name }}</span>
          </div>
          <div>
            <span class="block uppercase tracking-wide text-[10px]" style="color:rgba(255,255,255,0.3);">Email</span>
            <span class="font-medium text-white/80">{{ $ticket->submitter_email }}</span>
          </div>
          <div>
            <span class="block uppercase tracking-wide text-[10px]" style="color:rgba(255,255,255,0.3);">Category</span>
            <span class="font-medium text-white/80">{{ $categoryLabels[$ticket->category] ?? $ticket->category }}</span>
          </div>
          <div>
            <span class="block uppercase tracking-wide text-[10px]" style="color:rgba(255,255,255,0.3);">Assigned To</span>
            <span class="font-medium text-white/80">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</span>
          </div>
          <div>
            <span class="block uppercase tracking-wide text-[10px]" style="color:rgba(255,255,255,0.3);">Created</span>
            <span class="font-medium text-white/80">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
          </div>
          @if($ticket->resolved_at)
          <div>
            <span class="block uppercase tracking-wide text-[10px]" style="color:rgba(255,255,255,0.3);">Resolved</span>
            <span class="font-medium text-white/80">{{ $ticket->resolved_at->format('d M Y, H:i') }}</span>
          </div>
          @endif
        </div>
      </div>

      {{-- Description card --}}
      <div class="gfs-card p-5">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Original Message</p>
        <p class="text-sm leading-relaxed whitespace-pre-wrap" style="color:var(--text-primary);">{{ $ticket->description }}</p>
      </div>

      {{-- ── Replies thread ──────────────────────────────────────────────── --}}
      @if($ticket->replies->isNotEmpty())
        <div class="space-y-3">
          <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">
            Replies ({{ $ticket->replies->count() }})
          </p>
          @foreach($ticket->replies as $reply)
            @if($reply->is_staff_reply)
              {{-- Staff reply — dark --}}
              <div class="rounded-2xl p-4" style="background:var(--sidebar-bg);">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold text-white"
                      style="background:rgba(34,197,94,0.25);">
                      {{ strtoupper(substr($reply->reply_by_name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold text-white">{{ $reply->reply_by_name }}</span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" style="background:rgba(34,197,94,0.15);color:#4ade80;">Staff</span>
                  </div>
                  <span class="text-[11px]" style="color:rgba(255,255,255,0.35);">{{ $reply->created_at->format('d M Y, H:i') }}</span>
                </div>
                <p class="text-sm leading-relaxed whitespace-pre-wrap" style="color:rgba(255,255,255,0.80);">{{ $reply->message }}</p>
              </div>
            @else
              {{-- Submitter reply — light --}}
              <div class="gfs-card p-4">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold text-white bg-gray-400">
                      {{ strtoupper(substr($reply->reply_by_name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold" style="color:var(--text-primary);">{{ $reply->reply_by_name }}</span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-500">Submitter</span>
                  </div>
                  <span class="text-[11px]" style="color:var(--text-muted);">{{ $reply->created_at->format('d M Y, H:i') }}</span>
                </div>
                <p class="text-sm leading-relaxed whitespace-pre-wrap" style="color:var(--text-secondary);">{{ $reply->message }}</p>
              </div>
            @endif
          @endforeach
        </div>
      @endif

      {{-- ── Reply form ──────────────────────────────────────────────────── --}}
      <div class="gfs-card p-5">
        <p class="mb-3 text-sm font-semibold" style="color:var(--text-primary);">Add Reply</p>
        <form method="POST" action="{{ route('support.tickets.reply', $ticket->id) }}">
          @csrf
          <textarea name="message" rows="4" required
            placeholder="Type your reply here..."
            class="w-full rounded-xl border px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 resize-none transition"
            style="border-color:var(--card-border);color:var(--text-primary);background:var(--content-bg);">{{ old('message') }}</textarea>
          @error('message')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
          @enderror
          <div class="mt-3 flex justify-end">
            <button type="submit"
              class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5"
              style="background:#16a34a;">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
              </svg>
              Send Reply
            </button>
          </div>
        </form>
      </div>

    </div>

    {{-- ── RIGHT COLUMN: actions sidebar ───────────────────────────────────── --}}
    <div class="space-y-4">

      {{-- Update Status --}}
      <div class="gfs-card p-4">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Update Status</p>
        <form method="POST" action="{{ route('support.tickets.updateStatus', $ticket->id) }}">
          @csrf
          @method('PATCH')
          <select name="status"
            class="w-full rounded-xl border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 mb-3 appearance-none"
            style="border-color:var(--card-border);color:var(--text-primary);background:var(--card-bg);">
            @foreach($statusLabels as $val => $lbl)
              <option value="{{ $val }}" {{ $ticket->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
          </select>
          <button type="submit"
            class="w-full rounded-xl py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
            style="background:var(--sidebar-bg);">
            Save Status
          </button>
        </form>
      </div>

      {{-- Assign to staff --}}
      <div class="gfs-card p-4">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Assign To</p>
        <form method="POST" action="{{ route('support.tickets.assign', $ticket->id) }}">
          @csrf
          @method('PATCH')
          <select name="assigned_to"
            class="w-full rounded-xl border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 mb-3 appearance-none"
            style="border-color:var(--card-border);color:var(--text-primary);background:var(--card-bg);">
            <option value="">— Unassigned —</option>
            @foreach($staffList as $s)
              <option value="{{ $s->id }}" {{ $ticket->assigned_to == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
          </select>
          <button type="submit"
            class="w-full rounded-xl py-2.5 text-sm font-semibold transition hover:bg-gray-100"
            style="border:1px solid var(--card-border);color:var(--text-secondary);background:var(--card-bg);">
            Save Assignment
          </button>
        </form>
      </div>

      {{-- Ticket meta summary --}}
      <div class="gfs-card p-4 space-y-3">
        <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Ticket Info</p>
        @php
          $meta = [
            ['Ticket #',  $ticket->ticket_number],
            ['Category',  $categoryLabels[$ticket->category] ?? $ticket->category],
            ['Priority',  $priorityLabels[$ticket->priority] ?? $ticket->priority],
            ['Replies',   $ticket->replies->count()],
            ['Opened',    $ticket->created_at->diffForHumans()],
          ];
        @endphp
        @foreach($meta as [$label, $value])
          <div class="flex items-center justify-between text-xs">
            <span style="color:var(--text-muted);">{{ $label }}</span>
            <span class="font-medium {{ $label === 'Ticket #' ? 'font-mono' : '' }}" style="color:var(--text-primary);">{{ $value }}</span>
          </div>
        @endforeach
      </div>

    </div>
  </div>

</div>
@endsection
