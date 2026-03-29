<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'GFS Dashboard' }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-900">
  <div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Right side --}}
    <div class="flex min-w-0 flex-1 flex-col">

      {{-- Header --}}
      @include('partials.header')

      {{-- Scrollable content --}}
      <main class="min-h-0 flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
        @yield('content')
      </main>

      {{-- Footer --}}
      @include('partials.footer')

    </div>
  </div>
</body>
</html>