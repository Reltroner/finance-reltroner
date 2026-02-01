<?php
// app/Http/Requests/TransactionStoreRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Transaction;
use App\Models\Account;
use App\Services\Accounting\TransactionGuard;

class TransactionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization detail sebaiknya di Policy.
        return true;
    }

    /**
     * Structural validation only.
     * No domain side-effects here.
     */
    public function rules(): array
    {
        return [
            'reference'    => 'nullable|string|max:255|unique:transactions,reference',
            'description'  => 'nullable|string|max:1000',
            'date'         => 'required|date',
            'currency_id'  => 'required|exists:currencies,id',
            'exchange_rate'=> 'nullable|numeric|min:0.0000000001',
            'status'       => 'nullable|in:draft,posted,voided',

            'type' => 'nullable|string|in:' . implode(',', [
                Transaction::TYPE_GENERAL,
                Transaction::TYPE_EQUITY_OPENING,
                Transaction::TYPE_EQUITY_INJECTION,
                Transaction::TYPE_PERIOD_CLOSING,
                Transaction::TYPE_SYSTEM_ADJUSTMENT,
            ]),

            'details'                  => 'required|array|min:2',
            'details.*.account_id'     => 'required|exists:accounts,id',
            'details.*.debit'          => 'nullable|numeric|min:0',
            'details.*.credit'         => 'nullable|numeric|min:0',
            'details.*.cost_center_id' => 'nullable|exists:cost_centers,id',
            'details.*.memo'           => 'nullable|string|max:255',
        ];
    }

    /**
     * Cross-field & domain-aware validation.
     *
     * Still READ-ONLY.
     */
    public function withValidator(Validator $validator): void
    {
        /*
         * Double-entry invariant
         */
        $validator->after(function (Validator $v) {
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
         * Equity account usage guard
         */
        $validator->after(function (Validator $v) {
            $type  = $this->input('type', Transaction::TYPE_GENERAL);
            $lines = collect($this->input('details', []));

            if ($lines->isEmpty()) return;

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

        /*
         * Fiscal period guard (STEP 5.2B.4)
         */
        $validator->after(function () {
            $guard = app(TransactionGuard::class);

            $guard->assertDateWritable(
                $this->input('date'),
                $this->input('type')
            );
        });
    }
}
