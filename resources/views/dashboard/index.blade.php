@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ (request('start') || request('end')) ? 'true' : 'false' }} }" class="space-y-6">

  {{-- Header + Filter toggle --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Dashboard</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">
        {{ \Carbon\Carbon::parse($start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
        <span class="ml-1.5 rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700">
          {{ $days }} day{{ $days > 1 ? 's' : '' }}
        </span>
      </p>
    </div>
    <button type="button" @click="filtersOpen = !filtersOpen"
      class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-white transition active:scale-95"
      :class="filtersOpen ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
      </svg>
      <span x-text="filtersOpen ? 'Hide Filters' : 'Filters'"></span>
    </button>
  </div>

  {{-- Filter panel --}}
  <div x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2">
    <form method="GET" action="{{ route('dashboard') }}" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label for="filter-start" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">From</label>
          <input id="filter-start" type="date" name="start" value="{{ $start }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
        <div>
          <label for="filter-end" class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted)">To</label>
          <input id="filter-end" type="date" name="end" value="{{ $end }}"
            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-green-400 focus:outline-none focus:ring-2 focus:ring-green-100">
        </div>
      </div>
      <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
        <div class="flex flex-wrap gap-2">
          <span class="text-xs font-medium" style="color:var(--text-muted)">Quick:</span>
          @foreach([
            ['Today',      ['start' => now()->toDateString(),                              'end' => now()->toDateString()]],
            ['Yesterday',  ['start' => now()->subDay()->toDateString(),                     'end' => now()->subDay()->toDateString()]],
            ['This Week',  ['start' => now()->startOfWeek()->toDateString(),                'end' => now()->endOfWeek()->toDateString()]],
            ['This Month', ['start' => now()->startOfMonth()->toDateString(),               'end' => now()->endOfMonth()->toDateString()]],
            ['Last Month', ['start' => now()->subMonth()->startOfMonth()->toDateString(),   'end' => now()->subMonth()->endOfMonth()->toDateString()]],
          ] as [$ql, $qp])
            <a href="{{ route('dashboard', $qp) }}"
              class="rounded-lg border border-gray-200 px-3 py-1 text-xs font-medium transition hover:border-green-400 hover:text-green-600"
              style="color:var(--text-secondary)">{{ $ql }}</a>
          @endforeach
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('dashboard') }}"
            class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
            style="color:var(--text-secondary)">Reset</a>
          <button type="submit" @click="loading = true"
            class="rounded-xl bg-green-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-700 active:scale-95">
            Apply
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- KPI Cards — 4 primary + 4 secondary --}}
  @php
    $grossSales  = (float)($kpi->grossSales  ?? 0);
    $netSales    = (float)($kpi->netSales    ?? 0);
    $discount    = (float)($kpi->discount    ?? 0);
    $total       = (float)($kpi->total       ?? 0);
    $trx         = (int)  ($kpi->trx         ?? 0);
    $avgTicket   = (float)($kpi->avgTicket   ?? 0);
    $totalPax    = (int)  ($kpi->totalPax    ?? 0);
    $avgPerPax   = (float)($kpi->avgPerPax   ?? 0);
  @endphp

  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    {{-- Total Sales --}}
    <div class="gfs-card flex flex-col gap-2 p-5" style="background:var(--sidebar-bg)">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-semibold uppercase tracking-wider text-green-400/70">Total Sales</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl" style="background:rgba(34,197,94,0.2)">
          <svg class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </span>
      </div>
      <p class="text-xl font-bold leading-tight text-green-400 tabular-nums">
        {{ number_format($total, 0, ',', '.') }}
      </p>
      <p class="text-[10px]" style="color:rgba(255,255,255,0.4)">Gross: {{ number_format($grossSales, 0, ',', '.') }}</p>
    </div>

    {{-- Transactions --}}
    <div class="gfs-card flex flex-col gap-2 p-5">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Transactions</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-100">
          <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </span>
      </div>
      <p class="text-xl font-bold leading-tight tabular-nums" style="color:var(--text-primary)">
        {{ number_format($trx, 0, ',', '.') }}
      </p>
      <p class="text-[10px]" style="color:var(--text-muted)">{{ $days > 1 ? number_format($trx / $days, 1).' / day' : 'Total orders' }}</p>
    </div>

    {{-- Avg Ticket --}}
    <div class="gfs-card flex flex-col gap-2 p-5">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Avg Ticket</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-100">
          <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
          </svg>
        </span>
      </div>
      <p class="text-xl font-bold leading-tight tabular-nums" style="color:var(--text-primary)">
        {{ number_format($avgTicket, 0, ',', '.') }}
      </p>
      <p class="text-[10px]" style="color:var(--text-muted)">Per transaction</p>
    </div>

    {{-- Discount --}}
    <div class="gfs-card flex flex-col gap-2 p-5 {{ $discount > 0 ? 'border-red-200' : '' }}">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">Discount</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $discount > 0 ? 'bg-red-100' : 'bg-gray-100' }}">
          <svg class="h-4 w-4 {{ $discount > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
          </svg>
        </span>
      </div>
      <p class="text-xl font-bold leading-tight tabular-nums {{ $discount > 0 ? 'text-red-500' : '' }}"
        @if(!$discount) style="color:var(--text-primary)" @endif>
        {{ number_format($discount, 0, ',', '.') }}
      </p>
      <p class="text-[10px]" style="color:var(--text-muted)">
        Net sales: {{ number_format($netSales, 0, ',', '.') }}
      </p>
    </div>
  </div>

  {{-- Secondary KPIs --}}
  <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
    @foreach([
      ['Pax / Guests',   number_format($totalPax),                    'Total guests served'],
      ['Avg / Pax',      number_format($avgPerPax, 0, ',', '.'),      'Revenue per guest'],
      ['Net Sales',      number_format($netSales, 0, ',', '.'),       'After discount'],
      ['Sales / Day',    $days > 1 ? number_format($total / $days, 0, ',', '.') : '—', 'Daily avg revenue'],
    ] as [$label, $val, $sub])
      <div class="gfs-card px-4 py-3">
        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:var(--text-muted)">{{ $label }}</p>
        <p class="mt-1 text-base font-bold tabular-nums" style="color:var(--text-primary)">{{ $val }}</p>
        <p class="text-[10px]" style="color:var(--text-muted)">{{ $sub }}</p>
      </div>
    @endforeach
  </div>

  {{-- Daily trend (only when > 1 day) --}}
  @if($days > 1)
  <div class="gfs-card p-5">
    <div class="mb-4 flex items-start justify-between">
      <div>
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Daily Sales Trend</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Revenue per day over the selected period.</p>
      </div>
    </div>
    <div class="relative h-55">
      <canvas id="dailyChart"></canvas>
    </div>
  </div>
  @endif

  {{-- Hourly Sales --}}
  <div class="gfs-card p-5">
    <div class="mb-4">
      <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Hourly Sales</h2>
      <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Revenue distribution across operating hours — identify peak times.</p>
    </div>
    <div class="relative h-65">
      <canvas id="hourlyChart"></canvas>
    </div>
  </div>

  {{-- Dept + Category --}}
  <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="gfs-card p-5">
      <div class="mb-4">
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Sales by Department</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Revenue split by department.</p>
      </div>
      <div class="relative h-65">
        <canvas id="deptChart"></canvas>
      </div>
    </div>
    <div class="gfs-card p-5">
      <div class="mb-4">
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Sales by Category</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Top 10 categories by revenue.</p>
      </div>
      <div class="relative h-65">
        <canvas id="catChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Payment + Outlet --}}
  <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="gfs-card p-5">
      <div class="mb-4">
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Payment Breakdown</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Cash, QRIS, EDC and others.</p>
      </div>
      <div class="relative h-65">
        <canvas id="paymentChart"></canvas>
      </div>
    </div>
    <div class="gfs-card p-5">
      <div class="mb-4">
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Outlet Comparison</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Hall, Balkon, VIP and other areas.</p>
      </div>
      <div class="relative h-65">
        <canvas id="outletChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Top Products --}}
  <div class="gfs-card p-5">
    <div class="mb-4">
      <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Top Selling Products</h2>
      <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Top 10 by quantity sold.</p>
    </div>
    <div class="relative h-90">
      <canvas id="topChart"></canvas>
    </div>
  </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
  const P = {
    green:  '#22c55e', teal:   '#14b8a6', blue:   '#3b82f6',
    amber:  '#f59e0b', red:    '#ef4444', purple: '#a855f7',
    slate:  '#64748b', lime:   '#84cc16', orange: '#f97316', pink: '#ec4899',
  };
  const palette = Object.values(P);

  Chart.defaults.font.family = "'Sora', ui-sans-serif, system-ui, sans-serif";
  Chart.defaults.font.size   = 11;
  Chart.defaults.color       = '#6b6b6b';

  const grid   = { color: 'rgba(0,0,0,0.05)', lineWidth: 1 };
  const money  = v => Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
  const tip    = ctx => money(ctx.raw);

  const dailyData   = @json($dateRange);
  const hourlyData  = @json($hourlySales);
  const deptData    = @json($byDept);
  const catData     = @json($byCategory);
  const topData     = @json($topProducts);
  const paymentData = @json($paymentRows);
  const outletData  = @json($outletRows);

  // ── Daily Trend ──────────────────────────────────────────────────────────
  @if($days > 1)
  new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
      labels: dailyData.map(x => x.label),
      datasets: [{
        label: 'Sales',
        data:  dailyData.map(x => x.total),
        backgroundColor: 'rgba(34,197,94,0.7)',
        borderColor:     P.green,
        borderWidth: 1,
        borderRadius: 4,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: tip } }
      },
      scales: {
        x: { grid: { display: false } },
        y: { grid, ticks: { callback: v => money(v) } }
      }
    }
  });
  @endif

  // ── Hourly Sales ─────────────────────────────────────────────────────────
  new Chart(document.getElementById('hourlyChart'), {
    type: 'line',
    data: {
      labels: hourlyData.map(x => x.label),
      datasets: [{
        label: 'Sales',
        data:  hourlyData.map(x => x.total),
        tension: 0.4,
        fill: true,
        borderColor:     P.green,
        backgroundColor: 'rgba(34,197,94,0.08)',
        pointBackgroundColor: P.green,
        pointRadius: 3, pointHoverRadius: 5, borderWidth: 2,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: tip } } },
      scales: {
        x: { grid: { display: false } },
        y: { grid, ticks: { callback: v => money(v) } }
      }
    }
  });

  // ── Dept (doughnut) ───────────────────────────────────────────────────────
  new Chart(document.getElementById('deptChart'), {
    type: 'doughnut',
    data: {
      labels: deptData.map(x => x.name),
      datasets: [{ data: deptData.map(x => x.total), backgroundColor: palette, borderWidth: 2, hoverOffset: 6 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '60%',
      plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, padding: 14 } },
        tooltip: { callbacks: { label: ctx => `${ctx.label}: ${money(ctx.raw)}` } }
      }
    }
  });

  // ── Category (horizontal bar) ─────────────────────────────────────────────
  new Chart(document.getElementById('catChart'), {
    type: 'bar',
    data: {
      labels: catData.map(x => x.name),
      datasets: [{
        label: 'Revenue',
        data:  catData.map(x => x.total),
        backgroundColor: palette,
        borderRadius: 4, borderSkipped: false,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: tip } } },
      scales: {
        x: { grid, ticks: { callback: v => money(v) } },
        y: { grid: { display: false } }
      }
    }
  });

  // ── Payment (doughnut) ────────────────────────────────────────────────────
  const payColors = { CASH: P.green, QRIS: P.amber, EDC: P.blue, OTHER: P.slate };
  new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
      labels: paymentData.map(x => x.bucket),
      datasets: [{
        data: paymentData.map(x => x.total),
        backgroundColor: paymentData.map(x => payColors[x.bucket] ?? P.slate),
        borderWidth: 2, hoverOffset: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '60%',
      plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, padding: 14 } },
        tooltip: { callbacks: { label: ctx => `${ctx.label}: ${money(ctx.raw)}` } }
      }
    }
  });

  // ── Outlet (bar) ──────────────────────────────────────────────────────────
  new Chart(document.getElementById('outletChart'), {
    type: 'bar',
    data: {
      labels: outletData.map(x => x.outlet),
      datasets: [{
        label: 'Revenue',
        data:  outletData.map(x => x.total),
        backgroundColor: [P.green, P.teal, P.blue, P.slate],
        borderRadius: 6, borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: tip } } },
      scales: {
        x: { grid: { display: false } },
        y: { grid, ticks: { callback: v => money(v) } }
      }
    }
  });

  // ── Top Products (horizontal bar) ─────────────────────────────────────────
  new Chart(document.getElementById('topChart'), {
    type: 'bar',
    data: {
      labels: topData.map(x => x.name),
      datasets: [
        {
          label: 'Qty',
          data:  topData.map(x => x.qty),
          backgroundColor: 'rgba(34,197,94,0.75)',
          borderRadius: 4, borderSkipped: false,
          yAxisID: 'yQty',
        },
        {
          label: 'Revenue',
          data:  topData.map(x => x.total),
          backgroundColor: 'rgba(59,130,246,0.55)',
          borderRadius: 4, borderSkipped: false,
          yAxisID: 'yRev',
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } },
        tooltip: {
          callbacks: {
            label: ctx => ctx.dataset.label === 'Qty'
              ? `Qty: ${Number(ctx.raw).toLocaleString('id-ID')}`
              : `Revenue: ${money(ctx.raw)}`
          }
        }
      },
      scales: {
        yQty: { position: 'left',  display: false },
        yRev: { position: 'right', display: false },
        y:    { grid: { display: false } },
        x:    { display: false }
      }
    }
  });
</script>
@endpush

@endsection
