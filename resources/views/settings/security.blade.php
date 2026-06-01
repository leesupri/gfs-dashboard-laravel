@extends('layouts.app')

@section('content')
<div class="space-y-6">

  {{-- Page header --}}
  <div>
    <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Security & Permissions</h1>
    <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Control which pages each staff member can access</p>
  </div>

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">

    {{-- LEFT — Staff list --}}
    <div class="xl:col-span-4">
      <div class="gfs-card overflow-hidden">

        <div class="border-b px-4 py-4" style="border-color:var(--card-border)">
          <h2 class="mb-1 text-sm font-bold" style="color:var(--text-primary)">Staff List</h2>
          <p class="mb-3 text-xs" style="color:var(--text-muted)">Select to manage page access</p>
          <form method="GET" action="{{ route('settings.security') }}" class="space-y-2">
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

        <div class="max-h-[70vh] space-y-1.5 overflow-y-auto p-3">
          @forelse($staffUsers as $staff)
            @php
              $isSelected     = $selectedStaff && $selectedStaff->id === $staff->id;
              $permCount      = $staff->permissions->where('can_view', true)->count();
            @endphp
            <a href="{{ route('settings.security', ['staff' => $staff->id, 'search' => $search, 'status' => $status]) }}"
               class="flex items-center gap-3 rounded-xl border px-3 py-2.5 transition
                 {{ $isSelected ? 'border-transparent text-white shadow-sm' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}"
               style="{{ $isSelected ? 'background:var(--sidebar-bg)' : '' }}">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold
                {{ $isSelected ? 'bg-white/15 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ strtoupper(substr($staff->name, 0, 1)) }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ $staff->name }}</p>
                <p class="truncate text-[10px]" style="{{ $isSelected ? 'color:rgba(255,255,255,0.55)' : 'color:var(--text-muted)' }}">
                  {{ $staff->username }}@if($staff->title) · {{ $staff->title }}@endif
                </p>
                <p class="mt-0.5 text-[10px]" style="{{ $isSelected ? 'color:rgba(255,255,255,0.5)' : 'color:var(--text-muted)' }}">
                  {{ $permCount }} route(s) allowed
                </p>
              </div>
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

    {{-- RIGHT — Permissions panel --}}
    <div class="xl:col-span-8">
      <div class="gfs-card overflow-hidden">

        @if($selectedStaff)
          @php
            $grantedRoutes = $selectedStaff->permissions->where('can_view', true)->pluck('route_name')->toArray();
            $totalRoutes   = count($availableRoutes);
            $grantedCount  = count(array_intersect(array_keys($availableRoutes), $grantedRoutes));

            // Group routes by first segment for visual organisation
            $routeGroups = [];
            foreach ($availableRoutes as $routeName => $label) {
              $seg = ucfirst(explode('.', $routeName)[0]);
              $routeGroups[$seg][$routeName] = $label;
            }
          @endphp

          {{-- Header --}}
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
                  {{ $grantedCount }} of {{ $totalRoutes }} routes allowed
                </p>
              </div>
            </div>
            <div class="text-right">
              <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                <div class="h-full rounded-full bg-green-500 transition-all"
                  style="width:{{ $totalRoutes > 0 ? number_format($grantedCount / $totalRoutes * 100, 1) : 0 }}%">
                </div>
              </div>
              <p class="mt-1 text-[10px]" style="color:var(--text-muted)">
                {{ $totalRoutes > 0 ? number_format($grantedCount / $totalRoutes * 100, 0) : 0 }}% access
              </p>
            </div>
          </div>

          <form method="POST" action="{{ route('settings.security.update', $selectedStaff) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            @foreach($routeGroups as $groupName => $routes)
              <div>
                <h3 class="mb-3 text-[10px] font-bold uppercase tracking-widest" style="color:var(--text-muted)">
                  {{ $groupName }}
                </h3>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                  @foreach($routes as $routeName => $label)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border px-4 py-3 transition hover:bg-gray-50/80
                      {{ in_array($routeName, $grantedRoutes, true) ? 'border-green-200 bg-green-50/60' : 'border-gray-200 bg-white' }}">
                      <input type="checkbox" name="routes[]" value="{{ $routeName }}"
                        {{ in_array($routeName, $grantedRoutes, true) ? 'checked' : '' }}
                        class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-green-600 focus:ring-green-500">
                      <div class="min-w-0">
                        <p class="text-sm font-medium leading-snug" style="color:var(--text-primary)">{{ $label }}</p>
                        <p class="font-mono text-[10px] leading-snug" style="color:var(--text-muted)">{{ $routeName }}</p>
                      </div>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach

            <div class="border-t pt-4" style="border-color:var(--card-border)">
              <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save Permissions
              </button>
            </div>
          </form>

        @else
          <div class="flex flex-col items-center justify-center gap-3 py-20">
            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <p class="text-sm font-medium" style="color:var(--text-muted)">Select a staff member to manage page permissions</p>
          </div>
        @endif

      </div>
    </div>

  </div>
</div>
@endsection
