@extends('layouts.error')

@section('content')

  {{-- Mascot --}}
  <div class="slide-up-1 relative mx-auto mb-2 w-56">
    <span class="q-mark-1 absolute -right-2 -top-4 text-2xl font-black" style="color:#38bdf8">⏱</span>
    <span class="q-mark-2 absolute -left-1 top-2 text-lg font-black" style="color:#38bdf8; opacity:0.7">⌛</span>
    <div class="mascot-float">
      <img src="{{ asset('images/brand/53.png') }}"
           alt="Waiting farmstead cow mascot"
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
    504
  </div>

  <h1 class="slide-up-3 mt-2 text-xl font-bold sm:text-2xl" style="color:rgba(255,255,255,0.95)">
    Gateway Timeout
  </h1>

  <p class="slide-up-3 mx-auto mt-3 max-w-sm text-sm leading-relaxed" style="color:rgba(255,255,255,0.5)">
    The server took too long to respond — the gate's stuck.<br>
    This is usually temporary. Please wait a moment and try again.
  </p>

  {{-- Countdown hint --}}
  <p class="slide-up-3 mt-3 text-xs" style="color:rgba(56,189,248,0.6)">
    Auto-retrying in <span id="gfs-countdown" style="color:#38bdf8; font-weight:700">15</span>s
  </p>

  <div class="slide-up-4 mt-8 flex flex-wrap justify-center gap-3">
    <button onclick="location.reload()"
      class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 active:scale-95"
      style="background:linear-gradient(135deg,#22c55e,#16a34a); box-shadow:0 4px 20px rgba(34,197,94,0.3)">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
      </svg>
      Try Again
    </button>
    <a href="{{ route('welcome') }}"
       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-medium transition-all hover:-translate-y-0.5 active:scale-95"
       style="background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.75); border:1px solid rgba(255,255,255,0.15)">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 4.5l9 5.25v9.75a1.5 1.5 0 01-1.5 1.5h-3.75v-6h-6v6H4.5A1.5 1.5 0 013 19.5V9.75z"/>
      </svg>
      Back to Home
    </a>
  </div>

  <script>
    (function () {
      var n = 15;
      var el = document.getElementById('gfs-countdown');
      var timer = setInterval(function () {
        n--;
        if (el) el.textContent = n;
        if (n <= 0) { clearInterval(timer); location.reload(); }
      }, 1000);
    })();
  </script>

@endsection
