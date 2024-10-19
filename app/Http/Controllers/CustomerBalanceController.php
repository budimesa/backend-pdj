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
    public function update(Request $request, $id)
    {
        // Validasi input
        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'deposit_amount' => 'sometimes|nullable|numeric',
            'deposit_date' => 'sometimes|required|date', // Validasi deposit date
        ]);

        // Temukan deposit berdasarkan ID
        $customerBalanceDeposit = CustomerBalanceDeposit::findOrFail($id);
        
        // Simpan nilai deposit_amount lama
        $oldDepositAmount = $customerBalanceDeposit->deposit_amount;

        // Update customer balance jika customer_id ada
        if (isset($validated['customer_id'])) {
            $customerBalance = CustomerBalance::where('customer_id', $validated['customer_id'])->first();

            if ($customerBalance) {
                // Update balance amount berdasarkan perubahan deposit_amount
                $newDepositAmount = $validated['deposit_amount'] ?? $oldDepositAmount; // Ambil nilai baru atau tetap

                // Hitung selisih dan perbarui balance_amount
                $difference = $newDepositAmount - $oldDepositAmount;

                $customerBalance->balance_amount += $difference;
                $customerBalance->updated_by = Auth::id();
                $customerBalance->save();
            } else {
                // Jika customer balance tidak ada, buat baru
                $customerBalance = CustomerBalance::create([
                    'customer_id' => $validated['customer_id'],
                    'balance_amount' => $validated['deposit_amount'] ?? 0, // Jika tidak ada, gunakan 0
                    'created_by' => Auth::id(),
                ]);
            }
        }

        // Update deposit record
        if (isset($validated['deposit_amount'])) {
            $customerBalanceDeposit->deposit_amount = $validated['deposit_amount'];
        }
        if (isset($validated['deposit_date'])) {
            $customerBalanceDeposit->deposit_date = Carbon::parse($validated['deposit_date'])->toDateTimeString();
        }
        $customerBalanceDeposit->updated_by = Auth::id();
        $customerBalanceDeposit->save();

        return response()->json($customerBalanceDeposit, 200);
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
