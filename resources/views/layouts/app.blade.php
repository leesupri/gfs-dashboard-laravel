<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'GFS Dashboard' }}</title>

  {{-- Sora: geometric, farmstead-premium. Pairs with the glassmorphism login. --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* ── Global token system ───────────────────────── */
    :root {
      --font-sans: 'Sora', ui-sans-serif, system-ui, sans-serif;

      /* Sidebar dark palette — mirrors the login overlay */
      --sidebar-bg:        #0f1117;
      --sidebar-border:    rgba(255,255,255,0.07);
      --sidebar-text:      rgba(255,255,255,0.55);
      --sidebar-text-hover:rgba(255,255,255,0.90);
      --sidebar-active-bg: rgba(255,255,255,0.10);
      --sidebar-active-text: #ffffff;
      --sidebar-pill:      #22c55e;  /* green accent — farmstead/natural */

      /* Content area */
      --content-bg:        #f5f4f1;  /* warm off-white, not cold gray */
      --card-bg:           #ffffff;
      --card-border:       rgba(0,0,0,0.07);
      --card-shadow:       0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);

      /* Header */
      --header-bg:         rgba(255,255,255,0.85);
      --header-blur:       12px;
      --header-border:     rgba(0,0,0,0.07);

      /* Text */
      --text-primary:      #141414;
      --text-secondary:    #6b6b6b;
      --text-muted:        #a3a3a3;
    }

    /* ── Base ───────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; }

    html, body {
      font-family: var(--font-sans);
      background: var(--content-bg);
      color: var(--text-primary);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* ── Loading Overlay ────────────────────────────── */
    .loading-overlay {
      background: rgba(15, 17, 23, 0.75);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }

    .loading-card {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 20px;
      padding: 2rem 2.5rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1.25rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
    }

    .loading-logo {
      height: 56px;
      width: auto;
      filter: brightness(0) invert(1);
      opacity: 0.9;
    }

    .loading-dots {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .loading-dots span {
      display: block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: rgba(255,255,255,0.50);
      animation: dot-pulse 1.2s ease-in-out infinite;
    }
    .loading-dots span:nth-child(2) { animation-delay: 0.2s; }
    .loading-dots span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes dot-pulse {
      0%, 80%, 100% { transform: scale(0.7); opacity: 0.4; }
      40%            { transform: scale(1.1); opacity: 1;   }
    }

    /* ── Sidebar ────────────────────────────────────── */
    .sidebar {
      background: var(--sidebar-bg);
      border-right: 1px solid var(--sidebar-border);
      transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      will-change: width;
    }

    /* ── Header ─────────────────────────────────────── */
    .app-header {
      background: var(--header-bg);
      backdrop-filter: blur(var(--header-blur));
      -webkit-backdrop-filter: blur(var(--header-blur));
      border-bottom: 1px solid var(--header-border);
    }

    /* ── Main content ───────────────────────────────── */
    .main-content {
      background: var(--content-bg);
    }

    /* ── Page-entry animation ───────────────────────── */
    @keyframes page-in {
      from { opacity: 0; transform: translateY(6px); }
      to   { opacity: 1; transform: translateY(0);   }
    }

    .page-animate {
      animation: page-in 0.28s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* ── Footer ─────────────────────────────────────── */
    .app-footer {
      background: var(--card-bg);
      border-top: 1px solid var(--card-border);
    }

    /* ── Scrollbar (Webkit) ─────────────────────────── */
    ::-webkit-scrollbar       { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 99px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.25); }

    /* ── Card / table utility ───────────────────────── */
    .gfs-card {
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      box-shadow: var(--card-shadow);
      border-radius: 16px;
    }

    /* ── Focus ring override ────────────────────────── */
    :focus-visible {
      outline: 2px solid #22c55e;
      outline-offset: 2px;
    }
  </style>
</head>

<body
  x-data="{ mobileMenuOpen: false, loading: false, collapsed: false }"
  x-init="window.addEventListener('pageshow', () => loading = false)"
>

  {{-- ── Loading Overlay ────────────────────────────── --}}
  <div
    x-show="loading"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="loading-overlay fixed inset-0 z-[999] flex items-center justify-center"
    style="display:none"
  >
    <div class="loading-card">
      <img
        src="{{ asset('images/brand/62.png') }}"
        alt="Gundaling Farmstead"
        class="loading-logo"
        x-data="{ loaded: false }"
        @load="loaded = true"
        :class="{ 'opacity-0': !loaded }"
      >
      <div class="loading-dots">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>

  {{-- ── App Shell ───────────────────────────────────── --}}
  <div class="flex h-screen overflow-hidden">

    @include('partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

      {{-- Header --}}
      <header class="app-header shrink-0">
        @include('partials.header')
      </header>

      {{-- Main --}}
      <main class="main-content page-animate min-h-0 flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
        @yield('content')
      </main>

      {{-- Footer --}}
      <footer class="app-footer shrink-0">
        @include('partials.footer')
      </footer>

    </div>
  </div>

</body>
</html>