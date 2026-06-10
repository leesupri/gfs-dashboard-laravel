<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Print — GFS Dashboard' }}</title>
  @php $faviconPath = \App\Models\AppSetting::get('logo_path'); @endphp
  <link rel="icon" href="{{ $faviconPath ? asset('storage/' . $faviconPath) : asset('favicon.ico') }}">
  <style>
    /* ── Thermal paper: 80mm ───────────────────────────── */
    @page {
      size: 80mm auto;
      margin: 4mm 3mm;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Courier New', Courier, monospace;
      font-size: 10.5px;
      line-height: 1.5;
      color: #000;
      background: #fff;
      width: 72mm;
      margin: 0 auto;
    }

    /* ── Screen preview wrapper ─────────────────────────── */
    @media screen {
      html { background: #e5e7eb; min-height: 100vh; padding: 60px 0 32px; }
      body {
        width: 80mm;
        margin: 0 auto;
        padding: 5mm 4mm;
        background: #fff;
        border: 1px solid #ccc;
        box-shadow: 0 4px 24px rgba(0,0,0,0.15);
        border-radius: 4px;
      }
    }

    /* ── Screen toolbar ─────────────────────────────────── */
    .thermal-toolbar {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: 48px;
      background: #0f1117;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      display: flex;
      align-items: center;
      padding: 0 16px;
      gap: 8px;
      z-index: 1000;
    }
    .thermal-toolbar span {
      color: rgba(255,255,255,0.5);
      font-family: ui-sans-serif, system-ui, sans-serif;
      font-size: 12px;
      margin-right: auto;
    }
    .btn-print {
      background: #22c55e;
      color: #fff;
      border: none;
      padding: 7px 18px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      font-family: ui-sans-serif, system-ui, sans-serif;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-print:hover { background: #16a34a; }
    .btn-back {
      background: rgba(255,255,255,0.1);
      color: rgba(255,255,255,0.75);
      border: 1px solid rgba(255,255,255,0.15);
      padding: 7px 14px;
      border-radius: 8px;
      font-size: 12px;
      cursor: pointer;
      font-family: ui-sans-serif, system-ui, sans-serif;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-back:hover { background: rgba(255,255,255,0.18); }

    @media print {
      .thermal-toolbar { display: none !important; }
      html { background: #fff !important; padding: 0 !important; }
      body { border: none !important; box-shadow: none !important; border-radius: 0 !important; padding: 0 !important; }
    }

    /* ── Thermal elements ───────────────────────────────── */
    .t-logo     { text-align: center; margin-bottom: 3mm; }
    .t-logo img { max-width: 50mm; max-height: 18mm; object-fit: contain; }

    .t-biz  { text-align: center; font-size: 13px; font-weight: bold; letter-spacing: .5px; }
    .t-sub  { text-align: center; font-size: 9.5px; color: #555; margin-top: 1px; }

    .t-title {
      text-align: center;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin: 2mm 0 1mm;
    }
    .t-range { text-align: center; font-size: 9px; color: #444; }

    .t-divider      { border: none; border-top: 1px dashed #000; margin: 2mm 0; }
    .t-divider-solid{ border: none; border-top: 1px solid #000;  margin: 2mm 0; }

    table   { width: 100%; border-collapse: collapse; }
    td, th  { font-size: 10px; padding: 0.5px 0; vertical-align: top; }
    .r      { text-align: right; }
    .c      { text-align: center; }
    .bold   { font-weight: bold; }
    .small  { font-size: 9px; }
    .indent { padding-left: 3mm; }

    .kpi-row { display: flex; justify-content: space-between; margin: 0.5mm 0; font-size: 10px; }
    .kpi-row .kpi-val { font-weight: bold; }
    .kpi-row.total    { font-size: 11px; font-weight: bold; border-top: 1px solid #000; padding-top: 1mm; margin-top: 1mm; }

    .t-footer {
      text-align: center;
      font-size: 8.5px;
      color: #777;
      margin-top: 3mm;
    }
    .t-printed {
      text-align: right;
      font-size: 8px;
      color: #aaa;
      margin-top: 2mm;
    }
  </style>
</head>
<body>

  {{-- Toolbar (screen only) --}}
  <div class="thermal-toolbar">
    <span>{{ $title ?? 'Print Preview' }} — {{ $dateRange ?? '' }}</span>
    <button class="btn-back" onclick="history.back()">
      ← Back
    </button>
    <button class="btn-print" onclick="window.print()">
      🖨 Print
    </button>
  </div>

  {{-- Logo --}}
  @if(!empty($logo))
    <div class="t-logo"><img src="{{ $logo }}" alt="Logo"></div>
  @endif

  <div class="t-biz">Gundaling Farmstead</div>
  <div class="t-sub">GFS Dashboard</div>

  <div class="t-title">{{ $title ?? '' }}</div>
  <div class="t-range">{{ $dateRange ?? '' }}</div>

  <hr class="t-divider-solid">

  @yield('content')

  <hr class="t-divider">
  <div class="t-footer">
    Gundaling Farmstead &mdash; Internal Use Only
  </div>
  <div class="t-printed">Printed: {{ now()->format('d/m/Y H:i') }}</div>

  <script>
    @php
      $cutStation = $station ?? null;
      if (!$cutStation && request()->filled('station')) {
          $cutStation = \App\Models\PrinterStation::find((int) request('station'));
      }
      $cutUrl = ($cutStation && $cutStation->is_auto_cut && $cutStation->ip_address)
                ? route('print.cut', $cutStation)
                : null;
    @endphp

    @if(request()->boolean('autoprint'))
      window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
      });
    @endif

    @if($cutUrl)
      window.addEventListener('afterprint', function () {
        fetch('{{ $cutUrl }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        }).catch(function () {});
      });
    @endif
  </script>
</body>
</html>
