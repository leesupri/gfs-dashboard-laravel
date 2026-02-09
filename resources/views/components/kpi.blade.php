@props([
  'label',
  'value' => 0,
  'highlight' => false,
  'negative' => false,
])

<div class="rounded-xl border p-4 bg-white">
  <div class="text-xs text-gray-500 mb-1">
    {{ $label }}
  </div>

  <div class="text-lg font-semibold
    {{ $highlight ? 'text-emerald-600' : '' }}
    {{ $negative ? 'text-red-600' : '' }}
  ">
    {{ number_format($value, 2) }}
  </div>
</div>
