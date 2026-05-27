{{--
  resources/views/components/modifier-page.blade.php

  Recursive component — renders one modifier page and its items.
  If any item has its own modifier page IDs, those are rendered
  by calling this component again (nested), with depth+1.

  Props:
    $pageId    — tbl_pages.id to render
    $slot      — modifier slot number (1–4)
    $allPages  — flat collection keyed by page id (passed from controller)
    $allItems  — flat collection keyed by page_id (passed from controller)
    $depth     — current nesting depth, starts at 0
--}}
@props([
    'pageId',
    'slot',
    'allPages',
    'allItems',
    'depth' => 0,
    'color' => 'indigo',   // 'indigo' for modifiers, 'orange' for combo
])

@php
  // Guard: unknown page or runaway recursion
  if ($depth > 5 || ! isset($allPages[$pageId])) {
      return;
  }

  $page  = $allPages[$pageId];
  $items = $allItems[$pageId] ?? collect();

  // Color palette — indigo (modifiers) vs orange (combo)
  $palette = $color === 'orange' ? [
      'border'     => 'border-orange-100',
      'borderL'    => 'border-l-orange-200',
      'headerBg'   => ['bg-orange-50', 'bg-orange-50/70', 'bg-orange-50/40'],
      'slotText'   => 'text-orange-400',
      'nameText'   => 'text-orange-800',
      'divider'    => 'divide-orange-50/60',
      'forcedBg'   => 'bg-rose-50 text-rose-600 ring-rose-200',
      'nestedBadge'=> 'bg-orange-50 text-orange-500 ring-orange-200',
      'chevron'    => 'text-orange-400',
  ] : [
      'border'     => 'border-indigo-100',
      'borderL'    => 'border-l-indigo-200',
      'headerBg'   => ['bg-indigo-50', 'bg-indigo-50/70', 'bg-indigo-50/40'],
      'slotText'   => 'text-indigo-400',
      'nameText'   => 'text-indigo-800',
      'divider'    => 'divide-indigo-50/60',
      'forcedBg'   => 'bg-rose-50 text-rose-600 ring-rose-200',
      'nestedBadge'=> 'bg-indigo-50 text-indigo-500 ring-indigo-200',
      'chevron'    => 'text-indigo-400',
  ];

  $wrapClass = $depth === 0
      ? "overflow-hidden rounded-xl border {$palette['border']} bg-white"
      : "mt-2 overflow-hidden rounded-lg border {$palette['border']} bg-white ml-3 border-l-2 {$palette['borderL']}";

  $headerBg = $palette['headerBg'][$depth] ?? $palette['headerBg'][2];
@endphp

<div class="{{ $wrapClass }}">

  {{-- Page header ── --}}
  <div class="flex items-center justify-between gap-2 {{ $headerBg }} px-3 py-2">
    <div class="flex items-center gap-1.5 min-w-0">
      <span class="shrink-0 text-[10px] font-bold {{ $palette['slotText'] }}">
        M{{ $slot }}
      </span>
      <span class="truncate text-xs font-medium {{ $palette['nameText'] }}">
        {{ $page->name }}
      </span>
    </div>
    <div class="flex shrink-0 items-center gap-1">
      @if ($page->isForced === 'yes')
        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[9px] font-semibold text-rose-600 ring-1 ring-inset ring-rose-200">
          forced
        </span>
      @endif
      @if ($page->isActive === 'no')
        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[9px] text-gray-400 ring-1 ring-inset ring-gray-200">
          inactive
        </span>
      @endif
    </div>
  </div>

  {{-- Items ── --}}
  @if ($items->isEmpty())
    <div class="px-3 py-2 text-[10px] italic text-gray-400">No items in this page.</div>
  @else
    <div class="divide-y {{ $palette['divider'] }}">
      @foreach ($items as $modItem)
        @php
          // Check if this item has its own nested modifier pages
          $nestedSlots = [];
          foreach ([1, 2, 3, 4] as $n) {
              $nestedPageId = $modItem->{"modifier{$n}_id"} ?? null;
              if ($nestedPageId && isset($allPages[$nestedPageId])) {
                  $nestedSlots[] = ['slot' => $n, 'pageId' => $nestedPageId];
              }
          }
          $hasNested = ! empty($nestedSlots);
        @endphp

        {{-- Wrap in Alpine scope only if item has nested modifiers ── --}}
        @if ($hasNested)
          <div x-data="{ open: false }" class="px-3 py-1.5">
        @else
          <div class="px-3 py-1.5">
        @endif

            {{-- Item row ── --}}
            <div
              class="flex items-center justify-between gap-2 {{ $hasNested ? 'cursor-pointer select-none' : '' }}"
              @if ($hasNested) @click="open = !open" @endif
            >
              <div class="flex min-w-0 items-center gap-1.5">
                @if ($hasNested)
                  <svg
                    :class="open ? 'rotate-90' : ''"
                    class="h-2.5 w-2.5 shrink-0 {{ $palette['chevron'] }} transition-transform duration-150"
                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                @else
                  <span class="h-2.5 w-2.5 shrink-0"></span>
                @endif

                <div class="min-w-0">
                  <span class="block truncate text-xs text-gray-800">{{ $modItem->name }}</span>
                  @if ($modItem->code)
                    <span class="font-mono text-[10px] text-gray-400">{{ $modItem->code }}</span>
                  @endif
                </div>
              </div>

              <div class="flex shrink-0 items-center gap-1">
                @if ($hasNested)
                  <span class="rounded-full {{ $palette['nestedBadge'] }} px-1.5 py-0.5 text-[9px] font-medium ring-1 ring-inset">
                    {{ count($nestedSlots) }} mod
                  </span>
                @endif
                @if ($modItem->isActive === 'no')
                  <span class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[9px] text-gray-400 ring-1 ring-inset ring-gray-200">
                    inactive
                  </span>
                @endif
              </div>
            </div>

            {{-- Nested modifier pages for this item ── --}}
            @if ($hasNested)
              <div x-show="open" x-collapse class="mt-1">
                @foreach ($nestedSlots as $nested)
                  <x-modifier-page
                    :page-id="$nested['pageId']"
                    :slot="$nested['slot']"
                    :all-pages="$allPages"
                    :all-items="$allItems"
                    :depth="$depth + 1"
                    :color="$color"
                  />
                @endforeach
              </div>
            @endif

        </div>
      @endforeach
    </div>
  @endif

</div>