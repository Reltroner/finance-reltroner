<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\{
    Transaction,
    Account,
    Invoice,
    Budget
};

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'stats' => [
                'transactions'      => Transaction::query()->count(),
                'accounts'          => Account::query()->count(),
                'invoices'          => Invoice::query()->count(),
                'budget_categories' => Budget::query()->count(),
            ],
        ]);
    }
}
