@props([
  'label',
  'value' => 0,
  'note' => null,
  'negative' => false,
  'strong' => false,
])

<div class="flex justify-between text-sm py-1
  {{ $strong ? 'font-semibold' : '' }}
  {{ $negative ? 'text-red-600' : '' }}
">
  <span>{{ $label }}</span>

  <span class="text-right">
    {{ number_format($value, 2) }}
    @if($note)
      <span class="text-xs text-gray-500 ml-1">
        ({{ $note }})
      </span>
    @endif
  </span>
</div>
