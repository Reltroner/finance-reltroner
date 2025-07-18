<?php
// app/Http/Controllers/CurrencyController.php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    /**
     * Display a listing of currencies (Blade UI).
     */
    public function index(Request $request)
    {
        $query = Currency::query();

        if ($request->filled('code')) {
            $query->where('code', $request->input('code'));
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $currencies = $query->orderBy('code')->paginate(20);

        // Blade UI request (default)
        if (!$request->wantsJson()) {
            return view('currencies.index', compact('currencies'));
        }

        // JSON (API) response
        return response()->json($currencies);
    }

    /**
     * Show the form for creating a new currency.
     */
    public function create()
    {
        return view('currencies.create');
    }

    /**
     * Store a newly created currency.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'   => 'required|string|max:10|unique:currencies,code',
            'name'   => 'required|string|max:100',
            'symbol' => 'nullable|string|max:10',
            'rate'   => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // default is_active to true if not set
        $validated['is_active'] = $request->boolean('is_active', true);

        $currency = Currency::create($validated);

        // Blade UI response
        if (!$request->wantsJson()) {
            return redirect()->route('currencies.index')->with('success', 'Currency created successfully!');
        }

        // JSON API response
        return response()->json([
            'message' => 'Currency created successfully!',
            'data'    => $currency,
        ], 201);
    }

    /**
     * Display the specified currency (Blade or JSON).
     */
    public function show(Request $request, Currency $currency)
    {
        if (!$request->wantsJson()) {
            return view('currencies.show', compact('currency'));
        }
        return response()->json($currency);
    }

    /**
     * Show the form for editing the specified currency.
     */
    public function edit(Currency $currency)
    {
        return view('currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency.
     */
    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'code'   => 'sometimes|required|string|max:10|unique:currencies,code,' . $currency->id,
            'name'   => 'sometimes|required|string|max:100',
            'symbol' => 'nullable|string|max:10',
            'rate'   => 'sometimes|required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $currency->update($validated);

        if (!$request->wantsJson()) {
            return redirect()->route('currencies.index')->with('success', 'Currency updated successfully!');
        }

        return response()->json([
            'message' => 'Currency updated successfully!',
            'data'    => $currency,
        ]);
    }

    /**
     * Remove the specified currency (soft delete).
     */
    public function destroy(Request $request, Currency $currency)
    {
        $currency->delete();

        if (!$request->wantsJson()) {
            return redirect()->route('currencies.index')->with('success', 'Currency deleted successfully!');
        }

        return response()->json([
            'message' => 'Currency deleted successfully!',
        ]);
    }
}
