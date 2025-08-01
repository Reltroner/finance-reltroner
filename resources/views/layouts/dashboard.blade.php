<!DOCTYPE html>
<html lang="en">
<!--http://finance.reltroner.local resource/views/layouts/dashboard.blade.php -->
<style>
@media print {
    header, nav, .breadcrumb-header, .btn, .alert, .sidebar, footer {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: none !important;
    }

    body {
        background: white !important;
        color: black;
    }

    .table th, .table td {
        color: black !important;
        background-color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .container, .card-body {
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .btn {
        display: none !important;
    }
}
</style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reltroner Finance Management</title>
    
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACEAAAAiCAYAAADRcLDBAAAEs2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS41LjAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgZXhpZjpQaXhlbFhEaW1lbnNpb249IjMzIgogICBleGlmOlBpeGVsWURpbWVuc2lvbj0iMzQiCiAgIGV4aWY6Q29sb3JTcGFjZT0iMSIKICAgdGlmZjpJbWFnZVdpZHRoPSIzMyIKICAgdGlmZjpJbWFnZUxlbmd0aD0iMzQiCiAgIHRpZmY6UmVzb2x1dGlvblVuaXQ9IjIiCiAgIHRpZmY6WFJlc29sdXRpb249Ijk2LjAiCiAgIHRpZmY6WVJlc29sdXRpb249Ijk2LjAiCiAgIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiCiAgIHBob3Rvc2hvcDpJQ0NQcm9maWxlPSJzUkdCIElFQzYxOTY2LTIuMSIKICAgeG1wOk1vZGlmeURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiCiAgIHhtcDpNZXRhZGF0YURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiPgogICA8eG1wTU06SGlzdG9yeT4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGkKICAgICAgc3RFdnQ6YWN0aW9uPSJwcm9kdWNlZCIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWZmaW5pdHkgRGVzaWduZXIgMS4xMC4xIgogICAgICBzdEV2dDp3aGVuPSIyMDIyLTAzLTMxVDEwOjUwOjIzKzAyOjAwIi8+CiAgICA8L3JkZjpTZXE+CiAgIDwveG1wTU06SGlzdG9yeT4KICA8L3JkZjpEZXNjcmlwdGlvbj4KIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+Cjw/eHBhY2tldCBlbmQ9InIiPz5V57uAAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kc8rRFEUxz9maORHo1hYKC9hISNGTWwsRn4VFmOUX5uZZ36oeTOv954kW2WrKLHxa8FfwFZZK0WkZClrYoOe87ypmWTO7dzzud97z+nec8ETzaiaWd4NWtYyIiNhZWZ2TvE946WZSjqoj6mmPjE1HKWkfdxR5sSbgFOr9Ll/rXoxYapQVik8oOqGJTwqPL5i6Q5vCzeo6dii8KlwpyEXFL519LjLLw6nXP5y2IhGBsFTJ6ykijhexGra0ITl5bRqmWU1fx/nJTWJ7PSUxBbxJkwijBBGYYwhBgnRQ7/MIQIE6ZIVJfK7f/MnyUmuKrPOKgZLpEhj0SnqslRPSEyKnpCRYdXp/9++msneoFu9JgwVT7b91ga+LfjetO3PQ9v+PgLvI1xkC/m5A+h7F32zoLXug38dzi4LWnwHzjeg8UGPGbFfySvuSSbh9QRqZ6H+Gqrm3Z7l9zm+h+iafNUV7O5Bu5z3L/wAdthn7QIme0YAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAJTSURBVFiF7Zi9axRBGIefEw2IdxFBRQsLWUTBaywSK4ubdSGVIY1Y6HZql8ZKCGIqwX/AYLmCgVQKfiDn7jZeEQMWfsSAHAiKqPiB5mIgELWYOW5vzc3O7niHhT/YZvY37/swM/vOzJbIqVq9uQ04CYwCI8AhYAlYAB4Dc7HnrOSJWcoJcBS4ARzQ2F4BZ2LPmTeNuykHwEWgkQGAet9QfiMZjUSt3hwD7psGTWgs9pwH1hC1enMYeA7sKwDxBqjGnvNdZzKZjqmCAKh+U1kmEwi3IEBbIsugnY5avTkEtIAtFhBrQCX2nLVehqyRqFoCAAwBh3WGLAhbgCRIYYinwLolwLqKUwwi9pxV4KUlxKKKUwxC6ZElRCPLYAJxGfhSEOCz6m8HEXvOB2CyIMSk6m8HoXQTmMkJcA2YNTHm3congOvATo3tE3A29pxbpnFzQSiQPcB55IFmFNgFfEQeahaAGZMpsIJIAZWAHcDX2HN+2cT6r39GxmvC9aPNwH5gO1BOPFuBVWAZue0vA9+A12EgjPadnhCuH1WAE8ivYAQ4ohKaagV4gvxi5oG7YSA2vApsCOH60WngKrA3R9IsvQUuhIGY00K4flQG7gHH/mLytB4C42EgfrQb0mV7us8AAMeBS8mGNMR4nwHamtBB7B4QRNdaS0M8GxDEog7iyoAguvJ0QYSBuAOcAt71Kfl7wA8DcTvZ2KtOlJEr+ByyQtqqhTyHTIeB+ONeqi3brh+VgIN0fohUgWGggizZFTplu12yW8iy/YLOGWMpDMTPXnl+Az9vj2HERYqPAAAAAElFTkSuQmCC" type="image/png">

    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/iconly.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/extensions/@icon/dripicons/dripicons.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/ui-icons-dripicons.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/table-datatable.html') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" >
</head>

<body>
    <script src="{{ asset('mazer/assets/static/js/initTheme.js') }}"></script>
    <div id="app">
        <div id="sidebar">
            <div class="sidebar-wrapper active">
    <div class="sidebar-header position-relative">
        <div class="d-flex justify-content-between align-items-center">
            <div class="logo">
                <a href="{{ route('dashboard') }}"><img src="{{ asset('images/reltroner.png') }}" alt="Logo" srcset=""></a>
            </div>
            <div class="theme-toggle d-flex gap-2  align-items-center mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                    role="img" class="iconify iconify--system-uicons" width="20" height="20"
                    preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                    <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                            opacity=".3"></path>
                        <g transform="translate(-210 -1)">
                            <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                            <circle cx="220.5" cy="11.5" r="4"></circle>
                            <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                        </g>
                    </g>
                </svg>
                <div class="form-check form-switch fs-6">
                    <input class="form-check-input  me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                    <label class="form-check-label"></label>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                    role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet"
                    viewBox="0 0 24 24">
                    <path fill="currentColor"
                        d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                    </path>
                </svg>
            </div>
            <div class="sidebar-toggler  x">
                <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
            </div>
        </div>
    </div>
    <div class="sidebar-menu">
        <ul class="menu">
            <li class="sidebar-title">Finance Menu</li>
            <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="sidebar-link">
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('transactions*') ? 'active' : '' }}">
                <a href="{{ route('transactions.index') }}" class="sidebar-link">
                    <i class="bi bi-wallet2"></i>
                    <span>Transactions</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('accounts*') ? 'active' : '' }}">
                <a href="{{ route('accounts.index') }}" class="sidebar-link">
                    <i class="bi bi-bank"></i>
                    <span>Accounts</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('attachments*') ? 'active' : '' }}">
                <a href="{{ route('attachments.index') }}" class="sidebar-link">
                    <i class="bi bi-paperclip"></i>
                    <span>Attachments</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('auditlogs*') ? 'active' : '' }}">
                <a href="{{ route('auditlogs.index') }}" class="sidebar-link">
                    <i class="bi bi-list-check"></i>
                    <span>AuditLog</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('budgets*') ? 'active' : '' }}">
                <a href="{{ route('budgets.index') }}" class="sidebar-link">
                    <i class="bi bi-cash-coin"></i>
                    <span>Budgets</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('costcenters*') ? 'active' : '' }}">
                <a href="{{ route('costcenters.index') }}" class="sidebar-link">
                    <i class="bi bi-wallet"></i>
                    <span>Cost Centers</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('currencies*') ? 'active' : '' }}">
                <a href="{{ route('currencies.index') }}" class="sidebar-link">
                    <i class="bi bi-currency-exchange"></i>
                    <span>Currencies</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('customers*') ? 'active' : '' }}">
                <a href="{{ route('customers.index') }}" class="sidebar-link">
                    <i class="bi bi-people"></i>
                    <span>Customers</span>
                </a>
            </li>
            {{-- <li class="sidebar-item {{ request()->is('categories*') ? 'active' : '' }}">
                <a href="{{ route('categories.index') }}" class="sidebar-link">
                    <i class="bi bi-tags"></i>
                    <span>Categories</span>
                </a>
            </li> --}}
            <li class="sidebar-item {{ request()->is('taxes*') ? 'active' : '' }}">
                <a href="{{ route('taxes.index') }}" class="sidebar-link">
                    <i class="bi bi-percent"></i>
                    <span>Taxes</span>
                </a>
            </li>
            {{-- <li class="sidebar-item {{ request()->is('reports*') ? 'active' : '' }}">
                <a href="{{ route('reports.index') }}" class="sidebar-link">
                    <i class="bi bi-graph-up"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('settings*') ? 'active' : '' }}">
                <a href="{{ route('settings.index') }}" class="sidebar-link">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="sidebar-title">Other Features</li>
            <li class="sidebar-item {{ request()->is('users*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="sidebar-link">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
            </li> --}}
            <li class="sidebar-item {{ request()->is('invoices*') ? 'active' : '' }}">
                <a href="{{ route('invoices.index') }}" class="sidebar-link">
                    <i class="bi bi-receipt"></i>
                    <span>Invoices</span>
                </a>
            </li>
            <li class="sidebar-item {{ request()->is('payments*') ? 'active' : '' }}">
                <a href="{{ route('payments.index') }}" class="sidebar-link">
                    <i class="bi bi-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li class="sidebar-item">
                <form method="POST" class="sidebar-link" action="index.html">
                    @csrf
                    <button type="submit" class="sidebar-link btn btn-link text-start w-100 p-0 m-0">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>
        </div>
        <div id="main">

        @yield('content')

        <footer>
            <div class="footer clearfix mb-0 text-muted">
                <div class="float-start">
                    <p>2025 &copy; Reltroner Studio</p>
                </div>
                <div class="float-end">
                    <p>Crafted with <span class="text-danger"><i class="bi bi-heart-fill icon-mid"></i></span>
                        by <a href="https://www.reltroner.com/blog/for-recruiters">Rei Reltroner</a></p>
                </div>
            </div>
        </footer>
        </div>
    </div>
