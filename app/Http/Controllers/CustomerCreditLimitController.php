<?php

namespace App\Http\Controllers;

use App\Models\CustomerCreditLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerCreditLimitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CustomerCreditLimit::with('customer')->get(); // Menampilkan limit kredit dengan relasi customer
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id', // Pastikan customer ada
            'limit_amount' => 'nullable|numeric',
            'limit_used' => 'nullable|numeric',
            'limit_remaining' => 'nullable|numeric',
            'is_unlimited' => 'required|boolean',
        ]);

        $validated['created_by'] = Auth::id();

        $creditLimit = CustomerCreditLimit::create($validated);

        return response()->json($creditLimit, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerCreditLimit $creditLimit)
    {
        return $creditLimit->load('customer'); // Menampilkan detail limit kredit dengan relasi customer
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerCreditLimit $creditLimit)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'limit_amount' => 'nullable|numeric',
            'limit_used' => 'nullable|numeric',
            'limit_remaining' => 'nullable|numeric',
            'is_unlimited' => 'required|boolean',
        ]);

        $validated['updated_by'] = Auth::id();

        $creditLimit->update($validated);

        return response()->json($creditLimit, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerCreditLimit $creditLimit)
    {
        $creditLimit->delete();

        return response()->json(['message' => 'Customer credit limit deleted'], 204);
    }
}
