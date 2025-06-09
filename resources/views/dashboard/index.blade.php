@extends('layouts.dashboard')
@section('content')
<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>
{{-- resources/views/finance/dashboard/index.blade.php --}}
<div class="page-heading">
    <h3>Finance Dashboard</h3>
</div> 
<div class="page-content"> 
    <div class="row">
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-5 d-flex justify-content-start">
                            <div class="stats-icon purple mb-2">
                                <i class="icon dripicons dripicons-wallet"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <h6 class="text-muted font-semibold">Transactions</h6>
                            <h6 class="font-extrabold mb-0">{{ $transactions }}</h6>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card"> 
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2">
                                <i class="icon dripicons dripicons-bank"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <h6 class="text-muted font-semibold">Accounts</h6>
                            <h6 class="font-extrabold mb-0">{{ $accounts }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-5 d-flex justify-content-start">
                            <div class="stats-icon green mb-2">
                                <i class="icon dripicons dripicons-document"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <h6 class="text-muted font-semibold">Invoices</h6>
                            <h6 class="font-extrabold mb-0">{{ $invoices }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-5 d-flex justify-content-start">
                            <div class="stats-icon red mb-2">
                                <i class="icon dripicons dripicons-list"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <h6 class="text-muted font-semibold">Budget Categories</h6>
                            <h6 class="font-extrabold mb-0">{{ $budget_categories }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Optional: You can add chart visualizations or finance summaries below --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Cash Flow Summary</h4>
                </div>
                <div class="card-body">
                    <canvas id="cashFlowChart" style="width:100%;height:300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Balance Sheet Overview</div>
                <div class="card-body">
                    <canvas id="balanceSheetChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Profit & Loss Summary</div>
                <div class="card-body">
                    <canvas id="profitLossChart" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
