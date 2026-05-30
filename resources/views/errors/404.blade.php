@extends('layouts.error')

@section('content')

  {{-- Mascot --}}
  <div class="slide-up-1 relative mx-auto mb-2 w-56">
    <span class="q-mark-1 absolute -right-2 -top-4 text-2xl font-black" style="color:#a78bfa">?</span>
    <span class="q-mark-2 absolute -left-1 top-2 text-lg font-black" style="color:#a78bfa; opacity:0.7">?</span>
    <div class="mascot-float">
      <img src="{{ asset('images/brand/53.png') }}"
           alt="Confused farmstead cow mascot"
           class="mascot-wiggle w-full drop-shadow-2xl"
           style="filter:drop-shadow(0 20px 40px rgba(167,139,250,0.15))">
    </div>
  </div>

  {{-- Error code --}}
  <div class="slide-up-2 error-number select-none font-extrabold leading-none"
    style="font-size:clamp(80px,14vw,160px);
           background:linear-gradient(135deg,#a78bfa 0%,#818cf8 50%,#a78bfa 100%);
           -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
           animation:glowPulse 2.5s ease-in-out infinite;">
    404
  </div>

  <h1 class="slide-up-3 mt-2 text-xl font-bold sm:text-2xl" style="color:rgba(255,255,255,0.95)">
    Lost in the Pasture
  </h1>

  <p class="slide-up-3 mx-auto mt-3 max-w-sm text-sm leading-relaxed" style="color:rgba(255,255,255,0.5)">
    This page wandered off and can't be found.<br>
    The URL might be wrong, or the page no longer exists.
  </p>

  <div class="slide-up-4 mt-8 flex flex-wrap justify-center gap-3">
    <a href="{{ route('welcome') }}"
       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 active:scale-95"
       style="background:linear-gradient(135deg,#22c55e,#16a34a); box-shadow:0 4px 20px rgba(34,197,94,0.3)">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 4.5l9 5.25v9.75a1.5 1.5 0 01-1.5 1.5h-3.75v-6h-6v6H4.5A1.5 1.5 0 013 19.5V9.75z"/>
      </svg>
      Back to Home
    </a>
    <button onclick="history.back()"
      class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-medium transition-all hover:-translate-y-0.5 active:scale-95"
      style="background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.75); border:1px solid rgba(255,255,255,0.15)">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Go Back
    </button>
  </div>

@endsection
