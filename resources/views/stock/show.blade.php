@extends('layouts.app')

@section('content')
<div class="space-y-5" x-data="stockCountActions">

  <div>
    <a href="{{ route('stock.counts.index') }}"
      class="inline-flex items-center gap-1.5 text-sm transition hover:-translate-x-0.5"
      style="color:var(--text-muted);">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Back to Stock Counts
    </a>
  </div>

  {{-- Header card --}}
  <div class="gfs-card p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h1 class="text-lg font-bold" style="color:var(--text-primary)">Stock Count #{{ $count->id }}</h1>
        <p class="mt-0.5 text-sm" style="color:var(--text-muted)">{{ $count->warehouse_name }} &middot; {{ $count->count_date->format('d M Y') }}</p>
      </div>
      <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $count->statusBadge() }}">
        {{ ucfirst($count->status) }}
      </span>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
      <div class="space-y-3">
        <div>
          <span class="block text-[10px] font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Type</span>
          <span class="text-sm capitalize" style="color:var(--text-primary);">{{ $count->count_type }}</span>
        </div>
        <div>
          <span class="block text-[10px] font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Created by</span>
          <span class="text-sm" style="color:var(--text-primary);">{{ $count->creator?->name ?? '—' }}</span>
        </div>
        @if($count->notes)
          <div>
            <span class="block text-[10px] font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Notes</span>
            <span class="text-sm whitespace-pre-wrap" style="color:var(--text-primary);">{{ $count->notes }}</span>
          </div>
        @endif
      </div>
      <div class="space-y-3">
        @if($count->submitted_at)
          <div>
            <span class="block text-[10px] font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Submitted at</span>
            <span class="text-sm" style="color:var(--text-primary);">{{ $count->submitted_at->format('d M Y, H:i') }}</span>
          </div>
        @endif
        @if($count->approved_at)
          <div>
            <span class="block text-[10px] font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Approved by</span>
            <span class="text-sm" style="color:var(--text-primary);">{{ $count->approver?->name ?? '—' }} &middot; {{ $count->approved_at->format('d M Y, H:i') }}</span>
          </div>
        @endif
        @if($count->status === 'rejected' && $count->rejected_reason)
          <div>
            <span class="block text-[10px] font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Rejection reason</span>
            <span class="text-sm whitespace-pre-wrap" style="color:#b91c1c;">{{ $count->rejected_reason }}</span>
          </div>
        @endif
      </div>
    </div>

    @php $staffUser = app('currentStaffUser'); @endphp
    @if($count->status === 'submitted' && $staffUser && $staffUser->hasAccess('stock.approve'))
      <div class="mt-5 flex flex-wrap gap-2 border-t pt-4" style="border-color:var(--card-border);">
        <button type="button" @click="openApprove()"
          class="rounded-xl px-4 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5 active:scale-95"
          style="background:linear-gradient(135deg,#22c55e,#16a34a); box-shadow:0 4px 16px rgba(34,197,94,0.25)">
          Approve
        </button>
        <button type="button" @click="openReject()"
          class="rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-red-50"
          style="border-color:#fecaca;color:#b91c1c;">
          Reject
        </button>
      </div>
    @endif
  </div>

  {{-- Lines table --}}
  <div class="gfs-card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr style="border-bottom:1px solid var(--card-border);background:var(--content-bg);">
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">#</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Code</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Item Name</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Base UOM</th>
            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Entered Qty</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Entered UOM</th>
            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Qty in Base UOM</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Notes</th>
          </tr>
        </thead>
        <tbody>
          @forelse($count->lines as $i => $line)
            <tr class="transition-colors hover:bg-gray-50" style="border-bottom:1px solid var(--card-border);">
              <td class="px-4 py-3 text-xs" style="color:var(--text-muted);">{{ $i + 1 }}</td>
              <td class="px-4 py-3 font-mono text-xs" style="color:var(--text-primary);">{{ $line->item_code ?? '—' }}</td>
              <td class="px-4 py-3 text-sm" style="color:var(--text-primary);">{{ $line->item_name ?? '—' }}</td>
              <td class="px-4 py-3 text-xs" style="color:var(--text-secondary);">{{ $line->item_uom ?? '—' }}</td>
              <td class="px-4 py-3 text-right text-sm" style="color:var(--text-primary);">{{ rtrim(rtrim(number_format($line->qty_entered, 4, '.', ''), '0'), '.') }}</td>
              <td class="px-4 py-3 text-xs" style="color:var(--text-secondary);">{{ $line->uom_entered }}</td>
              <td class="px-4 py-3 text-right text-sm font-semibold" style="color:var(--text-primary);">{{ rtrim(rtrim(number_format($line->qty_in_base_uom, 4, '.', ''), '0'), '.') }}</td>
              <td class="px-4 py-3 text-xs" style="color:var(--text-muted);">{{ $line->notes ?? '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-4 py-10 text-center text-sm" style="color:var(--text-muted);">No items recorded for this count.</td>
            </tr>
          @endforelse
        </tbody>
        @if($count->lines->isNotEmpty())
          <tfoot>
            <tr style="border-top:1px solid var(--card-border);background:var(--content-bg);">
              <td colspan="6" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Total (base UOM)</td>
              <td class="px-4 py-3 text-right text-sm font-bold" style="color:var(--text-primary);">{{ rtrim(rtrim(number_format($count->lines->sum('qty_in_base_uom'), 4, '.', ''), '0'), '.') }}</td>
              <td></td>
            </tr>
          </tfoot>
        @endif
      </table>
    </div>
  </div>

  {{-- Approve modal --}}
  <template x-teleport="body">
    <div x-show="showApproveModal" x-cloak>
      <div class="fixed inset-0 z-40" style="background:rgba(0,0,0,0.5)" @click="closeApprove()"></div>
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeApprove()">
        <div class="w-full max-w-md rounded-2xl p-6" style="background:var(--card-bg); box-shadow:0 25px 60px rgba(0,0,0,0.3)"
             @click.stop x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
          <h3 class="mb-3 text-base font-bold" style="color:var(--text-primary)">Approve Stock Count #{{ $count->id }}?</h3>
          <p class="mb-5 text-sm" style="color:var(--text-secondary)">This action cannot be undone.</p>
          <form action="{{ route('stock.counts.approve', $count->id) }}" method="POST" class="flex justify-end gap-3">
            @csrf
            <button type="button" @click="closeApprove()"
              class="rounded-xl px-4 py-2 text-sm font-medium"
              style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)">Cancel</button>
            <button type="submit"
              class="rounded-xl px-5 py-2 text-sm font-semibold text-white"
              style="background:linear-gradient(135deg,#22c55e,#16a34a)">Approve</button>
          </form>
        </div>
      </div>
    </div>
  </template>

  {{-- Reject modal --}}
  <template x-teleport="body">
    <div x-show="showRejectModal" x-cloak>
      <div class="fixed inset-0 z-40" style="background:rgba(0,0,0,0.5)" @click="closeReject()"></div>
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeReject()">
        <div class="w-full max-w-md rounded-2xl p-6" style="background:var(--card-bg); box-shadow:0 25px 60px rgba(0,0,0,0.3)"
             @click.stop x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
          <h3 class="mb-3 text-base font-bold" style="color:var(--text-primary)">Reject Stock Count #{{ $count->id }}</h3>
          <form action="{{ route('stock.counts.reject', $count->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
              <label class="mb-1 block text-xs font-semibold" style="color:var(--text-secondary)">Reason *</label>
              <textarea name="rejected_reason" x-model="rejectReason" required maxlength="500" rows="4"
                placeholder="Explain why this count is being rejected..."
                class="w-full rounded-xl px-3 py-2.5 text-sm outline-none"
                style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)"></textarea>
            </div>
            <div class="flex justify-end gap-3">
              <button type="button" @click="closeReject()"
                class="rounded-xl px-4 py-2 text-sm font-medium"
                style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)">Cancel</button>
              <button type="submit" class="rounded-xl px-5 py-2 text-sm font-semibold text-white" style="background:#dc2626">Reject</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>

</div>
@endsection

@push('scripts')
@vite('resources/js/pages/stock-counts.js')
@endpush
