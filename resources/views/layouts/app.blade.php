<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'GFS Dashboard' }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
  x-data="{ mobileMenuOpen: false, loading: false, collapsed: false }"
  x-init="window.addEventListener('pageshow', () => loading = false)"
  class="bg-gray-50 text-gray-900"
>
  <!-- Global Loading Overlay -->
  <div
  x-show="loading"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="fixed inset-0 z-[999] flex items-center justify-center bg-white/80 backdrop-blur-md"
>
  <div class="flex flex-col items-center gap-4">

    <!-- Logo container -->
    <div class="relative h-50 w-100 flex items-center justify-center">

      <!-- Skeleton shimmer -->
      <div class="absolute inset-0 rounded-lg bg-gray-200 animate-pulse"></div>

      <!-- Logo -->
      <img
        src="{{ asset('images/brand/cowRunning1.gif') }}"
        alt="Cow running"
        class="relative h-48 w-auto opacity-0 scale-95 transition-all duration-500"
        x-data="{ loaded: false }"
        @load="loaded = true"
        :class="loaded ? 'opacity-100 scale-100' : ''"
      />
    </div>

    <!-- Animated dots instead of boring text -->
    <div class="flex items-center gap-1 text-sm text-gray-500">
      <span>Loading</span>
      <span class="animate-bounce [animation-delay:0ms]">.</span>
      <span class="animate-bounce [animation-delay:150ms]">.</span>
      <span class="animate-bounce [animation-delay:300ms]">.</span>
    </div>

  </div>
</div>

  <div class="flex h-screen overflow-hidden">
    @include('partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col">
      @include('partials.header')

      <main
        x-data
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="min-h-0 flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8"
      >
        @yield('content')
      </main>

      @include('partials.footer')
    </div>
  </div>
</body>
</html>