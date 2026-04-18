@props([
    'href',
    'label',
    'icon' => 'file',
    'active' => false,
])

<div class="relative group">
    <a href="{{ $href }}"
       @click="loading = true; mobileMenuOpen = false"
       :class="collapsed ? 'justify-center' : 'gap-3'"
       class="flex items-center rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200
              {{ $active ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1' }}">

        <span class="absolute left-0 top-0 h-full w-1 rounded-r bg-green-500 transition-all duration-300
                     {{ $active ? 'opacity-100' : 'opacity-0 group-hover:opacity-50' }}"></span>

        <x-nav-icon :name="$icon" :active="$active" />

        <span x-show="!collapsed" x-transition>{{ $label }}</span>
    </a>

    <div x-show="collapsed"
         x-cloak
         class="pointer-events-none absolute left-full top-1/2 z-[9999] ml-3 -translate-y-1/2 whitespace-nowrap rounded bg-gray-900 px-3 py-1.5 text-xs text-white shadow-xl opacity-0 transition group-hover:opacity-100">
        {{ $label }}
    </div>
</div>