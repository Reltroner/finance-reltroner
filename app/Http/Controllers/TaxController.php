<?php
// app/Http/Controllers/TaxController.php
namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaxController extends Controller
{
    /**
     * Display a listing of taxes, with optional filtering by name.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tax::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $taxes = $query->orderBy('name')->paginate(20);

        return response()->json($taxes);
    }

    /**
     * Store a newly created tax in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:taxes,name',
            'percentage'  => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $tax = Tax::create($validated);

        return response()->json([
            'message' => 'Tax created successfully!',
            'data'    => $tax,
        ], 201);
    }

    /**
     * Display the specified tax.
     */
    public function show(Tax $tax): JsonResponse
    {
        return response()->json($tax);
    }

    /**
     * Update the specified tax in storage.
     */
    public function update(Request $request, Tax $tax): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:100|unique:taxes,name,' . $tax->id,
            'percentage'  => 'sometimes|required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $tax->update($validated);

        return response()->json([
            'message' => 'Tax updated successfully!',
            'data'    => $tax,
        ]);
    }

    /**
     * Remove the specified tax from storage (soft delete).
     */
    public function destroy(Tax $tax): JsonResponse
    {
        $tax->delete();

        return response()->json([
            'message' => 'Tax deleted successfully!',
        ]);
    }
}
