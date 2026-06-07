@extends('layouts.thermal')

@section('content')

{{-- Station info --}}
<table>
  <tr><td class="bold">Station</td><td class="r">{{ $station->name }}</td></tr>
  @if($station->ip_address)
  <tr><td>IP Address</td><td class="r">{{ $station->ip_address }}</td></tr>
  @endif
  @if($station->location)
  <tr><td>Location</td><td class="r">{{ $station->location }}</td></tr>
  @endif
  <tr><td>Status</td><td class="r">{{ $station->is_active ? 'Active' : 'Inactive' }}</td></tr>
</table>

<hr class="t-divider">

{{-- Print quality check --}}
<div style="text-align:center;font-size:9px;margin-bottom:1mm">--- PRINT QUALITY CHECK ---</div>

<div style="font-size:9.5px;letter-spacing:0;word-break:break-all;line-height:1.6">
  <div>123456789012345678901234567890123456</div>
  <div>|       |       |       |       |  </div>
  <div>ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789</div>
  <div>abcdefghijklmnopqrstuvwxyz!@#$%&amp;*()</div>
  <div>IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII</div>
  <div>MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM</div>
  <div>||||||||||||||||||||||||||||||||||||</div>
</div>

<hr class="t-divider">

{{-- Sample receipt lines --}}
<table>
  <tr>
    <td>Nasi Goreng Spesial</td>
    <td class="r bold">Rp 45.000</td>
  </tr>
  <tr>
    <td class="indent small">+ Extra Egg</td>
    <td class="r small">Rp 5.000</td>
  </tr>
  <tr>
    <td>Es Teh Manis</td>
    <td class="r bold">Rp 8.000</td>
  </tr>
  <tr>
    <td>Ayam Bakar Bumbu Rujak</td>
    <td class="r bold">Rp 62.000</td>
  </tr>
</table>
<hr class="t-divider">
<table>
  <tr><td>Subtotal</td><td class="r">Rp 120.000</td></tr>
  <tr><td>Discount</td><td class="r">Rp 0</td></tr>
  <tr><td>Service (5%)</td><td class="r">Rp 6.000</td></tr>
  <tr><td>Tax (10%)</td><td class="r">Rp 12.000</td></tr>
</table>
<hr class="t-divider-solid">
<table>
  <tr class="bold" style="font-size:12px">
    <td>TOTAL</td>
    <td class="r">Rp 138.000</td>
  </tr>
  <tr><td>Cash</td><td class="r">Rp 150.000</td></tr>
  <tr><td>Change</td><td class="r">Rp 12.000</td></tr>
</table>

<hr class="t-divider">
<div style="text-align:center;font-size:9px">--- END OF TEST ---</div>
<div style="text-align:center;font-size:8px;color:#888;margin-top:1mm">
  If text is clear and aligned, printer is working correctly.
</div>

@endsection
