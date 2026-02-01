<?php
// app/Http/Requests/TransactionStoreRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Transaction;
use App\Models\Account;
use App\Services\Accounting\TransactionGuard;

class TransactionUpdateRequest extends FormRequest
{
    protected Transaction $transaction;

    public function authorize(): bool
    {
        $this->transaction = $this->route('transaction');
        return true;
    }

    /**
     * Structural validation only.
     */
    public function rules(): array
    {
        return [
            'journal_no'   => 'sometimes|nullable|string|max:40|unique:transactions,journal_no,' . $this->transaction->id,
            'reference'    => 'sometimes|nullable|string|max:255|unique:transactions,reference,' . $this->transaction->id,
            'description'  => 'sometimes|nullable|string|max:1000',
            'date'         => 'sometimes|required|date',
            'currency_id'  => 'sometimes|required|exists:currencies,id',
            'exchange_rate'=> 'sometimes|nullable|numeric|min:0.0000000001',
            'status'       => 'sometimes|in:draft,posted,voided',

            'type' => 'sometimes|string|in:' . implode(',', [
                Transaction::TYPE_GENERAL,
                Transaction::TYPE_EQUITY_OPENING,
                Transaction::TYPE_EQUITY_INJECTION,
                Transaction::TYPE_PERIOD_CLOSING,
                Transaction::TYPE_SYSTEM_ADJUSTMENT,
            ]),

            'details'                  => 'sometimes|array|min:2',
            'details.*.account_id'     => 'required_with:details|exists:accounts,id',
            'details.*.debit'          => 'nullable|numeric|min:0',
            'details.*.credit'         => 'nullable|numeric|min:0',
            'details.*.cost_center_id' => 'nullable|exists:cost_centers,id',
            'details.*.memo'           => 'nullable|string|max:255',
        ];
    }

    /**
     * Domain-aware guards.
     */
    public function withValidator(Validator $validator): void
    {
        /*
         * Immutable journal guard (STEP 5.2B.2)
         */
        $validator->after(function () {
            if (!$this->transaction->isEditable()) {
                abort(403, 'This transaction is immutable.');
            }
        });

        /*
         * Fiscal period lock guard (STEP 5.2B.4)
         */
        $validator->after(function () {
            $guard = app(TransactionGuard::class);

            // Existing period
            $guard->assertPeriodWritable(
                $this->transaction->fiscal_year,
                $this->transaction->fiscal_period,
                $this->transaction->type
            );

            // New date (if changed)
            if ($this->filled('date')) {
                $guard->assertDateWritable(
                    $this->input('date'),
                    $this->input('type', $this->transaction->type)
                );
            }
        });

        /*
         * Double-entry invariant (only if details replaced)
         */
        $validator->after(function (Validator $v) {
            if (!$this->has('details')) return;

            $lines = collect($this->input('details', []));

            if ($lines->count() < 2) {
                $v->errors()->add('details', 'At least 2 journal lines are required.');
                return;
            }

            $sumDebit = 0.0;
            $sumCredit = 0.0;
            $hasDebit = false;
            $hasCredit = false;

            foreach ($lines as $i => $line) {
                $debit  = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    $v->errors()->add("details.$i.debit", 'Debit and credit cannot both be filled.');
                    $v->errors()->add("details.$i.credit", 'Debit and credit cannot both be filled.');
                }

                if ($debit <= 0 && $credit <= 0) {
                    $v->errors()->add("details.$i.debit", 'Either debit or credit must be greater than zero.');
                }

                $sumDebit  += $debit;
                $sumCredit += $credit;

                if ($debit  > 0) $hasDebit  = true;
                if ($credit > 0) $hasCredit = true;
            }

            if (!$hasDebit || !$hasCredit) {
                $v->errors()->add('details', 'At least one debit and one credit line is required.');
            }

            if (round($sumDebit, 2) !== round($sumCredit, 2)) {
                $v->errors()->add('details', 'Total debit must equal total credit.');
            }

            if ($sumDebit <= 0) {
                $v->errors()->add('details', 'Transaction total must be greater than zero.');
            }
        });

        /*
         * Locked account guard
         */
        $validator->after(function (Validator $v) {
            if (!$this->has('details')) return;

            $lines = collect($this->input('details', []));
            if ($lines->isEmpty()) return;

            $accounts = Account::whereIn(
                'id',
                $lines->pluck('account_id')->unique()
            )->get()->keyBy('id');

            foreach ($lines as $i => $line) {
                $acc = $accounts[$line['account_id']] ?? null;

                if ($acc && $acc->is_locked) {
                    $v->errors()->add(
                        "details.$i.account_id",
                        "Account {$acc->code} - {$acc->name} is locked."
                    );
                }
            }
        });

        /*
         * Equity usage guard
         */
        $validator->after(function (Validator $v) {
            if (!$this->has('details')) return;

            $type  = $this->input('type', $this->transaction->type);
            $lines = collect($this->input('details', []));

            $accounts = Account::whereIn(
                'id',
                $lines->pluck('account_id')->unique()
            )->get()->keyBy('id');

            foreach ($lines as $i => $line) {
                $acc = $accounts[$line['account_id']] ?? null;

                if (!$acc || $acc->type !== 'equity') continue;

                if (!in_array($type, [
                    Transaction::TYPE_EQUITY_OPENING,
                    Transaction::TYPE_EQUITY_INJECTION,
                    Transaction::TYPE_PERIOD_CLOSING,
                    Transaction::TYPE_SYSTEM_ADJUSTMENT,
                ], true)) {
                    $v->errors()->add(
                        "details.$i.account_id",
                        "Equity account {$acc->code} - {$acc->name} is not allowed for transaction type '{$type}'."
                    );
                }
            }
        });
    }
}
