<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the accounts.
     */
    public function index()
    {
        return response()->json(Account::all());
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name'   => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'balance'        => 'nullable|numeric|min:0',
            'currency'       => 'nullable|string|max:10',
        ]);

        $account = Account::create($validated);

        return response()->json($account, 201);
    }

    /**
     * Display the specified account.
     */
    public function show($id)
    {
        $account = Account::findOrFail($id);

        return response()->json($account);
    }

    /**
     * Update the specified account in storage.
     */
    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $validated = $request->validate([
            'account_name'   => 'sometimes|required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'balance'        => 'nullable|numeric|min:0',
            'currency'       => 'nullable|string|max:10',
        ]);

        $account->update($validated);

        return response()->json($account);
    }

    /**
     * Remove the specified account from storage (soft delete).
     */
    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        $account->delete();

        return response()->json(['message' => 'Account deleted.']);
    }
}
