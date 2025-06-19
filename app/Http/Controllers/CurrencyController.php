<?php
// app/Http/Controllers/CurrencyController.php
namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    /**
     * Display a listing of currencies, with optional filtering by code or name.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Currency::query();

        if ($request->filled('code')) {
            $query->where('code', $request->input('code'));
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $currencies = $query->orderBy('code')->paginate(20);

        return response()->json($currencies);
    }

    /**
     * Store a newly created currency in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'   => 'required|string|max:10|unique:currencies,code',
            'name'   => 'required|string|max:100',
            'symbol' => 'nullable|string|max:10',
            'rate'   => 'required|numeric|min:0',
        ]);

        $currency = Currency::create($validated);

        return response()->json([
            'message' => 'Currency created successfully!',
            'data'    => $currency,
        ], 201);
    }

    /**
     * Display the specified currency.
     */
    public function show(Currency $currency): JsonResponse
    {
        return response()->json($currency);
    }

    /**
     * Update the specified currency in storage.
     */
    public function update(Request $request, Currency $currency): JsonResponse
    {
        $validated = $request->validate([
            'code'   => 'sometimes|required|string|max:10|unique:currencies,code,' . $currency->id,
            'name'   => 'sometimes|required|string|max:100',
            'symbol' => 'nullable|string|max:10',
            'rate'   => 'sometimes|required|numeric|min:0',
        ]);

        $currency->update($validated);

        return response()->json([
            'message' => 'Currency updated successfully!',
            'data'    => $currency,
        ]);
    }

    /**
     * Remove the specified currency from storage (soft delete).
     */
    public function destroy(Currency $currency): JsonResponse
    {
        $currency->delete();

        return response()->json([
            'message' => 'Currency deleted successfully!',
        ]);
    }
}
