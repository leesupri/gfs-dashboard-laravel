@extends('layouts.thermal')

@section('content')

{{-- Header info --}}
<table>
  <tr>
    <td class="small" style="color:#555">Invoice</td>
    <td class="r small bold">#{{ $sale->invoice_id }}</td>
  </tr>
  <tr>
    <td class="small" style="color:#555">Date</td>
    <td class="r small">{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</td>
  </tr>
  <tr>
    <td class="small" style="color:#555">Time</td>
    <td class="r small">{{ $sale->closedTime ? \Carbon\Carbon::parse($sale->closedTime)->format('H:i') : '-' }}</td>
  </tr>
  @if($sale->tableName)
  <tr>
    <td class="small" style="color:#555">Table</td>
    <td class="r small">{{ $sale->tableName }}</td>
  </tr>
  @endif
  @if($sale->cashier)
  <tr>
    <td class="small" style="color:#555">Cashier</td>
    <td class="r small">{{ $sale->cashier }}</td>
  </tr>
  @endif
  @if($sale->pax)
  <tr>
    <td class="small" style="color:#555">Guests</td>
    <td class="r small">{{ $sale->pax }}</td>
  </tr>
  @endif
</table>

<hr class="t-divider">

{{-- Items --}}
@foreach($items as $item)
  @php
    $isModifier = str_starts_with(trim($item->description), '~')
                || str_starts_with(trim($item->description), '-')
                || strtoupper($item->category ?? '') === 'MODIFIER'
                || strtoupper($item->department ?? '') === 'MODIFIER';
    $lineTotal = $item->quantity * $item->unitPrice;
  @endphp
  @if($isModifier)
    <div style="font-size:9px; color:#777; padding-left:3mm;">
      {{ ltrim($item->description, '~- ') }}
    </div>
  @else
    <table style="margin-bottom:1px">
      <tr>
        <td style="font-size:10px; font-weight:bold; width:70%">{{ $item->description }}</td>
        <td class="r bold" style="font-size:10px; width:30%">
          {{ $lineTotal > 0 ? number_format($lineTotal, 0, ',', '.') : '' }}
        </td>
      </tr>
      @if($item->quantity != 1 || $item->unitPrice != $lineTotal)
      <tr>
        <td class="small indent" style="color:#666">
          {{ number_format($item->quantity, 0) }} × {{ number_format($item->unitPrice, 0, ',', '.') }}
        </td>
        <td></td>
      </tr>
      @endif
      @if($item->discountAmount > 0)
      <tr>
        <td class="small indent" style="color:#e11d48">Discount</td>
        <td class="r small" style="color:#e11d48">-{{ number_format($item->discountAmount, 0, ',', '.') }}</td>
      </tr>
      @endif
    </table>
  @endif
@endforeach

<hr class="t-divider">

{{-- Totals --}}
<div class="kpi-row">
  <span>Subtotal</span>
  <span class="kpi-val">{{ number_format($sale->subtotal, 0, ',', '.') }}</span>
</div>
@if($sale->discountAmount > 0)
<div class="kpi-row" style="color:#e11d48">
  <span>Discount</span>
  <span class="kpi-val">-{{ number_format($sale->discountAmount, 0, ',', '.') }}</span>
</div>
@endif
@if($sale->serviceChargeAmount > 0)
<div class="kpi-row">
  <span>Service</span>
  <span class="kpi-val">{{ number_format($sale->serviceChargeAmount, 0, ',', '.') }}</span>
</div>
@endif
@if($sale->taxAmount > 0)
<div class="kpi-row">
  <span>Tax</span>
  <span class="kpi-val">{{ number_format($sale->taxAmount, 0, ',', '.') }}</span>
</div>
@endif
<div class="kpi-row total">
  <span>TOTAL</span>
  <span class="kpi-val">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
</div>

<hr class="t-divider">

{{-- Payment --}}
<div class="bold small" style="margin-bottom:1mm">Payment</div>
@foreach($payments as $pmt)
<div class="kpi-row">
  <span>{{ $pmt->method }}</span>
  <span class="kpi-val">{{ number_format($pmt->amount, 0, ',', '.') }}</span>
</div>
@endforeach
@if($change > 0)
<div class="kpi-row" style="color:#16a34a">
  <span>Change</span>
  <span class="kpi-val">{{ number_format($change, 0, ',', '.') }}</span>
</div>
@endif

@if($sale->member)
<hr class="t-divider">
<div class="small c">Member: {{ $sale->member }}</div>
@endif

<hr class="t-divider">
<div class="t-footer">
  Thank you for dining with us!<br>
  Gundaling Farmstead
</div>

@endsection
