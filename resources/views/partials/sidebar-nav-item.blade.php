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
              {{ $active
                  ? 'bg-white/10 text-white'
                  : 'hover:bg-white/6 hover:translate-x-0.5' }}"
       style="{{ $active ? '' : 'color:var(--sidebar-text)' }}"
       @mouseenter="{{ $active ? '' : '$el.style.color=\'var(--sidebar-text-hover)\'' }}"
       @mouseleave="{{ $active ? '' : '$el.style.color=\'var(--sidebar-text)\'' }}"
    >
        {{-- Active pill indicator --}}
        <span class="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-1 rounded-r transition-all duration-300
                     {{ $active ? 'opacity-100' : 'opacity-0 group-hover:opacity-40' }}"
              style="background: var(--sidebar-pill)"></span>

        <x-nav-icon :name="$icon" :active="$active" />

        <span x-show="!collapsed" x-transition class="{{ $active ? 'text-white' : '' }}">
            {{ $label }}
        </span>
    </a>

    {{-- Tooltip when collapsed --}}
    <div x-show="collapsed"
         x-cloak
         class="pointer-events-none absolute left-full top-1/2 z-[9999] ml-3 -translate-y-1/2
                whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-medium text-white shadow-xl
                opacity-0 transition group-hover:opacity-100"
         style="background: var(--sidebar-bg); border: 1px solid rgba(255,255,255,0.12);">
        {{ $label }}
    </div>
</div>