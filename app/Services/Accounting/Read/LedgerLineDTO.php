<?php
// app/Services/Accounting/Read/LedgerLineDTO.php
namespace App\Services\Accounting\Read;

class LedgerLineDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $accountId,
        public readonly float $debit,
        public readonly float $credit,
        public readonly string $journalNo,
        public readonly string $transactionType,
        public readonly ?string $postedAt // ✅ nullable
    ) {}

    public static function fromRow(object $row): self
    {
        return new self(
            id: $row->id,
            accountId: $row->account_id,
            debit: (float) $row->debit,
            credit: (float) $row->credit,
            journalNo: $row->journal_no,
            transactionType: $row->transaction_type,
            postedAt: $row->posted_at // can be null
        );
    }

    public function amount(): float
    {
        return $this->debit - $this->credit;
    }
}
