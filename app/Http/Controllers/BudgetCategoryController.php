<?php
// app/Http/Controllers/BudgetCategoryController.php
namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use Illuminate\Http\Request;

class BudgetCategoryController extends Controller
{
    /**
     * Display a listing of budget categories.
     */
    public function index()
    {
        return response()->json(BudgetCategory::all());
    }

    /**
     * Store a newly created budget category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:income,expense',
        ]);

        $category = BudgetCategory::create($validated);
        return response()->json($category, 201);
    }

    /**
     * Display the specified budget category.
     */
    public function show($id)
    {
        $category = BudgetCategory::findOrFail($id);
        return response()->json($category);
    }

    /**
     * Update the specified budget category.
     */
    public function update(Request $request, $id)
    {
        $category = BudgetCategory::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'sometimes|required|in:income,expense',
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    /**
     * Remove the specified budget category.
     */
    public function destroy($id)
    {
        $category = BudgetCategory::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Budget category deleted successfully.']);
    }
}
