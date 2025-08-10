<?php
// app/Http/Controllers/TransactionDetailController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionDetailController extends Controller
{
    /**
     * List transaction details (JSON) + filters umum untuk ledger/report.
     *
     * Filters:
     * - transaction_id, account_id, cost_center_id
     * - date_from, date_to (via transactions.date)
     * - status (via transactions.status)
     * - reference (via transactions.reference like)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 20);

        $query = TransactionDetail::query()
            ->with(['transaction:id,date,reference,status,exchange_rate,currency_id', 'account:id,code,name', 'costCenter:id,name'])
            ->when($request->filled('transaction_id'), fn($q) => $q->where('transaction_id', $request->transaction_id))
            ->when($request->filled('account_id'), fn($q) => $q->where('account_id', $request->account_id))
            ->when($request->filled('cost_center_id'), fn($q) => $q->where('cost_center_id', $request->cost_center_id))
            ->when($request->filled('min_debit'), fn($q) => $q->where('debit', '>=', $request->min_debit))
            ->when($request->filled('max_debit'), fn($q) => $q->where('debit', '<=', $request->max_debit))
            ->when($request->filled('min_credit'), fn($q) => $q->where('credit', '>=', $request->min_credit))
            ->when($request->filled('max_credit'), fn($q) => $q->where('credit', '<=', $request->max_credit))
            // filters dari header transaksi
            ->when($request->filled('status'), fn($q) => $q->whereHas('transaction', fn($qq) => $qq->where('status', $request->status)))
            ->when($request->filled('reference'), fn($q) => $q->whereHas('transaction', fn($qq) => $qq->where('reference', 'like', '%'.$request->reference.'%')))
            ->when($request->filled('date_from'), fn($q) => $q->whereHas('transaction', fn($qq) => $qq->whereDate('date', '>=', $request->date_from)))
            ->when($request->filled('date_to'),   fn($q) => $q->whereHas('transaction', fn($qq) => $qq->whereDate('date', '<=', $request->date_to)));

        $details = $query->orderBy('transaction_id')->orderBy('line_no')->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($details);
    }

    /**
     * Create a new detail line (JSON).
     * - Validasi one-sided
     * - Optional line_no (auto oleh model kalau tidak dikirim)
     * - Recalculate totals pada header
     */
    public function store(Request $request): JsonResponse
    {
        // validasi dasar
        $baseRules = [
            'transaction_id' => ['required', 'exists:transactions,id'],
            'line_no'        => ['nullable', 'integer', 'min:1'],
            'account_id'     => ['required', 'exists:accounts,id'],
            'debit'          => ['required', 'numeric', 'min:0'],
            'credit'         => ['required', 'numeric', 'min:0'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'memo'           => ['nullable', 'string', 'max:255'],
        ];

        // Unique line_no per transaction jika line_no diisi
        if ($request->filled('line_no') && $request->filled('transaction_id')) {
            $baseRules['line_no'][] = Rule::unique('transaction_details')
                ->where('transaction_id', $request->transaction_id);
        }

        $validated = $request->validate($baseRules);

        // Custom rule: one-sided per line
        $debit  = (float) $validated['debit'];
        $credit = (float) $validated['credit'];
        if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
            return response()->json([
                'message' => 'Each line must be one-sided: either debit OR credit, and > 0.',
                'errors'  => ['debit' => ['One-sided rule violated'], 'credit' => ['One-sided rule violated']],
            ], 422);
        }

        $detail = null;

        DB::transaction(function () use (&$detail, $validated) {
            $detail = TransactionDetail::create($validated);
            $this->recalcHeader($detail->transaction_id);
        });

        return response()->json([
            'message' => 'Transaction detail created successfully!',
            'data'    => $detail->load(['transaction', 'account', 'costCenter']),
        ], 201);
    }

    /**
     * Show detail line (JSON).
     */
    public function show(TransactionDetail $transactionDetail): JsonResponse
    {
        $transactionDetail->load(['transaction', 'account', 'costCenter']);
        return response()->json($transactionDetail);
    }

    /**
     * Update a detail line (JSON).
     * - Validasi one-sided
     * - Jaga unique line_no per transaction
     * - Recalculate totals pada header
     */
    public function update(Request $request, TransactionDetail $transactionDetail): JsonResponse
    {
        $baseRules = [
            'transaction_id' => ['sometimes', 'required', 'exists:transactions,id'],
            'line_no'        => ['sometimes', 'nullable', 'integer', 'min:1'],
            'account_id'     => ['sometimes', 'required', 'exists:accounts,id'],
            'debit'          => ['sometimes', 'required', 'numeric', 'min:0'],
            'credit'         => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_center_id' => ['sometimes', 'nullable', 'exists:cost_centers,id'],
            'memo'           => ['sometimes', 'nullable', 'string', 'max:255'],
        ];

        $validated = $request->validate($baseRules);

        // Unique line_no per transaction jika diubah/ada di payload
        if (array_key_exists('line_no', $validated)) {
            $txIdForUnique = (int)($validated['transaction_id'] ?? $transactionDetail->transaction_id);
            if (!empty($validated['line_no'])) {
                $request->validate([
                    'line_no' => [
                        Rule::unique('transaction_details', 'line_no')
                            ->where('transaction_id', $txIdForUnique)
                            ->ignore($transactionDetail->id),
                    ]
                ]);
            }
        }

        // One-sided check jika salah satu sisi berubah
        $debit  = array_key_exists('debit', $validated)  ? (float)$validated['debit']  : (float)$transactionDetail->debit;
        $credit = array_key_exists('credit', $validated) ? (float)$validated['credit'] : (float)$transactionDetail->credit;
        if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
            return response()->json([
                'message' => 'Each line must be one-sided: either debit OR credit, and > 0.',
                'errors'  => ['debit' => ['One-sided rule violated'], 'credit' => ['One-sided rule violated']],
            ], 422);
        }

        $oldTxId = $transactionDetail->transaction_id;

        DB::transaction(function () use ($validated, $transactionDetail, $oldTxId) {
            $transactionDetail->update($validated);
            // Recalc lama & baru jika pindah transaksi
            $this->recalcHeader($oldTxId);
            $this->recalcHeader($transactionDetail->transaction_id);
        });

        return response()->json([
            'message' => 'Transaction detail updated successfully!',
            'data'    => $transactionDetail->load(['transaction', 'account', 'costCenter']),
        ]);
    }

    /**
     * Soft delete a detail line + recalc header.
     */
    public function destroy(TransactionDetail $transactionDetail): JsonResponse
    {
        $txId = $transactionDetail->transaction_id;

        DB::transaction(function () use ($transactionDetail, $txId) {
            $transactionDetail->delete();
            $this->recalcHeader($txId);
        });

        return response()->json(['message' => 'Transaction detail deleted successfully!']);
    }

    /**
     * Recalculate header totals (total_debit/credit + base) untuk sebuah transaksi.
     */
    protected function recalcHeader(int $transactionId): void
    {
        /** @var Transaction $tx */
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
