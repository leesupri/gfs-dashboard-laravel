<!-- resources\views\layouts\app.blade.php -->
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'GFS Dashboard' }}</title>
   
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
  <div class="flex h-screen overflow-hidden">

    {{-- Sidebar (fixed) --}}
    @include('partials.sidebar')

    {{-- Right side --}}
    <div class="flex min-w-0 flex-1 flex-col">

      {{-- Header (fixed) --}}
      <div class="sticky top-0 z-30">
        @include('partials.header')
      </div>

      {{-- Content (ONLY this scrolls) --}}
      <main class="flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
        @yield('content')
      </main>

      {{-- Footer (fixed at bottom) --}}
      <div class="sticky bottom-0 z-20">
        @include('partials.footer')
      </div>

    </div>
  </div>
</body>
</html>
