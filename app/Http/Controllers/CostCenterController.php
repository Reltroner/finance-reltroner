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

        $costCenters = $query->orderBy('name')->paginate(20);

        return view('costcenters.index', compact('costCenters'));
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
        // is_active default true (optional)
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        CostCenter::create($validated);

        return redirect()->route('cost-centers.index')->with('success', 'Cost center created successfully!');
    }

    /**
     * Display the specified cost center.
     */
    public function show(CostCenter $costCenter)
    {
        return view('costcenters.show', compact('costCenter'));
    }

    /**
     * Show the form for editing the specified cost center.
     */
    public function edit(CostCenter $costCenter)
    {
        return view('costcenters.edit', compact('costCenter'));
    }

    /**
     * Update the specified cost center in storage.
     */
    public function update(Request $request, CostCenter $costCenter)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255|unique:cost_centers,name,' . $costCenter->id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $costCenter->update($validated);

        return redirect()->route('cost-centers.index')->with('success', 'Cost center updated successfully!');
    }

    /**
     * Remove the specified cost center from storage (soft delete).
     */
    public function destroy(CostCenter $costCenter)
    {
        $costCenter->delete();

        return redirect()->route('cost-centers.index')->with('success', 'Cost center deleted successfully!');
    }
}
