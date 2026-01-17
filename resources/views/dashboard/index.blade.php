@extends('layouts.dashboard')
{{-- resources/view/dashboard/index.blade.php --}}
@section('content')

<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

<div class="page-heading">
    <h3>Finance Dashboard</h3>
    <p class="text-muted">
        System overview & financial summary
    </p>
</div>

<div class="page-content">

    {{-- === KPI CARDS === --}}
    <div class="row">

        @php
            $cards = [
                [
                    'label' => 'Transactions',
                    'value' => $stats['transactions'] ?? 0,
                    'icon'  => 'dripicons-wallet',
                    'color' => 'purple',
                ],
                [
                    'label' => 'Accounts',
                    'value' => $stats['accounts'] ?? 0,
                    'icon'  => 'bi-bank',
                    'color' => 'blue',
                ],
                [
                    'label' => 'Invoices',
                    'value' => $stats['invoices'] ?? 0,
                    'icon'  => 'dripicons-document',
                    'color' => 'green',
                ],
                [
                    'label' => 'Budget Categories',
                    'value' => $stats['budget_categories'] ?? 0,
                    'icon'  => 'dripicons-list',
                    'color' => 'red',
                ],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-5 d-flex justify-content-start">
                                <div class="stats-icon {{ $card['color'] }} mb-2">
                                    <i class="{{ $card['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <h6 class="text-muted font-semibold">
                                    {{ $card['label'] }}
                                </h6>
                                <h6 class="font-extrabold mb-0">
                                    {{ number_format($card['value']) }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    {{-- === CHART PLACEHOLDER (PHASE 5 READY) === --}}
    <div class="row mt-4">

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Cash Flow Summary</h4>
                </div>
                <div class="card-body">
                    <canvas id="cashFlowChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Balance Sheet Overview</div>
                <div class="card-body">
                    <canvas id="balanceSheetChart" height="160"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Profit & Loss Summary</div>
                <div class="card-body">
                    <canvas id="profitLossChart" height="160"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
