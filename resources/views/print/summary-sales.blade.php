@extends('layouts.thermal')

@section('content')

{{-- ── Summary Totals ───────────────────────────────── --}}
<div class="bold small" style="text-transform:uppercase; letter-spacing:.5px; margin-bottom:1mm">Summary</div>
<div class="kpi-row">
  <span>Subtotal</span>
  <span class="kpi-val">{{ number_format($summary->subtotal, 0, ',', '.') }}</span>
</div>
@if($summary->discount > 0)
<div class="kpi-row" style="color:#e11d48">
  <span>Discount</span>
  <span class="kpi-val">-{{ number_format($summary->discount, 0, ',', '.') }}</span>
</div>
@endif
<div class="kpi-row">
  <span>Net Sales</span>
  <span class="kpi-val">{{ number_format($summary->subtotal - $summary->discount, 0, ',', '.') }}</span>
</div>
@if($summary->service > 0)
<div class="kpi-row">
  <span>Service</span>
  <span class="kpi-val">{{ number_format($summary->service, 0, ',', '.') }}</span>
</div>
@endif
@if($summary->tax > 0)
<div class="kpi-row">
  <span>Tax</span>
  <span class="kpi-val">{{ number_format($summary->tax, 0, ',', '.') }}</span>
</div>
@endif
<div class="kpi-row total">
  <span>TOTAL</span>
  <span class="kpi-val">Rp {{ number_format($summary->total, 0, ',', '.') }}</span>
</div>
<div class="kpi-row" style="margin-top:1mm">
  <span class="small" style="color:#555">Transactions</span>
  <span class="small bold">{{ number_format($summary->trxCount, 0) }}</span>
</div>

<hr class="t-divider">

{{-- ── Payment Breakdown ────────────────────────────── --}}
<div class="bold small" style="text-transform:uppercase; letter-spacing:.5px; margin-bottom:1mm">Payment</div>
@foreach($payments as $pmt)
<table style="margin-bottom:0.5px">
  <tr>
    <td style="font-size:9.5px; width:50%">{{ $pmt->name ?: 'Unknown' }}</td>
    <td class="r" style="font-size:9.5px; width:20%; color:#555">{{ number_format($pmt->qty, 0) }}×</td>
    <td class="r bold" style="font-size:9.5px; width:30%">{{ number_format($pmt->amount, 0, ',', '.') }}</td>
  </tr>
</table>
@endforeach
@if($payments->isEmpty())
  <div class="small" style="color:#999">No payment data.</div>
@endif

<hr class="t-divider">

{{-- ── Department Breakdown ─────────────────────────── --}}
<div class="bold small" style="text-transform:uppercase; letter-spacing:.5px; margin-bottom:1mm">Department</div>
@foreach($departments as $dept)
<table style="margin-bottom:0.5px">
  <tr>
    <td style="font-size:9.5px; width:50%">{{ $dept->department ?: 'N/A' }}</td>
    <td class="r" style="font-size:9.5px; width:20%; color:#555">{{ number_format($dept->qty, 0) }}</td>
    <td class="r bold" style="font-size:9.5px; width:30%">{{ number_format($dept->price, 0, ',', '.') }}</td>
  </tr>
  <tr>
    <td colspan="3" style="font-size:8.5px; color:#888; padding-left:2mm">
      {{ number_format($dept->pct, 1) }}% of revenue
    </td>
  </tr>
</table>
@endforeach
@if($departments->isEmpty())
  <div class="small" style="color:#999">No data.</div>
@endif

<hr class="t-divider">

{{-- ── Profit & Loss ────────────────────────────────── --}}
<div class="bold small" style="text-transform:uppercase; letter-spacing:.5px; margin-bottom:1mm">Profit & Loss</div>
<div class="kpi-row">
  <span>Net Sales</span>
  <span class="kpi-val">{{ number_format($profit->netSales, 0, ',', '.') }}</span>
</div>
<div class="kpi-row">
  <span>Total Cost</span>
  <span class="kpi-val">{{ number_format($profit->totalCost, 0, ',', '.') }}</span>
</div>
<div class="kpi-row" style="{{ $profit->costPct > 45 ? 'color:#e11d48' : 'color:#16a34a' }}">
  <span>Cost %</span>
  <span class="kpi-val bold">{{ number_format($profit->costPct, 1) }}%</span>
</div>
<div class="kpi-row total" style="{{ $profit->profit < 0 ? 'color:#e11d48' : '' }}">
  <span>Profit</span>
  <span class="kpi-val">{{ number_format($profit->profit, 0, ',', '.') }}</span>
</div>

@endsection
