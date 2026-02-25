<?php
// app/Services/Accounting/Budget/Contracts/BudgetRepositoryInterface.php
namespace App\Services\Accounting\Budget\Contracts;

use Illuminate\Support\Collection;

interface BudgetRepositoryInterface
{
    public function getBySnapshotAndVersion(
        int $fiscalPeriodId,
        int $version
    ): Collection;
}