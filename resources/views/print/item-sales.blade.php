@extends('layouts.thermal')

@section('content')

{{-- KPI summary --}}
<table style="margin-bottom:1mm">
  <tr>
    <td class="small" style="color:#555">Total Qty</td>
    <td class="r small bold">{{ number_format($totals['qty'], 0) }}</td>
  </tr>
  <tr>
    <td class="small" style="color:#555">Revenue</td>
    <td class="r small bold">{{ number_format($totals['subtotal'], 0, ',', '.') }}</td>
  </tr>
  @if($totals['discount'] > 0)
  <tr>
    <td class="small" style="color:#555">Discount</td>
    <td class="r small" style="color:#e11d48">-{{ number_format($totals['discount'], 0, ',', '.') }}</td>
  </tr>
  @endif
  <tr>
    <td class="small" style="color:#555">Net Revenue</td>
    <td class="r small bold">{{ number_format($totals['net'], 0, ',', '.') }}</td>
  </tr>
  <tr>
    <td class="small" style="color:#555">Cost</td>
    <td class="r small">{{ number_format($totals['cost'], 0, ',', '.') }}</td>
  </tr>
  <tr>
    <td class="small" style="color:#555">Cost %</td>
    <td class="r small {{ $totals['costPct'] > 45 ? '' : '' }}"
        style="{{ $totals['costPct'] > 45 ? 'color:#e11d48;font-weight:bold' : '' }}">
      {{ number_format($totals['costPct'], 1) }}%
    </td>
  </tr>
</table>

<hr class="t-divider">

{{-- Items by department → category --}}
@foreach($byDept as $dept => $categories)
  <div class="bold" style="font-size:10px; margin:1.5mm 0 0.5mm; text-transform:uppercase; letter-spacing:.5px">
    {{ $dept }}
  </div>

  @foreach($categories as $cat => $items)
    <div class="small" style="color:#555; margin-bottom:0.5mm; padding-left:2mm">
      — {{ $cat }}
    </div>

    <table>
      @foreach($items as $item)
        @if($item->quantity == 0 && $item->subtotal == 0) @continue @endif
        <tr>
          <td style="font-size:9.5px; width:54%; padding-left:3mm">{{ $item->name }}</td>
          <td class="r" style="font-size:9.5px; width:15%">{{ number_format($item->quantity, 0) }}</td>
          <td class="r" style="font-size:9.5px; width:31%">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
      @endforeach
    </table>
  @endforeach
@endforeach

<hr class="t-divider">

{{-- Grand total footer --}}
<table>
  <tr>
    <td class="bold" style="font-size:10.5px; width:54%">GRAND TOTAL</td>
    <td class="r bold" style="font-size:10.5px; width:15%">{{ number_format($totals['qty'], 0) }}</td>
    <td class="r bold" style="font-size:10.5px; width:31%">{{ number_format($totals['subtotal'], 0, ',', '.') }}</td>
  </tr>
</table>

{{-- Column legend --}}
<div class="small c" style="color:#999; margin-top:1.5mm">Qty | Revenue</div>

@endsection
