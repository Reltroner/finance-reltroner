<?php
// app/Models/FinancialBudget.php
declare(strict_types=1);

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

final class FinancialBudget extends Model
{
    use HasUuids;

    protected $table = 'financial_budgets';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * Prevent update.
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new DomainException('FinancialBudget is append-only. Update is not allowed.');
    }

    /**
     * Prevent delete.
     */
    public function delete(): ?bool
    {
        throw new DomainException('FinancialBudget is append-only. Delete is not allowed.');
    }
}