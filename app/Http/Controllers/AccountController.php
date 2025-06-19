<?php
// app/Http/Controllers/AccountController.php
namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts.
     */
    public function index(): JsonResponse
    {
        $accounts = Account::with('parent', 'children', 'budgets')->paginate(20);
        return response()->json($accounts);
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'      => 'required|string|unique:accounts,code',
            'name'      => 'required|string',
            'type'      => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
        ]);

        $account = Account::create($validated);

        return response()->json([
            'message' => 'Account created successfully!',
            'data'    => $account,
        ], 201);
    }

    /**
     * Display the specified account.
     */
    public function show(Account $account): JsonResponse
    {
        $account->load('parent', 'children', 'budgets');
        return response()->json($account);
    }

    /**
     * Update the specified account in storage.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'code'      => 'sometimes|required|string|unique:accounts,code,' . $account->id,
            'name'      => 'sometimes|required|string',
            'type'      => 'sometimes|required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
        ]);

        $account->update($validated);

        return response()->json([
            'message' => 'Account updated successfully!',
            'data'    => $account,
        ]);
    }

    /**
     * Remove the specified account from storage (soft delete).
     */
    public function destroy(Account $account): JsonResponse
    {
        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully!'
        ]);
    }
}
