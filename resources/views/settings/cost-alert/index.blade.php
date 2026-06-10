@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6 px-4 py-6">

  {{-- Page title --}}
  <div>
    <h1 class="text-xl font-bold" style="color:var(--text-primary)">Cost Alert Settings</h1>
    <p class="mt-1 text-sm" style="color:var(--text-secondary)">Configure who receives the daily cost alert email and the thresholds that trigger it.</p>
  </div>

  {{-- ── Settings Form ─────────────────────────────────────────────── --}}
  <div class="gfs-card p-6">
    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Alert Configuration</h2>

    <form action="{{ route('settings.costAlert.update') }}" method="POST" class="space-y-4">
      @csrf
      @method('PUT')

      {{-- Recipient email(s) --}}
      <div>
        <label class="mb-1 block text-xs font-semibold" style="color:var(--text-secondary)">Recipient Email(s)</label>
        <input type="text" name="cost_alert_email" value="{{ old('cost_alert_email', $email) }}"
               placeholder="e.g. manager@example.com, owner@example.com"
               class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition"
               style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)"
               onfocus="this.style.borderColor='#22c55e'" onblur="this.style.borderColor='var(--card-border)'">
        <p class="mt-1 text-xs" style="color:var(--text-secondary)">Comma-separated for multiple recipients.</p>
        @error('cost_alert_email')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {{-- Cost threshold --}}
        <div>
          <label class="mb-1 block text-xs font-semibold" style="color:var(--text-secondary)">Daily Cost Threshold (%)</label>
          <input type="number" name="cost_threshold" value="{{ old('cost_threshold', $costThreshold) }}"
                 step="0.1" min="0" max="1000" required
                 class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition"
                 style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)"
                 onfocus="this.style.borderColor='#22c55e'" onblur="this.style.borderColor='var(--card-border)'">
          <p class="mt-1 text-xs" style="color:var(--text-secondary)">Alert when total cost / revenue exceeds this %.</p>
          @error('cost_threshold')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Item cost threshold --}}
        <div>
          <label class="mb-1 block text-xs font-semibold" style="color:var(--text-secondary)">Item Cost Threshold (%)</label>
          <input type="number" name="item_cost_threshold" value="{{ old('item_cost_threshold', $itemCostThreshold) }}"
                 step="0.1" min="0" max="1000" required
                 class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition"
                 style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)"
                 onfocus="this.style.borderColor='#22c55e'" onblur="this.style.borderColor='var(--card-border)'">
          <p class="mt-1 text-xs" style="color:var(--text-secondary)">Flag individual items whose cost % exceeds this.</p>
          @error('item_cost_threshold')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-3 pt-2">
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 active:scale-95"
                style="background:linear-gradient(135deg,#22c55e,#16a34a); box-shadow:0 4px 16px rgba(34,197,94,0.25)">
          Save Settings
        </button>
      </div>
    </form>

    {{-- Send test email --}}
    <div class="mt-6 border-t pt-4" style="border-color:var(--card-border)">
      <h3 class="mb-1 text-sm font-semibold" style="color:var(--text-primary)">Send Test Email</h3>
      <p class="mb-3 text-xs" style="color:var(--text-secondary)">Send a sample cost alert email to the recipient(s) configured above to verify mail delivery is working.</p>
      <form action="{{ route('settings.costAlert.test') }}" method="POST">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition hover:opacity-80"
                style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)">
          Send Test Email
        </button>
      </form>
    </div>
  </div>

  {{-- ── Manual Check ──────────────────────────────────────────────── --}}
  <div class="gfs-card p-6">
    <h2 class="mb-1 text-sm font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Run Check Manually</h2>
    <p class="mb-3 text-xs" style="color:var(--text-secondary)">If the scheduled check didn't run (e.g. scheduler was down), pick a date and run the cost check now. This forces the check even if an alert was already sent for that date.</p>

    <form action="{{ route('settings.costAlert.run') }}" method="POST" class="flex flex-wrap items-end gap-3">
      @csrf
      <div>
        <label class="mb-1 block text-xs font-semibold" style="color:var(--text-secondary)">Date</label>
        <input type="date" name="check_date" value="{{ old('check_date', now()->toDateString()) }}" required
               max="{{ now()->toDateString() }}"
               class="rounded-xl px-3 py-2.5 text-sm outline-none transition"
               style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)"
               onfocus="this.style.borderColor='#22c55e'" onblur="this.style.borderColor='var(--card-border)'">
        @error('check_date')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>
      <button type="submit"
              class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition hover:opacity-80"
              style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)">
        Run Check Now
      </button>
    </form>

    @if(session('checkOutput'))
      <pre class="mt-4 overflow-x-auto rounded-xl p-4 text-xs leading-relaxed" style="background:var(--content-bg); border:1px solid var(--card-border); color:var(--text-primary)">{{ session('checkOutput') }}</pre>
    @endif
  </div>

  {{-- ── Schedule Info ─────────────────────────────────────────────── --}}
  <div class="gfs-card p-6">
    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Alert Schedule</h2>
    <div class="rounded-xl p-4 text-sm" style="background:var(--content-bg); border:1px solid var(--card-border); color:var(--text-primary)">
      <p>The cost check runs automatically <strong>every 30 minutes between 18:00 and 23:59</strong>, after the End-of-Day (EOD) is closed.</p>
      <p class="mt-2 text-xs" style="color:var(--text-secondary)">
        Defined in <code class="rounded px-1" style="background:var(--card-bg)">routes/console.php</code> via
        <code class="rounded px-1" style="background:var(--card-bg)">cost:check-daily</code>, executed by the
        <code class="rounded px-1" style="background:var(--card-bg)">gfs_scheduler</code> container.
      </p>
      <p class="mt-2 text-xs" style="color:var(--text-secondary)">
        Only one alert is sent per day — if the cost is over threshold and an alert was already sent for today, it won't be sent again unless run manually with <code class="rounded px-1" style="background:var(--card-bg)">--force</code>.
      </p>
    </div>
  </div>

</div>
@endsection
