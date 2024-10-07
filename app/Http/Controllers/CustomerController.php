<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Customer::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_type' => 'required|in:regular,non-regular',
            'customer_name' => 'required',
            'phone_number' => 'nullable',
            'address' => 'nullable',
        ]);

        $validated['created_by'] = Auth::id();

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return $customer;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'customer_type' => 'required|in:regular,non-regular',
            'customer_name' => 'required',
            'phone_number' => 'nullable',
            'address' => 'nullable',
        ]);

        $validated['updated_by'] = Auth::id();

        $customer->update($validated);

        return response()->json($customer, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json(['message' => 'Customer deleted'], 204);
    }
}
