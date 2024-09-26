<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IncomingItemDetail;

class IncomingItemDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return IncomingItemDetail::all();
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
            'storage_weight' => 'nullable',
            'unit_price' => 'required',
            'quantity' => 'required',
            'total_price' => 'required',
            'labor_cost' => 'nullable',
            'expiry_date' => 'required',
        ]);

        $validated['created_by'] = Auth::id();

        $incomingItemDetail = IncomingItemDetail::create($validated);

        return response()->json($incomingItemDetail, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomingItemDetail $incomingItemDetail)
    {
        return $incomingItemDetail;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IncomingItemDetail $incomingItemDetail)
    {
        $validated = $request->validate([
            'incoming_item_id' => 'required',
            'item_id' => 'required',
            'batch_id' => 'required',
            'barcode_number' => 'required',
            'gross_weight' => 'required',
            'net_weight' => 'required',
            'storage_weight' => 'nullable',
            'unit_price' => 'required',
            'quantity' => 'required',
            'total_price' => 'required',
            'labor_cost' => 'nullable',
            'expiry_date' => 'required',
        ]); 

        $validated['updated_by'] = Auth::id();

        $incomingItemDetail->update($validated);

        return response()->json($incomingItemDetail, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomingItemDetail $incomingItemDetail)
    {
        $incomingItemDetail->delete();

        return response()->json(['message' => 'Incoming Item Detail deleted'], 204);
    }
}
