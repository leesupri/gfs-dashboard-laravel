@extends('layouts.app')

@section('content')
<div class="space-y-6">

  {{-- Flash messages --}}
  @if(session('success'))
    <div class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
      <svg class="h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <svg class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      {{ session('error') }}
    </div>
  @endif

  {{-- Page header --}}
  <div>
    <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Staff Management</h1>
    <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Create and manage staff accounts</p>
  </div>

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">

    {{-- LEFT — Staff list --}}
    <div class="xl:col-span-4">
      <div class="gfs-card overflow-hidden">

        {{-- List header + search --}}
        <div class="border-b px-4 py-4" style="border-color:var(--card-border)">
          <div class="mb-3 flex items-center justify-between">
            <div>
              <h2 class="text-sm font-bold" style="color:var(--text-primary)">Staff List</h2>
              <p class="text-xs" style="color:var(--text-muted)">Select to view or edit</p>
            </div>
            <a href="{{ route('settings.staff', ['new' => 1]) }}"
               class="inline-flex items-center gap-1 rounded-xl bg-green-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-green-700">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              New
            </a>
          </div>
          <form method="GET" action="{{ route('settings.staff') }}" class="space-y-2">
            <input type="text" name="search" value="{{ $search }}"
              placeholder="Search name / username / title…"
              class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            <div class="flex gap-2">
              <select name="status"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
                <option value="all"      {{ $status === 'all'      ? 'selected' : '' }}>All Status</option>
                <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
              <button type="submit"
                class="shrink-0 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-medium transition hover:bg-gray-50"
                style="color:var(--text-secondary)">
                Filter
              </button>
            </div>
          </form>
        </div>

        {{-- Staff list --}}
        <div class="max-h-[70vh] space-y-1.5 overflow-y-auto p-3">

          {{-- Create new item --}}
          <a href="{{ route('settings.staff', ['new' => 1, 'search' => $search, 'status' => $status]) }}"
             class="flex items-center gap-3 rounded-xl border px-3 py-2.5 transition
               {{ $isCreateMode
                 ? 'border-green-500 bg-green-600 text-white shadow-sm'
                 : 'border-dashed border-gray-300 text-gray-500 hover:border-green-400 hover:text-green-600' }}">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
              {{ $isCreateMode ? 'bg-white/20' : 'bg-gray-100' }}">
              <svg class="h-4 w-4 {{ $isCreateMode ? 'text-white' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold">Create New Staff</p>
              <p class="text-[10px] {{ $isCreateMode ? 'text-green-100' : 'text-gray-400' }}">Add a new account</p>
            </div>
          </a>

          @forelse($staffUsers as $staff)
            @php $isSelected = !$isCreateMode && $selectedStaff && $selectedStaff->id === $staff->id; @endphp
            <a href="{{ route('settings.staff', ['staff' => $staff->id, 'search' => $search, 'status' => $status]) }}"
               class="flex items-center gap-3 rounded-xl border px-3 py-2.5 transition
                 {{ $isSelected
                   ? 'border-transparent text-white shadow-sm'
                   : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}"
               style="{{ $isSelected ? 'background:var(--sidebar-bg)' : '' }}">
              {{-- Avatar --}}
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold
                {{ $isSelected ? 'bg-white/15 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ strtoupper(substr($staff->name, 0, 1)) }}
              </div>
              {{-- Info --}}
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ $staff->name }}</p>
                <p class="truncate text-[10px] {{ $isSelected ? 'text-white/60' : '' }}"
                  style="{{ !$isSelected ? 'color:var(--text-muted)' : '' }}">
                  {{ $staff->username }}@if($staff->title) · {{ $staff->title }}@endif
                </p>
              </div>
              {{-- Status --}}
              <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold
                {{ $staff->is_active
                  ? ($isSelected ? 'bg-green-400/30 text-green-200' : 'bg-green-100 text-green-700')
                  : ($isSelected ? 'bg-red-400/30 text-red-200'   : 'bg-red-100 text-red-600') }}">
                {{ $staff->is_active ? 'Active' : 'Inactive' }}
              </span>
            </a>
          @empty
            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm" style="color:var(--text-muted)">
              No staff found.
            </div>
          @endforelse
        </div>

      </div>
    </div>

    {{-- RIGHT — Form panel --}}
    <div class="xl:col-span-8">
      <div class="gfs-card overflow-hidden">

        @if($isCreateMode)

          {{-- Create form --}}
          <div class="flex items-center gap-3 border-b px-6 py-5" style="border-color:var(--card-border); background:var(--content-bg)">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-100">
              <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-sm font-bold" style="color:var(--text-primary)">Create New Staff</h2>
              <p class="text-xs" style="color:var(--text-muted)">Fill in the account details below</p>
            </div>
          </div>

          <form method="POST" action="{{ route('settings.staff.store') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
            @csrf

            <div>
              <label for="c-name" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Full Name</label>
              <input id="c-name" type="text" name="name" value="{{ old('name') }}" required
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div>
              <label for="c-username" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Username</label>
              <input id="c-username" type="text" name="username" value="{{ old('username') }}" required
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div>
              <label for="c-title" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Job Title</label>
              <input id="c-title" type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Manager, Cashier, Admin"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div class="flex items-end pb-0.5">
              <label for="c-active" class="inline-flex cursor-pointer items-center gap-2 text-sm">
                <input id="c-active" type="checkbox" name="is_active" value="1" checked
                  class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                <span style="color:var(--text-secondary)">Active account</span>
              </label>
            </div>

            <div>
              <label for="c-password" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Password</label>
              <input id="c-password" type="password" name="password" required
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div>
              <label for="c-password-confirm" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Confirm Password</label>
              <input id="c-password-confirm" type="password" name="password_confirmation" required
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div class="md:col-span-2 border-t pt-4" style="border-color:var(--card-border)">
              <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Create Staff
              </button>
            </div>
          </form>

        @elseif($selectedStaff)

          {{-- Edit form header --}}
          <div class="flex items-center justify-between gap-4 border-b px-6 py-5"
            style="border-color:var(--card-border); background:var(--content-bg)">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white"
                style="background:var(--sidebar-bg)">
                {{ strtoupper(substr($selectedStaff->name, 0, 1)) }}
              </div>
              <div>
                <h2 class="text-sm font-bold" style="color:var(--text-primary)">{{ $selectedStaff->name }}</h2>
                <p class="text-xs" style="color:var(--text-muted)">
                  @{{ $selectedStaff->username }}
                  @if($selectedStaff->title) · {{ $selectedStaff->title }} @endif
                </p>
              </div>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold
              {{ $selectedStaff->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
              {{ $selectedStaff->is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          {{-- Edit form --}}
          <form method="POST" action="{{ route('settings.staff.update', $selectedStaff) }}"
            class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
            @csrf
            @method('PUT')

            <div>
              <label for="e-name" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Full Name</label>
              <input id="e-name" type="text" name="name" value="{{ old('name', $selectedStaff->name) }}" required
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div>
              <label for="e-username" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Username</label>
              <input id="e-username" type="text" name="username" value="{{ old('username', $selectedStaff->username) }}" required
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div>
              <label for="e-title" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Job Title</label>
              <input id="e-title" type="text" name="title" value="{{ old('title', $selectedStaff->title) }}"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div class="flex items-end pb-0.5">
              <label for="e-active" class="inline-flex cursor-pointer items-center gap-2 text-sm">
                <input id="e-active" type="checkbox" name="is_active" value="1"
                  {{ old('is_active', $selectedStaff->is_active) ? 'checked' : '' }}
                  class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                <span style="color:var(--text-secondary)">Active account</span>
              </label>
            </div>

            <div>
              <label for="e-password" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">New Password</label>
              <input id="e-password" type="password" name="password" placeholder="Leave blank to keep current"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div>
              <label for="e-password-confirm" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Confirm New Password</label>
              <input id="e-password-confirm" type="password" name="password_confirmation"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
            </div>

            <div class="md:col-span-2 flex items-center gap-3 border-t pt-4" style="border-color:var(--card-border)">
              <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
              </button>
            </div>
          </form>

          {{-- Delete form — separate, outside the edit form --}}
          <div class="flex items-center justify-between border-t px-6 py-4"
            style="border-color:var(--card-border); background:var(--content-bg)">
            <p class="text-xs" style="color:var(--text-muted)">
              Deleting this account is permanent and cannot be undone.
            </p>
            <form method="POST" action="{{ route('settings.staff.destroy', $selectedStaff) }}"
              onsubmit="return confirm('Permanently delete {{ $selectedStaff->name }}? This cannot be undone.')">
              @csrf
              @method('DELETE')
              <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete Account
              </button>
            </form>
          </div>

        @else
          <div class="flex flex-col items-center justify-center gap-3 py-20">
            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm font-medium" style="color:var(--text-muted)">Select a staff member or create a new account</p>
          </div>
        @endif

      </div>
    </div>

  </div>
</div>
@endsection
