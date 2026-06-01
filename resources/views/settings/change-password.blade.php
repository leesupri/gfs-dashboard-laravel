@extends('layouts.app')

@section('content')
<div class="flex justify-center">
  <div class="w-full max-w-md space-y-6">

    {{-- Card --}}
    <div class="gfs-card overflow-hidden">

      {{-- Header --}}
      <div class="flex items-center gap-3 border-b px-6 py-5"
        style="border-color:var(--card-border); background:var(--content-bg)">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100">
          <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-sm font-bold" style="color:var(--text-primary)">Change Password</h2>
          <p class="text-xs" style="color:var(--text-muted)">Update your account password</p>
        </div>
      </div>

      {{-- Form --}}
      <form method="POST" action="{{ route('settings.changePassword.update') }}" class="space-y-4 p-6">
        @csrf

        <div>
          <label for="cp-current" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">
            Current Password
          </label>
          <input id="cp-current" type="password" name="current_password" required
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100
              {{ $errors->has('current_password') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : '' }}">
          @error('current_password')
            <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
              <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ $message }}
            </p>
          @enderror
        </div>

        <div class="border-t border-gray-100 pt-4">
          <label for="cp-new" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">
            New Password
          </label>
          <input id="cp-new" type="password" name="password" required
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100
              {{ $errors->has('password') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : '' }}">
          @error('password')
            <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
              <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ $message }}
            </p>
          @enderror
        </div>

        <div>
          <label for="cp-confirm" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">
            Confirm New Password
          </label>
          <input id="cp-confirm" type="password" name="password_confirmation" required
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>

        <div class="border-t border-gray-100 pt-4">
          <button type="submit"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Update Password
          </button>
        </div>
      </form>

    </div>

  </div>
</div>
@endsection
