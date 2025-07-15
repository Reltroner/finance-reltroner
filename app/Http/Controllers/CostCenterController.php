<?php
// app/Http/Controllers/CostCenterController.php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    /**
     * Display a listing of cost centers in Blade view, with optional filter.
     */
    public function index(Request $request)
    {
        $query = CostCenter::query();

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $costcenters = $query->orderBy('name')->paginate(20);

        return view('costcenters.index', compact('costcenters'));
    }

    /**
     * Show the form for creating a new cost center.
     */
    public function create()
    {
        return view('costcenters.create');
    }

    /**
     * Store a newly created cost center in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:cost_centers,name',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        // Set default is_active to true if not provided
        $validated['is_active'] = $validated['is_active'] ?? true;

        CostCenter::create($validated);

        return redirect()->route('costcenters.index')->with('success', 'Cost center created successfully!');
    }

    /**
     * Display the specified cost center.
     */
    public function show(CostCenter $costcenter)
    {
        return view('costcenters.show', compact('costcenter'));
    }

    /**
     * Show the form for editing the specified cost center.
     */
    public function edit(CostCenter $costcenter)
    {
        return view('costcenters.edit', compact('costcenter'));
    }

    /**
     * Update the specified cost center in storage.
     */
    public function update(Request $request, CostCenter $costcenter)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255|unique:cost_centers,name,' . $costcenter->id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $costcenter->update($validated);

        return redirect()->route('costcenters.index')->with('success', 'Cost center updated successfully!');
    }

    /**
     * Remove the specified cost center from storage (soft delete).
     */
    public function destroy(CostCenter $costcenter)
    {
        $costcenter->delete();

        return redirect()->route('costcenters.index')->with('success', 'Cost center deleted successfully!');
    }
}
