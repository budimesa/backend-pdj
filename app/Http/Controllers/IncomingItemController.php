<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IncomingItem;

class IncomingItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return IncomingItem::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'incoming_item_code' => 'required|unique:incoming_items,incoming_item_code',
            'supplier_id' => 'required',
            'warehouse_id' => 'required',
            'shipment_date' => 'required',
            'received_date' => 'required',
            'total_item_price' => 'required',
            'shipping_cost' => 'nullable',
            'labor_cost' => 'nullable',
            'other_fee' => 'nullable',
            'total_cost' => 'required',
            'notes' => 'nullable',
        ]);

        $validated['created_by'] = Auth::id();

        $incomingItem = IncomingItem::create($validated);

        return response()->json($incomingItem, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomingItem $incomingItem)
    {
        return $incomingItem;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IncomingItem $incomingItem)
    {
        $validated = $request->validate([
            'incoming_item_code' => 'required|unique:incoming_items,incoming_item_code,' . $incomingItem->id,
            'supplier_id' => 'required',
            'warehouse_id' => 'required',
            'shipment_date' => 'required',
            'received_date' => 'required',
            'total_item_price' => 'required',
            'shipping_cost' => 'nullable',
            'labor_cost' => 'nullable',
            'other_fee' => 'nullable',
            'total_cost' => 'required',
            'notes' => 'nullable',
        ]);

        $validated['updated_by'] = Auth::id();

        $incomingItem->update($validated);

        return response()->json($incomingItem, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomingItem $incomingItem)
    {
        $incomingItem->delete();

        return response()->json(['message' => 'Incoming Item deleted'], 204);
    }
}
