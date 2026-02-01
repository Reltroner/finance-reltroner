<?php
// app/Http/Controllers/TransactionController.php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Currency;
use App\Models\Account;
use App\Models\CostCenter;
use App\Http\Requests\TransactionStoreRequest;
use App\Http\Requests\TransactionUpdateRequest;
use App\Services\Accounting\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * ============================
     * INDEX
     * ============================
     */
    public function index(Request $request)
    {
        $transactions = Transaction::with(['currency'])
            ->when($request->filled('journal_no'),
                fn ($q) => $q->where('journal_no', 'like', '%' . $request->journal_no . '%'))
            ->when($request->filled('status'),
                fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('currency_id'),
                fn ($q) => $q->where('currency_id', $request->currency_id))
            ->when($request->filled('reference'),
                fn ($q) => $q->where('reference', 'like', '%' . $request->reference . '%'))
            ->when($request->filled('date_from'),
                fn ($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'),
                fn ($q) => $q->whereDate('date', '<=', $request->date_to))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'currencies'   => Currency::orderBy('code')->get(),
        ]);
    }

    /**
     * ============================
     * CREATE
     * ============================
     */
    public function create()
    {
        return view('transactions.create', [
            'currencies'  => Currency::orderBy('code')->get(),
            'accounts'    => Account::orderBy('code')->orderBy('name')->get(),
            'costcenters' => CostCenter::orderBy('name')->get(),
        ]);
    }

    /**
     * ============================
     * STORE
     * ============================
     */
    public function store(TransactionStoreRequest $request)
    {
        $this->transactionService->create($request);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * ============================
     * SHOW
     * ============================
     */
    public function show(Transaction $transaction)
    {
        $transaction->load([
            'currency',
            'details.account',
            'details.costCenter',
            'attachments',
            'taxApplications',
        ]);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * ============================
     * EDIT
     * ============================
     */
    public function edit(Transaction $transaction)
    {
        $transaction->load('details');

        return view('transactions.edit', [
            'transaction' => $transaction,
            'currencies'  => Currency::orderBy('code')->get(),
            'accounts'    => Account::orderBy('code')->orderBy('name')->get(),
            'costcenters' => CostCenter::orderBy('name')->get(),
        ]);
    }

    /**
     * ============================
     * UPDATE
     * ============================
     */
    public function update(
        TransactionUpdateRequest $request,
        Transaction $transaction
    ) {
        $this->transactionService->update($transaction, $request);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * ============================
     * DELETE
     * ============================
     */
    public function destroy(Transaction $transaction)
    {
        $this->transactionService->delete($transaction);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    /**
     * ============================
     * GENERAL LEDGER
     * ============================
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

        $account = Account::findOrFail($request->account_id);

        $rows = TransactionDetail::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->where('transaction_details.account_id', $account->id)
            ->when($request->filled('status'),
                fn ($q) => $q->where('transactions.status', $request->status))
            ->when($request->filled('date_from'),
                fn ($q) => $q->whereDate('transactions.date', '>=', $request->date_from))
            ->when($request->filled('date_to'),
                fn ($q) => $q->whereDate('transactions.date', '<=', $request->date_to))
            ->when($request->filled('cost_center_id'),
                fn ($q) => $q->where('transaction_details.cost_center_id', $request->cost_center_id))
            ->when($request->filled('reference'),
                fn ($q) => $q->where('transactions.reference', 'like', '%' . $request->reference . '%'))
            ->orderBy('transactions.date')
            ->orderBy('transactions.id')
            ->orderBy('transaction_details.line_no')
            ->get();

        $running = 0.0;
        $entries = [];

        foreach ($rows as $r) {
            $delta = (float) $r->debit - (float) $r->credit;
            $running = round($running + $delta, 2);

            $entries[] = [
                'date'        => $r->date,
                'journal_no'  => $r->journal_no,
                'reference'   => $r->reference,
                'description' => $r->description,
                'status'      => $r->status,
                'debit'       => (float) $r->debit,
                'credit'      => (float) $r->credit,
                'memo'        => $r->memo,
                'running'     => $running,
            ];
        }

        return view('transactions.ledger', [
            'account'     => $account,
            'accounts'    => Account::orderBy('code')->orderBy('name')->get(),
            'costcenters' => CostCenter::orderBy('name')->get(),
            'entries'     => $entries,
            'closing'     => $running,
            'filters'     => $request->only([
                'account_id','date_from','date_to','cost_center_id','reference','status'
            ]),
        ]);
    }
}
