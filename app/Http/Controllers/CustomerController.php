<?php
// app/Http/Controllers/CustomerController.php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers, with optional search by name/email/phone.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }

        $customers = $query->orderBy('name')->paginate(20);

        return response()->json($customers);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'address'    => 'nullable|string|max:500',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:255|unique:customers,email',
            'tax_number' => 'nullable|string|max:30',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Customer created successfully!',
            'data'    => $customer,
        ], 201);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer);
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'address'    => 'nullable|string|max:500',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'tax_number' => 'nullable|string|max:30',
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully!',
            'data'    => $customer,
        ]);
    }

    /**
     * Remove the specified customer from storage (soft delete).
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully!',
        ]);
    }
}