<script src="{{ asset('mazer/assets/static/js/components/dark.js') }}"></script>
<script src="{{ asset('mazer/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('mazer/assets/compiled/js/app.js') }}"></script>
    
<!-- Need: Apexcharts -->
<script src="{{ asset('mazer/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('mazer/assets/static/js/pages/dashboard.js') }}"></script>

<!-- Need: chartJS -->
<script src="{{ asset('mazer/assets/extensions/chart.js/chart.umd.js') }}"></script>

<!-- Need: Simple Datatables -->
<script src="{{ asset('mazer/assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
<script src="{{ asset('mazer/assets/static/js/pages/simple-datatables.js') }}"></script>


<!-- Need: Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
        // Flatpickr for date only (Y-m-d)
        let date = flatpickr(".date", {
            dateFormat: "Y-m-d"
        });

        // Flatpickr for datetime (Y-m-d H:i:s)
        let datetime = flatpickr(".datetime", {
            enableTime: true,
            enableSeconds: true,
            dateFormat: "Y-m-d H:i:s",
            time_24hr: true
        });
</script>
<script>
    const ctxBalance = document.getElementById('balanceSheetChart').getContext('2d');
    const ctxProfit = document.getElementById('profitLossChart').getContext('2d');

    const balanceChart = new Chart(ctxBalance, {
        type: 'bar',
        data: {
            labels: ['Assets', 'Liabilities', 'Equity'],
            datasets: [{
                label: 'Balance Sheet',
                data: [0, 0, 0],
                backgroundColor: ['#4caf50', '#f44336', '#2196f3']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Balance Sheet Summary'
                }
            }
        }
    });

    const profitLossChart = new Chart(ctxProfit, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Profit',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: '#4caf50',
                fill: false
            }, {
                label: 'Loss',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: '#f44336',
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Profit & Loss (Last 6 Months)'
                }
            }
        }
    });

    // Example fetch update — replace with actual endpoint later
    fetch('/dashboard')
    .then(res => res.json())
    .then(data => {
        balanceChart.data.datasets[0].data = [
            data.assets, data.liabilities, data.equity
        ];
        profitLossChart.data.datasets[0].data = data.profit;
        profitLossChart.data.datasets[1].data = data.loss;
        profitLossChart.update();
        balanceChart.update();
    });

</script>
@stack('scripts')

</body>

</html>