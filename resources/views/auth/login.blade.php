<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — GFS Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Inter font — clean, professional, dashboard-grade */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');

        * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

        /* Full-bleed background image */
        .bg-scene {
            background-image: url('{{ asset('images/brand/login-bg.jpeg') }}');
            /* ↑ Replace login-bg.jpg with your actual filename */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Frosted glass card */
        .glass-card {
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.20);
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.30),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }

        /* Frosted input */
        .glass-input {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: white;
            transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .glass-input::placeholder { color: rgba(255,255,255,0.40); }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.50);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.12);
            outline: none;
        }

        /* Login button */
        .btn-login {
            background: rgba(255, 255, 255, 0.95);
            color: #1a1a1a;
            font-weight: 600;
            letter-spacing: 0.01em;
            transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
            box-shadow: 0 4px 14px rgba(0,0,0,0.20);
        }
        .btn-login:hover {
            background: white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
            transform: translateY(-1px);
        }
        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0,0,0,0.20);
        }

        /* Subtle gradient overlay on top of photo */
        .photo-overlay {
            background: linear-gradient(
                135deg,
                rgba(0, 0, 0, 0.55) 0%,
                rgba(0, 0, 0, 0.25) 50%,
                rgba(0, 0, 0, 0.50) 100%
            );
        }

        /* Label styling */
        .field-label {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.8125rem;
            font-weight: 500;
            letter-spacing: 0.02em;
        }

        /* Divider */
        .glass-divider {
            border-color: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>

<body class="bg-scene min-h-screen">

    {{-- Gradient overlay on photo --}}
    <div class="photo-overlay fixed inset-0"></div>

    {{-- Center the card --}}
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-12">
        <div class="glass-card w-full max-w-sm rounded-3xl px-8 py-10">

            {{-- Brand --}}
            <div class="text-center mb-8">
                <img
                    src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
                    alt="Gundaling Farmstead"
                    class="mx-auto h-14 w-auto mb-5 drop-shadow-lg"
                    style="filter: brightness(0) invert(1);"
                >
                <h1 class="text-lg font-semibold text-white leading-tight">
                    GFS Dashboard
                </h1>
                <p class="text-xs text-white/50 mt-1 font-light tracking-wide">
                    Staff access only
                </p>
            </div>

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="mb-5 rounded-xl px-4 py-3 text-sm text-red-200"
                     style="background: rgba(239,68,68,0.18); border: 1px solid rgba(239,68,68,0.30);">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3m0 4h.01M10.29 3.86L1.82 18a1.5 1.5 0 001.3 2.25h17.76a1.5 1.5 0 001.3-2.25L13.71 3.86a1.5 1.5 0 00-2.42 0z"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                {{-- Username --}}
                <div>
                    <label class="field-label block mb-1.5">Username</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="w-4 h-4 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            autocomplete="username"
                            placeholder="Enter your username"
                            class="glass-input w-full rounded-xl pl-10 pr-4 py-3 text-sm"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label class="field-label block mb-1.5">Password</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="w-4 h-4 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                        </div>
                        <input
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="glass-input w-full rounded-xl pl-10 pr-4 py-3 text-sm"
                        >
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit" class="btn-login cursor-pointer w-full rounded-xl px-4 py-3 text-sm">
                        Sign in
                    </button>
                </div>
            </form>

            {{-- Footer --}}
            <div class="mt-8 pt-6 glass-divider border-t text-center">
                <p class="text-xs text-white/30 font-light">
                    © {{ date('Y') }} Gundaling Farmstead
                </p>
            </div>

        </div>
    </div>

</body>
</html>