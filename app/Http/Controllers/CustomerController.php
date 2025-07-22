<?php
// app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers (web & JSON).
     */
    public function index(Request $request)
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

        // Blade UI (default)
        if (!$request->wantsJson()) {
            return view('customers.index', compact('customers'));
        }
        // JSON response (API)
        return response()->json($customers);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'address'    => 'nullable|string|max:500',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:255|unique:customers,email',
            'tax_number' => 'nullable|string|max:30',
        ]);

        $customer = Customer::create($validated);

        if (!$request->wantsJson()) {
            return redirect()->route('customers.index')->with('success', 'Customer created successfully!');
        }

        return response()->json([
            'message' => 'Customer created successfully!',
            'data'    => $customer,
        ], 201);
    }

    /**
     * Display the specified customer (web & JSON).
     */
    public function show(Request $request, Customer $customer)
    {
        if (!$request->wantsJson()) {
            return view('customers.show', compact('customer'));
        }
        return response()->json($customer);
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'address'    => 'nullable|string|max:500',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'tax_number' => 'nullable|string|max:30',
        ]);

        $customer->update($validated);

        if (!$request->wantsJson()) {
            return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
        }

        return response()->json([
            'message' => 'Customer updated successfully!',
            'data'    => $customer,
        ]);
    }

    /**
     * Remove the specified customer (soft delete).
     */
    public function destroy(Request $request, Customer $customer)
    {
        $customer->delete();

        if (!$request->wantsJson()) {
            return redirect()->route('customers.index')->with('success', 'Customer deleted successfully!');
        }

        return response()->json([
            'message' => 'Customer deleted successfully!',
        ]);
    }
}
