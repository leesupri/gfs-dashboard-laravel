<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Oops! — Gundaling Farmstead' }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  {{-- Google Fonts — SRI omitted intentionally; same as app.blade.php --}}
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet"> {{-- noqa Web:S5725 --}}

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    body { font-family: 'Sora', ui-sans-serif, system-ui, sans-serif; }

    /* ── Floating mascot ── */
    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      25%       { transform: translateY(-12px) rotate(-2deg); }
      75%       { transform: translateY(-6px)  rotate(1.5deg); }
    }

    /* ── Subtle head-scratch wiggle ── */
    @keyframes wiggle {
      0%, 100% { transform: rotate(0deg); }
      20%       { transform: rotate(-4deg); }
      40%       { transform: rotate(3deg); }
      60%       { transform: rotate(-2deg); }
      80%       { transform: rotate(1deg); }
    }

    /* ── Question mark bounce ── */
    @keyframes qBounce {
      0%, 100% { transform: translateY(0) scale(1);   opacity: 0.6; }
      50%       { transform: translateY(-8px) scale(1.15); opacity: 1; }
    }

    /* ── Error number glow pulse ── */
    @keyframes glowPulse {
      0%, 100% { text-shadow: 0 0 20px rgba(34,197,94,0.3), 0 0 60px rgba(34,197,94,0.1); }
      50%       { text-shadow: 0 0 40px rgba(34,197,94,0.6), 0 0 80px rgba(34,197,94,0.3); }
    }

    /* ── Dot grid sparkle ── */
    @keyframes sparkle {
      0%, 100% { opacity: 0.03; }
      50%       { opacity: 0.07; }
    }

    /* ── Slide up entrance ── */
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .mascot-float  { animation: float 4s ease-in-out infinite; }
    .mascot-wiggle { animation: wiggle 2s ease-in-out 1s 3; }
    .q-mark-1      { animation: qBounce 1.8s ease-in-out 0.2s infinite; }
    .q-mark-2      { animation: qBounce 1.8s ease-in-out 0.6s infinite; }
    .error-number  { animation: glowPulse 2.5s ease-in-out infinite; }
    .dot-bg        { animation: sparkle 3s ease-in-out infinite; }

    .slide-up-1 { animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1) both; }
    .slide-up-2 { animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1) 0.1s both; }
    .slide-up-3 { animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1) 0.2s both; }
    .slide-up-4 { animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1) 0.3s both; }
  </style>
</head>

<body style="background:#0f1117; min-height:100vh; color:#fff; display:flex; flex-direction:column;">

  {{-- Dot-grid background --}}
  <div class="dot-bg pointer-events-none fixed inset-0"
    style="background-image:radial-gradient(circle, rgba(255,255,255,0.5) 1px, transparent 1px); background-size:28px 28px;"></div>

  {{-- Green glow orbs --}}
  <div class="pointer-events-none fixed" style="top:-80px; right:-80px; width:320px; height:320px; border-radius:50%; background:radial-gradient(circle, rgba(34,197,94,0.12), transparent 70%);"></div>
  <div class="pointer-events-none fixed" style="bottom:-60px; left:-60px; width:240px; height:240px; border-radius:50%; background:radial-gradient(circle, rgba(34,197,94,0.08), transparent 70%);"></div>

  {{-- Header --}}
  <header class="relative z-10 flex items-center justify-between px-6 py-4" style="border-bottom:1px solid rgba(255,255,255,0.07)">
    <a href="{{ route('welcome') }}" class="flex items-center gap-3 transition-opacity hover:opacity-80">
      <img src="{{ asset('images/brand/62.png') }}" alt="Gundaling Farmstead"
        class="h-9 w-auto" style="filter:brightness(0) invert(1); opacity:0.9">
      <span class="text-sm font-semibold" style="color:rgba(255,255,255,0.75)">Gundaling Farmstead</span>
    </a>
    <span class="rounded-full px-3 py-1 text-xs font-semibold"
      style="background:rgba(34,197,94,0.15); color:#4ade80; border:1px solid rgba(34,197,94,0.3)">
      GFS Dashboard
    </span>
  </header>

  {{-- Main content --}}
  <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 py-12 text-center">
    @yield('content')
  </main>

  {{-- Footer --}}
  <footer class="relative z-10 py-4 text-center text-xs" style="color:rgba(255,255,255,0.3); border-top:1px solid rgba(255,255,255,0.07)">
    © {{ date('Y') }} Gundaling Farmstead &mdash; Dashboard
  </footer>

</body>
</html>
