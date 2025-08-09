<?php
// app/Http/Controllers/TransactionController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Currency;
use App\Models\Account;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    /**
     * List transactions (Blade)
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['currency', 'details.account', 'details.costCenter'])
            ->when($request->filled('journal_no'), fn($q) => $q->where('journal_no', 'like', '%'.$request->journal_no.'%'))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('currency_id'), fn($q) => $q->where('currency_id', $request->currency_id))
            ->when($request->filled('reference'), fn($q) => $q->where('reference', 'like', '%'.$request->reference.'%'))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn($q) => $q->whereDate('date', '<=', $request->date_to));

        $transactions = $query->orderByDesc('date')->orderByDesc('id')->paginate(20)->withQueryString();
        $currencies   = Currency::orderBy('code')->get();

        return view('transactions.index', compact('transactions', 'currencies'));
    }

    /**
     * Show create form (Blade)
     */
    public function create()
    {
        $currencies  = Currency::orderBy('code')->get();
        $accounts    = Account::orderBy('code')->orderBy('name')->get();
        $costcenters = CostCenter::orderBy('name')->get();

        return view('transactions.create', compact('currencies', 'accounts', 'costcenters'));
    }

    /**
     * Store with robust double-entry validation + GL fields
     */
    public function store(Request $request)
    {
        // 1) Basic rules & lines
        $validator = Validator::make($request->all(), [
            'reference'    => 'nullable|string|max:255|unique:transactions,reference',
            'description'  => 'nullable|string|max:1000',
            'date'         => 'required|date',
            'currency_id'  => 'required|exists:currencies,id',
            'exchange_rate'=> 'nullable|numeric|min:0.0000000001', // > 0, default 1
            'status'       => 'nullable|in:draft,posted,voided',

            'details'      => 'required|array|min:2',
            'details.*.account_id'     => 'required|exists:accounts,id',
            'details.*.debit'          => 'nullable|numeric|min:0',
            'details.*.credit'         => 'nullable|numeric|min:0',
            'details.*.cost_center_id' => 'nullable|exists:cost_centers,id',
            'details.*.memo'           => 'nullable|string|max:255',
        ]);

        // 2) Custom validation for lines (double-entry)
        $validator->after(function ($v) use ($request) {
            $lines = collect($request->input('details', []));

            if ($lines->isEmpty() || $lines->count() < 2) {
                $v->errors()->add('details', 'At least 2 lines are required.');
                return;
            }

            $sumDebit  = 0.0;
            $sumCredit = 0.0;
            $hasDebit  = false;
            $hasCredit = false;

            foreach ($lines as $idx => $line) {
                $debit  = (float)($line['debit']  ?? 0);
                $credit = (float)($line['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    $v->errors()->add("details.$idx.debit", 'A line cannot have both debit and credit.');
                    $v->errors()->add("details.$idx.credit", 'A line cannot have both debit and credit.');
                }
                if ($debit <= 0 && $credit <= 0) {
                    $v->errors()->add("details.$idx.debit", 'Either debit or credit must be greater than zero.');
                    $v->errors()->add("details.$idx.credit", 'Either debit or credit must be greater than zero.');
                }

                $sumDebit  += $debit;
                $sumCredit += $credit;
                if ($debit  > 0) $hasDebit  = true;
                if ($credit > 0) $hasCredit = true;
            }

            if (!$hasDebit || !$hasCredit) {
                $v->errors()->add('details', 'There must be at least one debit line and one credit line.');
            }
            if (round($sumDebit, 2) !== round($sumCredit, 2)) {
                $v->errors()->add('details', 'Total debit must equal total credit.');
            }
            if (round($sumDebit, 2) <= 0) {
                $v->errors()->add('details', 'Total amount must be greater than zero.');
            }
        });

        $validator->validate();

        // 3) Persist (balanced only)
        $lines      = collect($request->input('details'));
        $sumDebit   = round($lines->sum(fn($l) => (float)($l['debit']  ?? 0)), 2);
        $sumCredit  = round($lines->sum(fn($l) => (float)($l['credit'] ?? 0)), 2);
        $exRate     = $request->filled('exchange_rate') ? (float)$request->exchange_rate : 1.0;
        $sumDebitB  = round($sumDebit  * $exRate, 2);
        $sumCreditB = round($sumCredit * $exRate, 2);

        DB::transaction(function () use ($request, $lines, $sumDebit, $sumCredit, $sumDebitB, $sumCreditB, $exRate) {
            $dt          = Carbon::parse($request->date);
            $year        = (int)$dt->format('Y');
            $period      = (int)$dt->format('n');

            $tx = Transaction::create([
                'journal_no'        => Transaction::generateJournalNo($year, $period),
                'reference'         => $request->reference,
                'description'       => $request->description,
                'date'              => $request->date,
                'fiscal_year'       => $year,
                'fiscal_period'     => $period,
                'currency_id'       => $request->currency_id,
                'exchange_rate'     => $exRate,
                'total_debit'       => $sumDebit,
                'total_credit'      => $sumCredit,
                'total_debit_base'  => $sumDebitB,
                'total_credit_base' => $sumCreditB,
                'status'            => $request->input('status', 'draft'),
                'created_by'        => Auth::id(),
            ]);

            // Insert details
            $payload = $lines->map(function ($l) {
                return [
                    'account_id'     => $l['account_id'],
                    'debit'          => (float)($l['debit']  ?? 0),
                    'credit'         => (float)($l['credit'] ?? 0),
                    'cost_center_id' => $l['cost_center_id'] ?? null,
                    'memo'           => $l['memo'] ?? null,
                ];
            })->all();

            $tx->details()->createMany($payload);

            // Auto-post if requested
            if ($tx->status === 'posted') {
                $tx->markPosted(Auth::id());
            }
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully!');
    }

    /**
     * Show detail (Blade)
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['currency', 'details.account', 'details.costCenter', 'attachments', 'taxApplications']);
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Edit form (Blade)
     */
    public function edit(Transaction $transaction)
    {
        $transaction->load(['details']);
        $currencies  = Currency::orderBy('code')->get();
        $accounts    = Account::orderBy('code')->orderBy('name')->get();
        $costcenters = CostCenter::orderBy('name')->get();

        return view('transactions.edit', compact('transaction', 'currencies', 'accounts', 'costcenters'));
    }

    /**
     * Update with full-replace lines (optional)
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Basic + lines (if provided)
        $validator = Validator::make($request->all(), [
            'journal_no'   => 'sometimes|nullable|string|max:40|unique:transactions,journal_no,'.$transaction->id,
            'reference'    => 'sometimes|nullable|string|max:255|unique:transactions,reference,' . $transaction->id,
            'description'  => 'sometimes|nullable|string|max:1000',
            'date'         => 'sometimes|required|date',
            'currency_id'  => 'sometimes|required|exists:currencies,id',
            'exchange_rate'=> 'sometimes|nullable|numeric|min:0.0000000001',
            'status'       => 'sometimes|in:draft,posted,voided',

            'details'      => 'sometimes|array|min:2',
            'details.*.account_id'     => 'required_with:details|exists:accounts,id',
            'details.*.debit'          => 'nullable|numeric|min:0',
            'details.*.credit'         => 'nullable|numeric|min:0',
            'details.*.cost_center_id' => 'nullable|exists:cost_centers,id',
            'details.*.memo'           => 'nullable|string|max:255',
        ]);

        $validator->after(function ($v) use ($request) {
            if (!$request->has('details')) return; // header-only update

            $lines = collect($request->input('details', []));
            if ($lines->count() < 2) {
                $v->errors()->add('details', 'At least 2 lines are required.');
                return;
            }

            $sumDebit = 0.0; $sumCredit = 0.0; $hasDebit = false; $hasCredit = false;

            foreach ($lines as $idx => $line) {
                $debit  = (float)($line['debit']  ?? 0);
                $credit = (float)($line['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    $v->errors()->add("details.$idx.debit", 'A line cannot have both debit and credit.');
                    $v->errors()->add("details.$idx.credit", 'A line cannot have both debit and credit.');
                }
                if ($debit <= 0 && $credit <= 0) {
                    $v->errors()->add("details.$idx.debit", 'Either debit or credit must be greater than zero.');
                    $v->errors()->add("details.$idx.credit", 'Either debit or credit must be greater than zero.');
                }

                $sumDebit  += $debit;
                $sumCredit += $credit;
                if ($debit  > 0) $hasDebit  = true;
                if ($credit > 0) $hasCredit = true;
            }

            if (!$hasDebit || !$hasCredit) {
                $v->errors()->add('details', 'There must be at least one debit line and one credit line.');
            }
            if (round($sumDebit, 2) !== round($sumCredit, 2)) {
                $v->errors()->add('details', 'Total debit must equal total credit.');
            }
            if (round($sumDebit, 2) <= 0) {
                $v->errors()->add('details', 'Total amount must be greater than zero.');
            }
        });

        $validator->validate();

        DB::transaction(function () use ($request, $transaction) {
            // Header update
            $update = $request->only([
                'journal_no','reference','description','date',
                'currency_id','exchange_rate','status'
            ]);

            // Derive period when date changes
            if ($request->filled('date')) {
                $dt = Carbon::parse($request->date);
                $update['fiscal_year']   = (int)$dt->format('Y');
                $update['fiscal_period'] = (int)$dt->format('n');
            }

            // Default exchange rate if not set
            $exRate = $request->filled('exchange_rate')
                ? (float)$request->exchange_rate
                : (float)($transaction->exchange_rate ?? 1);

            $transaction->update($update);

            // If details provided: replace all + recompute totals
            if ($request->has('details')) {
                $lines = collect($request->input('details'));
                $sumDebit   = round($lines->sum(fn($l) => (float)($l['debit']  ?? 0)), 2);
                $sumCredit  = round($lines->sum(fn($l) => (float)($l['credit'] ?? 0)), 2);
                $sumDebitB  = round($sumDebit  * $exRate, 2);
                $sumCreditB = round($sumCredit * $exRate, 2);

                $transaction->update([
                    'total_debit'       => $sumDebit,
                    'total_credit'      => $sumCredit,
                    'total_debit_base'  => $sumDebitB,
                    'total_credit_base' => $sumCreditB,
                ]);

                // Replace
                $transaction->details()->delete();

                $payload = $lines->map(function ($l) {
                    return [
                        'account_id'     => $l['account_id'],
                        'debit'          => (float)($l['debit']  ?? 0),
                        'credit'         => (float)($l['credit'] ?? 0),
                        'cost_center_id' => $l['cost_center_id'] ?? null,
                        'memo'           => $l['memo'] ?? null,
                    ];
                })->all();

                $transaction->details()->createMany($payload);
            }

            // Auto mark posted/voided when status changed explicitly
            if ($request->filled('status')) {
                if ($transaction->status === 'posted' && !$transaction->posted_at) {
                    $transaction->markPosted(Auth::id());
                } elseif ($transaction->status === 'voided' && !$transaction->voided_at) {
                    $transaction->markVoided(Auth::id());
                }
            }
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully!');
    }

    /**
     * Soft delete
     */
    public function destroy(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $transaction->details()->delete();
            $transaction->delete();
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully!');
    }

    /**
     * ================================
     * GENERAL LEDGER (Buku Besar)
     * ================================
     * GET /ledger?account_id=&date_from=&date_to=&cost_center_id=&reference=
     */
    public function ledger(Request $request)
    {
        $request->validate([
            'account_id'     => 'required|exists:accounts,id',
            'date_from'      => 'nullable|date',
            'date_to'        => 'nullable|date|after_or_equal:date_from',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'reference'      => 'nullable|string|max:255',
            'status'         => 'nullable|in:draft,posted,voided',
        ]);

        $accountId    = (int) $request->account_id;
        $dateFrom     = $request->date_from;
        $dateTo       = $request->date_to;
        $costCenterId = $request->cost_center_id;

        $account      = Account::findOrFail($accountId);
        $accounts     = Account::orderBy('code')->orderBy('name')->get();
        $costcenters  = CostCenter::orderBy('name')->get();

        // Opening Balance (Σ debit-credit before date_from)
        $opening = 0.0;
        if ($dateFrom) {
            $opening = (float) TransactionDetail::query()
                ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->where('transaction_details.account_id', $accountId)
                ->when($request->filled('status'), fn($q) => $q->where('transactions.status', $request->status))
                ->whereDate('transactions.date', '<', $dateFrom)
                ->when($costCenterId, fn($q) => $q->where('transaction_details.cost_center_id', $costCenterId))
                ->sum(DB::raw('(transaction_details.debit - transaction_details.credit)'));
        }

        // Rows in period with running balance
        $rows = TransactionDetail::query()
            ->select([
                'transaction_details.id',
                'transaction_details.transaction_id',
                'transaction_details.account_id',
                'transaction_details.debit',
                'transaction_details.credit',
                'transaction_details.cost_center_id',
                'transaction_details.memo',
                'transactions.date',
                'transactions.reference',
                'transactions.description',
                'transactions.journal_no',
                'transactions.status',
            ])
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->where('transaction_details.account_id', $accountId)
            ->when($request->filled('status'), fn($q) => $q->where('transactions.status', $request->status))
            ->when($dateFrom, fn($q) => $q->whereDate('transactions.date', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('transactions.date', '<=', $dateTo))
            ->when($costCenterId, fn($q) => $q->where('transaction_details.cost_center_id', $costCenterId))
            ->when($request->filled('reference'), fn($q) => $q->where('transactions.reference', 'like', '%'.$request->reference.'%'))
            ->orderBy('transactions.date')
            ->orderBy('transactions.id')
            ->orderBy('transaction_details.id')
            ->get();

        $running = $opening;
        $entries = [];
        foreach ($rows as $r) {
            $delta   = ((float)$r->debit) - ((float)$r->credit);
            $running = round($running + $delta, 2);

            $entries[] = [
                'date'        => $r->date,
                'journal_no'  => $r->journal_no,
                'reference'   => $r->reference,
                'description' => $r->description,
                'status'      => $r->status,
                'debit'       => (float) $r->debit,
                'credit'      => (float) $r->credit,
                'cost_center' => optional($r->costCenter)->name ?? null,
                'memo'        => $r->memo,
                'running'     => $running,
                'transaction_detail_id' => $r->id,
                'transaction_id'        => $r->transaction_id,
            ];
        }

        $totalDebit  = round(collect($entries)->sum('debit'), 2);
        $totalCredit = round(collect($entries)->sum('credit'), 2);
        $closing     = round($opening + ($totalDebit - $totalCredit), 2);

        return view('transactions.ledger', [
            'account'       => $account,
            'accounts'      => $accounts,
            'costcenters'   => $costcenters,
            'filters'       => [
                'account_id'     => $accountId,
                'date_from'      => $dateFrom,
                'date_to'        => $dateTo,
                'cost_center_id' => $costCenterId,
                'reference'      => $request->reference,
                'status'         => $request->status,
            ],
            'opening'       => $opening,
            'entries'       => $entries,
            'totalDebit'    => $totalDebit,
            'totalCredit'   => $totalCredit,
            'closing'       => $closing,
        ]);
    }
}
