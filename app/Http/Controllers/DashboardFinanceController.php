<?php
// app/Http/Controllers/DashboardFinanceController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardFinanceController extends Controller
{
    public function financeSummary()
    {
        // Dummy data, nanti bisa ambil dari database tabel `accounts`, `transactions`, dsb.
        $assets = 120000;
        $liabilities = 50000;
        $equity = $assets - $liabilities;

        $profit = [15000, 12000, 18000, 20000, 17000, 21000];
        $loss = [2000, 1000, 3000, 2500, 1500, 1800];

        return response()->json([
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'profit' => $profit,
            'loss' => $loss,
        ]);
    }
}
