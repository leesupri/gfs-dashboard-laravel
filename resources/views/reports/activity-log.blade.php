@php
function activityBadge($text) {
    $t = strtoupper($text ?? '');

    if (str_contains($t, 'LOGIN')) return 'bg-green-100 text-green-700';
    if (str_contains($t, 'LOGOUT')) return 'bg-gray-100 text-gray-700';
    if (str_contains($t, 'VOID')) return 'bg-red-100 text-red-700';
    if (str_contains($t, 'PAYMENT')) return 'bg-blue-100 text-blue-700';
    if (str_contains($t, 'CLOSE')) return 'bg-purple-100 text-purple-700';

    return 'bg-yellow-100 text-yellow-700';
}
@endphp
@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">
                Activity log by date, description, sales id, invoice id, and employee.
            </p>
        </div>

        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs text-gray-500">Start</label>
                <input type="date" name="start" value="{{ $start }}"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">End</label>
                <input type="date" name="end" value="{{ $end }}"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Employee</label>
                <input type="text" name="employee" value="{{ $employee }}" placeholder="Employee name"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Invoice</label>
                <input type="text" name="invoice" value="{{ $invoice }}" placeholder="Invoice ID"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs text-gray-500">Search</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Description / Sales ID"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Apply
            </button>

            <a href="{{ route('reports.activityLog') }}"
               class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Clear
            </a>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">From</div>
            <div class="font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">To</div>
            <div class="font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="text-xs text-gray-500">Total Logs</div>
            <div class="font-semibold text-gray-900">
                {{ number_format($summary->total_logs ?? 0) }}
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        @if($rows->isEmpty())
            <div class="p-6 text-sm text-gray-500">
                No activity log data found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Sales ID</th>
                            <th class="px-4 py-3 text-left">Invoice ID</th>
                            <th class="px-4 py-3 text-left">Employee</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-2 text-gray-900">
    <div>{{ $row->description ?: '-' }}</div>
    <span class="inline-flex mt-1 rounded-full px-2 py-0.5 text-xs font-medium {{ activityBadge($row->description) }}">
        Activity
    </span>
</td>
                                <td class="px-4 py-2">
                                    {{ $row->salesId ?: '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $row->invoice_id ?: '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $row->employee_name ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</div>
@endsection