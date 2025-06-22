<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Budget;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'transactions' => Transaction::count(),
            'accounts' => Account::count(),
            'invoices' => Invoice::count(),
            'budget_categories' => Budget::count(),
        ]);
    }
}
