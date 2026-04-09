@extends('layouts.app')

@section('content')
@php
    $staffUser = $currentStaffUser ?? null;

    $shortcuts = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'desc' => 'Overview of the system'],
        ['label' => 'Sales', 'route' => 'sales.index', 'desc' => 'Sales report and details'],
        ['label' => 'Item Sales', 'route' => 'itemSales.index', 'desc' => 'Item sales summary'],
        ['label' => 'Order Board', 'route' => 'reports.orderBoard', 'desc' => 'Live order board view'],
        ['label' => 'Activity Logs', 'route' => 'reports.activityLog', 'desc' => 'Track staff and system activity'],
        ['label' => 'Market List', 'route' => 'reports.marketList', 'desc' => 'Inventory and market list view'],
        ['label' => 'Production Summary', 'route' => 'reports.productionSummary', 'desc' => 'Production summary report'],
        ['label' => 'Production Card', 'route' => 'reports.productionCard.index', 'desc' => 'Browse production cards'],
        ['label' => 'Purchase Summary', 'route' => 'reports.purchaseSummary', 'desc' => 'Purchase summary by item'],
        ['label' => 'Purchase Detail', 'route' => 'reports.purchaseDetail', 'desc' => 'Detailed purchase report'],
        ['label' => 'Purchase By Partner', 'route' => 'reports.purchaseDetailPartner', 'desc' => 'Purchase detail grouped by partner'],
        ['label' => 'Physical Stock Count', 'route' => 'reports.physicalStockCountSummary', 'desc' => 'Physical Stock Count'],
        ['label' => 'Recipes Report', 'route' => 'reports.recipe', 'desc' => 'Recipes Report'],
        ['label' => 'Transfer Detail', 'route' => 'reports.transferDetail', 'desc' => 'Transfer Detail'],
        ['label' => 'Waste Summary', 'route' => 'reports.wasteSummary', 'desc' => 'Waste Summary'],
        
        ['label' => 'Staff Settings', 'route' => 'settings.staff', 'desc' => 'Manage staff accounts'],
        ['label' => 'Security Settings', 'route' => 'settings.security', 'desc' => 'Manage route access'],
    ];

    $shortcuts = collect($shortcuts)->filter(function ($item) use ($staffUser) {
        return $staffUser && $staffUser->hasAccess($item['route']);
    })->values();
@endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold text-gray-900">
            Welcome, {{ $currentStaffUser->name ?? 'Staff' }}
        </h1>
        <p class="mt-2 text-sm text-gray-500">
            Use the shortcuts below to quickly access the pages available for your account.
        </p>
    </div>

    @if($shortcuts->isEmpty())
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-6 text-sm text-yellow-800 shadow-sm">
            No shortcuts are available for your account yet. Please contact your administrator.
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($shortcuts as $item)
                <a href="{{ route($item['route']) }}"
                    @click="loading = true"
                   class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md">
                    <div class="text-base font-semibold text-gray-900">
                        {{ $item['label'] }}
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        {{ $item['desc'] }}
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection

