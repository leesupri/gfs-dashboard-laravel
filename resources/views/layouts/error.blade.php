<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Error' }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">

  <div class="flex min-h-screen flex-col">

    {{-- Minimal Header --}}
    <header class="border-b border-gray-200 bg-white">
      <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center gap-3">
          <img
            src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
            alt="Gundaling Farmstead"
            class="h-10 w-auto"
          />
          <div class="text-sm font-semibold text-gray-900">
            Gundaling Farmstead
          </div>
        </div>

        <div class="text-xs text-gray-500">
          Offline Dashboard
        </div>
      </div>
    </header>

    {{-- Content --}}
    <main class="flex flex-1 items-center justify-center px-6 py-12">
      @yield('content')
    </main>

    {{-- Minimal Footer --}}
    <footer class="border-t border-gray-200 bg-white text-center text-xs text-gray-500 py-3">
      © {{ date('Y') }} Gundaling Farmstead
    </footer>

  </div>

</body>
</html>