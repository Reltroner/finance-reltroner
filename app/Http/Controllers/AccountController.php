<?php
// app/Http/Controllers/AccountController.php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts.
     */
    public function index()
    {
        $accounts = Account::with('parent', 'children', 'budgets')
            ->orderBy('code')
            ->paginate(20);

        return view('accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new account.
     */
    public function create()
    {
        $parents = Account::where('is_active', true)->orderBy('code')->get();
        return view('accounts.create', compact('parents'));
    }

    /**
     * Store a newly created account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'           => 'required|string|unique:accounts,code',
            'name'           => 'required|string',
            'type'           => 'required|in:asset,liability,equity,income,expense',
            'normal_balance' => 'nullable|in:debit,credit',
            'parent_id'      => 'nullable|exists:accounts,id',
            'is_active'      => 'boolean',
        ]);

        // Default normal balance (UX helper, bukan sumber kebenaran)
        if (empty($validated['normal_balance'])) {
            $validated['normal_balance'] =
                in_array($validated['type'], ['asset', 'expense'])
                    ? 'debit'
                    : 'credit';
        }

        $account = Account::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Account created successfully!',
                'data'    => $account,
            ], 201);
        }

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account created successfully!');
    }

    /**
     * Display the specified account.
     */
    public function show(Account $account)
    {
        $account->load('parent', 'children', 'budgets');
        return view('accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified account.
     */
    public function edit(Account $account)
    {
        $parents = Account::where('id', '!=', $account->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('accounts.edit', compact('account', 'parents'));
    }

    /**
     * Update the specified account.
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'code'           => 'sometimes|required|string|unique:accounts,code,' . $account->id,
            'name'           => 'sometimes|required|string',
            'type'           => 'sometimes|required|in:asset,liability,equity,income,expense',
            'normal_balance' => 'nullable|in:debit,credit',
            'parent_id'      => 'nullable|exists:accounts,id',
            'is_active'      => 'boolean',
        ]);

        // Jaga konsistensi jika type berubah tapi normal_balance tidak dikirim
        if (
            isset($validated['type']) &&
            !isset($validated['normal_balance'])
        ) {
            $validated['normal_balance'] =
                in_array($validated['type'], ['asset', 'expense'])
                    ? 'debit'
                    : 'credit';
        }

        $account->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Account updated successfully!',
                'data'    => $account,
            ]);
        }

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account updated successfully!');
    }

    /**
     * Soft delete account.
     */
    public function destroy(Account $account)
    {
        $account->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Account deleted successfully!',
            ]);
        }

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account deleted successfully!');
    }
}
