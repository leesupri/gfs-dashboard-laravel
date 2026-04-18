@extends('layouts.app')

@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
    <div>
      <h1 class="text-xl font-semibold text-gray-900">{{ $title ?? 'Dashboard' }}</h1>
      <p class="mt-1 text-sm text-gray-500">Overview of sales performance and trends.</p>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-2">
      <div>
        <label class="mb-1 block text-xs font-medium text-gray-500">Start</label>
        <input
          type="date"
          name="start"
          value="{{ $start }}"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 outline-none focus:border-gray-400"
        >
      </div>

      <div>
        <label class="mb-1 block text-xs font-medium text-gray-500">End</label>
        <input
          type="date"
          name="end"
          value="{{ $end }}"
          class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 outline-none focus:border-gray-400"
        >
      </div>

      <button
        type="submit"
        @click="loading = true"
        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
      >
        Apply
      </button>

      <a
        href="{{ route('dashboard') }}"
        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Reset
      </a>
    </form>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Total Sales</p>
      <p class="mt-2 text-2xl font-semibold text-gray-900">
        Rp {{ number_format((float)($kpi->sales ?? 0), 0, ',', '.') }}
      </p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Transactions</p>
      <p class="mt-2 text-2xl font-semibold text-gray-900">
        {{ number_format((int)($kpi->trx ?? 0), 0, ',', '.') }}
      </p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Avg Ticket</p>
      <p class="mt-2 text-2xl font-semibold text-gray-900">
        Rp {{ number_format((float)($kpi->avgTicket ?? 0), 0, ',', '.') }}
      </p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-medium text-gray-500">Date Range</p>
      <p class="mt-2 text-sm font-semibold text-gray-900">
        {{ \Carbon\Carbon::parse($start)->format('d M Y') }}
        —
        {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
      </p>
    </div>
  </div>

  {{-- Hourly Sales --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="mb-4">
      <h2 class="text-sm font-semibold text-gray-900">Hourly Sales</h2>
      <p class="text-xs text-gray-500">Useful to see restaurant peak hours.</p>
    </div>
    <div class="relative h-[320px]">
      <canvas id="hourlyChart"></canvas>
    </div>
  </div>

  {{-- 2-column charts --}}
  <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="mb-4">
        <h2 class="text-sm font-semibold text-gray-900">Sales by Department</h2>
        <p class="text-xs text-gray-500">Revenue grouped by department.</p>
      </div>
      <div class="relative h-[320px]">
        <canvas id="deptChart"></canvas>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="mb-4">
        <h2 class="text-sm font-semibold text-gray-900">Sales by Category</h2>
        <p class="text-xs text-gray-500">Top categories by revenue.</p>
      </div>
      <div class="relative h-[320px]">
        <canvas id="catChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Payment + Outlet --}}
  <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="mb-4">
        <h2 class="text-sm font-semibold text-gray-900">Payment Breakdown</h2>
        <p class="text-xs text-gray-500">Cash, QRIS, EDC and others.</p>
      </div>
      <div class="relative h-[320px]">
        <canvas id="paymentChart"></canvas>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="mb-4">
        <h2 class="text-sm font-semibold text-gray-900">Outlet Comparison</h2>
        <p class="text-xs text-gray-500">Hall, Balkon, VIP and other areas.</p>
      </div>
      <div class="relative h-[320px]">
        <canvas id="outletChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Top Products --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="mb-4">
      <h2 class="text-sm font-semibold text-gray-900">Top Selling Products</h2>
      <p class="text-xs text-gray-500">Based on quantity sold.</p>
    </div>
    <div class="relative h-[380px]">
      <canvas id="topChart"></canvas>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const moneyFormatter = (value) => {
    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
  };

  const hourlyData = @json($hourlySales);
  const deptData = @json($byDept);
  const catData = @json($byCategory);
  const topData = @json($topProducts);
  const paymentData = @json($paymentRows);
  const outletData = @json($outletRows);

  new Chart(document.getElementById('hourlyChart'), {
    type: 'line',
    data: {
      labels: hourlyData.map(x => x.label),
      datasets: [{
        label: 'Sales',
        data: hourlyData.map(x => x.total),
        tension: 0.35,
        fill: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true }
      },
      scales: {
        y: {
          ticks: {
            callback: function(value) {
              return moneyFormatter(value);
            }
          }
        }
      }
    }
  });

  new Chart(document.getElementById('deptChart'), {
    type: 'bar',
    data: {
      labels: deptData.map(x => x.name),
      datasets: [{
        label: 'Sales',
        data: deptData.map(x => x.total)
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true }
      },
      scales: {
        y: {
          ticks: {
            callback: function(value) {
              return moneyFormatter(value);
            }
          }
        }
      }
    }
  });

  new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
      labels: catData.map(x => x.name),
      datasets: [{
        data: catData.map(x => x.total)
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });

  new Chart(document.getElementById('paymentChart'), {
    type: 'pie',
    data: {
      labels: paymentData.map(x => x.bucket),
      datasets: [{
        data: paymentData.map(x => x.total)
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });

  new Chart(document.getElementById('outletChart'), {
    type: 'bar',
    data: {
      labels: outletData.map(x => x.outlet),
      datasets: [{
        label: 'Sales',
        data: outletData.map(x => x.total)
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true }
      },
      scales: {
        y: {
          ticks: {
            callback: function(value) {
              return moneyFormatter(value);
            }
          }
        }
      }
    }
  });

  new Chart(document.getElementById('topChart'), {
    type: 'bar',
    data: {
      labels: topData.map(x => x.name),
      datasets: [{
        label: 'Qty Sold',
        data: topData.map(x => x.qty)
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true }
      }
    }
  });
</script>
@endsection