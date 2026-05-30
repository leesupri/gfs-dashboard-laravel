@extends('layouts.error')

@section('content')
<div class="w-full max-w-xl mx-auto text-left slide-up-1">

  {{-- Card --}}
  <div class="rounded-2xl overflow-hidden" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);box-shadow:0 8px 32px rgba(0,0,0,0.3);">

    {{-- Header --}}
    <div class="px-8 py-6" style="border-bottom:1px solid rgba(255,255,255,0.08);">
      <div class="flex items-center gap-3 mb-1">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.15);">
          <svg class="h-5 w-5" style="color:#4ade80;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
          </svg>
        </div>
        <h1 class="text-lg font-semibold text-white">Submit a Support Ticket</h1>
      </div>
      <p class="text-sm" style="color:rgba(255,255,255,0.45);">We'll get back to you as soon as possible.</p>
    </div>

    {{-- Flash success --}}
    @if(session('success'))
      <div class="mx-8 mt-6 rounded-xl px-4 py-3 text-sm" style="background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:#4ade80;">
        {{ session('success') }}
      </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
      <div class="mx-8 mt-6 rounded-xl px-4 py-3 text-sm" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#fca5a5;">
        <ul class="space-y-1 list-disc list-inside">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('tickets.store') }}" class="px-8 py-6 space-y-5">
      @csrf

      {{-- Name + Email --}}
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:rgba(255,255,255,0.55);">Your Name <span style="color:#f87171;">*</span></label>
          <input type="text" name="submitter_name" value="{{ old('submitter_name') }}" required
            placeholder="Full name"
            class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none transition"
            style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);focus:border-color:rgba(34,197,94,0.5);">
        </div>
        <div>
          <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:rgba(255,255,255,0.55);">Email <span style="color:#f87171;">*</span></label>
          <input type="email" name="submitter_email" value="{{ old('submitter_email') }}" required
            placeholder="you@example.com"
            class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none transition"
            style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);">
        </div>
      </div>

      {{-- Subject --}}
      <div>
        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:rgba(255,255,255,0.55);">Subject <span style="color:#f87171;">*</span></label>
        <input type="text" name="subject" value="{{ old('subject') }}" required
          placeholder="Brief description of your issue"
          class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none transition"
          style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);">
      </div>

      {{-- Category + Priority --}}
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:rgba(255,255,255,0.55);">Category <span style="color:#f87171;">*</span></label>
          <select name="category" required
            class="w-full rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none transition appearance-none"
            style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);">
            @foreach($categories as $value => $label)
              <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}
                style="background:#1a1a2e;color:#fff;">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:rgba(255,255,255,0.55);">Priority <span style="color:#f87171;">*</span></label>
          <select name="priority" required
            class="w-full rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none transition appearance-none"
            style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);">
            @foreach($priorities as $value => $label)
              <option value="{{ $value }}" {{ old('priority', 'medium') === $value ? 'selected' : '' }}
                style="background:#1a1a2e;color:#fff;">{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Description --}}
      <div>
        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:rgba(255,255,255,0.55);">Description <span style="color:#f87171;">*</span></label>
        <textarea name="description" rows="5" required
          placeholder="Please describe your issue in detail..."
          class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none transition resize-none"
          style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);">{{ old('description') }}</textarea>
      </div>

      {{-- Submit --}}
      <div class="flex items-center justify-between pt-1">
        <a href="{{ route('tickets.status') }}" class="text-xs transition" style="color:rgba(255,255,255,0.4);" onmouseover="this.style.color='rgba(255,255,255,0.7)'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">
          Check existing ticket status &rarr;
        </a>
        <button type="submit"
          class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold text-white transition-all duration-150 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0"
          style="background:#16a34a;box-shadow:0 4px 14px rgba(22,163,74,0.35);"
          onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
          </svg>
          Submit Ticket
        </button>
      </div>
    </form>

  </div>

  {{-- Back to login --}}
  <p class="mt-6 text-center text-xs" style="color:rgba(255,255,255,0.3);">
    <a href="{{ route('login') }}" style="color:rgba(255,255,255,0.45);" onmouseover="this.style.color='rgba(255,255,255,0.7)'" onmouseout="this.style.color='rgba(255,255,255,0.45)'">
      &larr; Back to login
    </a>
  </p>

</div>
@endsection
