@extends('layouts.error')

@section('content')

  {{-- Mascot --}}
  <div class="slide-up-1 relative mx-auto mb-2 w-56">
    <span class="q-mark-1 absolute -right-2 -top-4 text-2xl font-black" style="color:#38bdf8">⏱</span>
    <div class="mascot-float">
      <img src="{{ asset('images/brand/53.png') }}"
           alt="Confused farmstead cow mascot"
           class="mascot-wiggle w-full drop-shadow-2xl"
           style="filter:drop-shadow(0 20px 40px rgba(56,189,248,0.15))">
    </div>
  </div>

  {{-- Error code --}}
  <div class="slide-up-2 error-number select-none font-extrabold leading-none"
    style="font-size:clamp(80px,14vw,160px);
           background:linear-gradient(135deg,#38bdf8 0%,#0ea5e9 50%,#38bdf8 100%);
           -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
           animation:glowPulse 2.5s ease-in-out infinite;">
    419
  </div>

  <h1 class="slide-up-3 mt-2 text-xl font-bold sm:text-2xl" style="color:rgba(255,255,255,0.95)">
    Session Expired
  </h1>

  <p class="slide-up-3 mx-auto mt-3 max-w-sm text-sm leading-relaxed" style="color:rgba(255,255,255,0.5)">
    The gate closed while you were away.<br>
    Your session has timed out — please go back and try again.
  </p>

  <div class="slide-up-4 mt-8 flex flex-wrap justify-center gap-3">
    <button onclick="history.back()"
      class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 active:scale-95"
      style="background:linear-gradient(135deg,#22c55e,#16a34a); box-shadow:0 4px 20px rgba(34,197,94,0.3)">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Go Back & Retry
    </button>
    <a href="{{ route('login') }}"
       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-medium transition-all hover:-translate-y-0.5 active:scale-95"
       style="background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.75); border:1px solid rgba(255,255,255,0.15)">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
      </svg>
      Log In Again
    </a>
  </div>

@endsection
