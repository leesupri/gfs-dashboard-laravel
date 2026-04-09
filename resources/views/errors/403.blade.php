@extends('layouts.error')

@section('content')

    <div class="text-center">

        {{-- BIG 403 --}}
        <div class="text-[150px] font-extrabold bg-gradient-to-r from-red-300 via-gray-400 to-red-300 bg-clip-text text-transparent animate-pulse">
            403
        </div>

        {{-- Title --}}
        <h1 class="mt-2 text-2xl font-semibold text-gray-900">
            Access Denied
        </h1>

        {{-- Description --}}
        <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
            You don’t have permission to access this page.
            Please contact your administrator if needed.
        </p>

        {{-- Actions --}}
        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ route('welcome') }}"
               class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800 transition">
                Back to Home
            </a>

            <button onclick="history.back()"
                class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Go Back
            </button>
        </div>

    </div>

@endsection