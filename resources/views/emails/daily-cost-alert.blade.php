<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    body    { font-family:Arial,sans-serif; background:#f5f4f1; margin:0; padding:24px; color:#141414; }
    .card   { background:#fff; border-radius:12px; max-width:640px; margin:0 auto; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08); }
    .header { background:#0f1117; padding:24px 32px; }
    .header h2 { color:#fff; margin:0 0 4px; font-size:17px; }
    .header p  { color:rgba(255,255,255,0.45); margin:0; font-size:12px; }
    .alert-badge { display:inline-block; background:rgba(239,68,68,0.2); color:#f87171; border:1px solid rgba(239,68,68,0.3); border-radius:20px; padding:4px 14px; font-size:12px; font-weight:700; margin-top:12px; }
    .body   { padding:28px 32px; }
    .kpi-row { display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap; }
    .kpi    { flex:1; min-width:120px; background:#f5f4f1; border-radius:10px; padding:14px 16px; }
    .kpi-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#a3a3a3; }
    .kpi-val   { font-size:20px; font-weight:800; margin-top:4px; color:#141414; }
    .kpi-val.danger { color:#ef4444; }
    .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#a3a3a3; margin:20px 0 10px; }
    table   { width:100%; border-collapse:collapse; font-size:12px; }
    th      { background:#f5f4f1; text-align:left; padding:8px 10px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#6b6b6b; }
    td      { padding:9px 10px; border-bottom:1px solid #f0eeeb; color:#141414; }
    tr:last-child td { border:none; }
    .cost-pct { font-weight:800; }
    .cost-pct.critical { color:#ef4444; }
    .cost-pct.high     { color:#f97316; }
    .info-row { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #f0eeeb; font-size:13px; }
    .info-row span:first-child { color:#6b6b6b; }
    .info-row span:last-child  { font-weight:600; }
    .footer { text-align:center; padding:14px 32px 18px; font-size:11px; color:#a3a3a3; border-top:1px solid #f0eeeb; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <h2>⚠️ Daily Cost Alert — Gundaling Farmstead</h2>
      <p>GFS Dashboard · Automated Cost Monitoring</p>
      <div class="alert-badge">
        Cost {{ number_format($costPct, 1) }}% — exceeds {{ number_format($threshold, 0) }}% threshold
      </div>
    </div>

    <div class="body">

      {{-- EOD info --}}
      <div class="section-title">End of Day Summary</div>
      <div>
        <div class="info-row">
          <span>Date</span>
          <span>{{ $date->format('l, d F Y') }}</span>
        </div>
        <div class="info-row">
          <span>EOD Closed At</span>
          <span>{{ $eod->closed ? \Carbon\Carbon::parse($eod->closed)->format('H:i') : '—' }} WIB</span>
        </div>
      </div>

      {{-- KPI cards --}}
      <div class="section-title" style="margin-top:20px">Financial Summary</div>
      <div class="kpi-row">
        <div class="kpi">
          <div class="kpi-label">Total Revenue</div>
          <div class="kpi-val">{{ number_format($summary->revenue, 0, ',', '.') }}</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Total Cost</div>
          <div class="kpi-val">{{ number_format($summary->cost, 0, ',', '.') }}</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Cost %</div>
          <div class="kpi-val danger">{{ number_format($costPct, 1) }}%</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Threshold</div>
          <div class="kpi-val">{{ number_format($threshold, 0) }}%</div>
        </div>
      </div>

      {{-- High cost items table --}}
      @if($items->isNotEmpty())
        <div class="section-title">
          Items with Cost &gt; {{ number_format($itemThreshold, 0) }}%
          <span style="font-weight:400;text-transform:none;color:#ef4444">
            ({{ $items->count() }} item{{ $items->count() > 1 ? 's' : '' }} found)
          </span>
        </div>
        <table>
          <thead>
            <tr>
              <th>Department</th>
              <th>Category</th>
              <th>Item</th>
              <th style="text-align:right">Qty</th>
              <th style="text-align:right">Revenue</th>
              <th style="text-align:right">Cost</th>
              <th style="text-align:right">Cost %</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              @php $pct = (float)$item->costPct; @endphp
              <tr>
                <td>{{ $item->department }}</td>
                <td>{{ $item->category }}</td>
                <td><strong>{{ $item->description }}</strong></td>
                <td style="text-align:right">{{ number_format($item->qty, 0) }}</td>
                <td style="text-align:right">{{ number_format($item->revenue, 0, ',', '.') }}</td>
                <td style="text-align:right">{{ number_format($item->cost, 0, ',', '.') }}</td>
                <td style="text-align:right">
                  <span class="cost-pct {{ $pct >= 150 ? 'critical' : 'high' }}">
                    {{ number_format($pct, 1) }}%
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <p style="color:#6b6b6b;font-size:13px;">
          No individual items exceeded the {{ number_format($itemThreshold, 0) }}% item cost threshold,
          but the overall daily cost is above {{ number_format($threshold, 0) }}%.
          Please review recipe costs and portion sizes.
        </p>
      @endif

      <p style="margin-top:20px;font-size:12px;color:#6b6b6b;line-height:1.6;">
        <strong>Recommended actions:</strong> Review the items above for incorrect recipe costs,
        price adjustments, or unusually high waste. Access the
        <a href="{{ url('/reports/daily-category') }}" style="color:#22c55e">Daily Category</a> or
        <a href="{{ url('/item-sales') }}" style="color:#22c55e">Item Sales</a> report for more detail.
      </p>

    </div>

    <div class="footer">
      Gundaling Farmstead — Automated alert. Do not reply to this email.<br>
      Thresholds: Overall cost &gt; {{ number_format($threshold, 0) }}% · Item cost &gt; {{ number_format($itemThreshold, 0) }}%
    </div>
  </div>
</body>
</html>
