<?php
// app/Http/Controllers/CostCenterController.php
namespace App\Http\Controllers;

use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CostCenterController extends Controller
{
    /**
     * Display a listing of cost centers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CostCenter::query();

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $costCenters = $query->orderBy('name')->paginate(20);

        return response()->json($costCenters);
    }

    /**
     * Store a newly created cost center in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:cost_centers,name',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $costCenter = CostCenter::create($validated);

        return response()->json([
            'message' => 'Cost center created successfully!',
            'data'    => $costCenter,
        ], 201);
    }

    /**
     * Display the specified cost center.
     */
    public function show(CostCenter $costCenter): JsonResponse
    {
        return response()->json($costCenter);
    }

    /**
     * Update the specified cost center in storage.
     */
    public function update(Request $request, CostCenter $costCenter): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255|unique:cost_centers,name,' . $costCenter->id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $costCenter->update($validated);

        return response()->json([
            'message' => 'Cost center updated successfully!',
            'data'    => $costCenter,
        ]);
    }

    /**
     * Remove the specified cost center from storage (soft delete).
     */
    public function destroy(CostCenter $costCenter): JsonResponse
    {
        $costCenter->delete();

        return response()->json([
            'message' => 'Cost center deleted successfully!',
        ]);
    }
}
