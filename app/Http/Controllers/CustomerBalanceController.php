<?php

namespace App\Http\Controllers;

use App\Models\CustomerBalance;
use App\Models\CustomerBalanceDeposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
             'customer_id' => 'required|exists:customers,id',
             'deposit_amount' => 'nullable|numeric',
             'deposit_date' => 'required|date', // Validate deposit date
         ]);
     
         $validated['created_by'] = Auth::id();
     
         // Convert deposit_date to a compatible format
         $validated['deposit_date'] = Carbon::parse($validated['deposit_date'])->toDateTimeString();
     
         // Check if a customer balance record exists for the given customer_id
         $customerBalance = CustomerBalance::where('customer_id', $validated['customer_id'])->first();
     
         if ($customerBalance) {
             // Update the existing balance
             $customerBalance->balance_amount += $validated['deposit_amount'];
             $customerBalance->updated_by = Auth::id();
             $customerBalance->save();
         } else {
             // Create a new customer balance record
             $customerBalance = CustomerBalance::create([
                 'customer_id' => $validated['customer_id'],
                 'balance_amount' => $validated['deposit_amount'],
                 'created_by' => Auth::id(),
             ]);
         }
     
         // Insert a new deposit record
         CustomerBalanceDeposit::create([
             'customer_balance_id' => $customerBalance->id,
             'deposit_amount' => $validated['deposit_amount'],
             'deposit_date' => $validated['deposit_date'], // Use the formatted date
             'created_by' => Auth::id(),
         ]);
     
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
