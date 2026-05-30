@extends('layouts.error')

@section('content')
<div class="w-full max-w-lg mx-auto text-left slide-up-1">

  {{-- Card --}}
  <div class="rounded-2xl overflow-hidden" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);box-shadow:0 8px 32px rgba(0,0,0,0.3);">

    {{-- Header --}}
    <div class="px-8 py-6" style="border-bottom:1px solid rgba(255,255,255,0.08);">
      <div class="flex items-center gap-3 mb-1">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.15);">
          <svg class="h-5 w-5" style="color:#4ade80;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <h1 class="text-lg font-semibold text-white">Check Ticket Status</h1>
      </div>
      <p class="text-sm" style="color:rgba(255,255,255,0.45);">Enter your ticket number to view its current status.</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
      <div class="mx-8 mt-6 rounded-xl px-4 py-3 text-sm" style="background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:#4ade80;">
        {{ session('success') }}
      </div>
    @endif

    {{-- Lookup Form --}}
    <form method="POST" action="{{ route('tickets.statusCheck') }}" class="px-8 pt-6 pb-4">
      @csrf
      <div class="flex gap-3">
        <input type="text" name="ticket_number"
          value="{{ $number }}"
          placeholder="e.g. TKT-202605-0001"
          class="flex-1 rounded-xl px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none font-mono uppercase transition"
          style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);">
        <button type="submit"
          class="rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all hover:-translate-y-0.5"
          style="background:#16a34a;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
          Look Up
        </button>
      </div>
      @error('ticket_number')
        <p class="mt-2 text-xs" style="color:#fca5a5;">{{ $message }}</p>
      @enderror
    </form>

    {{-- Result --}}
    @if($number !== '')
      @if($ticket)
        @php
          $statusColors = [
            'open'        => ['bg' => 'rgba(59,130,246,0.15)', 'border' => 'rgba(59,130,246,0.3)', 'text' => '#93c5fd', 'dot' => '#3b82f6'],
            'in_progress' => ['bg' => 'rgba(245,158,11,0.15)', 'border' => 'rgba(245,158,11,0.3)', 'text' => '#fcd34d', 'dot' => '#f59e0b'],
            'resolved'    => ['bg' => 'rgba(34,197,94,0.15)',  'border' => 'rgba(34,197,94,0.3)',  'text' => '#4ade80', 'dot' => '#22c55e'],
            'closed'      => ['bg' => 'rgba(107,114,128,0.15)','border' => 'rgba(107,114,128,0.3)','text' => '#d1d5db', 'dot' => '#6b7280'],
          ];
          $sc = $statusColors[$ticket->status] ?? $statusColors['closed'];
        @endphp

        <div class="px-8 pb-8 space-y-4 slide-up-2">

          {{-- Status badge prominent --}}
          <div class="flex items-center gap-3 rounded-xl px-4 py-3"
            style="background:{{ $sc['bg'] }};border:1px solid {{ $sc['border'] }};">
            <span class="inline-block h-2.5 w-2.5 rounded-full flex-shrink-0" style="background:{{ $sc['dot'] }};"></span>
            <span class="text-sm font-semibold" style="color:{{ $sc['text'] }};">
              {{ $statusLabels[$ticket->status] ?? $ticket->status }}
            </span>
            <span class="ml-auto text-xs font-mono" style="color:rgba(255,255,255,0.4);">{{ $ticket->ticket_number }}</span>
          </div>

          {{-- Ticket info table --}}
          <div class="rounded-xl overflow-hidden" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
            @php
              $rows = [
                ['Subject',    $ticket->subject],
                ['Category',   $categoryLabels[$ticket->category] ?? $ticket->category],
                ['Submitted',  $ticket->created_at->format('d M Y, H:i')],
                ['Last Updated', $ticket->updated_at->format('d M Y, H:i')],
              ];
              if ($ticket->resolved_at)
                $rows[] = ['Resolved At', $ticket->resolved_at->format('d M Y, H:i')];
            @endphp

            @foreach($rows as $i => [$label, $value])
              <div class="flex items-start px-4 py-3 {{ $i < count($rows)-1 ? '' : '' }}"
                style="{{ $i < count($rows)-1 ? 'border-bottom:1px solid rgba(255,255,255,0.06);' : '' }}">
                <span class="w-32 flex-shrink-0 text-xs font-semibold uppercase tracking-wide" style="color:rgba(255,255,255,0.4);">{{ $label }}</span>
                <span class="text-sm" style="color:rgba(255,255,255,0.85);">{{ $value }}</span>
              </div>
            @endforeach
          </div>

          <p class="text-xs" style="color:rgba(255,255,255,0.3);">
            For privacy, ticket descriptions and replies are not shown here. Contact support if you need further assistance.
          </p>
        </div>

      @else
        {{-- Not found --}}
        <div class="px-8 pb-8 slide-up-2">
          <div class="rounded-xl px-4 py-4 text-center" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);">
            <p class="text-sm font-semibold" style="color:#fca5a5;">Ticket not found</p>
            <p class="mt-1 text-xs" style="color:rgba(255,255,255,0.4);">No ticket found for <span class="font-mono">{{ $number }}</span>. Please check the number and try again.</p>
          </div>
        </div>
      @endif
    @endif

    {{-- Footer links --}}
    <div class="px-8 py-4" style="border-top:1px solid rgba(255,255,255,0.06);">
      <div class="flex items-center justify-between">
        <a href="{{ route('login') }}" class="text-xs transition" style="color:rgba(255,255,255,0.35);"
          onmouseover="this.style.color='rgba(255,255,255,0.65)'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
          &larr; Back to login
        </a>
        <a href="{{ route('tickets.create') }}" class="text-xs transition" style="color:rgba(34,197,94,0.7);"
          onmouseover="this.style.color='#4ade80'" onmouseout="this.style.color='rgba(34,197,94,0.7)'">
          Submit new ticket &rarr;
        </a>
      </div>
    </div>

  </div>

</div>
@endsection
