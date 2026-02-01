<?php
// app/Http/Controllers/TransactionDetailController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Currency;
use App\Models\Account;
use App\Models\CostCenter;
use Illuminate\Http\Request;

class TransactionDetailController extends Controller
{
    /**
     * READ-ONLY index
     * Tidak ada mutasi data di sini (STEP 5.2B.4)
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 20);

        $details = TransactionDetail::query()
            ->with([
                'transaction:id,journal_no,reference,status,date,currency_id',
                'transaction.currency:id,code,name',
                'account:id,code,name',
                'costCenter:id,name',
            ])
            ->when($request->filled('transaction_id'), fn($q) => $q->where('transaction_id', $request->transaction_id))
            ->when($request->filled('account_id'), fn($q) => $q->where('account_id', $request->account_id))
            ->when($request->filled('cost_center_id'), fn($q) => $q->where('cost_center_id', $request->cost_center_id))
            ->when($request->filled('memo'), fn($q) => $q->where('memo', 'like', '%'.$request->memo.'%'))
            ->when($request->filled('status'), fn($q) =>
                $q->whereHas('transaction', fn($t) => $t->where('status', $request->status))
            )
            ->when($request->filled('currency_id'), fn($q) =>
                $q->whereHas('transaction', fn($t) => $t->where('currency_id', $request->currency_id))
            )
            ->when($request->filled('date_from'), fn($q) =>
                $q->whereHas('transaction', fn($t) => $t->whereDate('date', '>=', $request->date_from))
            )
            ->when($request->filled('date_to'), fn($q) =>
                $q->whereHas('transaction', fn($t) => $t->whereDate('date', '<=', $request->date_to))
            )
            ->orderByDesc(
                Transaction::select('date')
                    ->whereColumn('transactions.id', 'transaction_details.transaction_id')
            )
            ->orderBy('transaction_id')
            ->orderBy('line_no')
            ->paginate($perPage)
            ->withQueryString();

        $transactionsLite = Transaction::select('id', 'journal_no')
            ->orderByDesc('date')->limit(200)->get();

        $accounts    = Account::orderBy('code')->get(['id','code','name']);
        $costcenters = CostCenter::orderBy('name')->get(['id','name']);
        $currencies  = Currency::orderBy('code')->get(['id','code','name']);

        return view('transaction-details.index', compact(
            'details', 'transactionsLite', 'accounts', 'costcenters', 'currencies'
        ));
    }

    /**
     * READ-ONLY show
     */
    public function show(TransactionDetail $transactionDetail)
    {
        $transactionDetail->load([
            'transaction.currency',
            'account',
            'costCenter'
        ]);

        return view('transaction-details.show', [
            'detail'      => $transactionDetail,
            'transaction' => $transactionDetail->transaction,
        ]);
    }

    /**
     * UI-only edit page
     * FORM HARUS submit ke TransactionController@update
     */
    public function edit(TransactionDetail $transactionDetail)
    {
        $transactionDetail->load([
            'transaction.currency',
            'account',
            'costCenter'
        ]);

        return view('transaction-details.edit', [
            'transactionDetail' => $transactionDetail,
            'transaction'       => $transactionDetail->transaction,
            'accounts'          => Account::orderBy('code')->get(),
            'costcenters'       => CostCenter::orderBy('name')->get(),
            'currencies'        => Currency::orderBy('code')->get(),
        ]);
    }

    /**
     * WRITE OPERATIONS ARE FORBIDDEN
     * Semua mutasi jurnal WAJIB lewat TransactionService
     */

    public function store()
    {
        abort(403, 'Direct journal line creation is forbidden. Use TransactionService.');
    }

    public function update()
    {
        abort(403, 'Direct journal line mutation is forbidden. Use TransactionService.');
    }

    public function destroy()
    {
        abort(403, 'Direct journal line deletion is forbidden. Use TransactionService.');
    }
}
