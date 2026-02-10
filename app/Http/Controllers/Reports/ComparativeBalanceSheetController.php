<?php
// app/Http/Controllers/Reports/ComparativeBalanceSheetController.php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Read\Statements\Comparative\ComparativeBalanceSheetService;

class ComparativeBalanceSheetController extends Controller
{
    public function __invoke(
        ComparativeBalanceSheetService $service
    ) {
        return view('reports.bs-comparative', [
            'statement' => $service->generate([
                202401 => 'Jan 2024',
                202402 => 'Feb 2024',
                202403 => 'Mar 2024',
            ])
        ]);
    }
}
