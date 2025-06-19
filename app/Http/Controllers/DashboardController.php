<?php
// app/Http/Controllers/Finance/DashboardController.php
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\BudgetCategory;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'transactions' => Transaction::count(),
            'accounts' => Account::count(),
            'invoices' => Invoice::count(),
            'budget_categories' => BudgetCategory::count(),
        ]);
    }
}
