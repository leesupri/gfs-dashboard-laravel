<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GFS Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-200 px-4 p-8 ">
        <div class="text-center mb-6">
            <img src="{{ asset('images/brand/Logo_GUNDALING_full-color_tall_on-white.png') }}"
                 alt="Gundaling Farmstead"
                 class="mx-auto h-16 w-auto mb-4">
            <h1 class="text-xl font-semibold text-gray-900">GFS Dashboard Login</h1>
            <p class="text-sm text-gray-500 mt-1">Secure access for staff</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username') }}"
                       class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-gray-900 focus:outline-none">
                @error('username')
                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password"
                       class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-gray-900 focus:outline-none">
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-gray-900 px-4 py-3 text-sm font-medium text-white hover:bg-gray-800">
                Login
            </button>
        </form>
    </div>
</body>
</html>