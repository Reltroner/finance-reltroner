<?php
// app/Http/Controllers/Reports/ComparativeProfitLossController.php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Read\Statements\Comparative\ComparativeProfitLossService;

class ComparativeProfitLossController extends Controller
{
    public function __invoke(
        ComparativeProfitLossService $service
    ) {
        return view('reports.pl-comparative', [
            'statement' => $service->generate([
                202401 => 'Jan 2024',
                202402 => 'Feb 2024',
                202403 => 'Mar 2024',
            ])
        ]);
    }
}
