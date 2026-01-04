<?php
// app/Http/Controllers/TransactionDetailController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Currency;
use App\Models\Account;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionDetailController extends Controller
{
    /**
     * Index untuk halaman transaction-details.index (menampilkan daftar transaksi dengan filter
     * seperti di view kamu).
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 20);

        $details = TransactionDetail::query()
            ->with([
                'transaction:id,journal_no,reference,status,date,currency_id,exchange_rate',
                'transaction.currency:id,code,name',
                'account:id,code,name',
                'costCenter:id,name',
            ])
            // filter by own columns
            ->when($request->filled('transaction_id'), fn($q) => $q->where('transaction_id', $request->transaction_id))
            ->when($request->filled('account_id'), fn($q) => $q->where('account_id', $request->account_id))
            ->when($request->filled('cost_center_id'), fn($q) => $q->where('cost_center_id', $request->cost_center_id))
            ->when($request->filled('min_debit'), fn($q) => $q->where('debit', '>=', $request->min_debit))
            ->when($request->filled('max_debit'), fn($q) => $q->where('debit', '<=', $request->max_debit))
            ->when($request->filled('min_credit'), fn($q) => $q->where('credit', '>=', $request->min_credit))
            ->when($request->filled('max_credit'), fn($q) => $q->where('credit', '<=', $request->max_credit))
            ->when($request->filled('memo'), fn($q) => $q->where('memo', 'like', '%'.$request->memo.'%'))
            // filter dari header transaksi
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
                Transaction::select('date')->whereColumn('transactions.id', 'transaction_details.transaction_id')
            )
            ->orderBy('transaction_id')
            ->orderBy('line_no')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        // data dropdown/filter
        $transactionsLite = Transaction::select('id', 'journal_no')
            ->orderByDesc('date')->limit(200)->get();
        $accounts    = Account::orderBy('code')->orderBy('name')->get(['id','code','name']);
        $costcenters = CostCenter::orderBy('name')->get(['id','name']);
        $currencies  = Currency::orderBy('code')->get(['id','code','name']);

        return view('transaction-details.index', compact(
            'details', 'transactionsLite', 'accounts', 'costcenters', 'currencies'
        ));
    }

    /**
     * Form create line (kamu pakai view yang mem‐POST ke transactions.store — tetap dilayani).
     * Menyediakan master data yang dibutuhkan oleh view.
     */
    public function create()
    {
        $currencies  = Currency::orderBy('code')->get();
        $accounts    = Account::orderBy('code')->orderBy('name')->get();
        $costcenters = CostCenter::orderBy('name')->get();

        return view('transaction-details.create', compact('currencies', 'accounts', 'costcenters'));
    }

    /**
     * Tampilkan 1 baris jurnal (detail) sesuai dengan view transaction-details.show.
     */
    public function show(TransactionDetail $transactionDetail)
    {
        $transactionDetail->load(['transaction.currency', 'account', 'costCenter']);
        $detail      = $transactionDetail;
        $transaction = $transactionDetail->transaction;

        return view('transaction-details.show', compact('detail', 'transaction'));
    }

    /**
     * Form edit 1 baris jurnal (detail). View ini menampilkan header ringkas + tabel baris
     * dan mem‐POST ke transactions.update (sesuai template kamu) ATAU bisa dipakai untuk submit ke route detail.update.
     */
    public function edit(TransactionDetail $transactionDetail)
    {
        $transactionDetail->load(['transaction.currency', 'account', 'costCenter']);
        $transaction = $transactionDetail->transaction;

        $currencies  = Currency::orderBy('code')->get();
        $accounts    = Account::orderBy('code')->orderBy('name')->get();
        $costcenters = CostCenter::orderBy('name')->get();

        return view('transaction-details.edit', compact(
            'transactionDetail', 'transaction', 'currencies', 'accounts', 'costcenters'
        ));
    }

    /**
     * Update 1 baris jurnal (detail) — menjaga aturan one-sided + unique line_no per transaksi,
     * dan recalculation total header setelah perubahan.
     */
    public function update(Request $request, TransactionDetail $transactionDetail)
    {
        // aturan dasar
        $baseRules = [
            'transaction_id' => ['sometimes', 'required', 'exists:transactions,id'],
            'line_no'        => ['sometimes', 'nullable', 'integer', 'min:1', Rule::unique('transaction_details','line_no')
        ->where(fn($q)=>$q->where('transaction_id',$request->transaction_id))],
            'account_id'     => ['sometimes', 'required', 'exists:accounts,id'],
            'debit'          => ['sometimes', 'required', 'numeric', 'min:0'],
            'credit'         => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_center_id' => ['sometimes', 'nullable', 'exists:cost_centers,id'],
            'memo'           => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
        $validated = $request->validate($baseRules);

        // Unique line_no per transaction (jika diubah/ada)
        if (array_key_exists('line_no', $validated)) {
            $txIdForUnique = (int)($validated['transaction_id'] ?? $transactionDetail->transaction_id);
            if (!empty($validated['line_no'])) {
                $request->validate([
                    'line_no' => [
                        Rule::unique('transaction_details', 'line_no')
                            ->where('transaction_id', $txIdForUnique)
                            ->ignore($transactionDetail->id),
                    ],
                ]);
            }
        }

        // One-sided guard (pakai nilai baru jika ada, jika tidak pakai nilai lama)
        $debit  = array_key_exists('debit', $validated)  ? (float)$validated['debit']  : (float)$transactionDetail->debit;
        $credit = array_key_exists('credit', $validated) ? (float)$validated['credit'] : (float)$transactionDetail->credit;
        if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
            return back()
                ->withInput()
                ->withErrors([
                    'details' => 'Each line must be one-sided: either debit OR credit, and > 0.',
                    'debit'   => 'One-sided rule violated.',
                    'credit'  => 'One-sided rule violated.',
                ]);
        }

        $oldTxId = $transactionDetail->transaction_id;

        DB::transaction(function () use ($validated, $transactionDetail, $oldTxId) {
            $transactionDetail->update($validated);
            // Recalc header lama & baru jika pindah transaksi
            $this->recalcHeader($oldTxId);
            $this->recalcHeader($transactionDetail->transaction_id);
        });

        return redirect()
            ->route('transaction-details.show', $transactionDetail->id)
            ->with('success', 'Journal line updated successfully!');
    }

    /**
     * Hapus (soft delete) 1 baris jurnal + recalculation header.
     */
    public function destroy(TransactionDetail $transactionDetail)
    {
        $txId = $transactionDetail->transaction_id;

        DB::transaction(function () use ($transactionDetail, $txId) {
            $transactionDetail->delete();
            $this->recalcHeader($txId);
        });

        return redirect()
            ->route('transaction-details.index')
            ->with('success', 'Journal line deleted successfully!');
    }

    /**
     * Utility: hitung ulang total di header transaksi (amount + base).
     */
    protected function recalcHeader(int $transactionId): void
    {
        /** @var Transaction|null $tx */
        $tx = Transaction::query()->lockForUpdate()->find($transactionId);
        if (!$tx) return;

        $sumD = (float) $tx->details()->sum('debit');
        $sumC = (float) $tx->details()->sum('credit');
        $rate = (float) ($tx->exchange_rate ?? 1);

        $tx->forceFill([
            'total_debit'       => round($sumD, 2),
            'total_credit'      => round($sumC, 2),
            'total_debit_base'  => round($sumD * $rate, 2),
            'total_credit_base' => round($sumC * $rate, 2),
        ])->save();
    }
}
