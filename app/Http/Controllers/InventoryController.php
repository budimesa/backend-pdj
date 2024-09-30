<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventory;
class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inventory::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'incoming_item_id' => 'required',
            'item_id' => 'required',
            'batch_id' => 'required',
            'barcode_number' => 'required',
            'gross_weight' => 'required',
            'net_weight' => 'required',
            'actual_weight' => 'nullable',
            'unit_price' => 'required',
            'quantity' => 'required',
            'total_price' => 'required',
            'labor_cost' => 'nullable',
            'expiry_date' => 'required',
        ]);

        $validated['created_by'] = Auth::id();

        $inventory = Inventory::create($validated);

        return response()->json($inventory, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
