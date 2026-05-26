<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GFS Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{--
    BACKGROUND IMAGE PATH:
    Place your JPEG at:  public/images/brand/login-bg.jpeg
    Then change the url() below to match your actual filename.
--}}

<body
    class="min-h-screen flex items-center justify-center px-4"
    style="
        background-image: url('{{ asset('images/brand/login-bg.jpeg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    "
>
    {{-- Dark overlay so the card always reads cleanly over any photo --}}
    <div class="absolute inset-0 bg-black/40"></div>

    {{-- Login card — frosted glass --}}
    <div class="relative z-10 w-full max-w-md rounded-2xl border border-white/20 bg-white/10 px-8 py-10 shadow-2xl backdrop-blur-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <img
                src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
                alt="Gundaling Farmstead"
                class="mx-auto h-16 w-auto mb-4 drop-shadow-md"
            >
            <h1 class="text-xl font-semibold text-white">GFS Dashboard</h1>
            <p class="text-sm text-white/70 mt-1">Secure access for staff</p>
        </div>

        {{-- Error banner --}}
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-400/40 bg-red-500/20 px-4 py-3 text-sm text-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-white/80 mb-1.5">Username</label>
                <input
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    autocomplete="username"
                    class="w-full rounded-xl border border-white/30 bg-white/15 px-4 py-3 text-sm text-white placeholder-white/40
                           focus:border-white/60 focus:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/20
                           transition"
                    placeholder="Enter your username"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-white/80 mb-1.5">Password</label>
                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    class="w-full rounded-xl border border-white/30 bg-white/15 px-4 py-3 text-sm text-white placeholder-white/40
                           focus:border-white/60 focus:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/20
                           transition"
                    placeholder="Enter your password"
                >
            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-white px-4 py-3 text-sm font-semibold text-gray-900
                       hover:bg-white/90 active:scale-[0.98] transition-all duration-150 shadow-md mt-2"
            >
                Login
            </button>
        </form>

        <p class="mt-6 text-center text-xs text-white/40">
            © {{ date('Y') }} Gundaling Farmstead
        </p>
    </div>
</body>
</html>