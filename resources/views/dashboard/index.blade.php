@extends('layouts.app')

@section('content')
<div x-data="{ filtersOpen: {{ (request('start') || request('end')) ? 'true' : 'false' }} }" class="space-y-6">

  {{-- Header + Filter --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-xl font-semibold" style="color:var(--text-primary)">{{ $title ?? 'Dashboard' }}</h1>
      <p class="mt-0.5 text-sm" style="color:var(--text-secondary)">Sales performance overview for Gundaling Farmstead.</p>
    </div>
    <div class="flex items-center gap-2">
      <button type="button" @click="filtersOpen = !filtersOpen"
        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-white transition active:scale-95"
        :class="filtersOpen ? 'bg-green-700' : 'bg-green-600 hover:bg-green-700'">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span x-text="filtersOpen ? 'Hide Filters' : 'Filters'"></span>
      </button>
    </div>
  </div>

  {{-- Filter panel --}}
  <div
    x-show="filtersOpen" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
  >
    <form method="GET" class="gfs-card p-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <label for="filter-start" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">Start</label>
          <input id="filter-start" type="date" name="start" value="{{ $start }}"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
        </div>
        <div>
          <label for="filter-end" class="mb-1 block text-xs font-medium" style="color:var(--text-muted)">End</label>
          <input id="filter-end" type="date" name="end" value="{{ $end }}"
            class="w-full rounded-lg border bg-white px-3 py-2 text-sm outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
            style="border-color:var(--card-border); color:var(--text-primary)">
        </div>
      </div>
      <div class="mt-4 flex items-center justify-end gap-2 border-t pt-4" style="border-color:var(--card-border)">
        <a href="{{ route('dashboard') }}"
          class="rounded-lg border bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
          style="border-color:var(--card-border); color:var(--text-secondary)">
          Reset
        </a>
        <button type="submit" @click="loading = true"
          class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 active:scale-95">
          Apply
        </button>
      </div>
    </form>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    <div class="gfs-card p-5 flex flex-col gap-3">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Total Sales</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-green-50 text-green-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </span>
      </div>
      <div>
        <p class="text-2xl font-semibold tracking-tight" style="color:var(--text-primary)">
          Rp {{ number_format((float)($kpi->sales ?? 0), 0, ',', '.') }}
        </p>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">
          {{ \Carbon\Carbon::parse($start)->format('d M') }} – {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
        </p>
      </div>
    </div>

    <div class="gfs-card p-5 flex flex-col gap-3">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Transactions</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </span>
      </div>
      <div>
        <p class="text-2xl font-semibold tracking-tight" style="color:var(--text-primary)">
          {{ number_format((int)($kpi->trx ?? 0), 0, ',', '.') }}
        </p>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Total orders placed</p>
      </div>
    </div>

    <div class="gfs-card p-5 flex flex-col gap-3">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Avg Ticket</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
          </svg>
        </span>
      </div>
      <div>
        <p class="text-2xl font-semibold tracking-tight" style="color:var(--text-primary)">
          Rp {{ number_format((float)($kpi->avgTicket ?? 0), 0, ',', '.') }}
        </p>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Per transaction average</p>
      </div>
    </div>

    <div class="gfs-card p-5 flex flex-col gap-3">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium" style="color:var(--text-muted)">Date Range</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </span>
      </div>
      <div>
        @php $days = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1; @endphp
        <p class="text-2xl font-semibold tracking-tight" style="color:var(--text-primary)">{{ $days }} days</p>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">
          {{ \Carbon\Carbon::parse($start)->format('d M') }} – {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
        </p>
      </div>
    </div>

  </div>

  {{-- Hourly Sales --}}
  <div class="gfs-card p-5">
    <div class="mb-5 flex items-start justify-between">
      <div>
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Hourly Sales</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Revenue distribution across operating hours — identify peak times.</p>
      </div>
    </div>
    <div class="relative h-[300px]">
      <canvas id="hourlyChart"></canvas>
    </div>
  </div>

  {{-- Dept + Category --}}
  <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="gfs-card p-5">
      <div class="mb-5">
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Sales by Department</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Revenue grouped by department.</p>
      </div>
      <div class="relative h-[280px]">
        <canvas id="deptChart"></canvas>
      </div>
    </div>

    <div class="gfs-card p-5">
      <div class="mb-5">
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Sales by Category</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Top categories by revenue.</p>
      </div>
      <div class="relative h-[280px]">
        <canvas id="catChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Payment + Outlet --}}
  <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="gfs-card p-5">
      <div class="mb-5">
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Payment Breakdown</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Cash, QRIS, EDC and others.</p>
      </div>
      <div class="relative h-[280px]">
        <canvas id="paymentChart"></canvas>
      </div>
    </div>

    <div class="gfs-card p-5">
      <div class="mb-5">
        <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Outlet Comparison</h2>
        <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Hall, Balkon, VIP and other areas.</p>
      </div>
      <div class="relative h-[280px]">
        <canvas id="outletChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Top Products --}}
  <div class="gfs-card p-5">
    <div class="mb-5">
      <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Top Selling Products</h2>
      <p class="mt-0.5 text-xs" style="color:var(--text-muted)">Ranked by quantity sold within selected date range.</p>
    </div>
    <div class="relative h-[400px]">
      <canvas id="topChart"></canvas>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Farmstead palette — green primary + earthy secondaries
  const P = {
    green:  '#22c55e',
    teal:   '#14b8a6',
    blue:   '#3b82f6',
    amber:  '#f59e0b',
    red:    '#ef4444',
    purple: '#a855f7',
    slate:  '#64748b',
    lime:   '#84cc16',
    orange: '#f97316',
    pink:   '#ec4899',
  };
  const palette = Object.values(P);

  Chart.defaults.font.family = "'Sora', ui-sans-serif, system-ui, sans-serif";
  Chart.defaults.font.size   = 11;
  Chart.defaults.color       = '#6b6b6b';

  const grid = { color: 'rgba(0,0,0,0.05)', lineWidth: 1 };
  const money = v => 'Rp ' + Number(v || 0).toLocaleString('id-ID');
  const moneyTip = ctx => money(ctx.raw);

  const hourlyData  = @json($hourlySales);
  const deptData    = @json($byDept);
  const catData     = @json($byCategory);
  const topData     = @json($topProducts);
  const paymentData = @json($paymentRows);
  const outletData  = @json($outletRows);

  // Hourly — area line
  new Chart(document.getElementById('hourlyChart'), {
    type: 'line',
    data: {
      labels: hourlyData.map(x => x.label),
      datasets: [{
        label: 'Sales',
        data: hourlyData.map(x => x.total),
        tension: 0.4,
        fill: true,
        borderColor: P.green,
        backgroundColor: 'rgba(34,197,94,0.08)',
        pointBackgroundColor: P.green,
        pointRadius: 3,
        pointHoverRadius: 5,
        borderWidth: 2,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: moneyTip } }
      },
      scales: {
        x: { grid },
        y: { grid, ticks: { callback: money } }
      }
    }
  });

  // Dept — vertical bar
  new Chart(document.getElementById('deptChart'), {
    type: 'bar',
    data: {
      labels: deptData.map(x => x.name),
      datasets: [{
        label: 'Sales',
        data: deptData.map(x => x.total),
        backgroundColor: palette.map(c => c + 'cc'),
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: moneyTip } }
      },
      scales: {
        x: { grid: { display: false } },
        y: { grid, ticks: { callback: money } }
      }
    }
  });

  // Category — doughnut
  new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
      labels: catData.map(x => x.name),
      datasets: [{
        data: catData.map(x => x.total),
        backgroundColor: palette,
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '62%',
      plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, padding: 16 } },
        tooltip: { callbacks: { label: ctx => ctx.label + ': ' + money(ctx.raw) } }
      }
    }
  });

  // Payment — doughnut
  new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
      labels: paymentData.map(x => x.bucket),
      datasets: [{
        data: paymentData.map(x => x.total),
        backgroundColor: [P.green, P.blue, P.amber, P.purple, P.red],
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '62%',
      plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, padding: 16 } },
        tooltip: { callbacks: { label: ctx => ctx.label + ': ' + money(ctx.raw) } }
      }
    }
  });

  // Outlet — vertical bar
  new Chart(document.getElementById('outletChart'), {
    type: 'bar',
    data: {
      labels: outletData.map(x => x.outlet),
      datasets: [{
        label: 'Sales',
        data: outletData.map(x => x.total),
        backgroundColor: [P.green, P.teal, P.blue, P.amber, P.purple].map(c => c + 'cc'),
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: moneyTip } }
      },
      scales: {
        x: { grid: { display: false } },
        y: { grid, ticks: { callback: money } }
      }
    }
  });

  // Top Products — horizontal bar
  new Chart(document.getElementById('topChart'), {
    type: 'bar',
    data: {
      labels: topData.map(x => x.name),
      datasets: [{
        label: 'Qty Sold',
        data: topData.map(x => x.qty),
        backgroundColor: P.green + 'cc',
        borderRadius: 4,
        borderSkipped: false,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid },
        y: { grid: { display: false } }
      }
    }
  });
</script>
@endsection
