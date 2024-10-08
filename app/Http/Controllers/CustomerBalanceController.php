<?php

namespace App\Http\Controllers;

use App\Models\CustomerBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CustomerBalance::with('customer')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id', // Pastikan customer ada
            'balance_amount' => 'nullable|numeric',
        ]);

        $validated['created_by'] = Auth::id();

        $customerBalance = CustomerBalance::create($validated);

        return response()->json($customerBalance, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerBalance $customerBalance)
    {
        return $customerBalance->load('customer');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerBalance $customerBalance)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id', // Pastikan customer ada
            'balance_amount' => 'nullable|numeric',
        ]);

        $validated['updated_by'] = Auth::id();

        $customerBalance->update($validated);

        return response()->json($customerBalance, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerBalance $customerBalance)
    {
        $customerBalance->delete();

        return response()->json(['message' => 'Customer balance deleted'], 204);
    }
}
