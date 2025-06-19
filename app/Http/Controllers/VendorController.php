<?php
// app/Http/Controllers/VendorController.php
namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors, with optional search by name, email, or phone.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vendor::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }

        $vendors = $query->orderBy('name')->paginate(20);

        return response()->json($vendors);
    }

    /**
     * Store a newly created vendor.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'address'    => 'nullable|string|max:500',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:255|unique:vendors,email',
            'tax_number' => 'nullable|string|max:30',
        ]);

        $vendor = Vendor::create($validated);

        return response()->json([
            'message' => 'Vendor created successfully!',
            'data'    => $vendor,
        ], 201);
    }

    /**
     * Display the specified vendor.
     */
    public function show(Vendor $vendor): JsonResponse
    {
        return response()->json($vendor);
    }

    /**
     * Update the specified vendor.
     */
    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'address'    => 'nullable|string|max:500',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:255|unique:vendors,email,' . $vendor->id,
            'tax_number' => 'nullable|string|max:30',
        ]);

        $vendor->update($validated);

        return response()->json([
            'message' => 'Vendor updated successfully!',
            'data'    => $vendor,
        ]);
    }

    /**
     * Remove the specified vendor from storage (soft delete).
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        $vendor->delete();

        return response()->json([
            'message' => 'Vendor deleted successfully!',
        ]);
    }
}
// End of app/Http/Controllers/VendorController.php