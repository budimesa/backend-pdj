<?php

namespace App\Http\Controllers;

use App\Models\ItemTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ItemTransfer::with('inventories')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_customer_id' => 'required|exists:customers,id', // Pastikan customer ada
            'to_customer_id' => 'required|exists:customers,id', // Pastikan customer ada
            'item_id' => 'required|exists:items,id', // Pastikan item ada
            'quantity' => 'required|numeric',
        ]);

        $validated['created_by'] = Auth::id();

        $itemTransfer = ItemTransfer::create($validated);

        return response()->json($itemTransfer, 201);
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
