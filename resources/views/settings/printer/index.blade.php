@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6 px-4 py-6"
     x-data="{
       showModal: false,
       editingId: null,
       form: { name: '', ip_address: '', location: '', is_active: true },
       storeUrl: '{{ route('settings.printer.store') }}',
       updateUrlTemplate: '{{ route('settings.printer.update', '__ID__') }}',
       logoPreview: null,
       get formAction() {
         return this.editingId
           ? this.updateUrlTemplate.replace('__ID__', this.editingId)
           : this.storeUrl;
       },
       get isEditing() { return this.editingId !== null; },
       openAdd() {
         this.editingId = null;
         this.form = { name: '', ip_address: '', location: '', is_active: true };
         this.showModal = true;
       },
       openEdit(station) {
         this.editingId       = station.id;
         this.form.name       = station.name       ?? '';
         this.form.ip_address = station.ip_address ?? '';
         this.form.location   = station.location   ?? '';
         this.form.is_active  = Boolean(station.is_active);
         this.showModal = true;
       },
       closeModal() { this.showModal = false; },
       previewLogo(event) {
         const file = event.target.files?.[0];
         if (!file) return;
         const reader = new FileReader();
         reader.onload = (e) => { this.logoPreview = e.target.result; };
         reader.readAsDataURL(file);
       }
     }">

  {{-- Page title --}}
  <div>
    <h1 class="text-xl font-bold" style="color:var(--text-primary)">Printer Settings</h1>
    <p class="mt-1 text-sm" style="color:var(--text-secondary)">Manage the receipt logo and thermal printer stations.</p>
  </div>

  {{-- ── Logo Card ─────────────────────────────────────────────────── --}}
  <div class="gfs-card p-6">
    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Receipt Logo</h2>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">

      {{-- Preview --}}
      <div class="flex h-28 w-48 shrink-0 items-center justify-center rounded-xl border"
           style="border-color:var(--card-border); background:var(--content-bg)">
        <template x-if="logoPreview">
          <img :src="logoPreview" alt="Logo preview" class="max-h-24 max-w-44 object-contain">
        </template>
        <template x-if="!logoPreview">
          @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Current logo" class="max-h-24 max-w-44 object-contain">
          @else
            <span class="text-xs" style="color:var(--text-secondary)">No logo</span>
          @endif
        </template>
      </div>

      {{-- Upload form --}}
      <form action="{{ route('settings.printer.logo') }}" method="POST" enctype="multipart/form-data" class="flex-1">
        @csrf
        <label class="block text-sm font-medium mb-1" style="color:var(--text-primary)">Upload new logo</label>
        <p class="mb-2 text-xs" style="color:var(--text-secondary)">PNG, JPG, or SVG — max 2 MB — recommended width 400px</p>

        <div class="flex flex-wrap items-center gap-3">
          <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                 class="block text-sm file:mr-3 file:rounded-lg file:border-0 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white cursor-pointer"
                 style="file:background:#22c55e; color:var(--text-primary)"
                 @change="previewLogo($event)">
          <button type="submit"
                  class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 active:scale-95"
                  style="background:linear-gradient(135deg,#22c55e,#16a34a); box-shadow:0 4px 16px rgba(34,197,94,0.25)">
            Upload
          </button>
        </div>

        @error('logo')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </form>
    </div>
  </div>

  {{-- ── Printer Stations Card ─────────────────────────────────────── --}}
  <div class="gfs-card p-6">

    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-sm font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Printer Stations</h2>
      <button @click="openAdd()"
              class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 active:scale-95"
              style="background:linear-gradient(135deg,#22c55e,#16a34a); box-shadow:0 4px 16px rgba(34,197,94,0.25)">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Station
      </button>
    </div>

    @if($stations->isEmpty())
      <div class="rounded-xl py-10 text-center text-sm" style="background:var(--content-bg); color:var(--text-secondary)">
        No printer stations configured yet.
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr style="border-bottom:1px solid var(--card-border)">
              <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Name</th>
              <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">IP Address</th>
              <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Location</th>
              <th class="pb-2 text-center text-xs font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Status</th>
              <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($stations as $station)
            <tr style="border-bottom:1px solid var(--card-border)">
              <td class="py-3 font-medium" style="color:var(--text-primary)">{{ $station->name }}</td>
              <td class="py-3 font-mono text-xs" style="color:var(--text-secondary)">{{ $station->ip_address ?: '—' }}</td>
              <td class="py-3 text-xs" style="color:var(--text-secondary)">{{ $station->location ?: '—' }}</td>
              <td class="py-3 text-center">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                      style="{{ $station->is_active
                          ? 'background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.25)'
                          : 'background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2)' }}">
                  {{ $station->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <a href="{{ route('print.test', $station) }}?autoprint=1" target="_blank"
                     class="rounded-lg px-3 py-1.5 text-xs font-medium transition hover:opacity-80"
                     style="background:rgba(234,179,8,0.12);color:#ca8a04">
                    Test
                  </a>
                  <button @click="openEdit({{ json_encode(['id'=>$station->id,'name'=>$station->name,'ip_address'=>$station->ip_address,'location'=>$station->location,'is_active'=>$station->is_active]) }})"
                          class="rounded-lg px-3 py-1.5 text-xs font-medium transition hover:opacity-80"
                          style="background:rgba(99,102,241,0.1);color:#818cf8">
                    Edit
                  </button>
                  <form action="{{ route('settings.printer.destroy', $station) }}" method="POST"
                        onsubmit="return confirm('Remove this printer station?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition hover:opacity-80"
                            style="background:rgba(239,68,68,0.1);color:#ef4444">
                      Remove
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  {{-- ── Quick Print Links ─────────────────────────────────────────── --}}
  <div class="gfs-card p-6">
    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider" style="color:var(--text-secondary)">Print Preview</h2>
    <p class="mb-4 text-xs" style="color:var(--text-secondary)">Open a print-ready page for these reports using today's data.</p>
    <div class="flex flex-wrap gap-3">
      <a href="{{ route('print.itemSales', ['start' => now()->toDateString(), 'end' => now()->toDateString()]) }}"
         target="_blank"
         class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition hover:opacity-80"
         style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)">
        Print Item Sales
      </a>
      <a href="{{ route('print.summarySales', ['start' => now()->toDateString(), 'end' => now()->toDateString()]) }}"
         target="_blank"
         class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition hover:opacity-80"
         style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)">
        Print Summary Sales
      </a>
    </div>
  </div>

  {{-- ── Station Modal ─────────────────────────────────────────────── --}}
  <template x-teleport="body">
  <div x-show="showModal" x-cloak>
    {{-- Backdrop --}}
    <div class="fixed inset-0 z-40" style="background:rgba(0,0,0,0.5)" @click="closeModal()"></div>

    {{-- Panel --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeModal()">
      <div class="w-full max-w-md rounded-2xl p-6"
           style="background:var(--card-bg); box-shadow:0 25px 60px rgba(0,0,0,0.3)"
           @click.stop
           x-transition:enter="transition duration-200"
           x-transition:enter-start="opacity-0 scale-95"
           x-transition:enter-end="opacity-100 scale-100">

        <h3 class="mb-5 text-base font-bold" style="color:var(--text-primary)"
            x-text="isEditing ? 'Edit Printer Station' : 'Add Printer Station'"></h3>

        <form :action="formAction" method="POST" class="space-y-4">
          @csrf
          <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">

          {{-- Name --}}
          <div>
            <label class="mb-1 block text-xs font-semibold" style="color:var(--text-secondary)">Station Name *</label>
            <input type="text" name="name" x-model="form.name" required maxlength="100"
                   placeholder="e.g. Kitchen Printer"
                   class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition"
                   style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)"
                   onfocus="this.style.borderColor='#22c55e'" onblur="this.style.borderColor='var(--card-border)'">
          </div>

          {{-- IP Address --}}
          <div>
            <label class="mb-1 block text-xs font-semibold" style="color:var(--text-secondary)">IP Address</label>
            <input type="text" name="ip_address" x-model="form.ip_address" maxlength="45"
                   placeholder="e.g. 192.168.1.100"
                   class="w-full rounded-xl px-3 py-2.5 text-sm font-mono outline-none transition"
                   style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)"
                   onfocus="this.style.borderColor='#22c55e'" onblur="this.style.borderColor='var(--card-border)'">
          </div>

          {{-- Location --}}
          <div>
            <label class="mb-1 block text-xs font-semibold" style="color:var(--text-secondary)">Location</label>
            <input type="text" name="location" x-model="form.location" maxlength="100"
                   placeholder="e.g. Main Kitchen"
                   class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition"
                   style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)"
                   onfocus="this.style.borderColor='#22c55e'" onblur="this.style.borderColor='var(--card-border)'">
          </div>

          {{-- Active toggle --}}
          <div class="flex items-center gap-3">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="is_active_modal" x-model="form.is_active"
                   class="h-4 w-4 rounded accent-green-500">
            <label for="is_active_modal" class="text-sm" style="color:var(--text-primary)">Active</label>
          </div>

          {{-- Buttons --}}
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="closeModal()"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition hover:opacity-80"
                    style="background:var(--content-bg);color:var(--text-primary);border:1px solid var(--card-border)">
              Cancel
            </button>
            <button type="submit"
                    class="rounded-xl px-5 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5 active:scale-95"
                    style="background:linear-gradient(135deg,#22c55e,#16a34a); box-shadow:0 4px 16px rgba(34,197,94,0.25)"
                    x-text="isEditing ? 'Save Changes' : 'Add Station'">
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
  </template>

</div>
@endsection
